<?php

namespace App\Controllers\Storefront;

use App\Models\Cart;
use App\Helpers\SecurityHelper;

class CartPageController {

    private function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    private function userId(): int {
        return (int)($_SESSION['user_id'] ?? 0);
    }

    private function json(bool $ok, string $msg, array $extra = [], int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
        exit;
    }

    /**
     * GET /cart — Full cart page (protected by middleware).
     */
    public function index(): void {
        $this->startSession();
        $userId = $this->userId();

        $items   = Cart::getItemsForUser($userId);
        $summary = Cart::getCartSummary();
        $user    = [
            'id'           => $userId,
            'encrypted_id' => SecurityHelper::encryptId($userId),
            'name'         => $_SESSION['user_name']  ?? '',
            'email'        => $_SESSION['user_email'] ?? '',
        ];

        $baseUrl   = defined('BASE_URL') ? BASE_URL : '';
        $siteTitle = 'Shopping Cart — ClickCodex';
        $activeTab = 'cart';

        require_once __DIR__ . '/../../Views/front/cart.php';
    }

    /**
     * POST /cart/update-qty — AJAX: update quantity.
     */
    public function updateQty(): void {
        $this->startSession();
        $userId = $this->userId();
        if (!$userId) $this->json(false, 'Unauthorized.', [], 401);

        $data      = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $rawItemId = $data['item_id'] ?? null;
        $itemId    = is_numeric($rawItemId) ? (int)$rawItemId : SecurityHelper::decryptId((string)$rawItemId);
        $qty       = (int)($data['qty'] ?? 1);

        if (!$itemId) $this->json(false, 'Invalid item.', [], 400);

        $ok      = Cart::updateQty($userId, $itemId, $qty);
        $summary = Cart::getCartSummary();

        $this->json($ok, $ok ? 'Quantity updated.' : 'Failed to update.', [
            'cart_count'   => $summary['item_count'],
            'total_amount' => $summary['total_amount'],
        ]);
    }

    /**
     * POST /cart/remove-item — AJAX: remove a cart item.
     */
    public function removeItem(): void {
        $this->startSession();
        $userId = $this->userId();
        if (!$userId) $this->json(false, 'Unauthorized.', [], 401);

        $data      = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $rawItemId = $data['item_id'] ?? null;
        $itemId    = is_numeric($rawItemId) ? (int)$rawItemId : SecurityHelper::decryptId((string)$rawItemId);

        if (!$itemId) $this->json(false, 'Invalid item.', [], 400);

        $ok      = Cart::removeItem($userId, $itemId);
        $summary = Cart::getCartSummary();

        $this->json($ok, $ok ? 'Item removed.' : 'Failed to remove.', [
            'cart_count'   => $summary['item_count'],
            'total_amount' => $summary['total_amount'],
        ]);
    }
}
