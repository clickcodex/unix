<?php

namespace App\Controllers\Admin;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\AuditLog;
use App\Helpers\SecurityHelper;

class InventoryController {

    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $movements = InventoryMovement::getMovements(100);
        $lowStockProducts = InventoryMovement::getLowStockProducts(10);
        $products = Product::getAllProducts(['limit' => 500])['products'] ?? [];

        require __DIR__ . '/../../Views/admin/inventory/index.php';
    }

    public function record(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();

        $rawPid = $input['product_id'] ?? '';
        $productId = is_numeric($rawPid) ? (int)$rawPid : SecurityHelper::decryptId((string)$rawPid);

        if (!$productId) {
            $this->jsonResponse(false, 'Invalid product selected.', 400);
            return;
        }

        $input['product_id'] = $productId;
        $ok = InventoryMovement::recordMovement($input);

        if ($ok) {
            AuditLog::log('inventory.adjustment', 'Inventory', $productId, null, $input);
        }

        $this->jsonResponse($ok, $ok ? 'Inventory movement recorded successfully.' : 'Failed to record inventory adjustment.');
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
