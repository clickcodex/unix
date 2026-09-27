<?php

namespace App\Controllers\Storefront;

use App\Config\Database;
use App\Models\Order;
use App\Models\Cart;
use App\Models\PaymentTransaction;
use App\Services\PhonePeService;
use App\Helpers\SecurityHelper;

class PhonePeController {

    private function json(bool $ok, string $msg, array $extra = [], int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
        exit;
    }

    /**
     * GET /payment/phonepe/initiate/{encOrderId}
     * Initiate PhonePe PG Gateway Transaction.
     */
    public function initiate(string $encOrderId): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);

        if (!$userId) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }

        $orderId = is_numeric($encOrderId) ? (int)$encOrderId : SecurityHelper::decryptId($encOrderId);
        $order = Order::getOrderById($orderId);

        if (!$order || (int)$order['user_id'] !== $userId) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/checkout');
            exit;
        }

        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $db = Database::connect();

        $phonePeService = new PhonePeService();
        $amount = (float)$order['grand_total'];
        $orderNumber = $order['order_number'];

        $redirectUrl = $baseUrl . '/payment/phonepe/redirect/' . SecurityHelper::encryptId($orderId);
        $callbackUrl = $baseUrl . '/payment/phonepe/callback';

        $payloadData = $phonePeService->createPaymentPayload($orderNumber, $amount, $_SESSION['user_phone'] ?? '9876543210', $redirectUrl, $callbackUrl);

        // Record initial payment_transactions entry
        $stmt = $db->prepare("
            INSERT INTO payment_transactions 
            (order_id, gateway, gateway_txn_id, amount, status, payment_method, payload, created_at)
            VALUES (?, 'PhonePe', ?, ?, 'initiated', 'PhonePe PG', ?, NOW())
            ON DUPLICATE KEY UPDATE status = 'initiated', payload = VALUES(payload), updated_at = NOW()
        ");
        if ($stmt) {
            $jsonPayload = json_encode($payloadData['raw']);
            $stmt->bind_param("isds", $orderId, $orderNumber, $amount, $jsonPayload);
            $stmt->execute();
            $stmt->close();
        }

        // Direct to PhonePe Interactive Gateway Simulator
        header('Location: ' . $baseUrl . '/payment/phonepe/simulator/' . SecurityHelper::encryptId($orderId));
        exit;
    }

    /**
     * GET /payment/phonepe/simulator/{encOrderId}
     * Render PhonePe Interactive Payment Gateway Portal.
     */
    public function simulator(string $encOrderId): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);

        $orderId = is_numeric($encOrderId) ? (int)$encOrderId : SecurityHelper::decryptId($encOrderId);
        $order = Order::getOrderById($orderId);

        if (!$order || (int)$order['user_id'] !== $userId) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/checkout');
            exit;
        }

        $baseUrl   = defined('BASE_URL') ? BASE_URL : '';
        $siteTitle = 'PhonePe Payment Gateway — ClickCodex';

        require_once __DIR__ . '/../../Views/front/phonepe_simulator.php';
    }

    /**
     * POST /payment/phonepe/callback or POST /payment/phonepe/redirect/{encOrderId}
     * Process PhonePe Gateway Payment Return / Webhook Result.
     */
    public function callback(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);

        $data = $_POST;
        if (empty($data)) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true) ?? [];
        }

        $status      = trim($data['code'] ?? $data['status'] ?? 'PAYMENT_ERROR');
        $orderNumber = trim($data['merchantTransactionId'] ?? $data['order_number'] ?? '');
        $txnId       = trim($data['transactionId'] ?? ('T' . time() . rand(100, 999)));
        $payMethod   = trim($data['payment_instrument'] ?? 'PhonePe UPI');

        $db = Database::connect();
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';

        if (empty($orderNumber)) {
            header('Location: ' . $baseUrl . '/checkout?error=invalid_transaction');
            exit;
        }

        // Fetch order by order_number
        $stmt = $db->prepare("SELECT id, user_id, grand_total FROM orders WHERE order_number = ? LIMIT 1");
        $stmt->bind_param("s", $orderNumber);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$order) {
            header('Location: ' . $baseUrl . '/checkout?error=order_not_found');
            exit;
        }

        $orderId = (int)$order['id'];
        $encOrderId = SecurityHelper::encryptId($orderId);

        if ($status === 'PAYMENT_SUCCESS' || $status === 'SUCCESS' || $status === '200') {
            // Update order status to paid & processing
            $db->query("UPDATE orders SET payment_status = 'paid', status = 'processing', updated_at = NOW() WHERE id = {$orderId}");

            // Record or update payment_transactions
            $stmt = $db->prepare("
                INSERT INTO payment_transactions (order_id, gateway, gateway_txn_id, amount, status, payment_method, created_at)
                VALUES (?, 'PhonePe', ?, ?, 'success', ?, NOW())
                ON DUPLICATE KEY UPDATE status = 'success', gateway_txn_id = VALUES(gateway_txn_id), updated_at = NOW()
            ");
            if ($stmt) {
                $amount = (float)$order['grand_total'];
                $stmt->bind_param("isds", $orderId, $txnId, $amount, $payMethod);
                $stmt->execute();
                $stmt->close();
            }

            // Clear user cart items
            $cart = Cart::getOrCreate((int)$order['user_id']);
            if (!empty($cart['id'])) {
                $db->query("DELETE FROM cart_items WHERE cart_id = " . (int)$cart['id']);
            }

            header('Location: ' . $baseUrl . '/checkout/success/' . $encOrderId);
            exit;
        } else {
            // Record pending/failed order & transaction
            $db->query("UPDATE orders SET payment_status = 'pending', status = 'pending', updated_at = NOW() WHERE id = {$orderId}");

            $stmt = $db->prepare("
                INSERT INTO payment_transactions (order_id, gateway, gateway_txn_id, amount, status, payment_method, created_at)
                VALUES (?, 'PhonePe', ?, ?, 'pending', ?, NOW())
                ON DUPLICATE KEY UPDATE status = 'pending', updated_at = NOW()
            ");
            if ($stmt) {
                $amount = (float)$order['grand_total'];
                $stmt->bind_param("isds", $orderId, $txnId, $amount, $payMethod);
                $stmt->execute();
                $stmt->close();
            }

            // Clear user cart items so items don't duplicate
            $cart = Cart::getOrCreate((int)$order['user_id']);
            if (!empty($cart['id'])) {
                $db->query("DELETE FROM cart_items WHERE cart_id = " . (int)$cart['id']);
            }

            header('Location: ' . $baseUrl . '/checkout/success/' . $encOrderId . '?payment=pending');
            exit;
        }
    }
}
