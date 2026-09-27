<?php

namespace App\Controllers\Admin;

use App\Models\Order;
use App\Helpers\SecurityHelper;

class OrderController {
    /**
     * Display or return JSON for Orders Manager index.
     */
    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $page = (int)($_GET['page'] ?? 1);
        $status = trim($_GET['status'] ?? 'all');
        $paymentStatus = trim($_GET['payment_status'] ?? 'all');
        $date = trim($_GET['date'] ?? '');
        $search = trim($_GET['search'] ?? '');
        $isAjax = (isset($_GET['ajax']) && $_GET['ajax'] == '1') || 
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        $orderData = Order::getPaginatedOrders($page, 10, $status, $paymentStatus, $date, $search);
        $kpiData = Order::getKpiMetrics();
        $statusCounts = Order::getStatusCounts();

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $orderData['orders'],
                'pagination' => [
                    'totalCount' => $orderData['totalCount'],
                    'totalPages' => $orderData['totalPages'],
                    'currentPage' => $orderData['currentPage'],
                    'perPage' => $orderData['perPage']
                ],
                'kpis' => $kpiData,
                'statusCounts' => $statusCounts
            ]);
            exit;
        }

        require_once __DIR__ . '/../../Views/admin/orders/index.php';
    }

    private function parseId($id): ?int {
        if (empty($id)) return null;
        if (is_numeric($id)) return (int)$id;
        return SecurityHelper::decryptId((string)$id);
    }

    /**
     * Fetch single order detail with encrypted/numeric ID support.
     */
    public function detail(string $encryptedId): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $orderId = $this->parseId($encryptedId);
        if (!$orderId) {
            $this->jsonResponse(false, 'Invalid or tampered Order ID.', 400);
            return;
        }

        $order = Order::getOrderById($orderId);
        if (!$order) {
            $this->jsonResponse(false, 'Order not found.', 404);
            return;
        }

        $this->jsonResponse(true, 'Order retrieved successfully.', 200, [
            'order' => $order
        ]);
    }

    /**
     * Update order status with encrypted ID validation.
     */
    public function updateStatus(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $isJson = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
        $input = $isJson ? (json_decode(file_get_contents('php://input'), true) ?? []) : $_POST;

        $encryptedId = trim($input['order_id'] ?? '');
        $newStatus = trim($input['new_status'] ?? '');
        $note = trim($input['note'] ?? '');
        $adminUserId = (int)($_SESSION['admin_user_id'] ?? 0);

        if (empty($encryptedId) || empty($newStatus)) {
            $this->jsonResponse(false, 'Order ID and new status are required.', 400);
            return;
        }

        $orderId = SecurityHelper::decryptId($encryptedId);
        if (!$orderId) {
            $this->jsonResponse(false, 'Invalid or tampered Order ID.', 400);
            return;
        }

        $success = Order::updateOrderStatus($orderId, $newStatus, $note, $adminUserId);
        if ($success) {
            \App\Models\AuditLog::log('order.status_changed', 'Order', $orderId, null, ['new_status' => $newStatus, 'note' => $note]);
            $this->jsonResponse(true, "Order status updated to '{$newStatus}' successfully.");
        } else {
            $this->jsonResponse(false, "Failed to update order status. Check if status value is valid.", 400);
        }
    }

    /**
     * Export Orders to CSV file.
     */
    public function export(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $status = trim($_GET['status'] ?? 'all');
        $paymentStatus = trim($_GET['payment_status'] ?? 'all');
        $date = trim($_GET['date'] ?? '');
        $search = trim($_GET['search'] ?? '');

        // Fetch up to 1000 orders for export
        $exportData = Order::getPaginatedOrders(1, 1000, $status, $paymentStatus, $date, $search);
        $orders = $exportData['orders'];

        \App\Models\AuditLog::log('order.exported', 'Order', null, null, ['exported_count' => count($orders)]);

        $filename = "orders_export_" . date('Y-m-d_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Order Number', 'Customer Name', 'Email', 'Phone', 'City', 'Subtotal', 'Discount', 'Shipping', 'Tax', 'Total Amount', 'Currency', 'Payment Status', 'Order Status', 'Placed Date']);

        foreach ($orders as $o) {
            fputcsv($out, [
                $o['order_number'],
                $o['shipping_name'],
                $o['user_email'],
                $o['shipping_phone'],
                $o['shipping_city'],
                $o['subtotal'],
                $o['discount_amount'],
                $o['shipping_charge'],
                $o['tax_amount'],
                $o['total_amount'],
                $o['currency'],
                $o['payment_status'],
                $o['status'],
                $o['placed_at']
            ]);
        }

        fclose($out);
        exit;
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
