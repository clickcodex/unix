<?php

namespace App\Controllers\Admin;

use App\Models\PaymentTransaction;
use App\Helpers\SecurityHelper;

class PaymentController {

    private function jsonResponse(bool $success, string $message, int $code = 200, array $extra = []): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    }

    private function getRequestInput(): array {
        $json = json_decode(file_get_contents('php://input'), true);
        return is_array($json) ? $json : $_POST;
    }

    /**
     * Payments management index view.
     */
    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'status'  => $_GET['status'] ?? '',
            'gateway' => $_GET['gateway'] ?? '',
            'search'  => $_GET['search'] ?? '',
        ];

        $result = PaymentTransaction::getPaginatedPayments($page, 15, $filters);
        $kpis = PaymentTransaction::getKpiMetrics();

        if (!empty($_GET['ajax'])) {
            $this->jsonResponse(true, 'Payments retrieved.', 200, [
                'data'       => $result['payments'],
                'pagination' => $result['pagination'],
                'kpis'       => $kpis,
            ]);
            return;
        }

        $payments = $result['payments'];
        $pagination = $result['pagination'];
        require __DIR__ . '/../../Views/admin/payments/index.php';
    }

    /**
     * Detail modal payload (AJAX).
     */
    public function detail(string $encryptedId): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $id = SecurityHelper::decryptId($encryptedId);
        if (!$id) {
            $this->jsonResponse(false, 'Invalid transaction ID.', 400);
            return;
        }

        $payment = PaymentTransaction::getPaymentById($id);
        if (!$payment) {
            $this->jsonResponse(false, 'Payment transaction not found.', 404);
            return;
        }

        $this->jsonResponse(true, 'Payment transaction details loaded.', 200, ['payment' => $payment]);
    }

    /**
     * Update transaction status (e.g. process refund or override status).
     */
    public function updateStatus(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $encryptedId = trim($input['payment_id'] ?? '');
        $status = trim($input['status'] ?? '');

        $id = SecurityHelper::decryptId($encryptedId);
        if (!$id) {
            $this->jsonResponse(false, 'Invalid transaction ID.', 400);
            return;
        }

        $oldTx = PaymentTransaction::getPaymentById($id);
        $success = PaymentTransaction::updateStatus($id, $status);
        if ($success) {
            \App\Models\AuditLog::log('payment.status_updated', 'PaymentTransaction', $id, $oldTx, ['status' => $status]);
            $this->jsonResponse(true, "Transaction status updated to {$status}.");
        } else {
            $this->jsonResponse(false, 'Failed to update transaction status.', 400);
        }
    }
}
