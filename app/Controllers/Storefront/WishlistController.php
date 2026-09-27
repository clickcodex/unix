<?php

namespace App\Controllers\Storefront;

use App\Models\Wishlist;
use App\Models\Cart;
use App\Helpers\SecurityHelper;

class WishlistController {

    private function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    private function userId(): int {
        return (int)($_SESSION['user_id'] ?? 0);
    }

    private function json(bool $success, string $message, array $extra = [], int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
        exit;
    }

    /**
     * GET /wishlist — Show customer's wishlist page (protected by middleware).
     */
    public function index(): void {
        $this->startSession();
        $userId = $this->userId();

        // Get or create the default wishlist
        $wishlist  = Wishlist::getOrCreate($userId);
        $wishlists = Wishlist::getAllForUser($userId);
        $items     = $wishlist ? Wishlist::getItems((int)$wishlist['id'], $userId) : [];

        $baseUrl   = defined('BASE_URL') ? BASE_URL : '';
        $siteTitle = 'My Wishlist — ClickCodex';
        $activeTab = 'wishlist';

        // Load user data for sidebar
        $user = ['id' => $userId, 'name' => $_SESSION['user_name'] ?? '', 'email' => $_SESSION['user_email'] ?? ''];

        require_once __DIR__ . '/../../Views/front/wishlist.php';
    }

    /**
     * POST /wishlist/add — AJAX: Add product to wishlist.
     */
    public function add(): void {
        $this->startSession();
        $userId = $this->userId();

        if (!$userId) {
            $this->json(false, 'Please login to add items to your wishlist.', [], 401);
        }

        $data   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $rawPid = $data['product_id'] ?? null;
        $productId = is_numeric($rawPid) ? (int)$rawPid : SecurityHelper::decryptId((string)$rawPid);

        if (!$productId) {
            $this->json(false, 'Invalid product.', [], 400);
        }

        $result = Wishlist::toggleItem($userId, $productId);
        $count  = Wishlist::getCount($userId);

        $this->json(
            $result['success'],
            $result['message'],
            [
                'wishlist_count' => $count,
                'action'         => $result['action'] ?? 'added',
                'already'        => $result['already'] ?? false
            ]
        );
    }

    /**
     * POST /wishlist/remove — AJAX: Remove item from wishlist by wishlist_item_id.
     */
    public function remove(): void {
        $this->startSession();
        $userId = $this->userId();

        if (!$userId) {
            $this->json(false, 'Unauthorized.', [], 401);
        }

        $data      = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $rawItemId = $data['item_id'] ?? null;
        $wishlistItemId = is_numeric($rawItemId) ? (int)$rawItemId : SecurityHelper::decryptId((string)$rawItemId);

        if (!$wishlistItemId) {
            $this->json(false, 'Invalid item.', [], 400);
        }

        $ok    = Wishlist::removeItem($userId, $wishlistItemId);
        $count = Wishlist::getCount($userId);

        $this->json($ok, $ok ? 'Removed from wishlist.' : 'Failed to remove item.', ['wishlist_count' => $count]);
    }

    /**
     * POST /wishlist/move-to-cart — AJAX: Move wishlist item to cart.
     */
    public function moveToCart(): void {
        $this->startSession();
        $userId = $this->userId();

        if (!$userId) {
            $this->json(false, 'Please login first.', [], 401);
        }

        $data      = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $rawItemId = $data['item_id'] ?? null;
        $wishlistItemId = is_numeric($rawItemId) ? (int)$rawItemId : SecurityHelper::decryptId((string)$rawItemId);

        if (!$wishlistItemId) {
            $this->json(false, 'Invalid item.', [], 400);
        }

        $result = Wishlist::moveToCart($userId, $wishlistItemId);
        $this->json($result['success'], $result['message'], ['cart_count' => $result['cart_count'] ?? 0]);
    }

    /**
     * GET /wishlist/count — AJAX: Get wishlist item count for badge.
     */
    public function count(): void {
        $this->startSession();
        $userId = $this->userId();
        $count  = $userId ? Wishlist::getCount($userId) : 0;
        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
        exit;
    }
}
