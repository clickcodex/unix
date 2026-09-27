<?php

namespace App\Controllers\Admin;

use App\Config\Database;
use App\Models\Order;
use App\Models\AuditLog;
use App\Helpers\SecurityHelper;

class ReturnController {

    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $db = Database::connect();

        $res = $db->query("
            SELECT o.*, u.name as customer_name, u.email as customer_email
            FROM orders o
            LEFT JOIN users u ON u.id = o.user_id
            WHERE o.status IN ('return_requested', 'returned', 'refunded')
            ORDER BY o.updated_at DESC
        ");

        $returns = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
                $returns[] = $row;
            }
        }

        require __DIR__ . '/../../Views/admin/orders/returns.php';
    }

    public function updateStatus(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();

        $rawId = $input['order_id'] ?? '';
        $orderId = is_numeric($rawId) ? (int)$rawId : SecurityHelper::decryptId((string)$rawId);
        $newStatus = trim($input['status'] ?? '');
        $note = trim($input['note'] ?? '');

        if (!$orderId || !in_array($newStatus, ['return_requested', 'returned', 'refunded'])) {
            $this->jsonResponse(false, 'Invalid order or return status.', 400);
            return;
        }

        $db = Database::connect();
        $stmt = $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        if (!$stmt) {
            $this->jsonResponse(false, 'Database error.', 500);
            return;
        }
        $stmt->bind_param("si", $newStatus, $orderId);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            if ($newStatus === 'refunded') {
                $db->query("UPDATE orders SET payment_status = 'refunded' WHERE id = {$orderId}");
            }

            // Log history
            $changedBy = (int)($_SESSION['user_id'] ?? 1);
            $stmtHist = $db->prepare("INSERT INTO order_status_history (order_id, status, note, changed_by, created_at) VALUES (?, ?, ?, ?, NOW())");
            if ($stmtHist) {
                $stmtHist->bind_param("issi", $orderId, $newStatus, $note, $changedBy);
                $stmtHist->execute();
                $stmtHist->close();
            }

            AuditLog::log('order.return_status_updated', 'Order', $orderId, null, ['new_status' => $newStatus, 'note' => $note]);
        }

        $this->jsonResponse($ok, $ok ? 'Return/Refund status updated.' : 'Failed to update return status.');
    }

    private function getRequestInput(): array {
        $isJson = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
        return $isJson ? (json_decode(file_get_contents('php://input'), true) ?? []) : $_POST;
    }

    private function jsonResponse(bool $success, string $message, int $statusCode = 200, array $extra = []): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $extra));
        exit;
    }
}
