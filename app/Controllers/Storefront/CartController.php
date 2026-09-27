<?php

namespace App\Controllers\Storefront;

use App\Models\Cart;
use App\Helpers\SecurityHelper;

class CartController {

    private function jsonResponse(bool $success, string $message, array $extra = [], int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $extra));
        exit;
    }

    /**
     * AJAX Add item to cart.
     */
    public function add(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);

        if (!$userId) {
            $this->jsonResponse(false, 'Please sign in to add items to your cart.', ['require_login' => true], 401);
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? $_POST;

        $productIdRaw = $data['product_id'] ?? null;
        $qty = max(1, (int)($data['quantity'] ?? 1));

        if (empty($productIdRaw)) {
            $this->jsonResponse(false, 'Missing product ID.', [], 400);
        }

        $productId = is_numeric($productIdRaw) ? (int)$productIdRaw : SecurityHelper::decryptId((string)$productIdRaw);
        if (!$productId) {
            $this->jsonResponse(false, 'Invalid Product ID.', [], 400);
        }

        $result = Cart::addItem($productId, $qty);
        if (!$result['success']) {
            $this->jsonResponse(false, $result['message'], [], 400);
        }

        $this->jsonResponse(true, $result['message'], [
            'cart_count' => $result['cart_count'],
            'total_amount' => $result['total_amount']
        ]);
    }

    /**
     * Get live cart item count.
     */
    public function count(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);

        if (!$userId) {
            $this->jsonResponse(true, 'Guest user.', [
                'cart_count' => 0,
                'total_amount' => 0
            ]);
        }

        $summary = Cart::getCartSummary();
        $this->jsonResponse(true, 'Cart summary retrieved.', [
            'cart_count' => $summary['item_count'],
            'total_amount' => $summary['total_amount']
        ]);
    }
}
