<?php

namespace App\Services;

use App\Config\Database;

class PhonePeService {

    private string $merchantId;
    private string $saltKey;
    private int $saltIndex;
    private string $env;
    private string $baseUrl;

    public function __construct() {
        $db = Database::connect();
        
        $settings = [];
        $res = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_group = 'payment' OR setting_key LIKE 'phonepe_%'");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        }

        $this->merchantId = $_ENV['PHONEPE_MERCHANT_ID'] ?? getenv('PHONEPE_MERCHANT_ID') ?: ($settings['phonepe_merchant_id'] ?? 'PGTESTPAYUAT');
        $this->saltKey    = $_ENV['PHONEPE_SALT_KEY'] ?? getenv('PHONEPE_SALT_KEY') ?: ($settings['phonepe_salt_key'] ?? '099eb0cd-02fa-4e2d-73a6-5746e40465af');
        $this->saltIndex  = (int)($_ENV['PHONEPE_SALT_INDEX'] ?? getenv('PHONEPE_SALT_INDEX') ?: ($settings['phonepe_salt_index'] ?? 1));
        $this->env        = $_ENV['PHONEPE_ENV'] ?? getenv('PHONEPE_ENV') ?: ($settings['phonepe_env'] ?? 'sandbox');

        $this->baseUrl = ($this->env === 'production')
            ? 'https://api.phonepe.com/apis/hermes'
            : 'https://api-preprod.phonepe.com/apis/pg-sandbox';
    }

    public function getMerchantId(): string {
        return $this->merchantId;
    }

    public function getEnv(): string {
        return $this->env;
    }

    /**
     * Build X-VERIFY Header for PhonePe API Requests.
     */
    public function generateChecksum(string $base64Payload, string $apiPath): string {
        $dataToHash = $base64Payload . $apiPath . $this->saltKey;
        $hash = hash('sha256', $dataToHash);
        return $hash . '###' . $this->saltIndex;
    }

    /**
     * Create Initiate Payment Payload for PhonePe PG v1 /pay
     */
    public function createPaymentPayload(string $orderNumber, float $amount, string $mobileNumber, string $redirectUrl, string $callbackUrl): array {
        $amountInPaise = (int)round($amount * 100);

        $payload = [
            'merchantId'            => $this->merchantId,
            'merchantTransactionId' => $orderNumber,
            'merchantUserId'        => 'MUID_' . md5($mobileNumber ?: $orderNumber),
            'amount'                => $amountInPaise,
            'redirectUrl'           => $redirectUrl,
            'redirectMode'          => 'POST',
            'callbackUrl'           => $callbackUrl,
            'paymentInstrument'     => [
                'type' => 'PAY_PAGE'
            ]
        ];

        $jsonPayload   = json_encode($payload);
        $base64Payload = base64_encode($jsonPayload);
        $checksum      = $this->generateChecksum($base64Payload, '/pg/v1/pay');

        return [
            'base64'   => $base64Payload,
            'checksum' => $checksum,
            'raw'      => $payload
        ];
    }

    /**
     * Check PhonePe Status API endpoint `/pg/v1/status/{merchantId}/{merchantTransactionId}`
     */
    public function checkStatus(string $orderNumber): array {
        $apiPath = "/pg/v1/status/{$this->merchantId}/{$orderNumber}";
        $checksum = hash('sha256', $apiPath . $this->saltKey) . '###' . $this->saltIndex;

        $url = $this->baseUrl . $apiPath;

        $headers = [
            'Content-Type: application/json',
            'X-VERIFY: ' . $checksum,
            'X-MERCHANT-ID: ' . $this->merchantId
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err || !$response) {
            return ['success' => false, 'message' => 'Curl Error: ' . $err];
        }

        return json_decode($response, true) ?? ['success' => false, 'message' => 'Invalid JSON from PhonePe API'];
    }
}
