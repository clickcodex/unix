<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class Cart {

    private static function getSessionToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['cart_session_token'])) {
            $_SESSION['cart_session_token'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['cart_session_token'];
    }

    private static function getUserId(): ?int {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_id'] ?? $_SESSION['customer_user_id'] ?? null;
    }

    /**
     * Get or create active cart array for user.
     */
    public static function getOrCreate(?int $userId = null): array {
        $cartId = self::getActiveCartId();
        return ['id' => $cartId];
    }

    /**
     * Clear all items from active cart.
     */
    public static function clearCart(): void {
        $db = Database::connect();
        $cartId = self::getActiveCartId();
        if ($cartId) {
            $db->query("DELETE FROM cart_items WHERE cart_id = " . (int)$cartId);
        }
    }

    /**
     * Get or create active cart ID.
     */
    public static function getActiveCartId(): int {
        $db = Database::connect();
        $userId = self::getUserId();
        $sessionToken = self::getSessionToken();

        if ($userId) {
            $stmt = $db->prepare("SELECT id FROM carts WHERE user_id = ? LIMIT 1");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($res) {
                return (int)$res['id'];
            }

            // Create cart for user
            $stmtIns = $db->prepare("INSERT INTO carts (user_id, session_token, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
            $stmtIns->bind_param("is", $userId, $sessionToken);
            $stmtIns->execute();
            $cartId = $stmtIns->insert_id;
            $stmtIns->close();
            return (int)$cartId;
        } else {
            $stmt = $db->prepare("SELECT id FROM carts WHERE session_token = ? AND user_id IS NULL LIMIT 1");
            $stmt->bind_param("s", $sessionToken);
            $stmt->execute();
            $res = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($res) {
                return (int)$res['id'];
            }

            // Create guest cart
            $stmtIns = $db->prepare("INSERT INTO carts (session_token, created_at, updated_at) VALUES (?, NOW(), NOW())");
            $stmtIns->bind_param("s", $sessionToken);
            $stmtIns->execute();
            $cartId = $stmtIns->insert_id;
            $stmtIns->close();
            return (int)$cartId;
        }
    }

    /**
     * Add product item to cart.
     */
    public static function addItem(int $productId, int $qty = 1, ?int $variantId = null): array {
        $db = Database::connect();
        $cartId = self::getActiveCartId();

        // Get Product Price
        $product = Product::getProductById($productId);
        if (!$product || (int)($product['is_active'] ?? 1) !== 1) {
            return ['success' => false, 'message' => 'Product is unavailable.'];
        }

        $unitPrice = !empty($product['sale_price']) ? (float)$product['sale_price'] : (float)$product['base_price'];

        // Check if item already in cart_items
        $stmtCheck = $db->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ? LIMIT 1");
        $stmtCheck->bind_param("ii", $cartId, $productId);
        $stmtCheck->execute();
        $existing = $stmtCheck->get_result()->fetch_assoc();
        $stmtCheck->close();

        if ($existing) {
            $newQty = (int)$existing['quantity'] + $qty;
            $stmtUpd = $db->prepare("UPDATE cart_items SET quantity = ?, unit_price = ?, updated_at = NOW() WHERE id = ?");
            $stmtUpd->bind_param("idi", $newQty, $unitPrice, $existing['id']);
            $stmtUpd->execute();
            $stmtUpd->close();
        } else {
            $stmtIns = $db->prepare("INSERT INTO cart_items (cart_id, product_id, variant_id, quantity, unit_price, added_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $stmtIns->bind_param("iiiid", $cartId, $productId, $variantId, $qty, $unitPrice);
            $stmtIns->execute();
            $stmtIns->close();
        }

        // Update cart timestamp
        $db->query("UPDATE carts SET updated_at = NOW() WHERE id = {$cartId}");

        $summary = self::getCartSummary();
        return [
            'success' => true,
            'message' => '"' . $product['name'] . '" added to shopping cart!',
            'cart_count' => $summary['item_count'],
            'total_amount' => $summary['total_amount']
        ];
    }

    /**
     * Get cart item count and total for logged in user.
     */
    public static function getCartSummary(): array {
        $userId = self::getUserId();
        if (!$userId) {
            return ['item_count' => 0, 'total_amount' => 0.00];
        }
        $db = Database::connect();
        $cartId = self::getActiveCartId();

        $stmt = $db->prepare("SELECT SUM(quantity) as total_count, SUM(quantity * unit_price) as total_amount FROM cart_items WHERE cart_id = ?");
        if (!$stmt) {
            return ['item_count' => 0, 'total_amount' => 0.00];
        }
        $stmt->bind_param("i", $cartId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return [
            'item_count' => (int)($res['total_count'] ?? 0),
            'total_amount' => (float)($res['total_amount'] ?? 0.00)
        ];
    }

    /**
     * Merge guest session cart into logged in user cart.
     */
    public static function mergeSessionCartToUser(int $userId): void {
        $sessionToken = self::getSessionToken();
        $db = Database::connect();

        // Get guest cart ID
        $stmtG = $db->prepare("SELECT id FROM carts WHERE session_token = ? AND (user_id IS NULL OR user_id != ?) LIMIT 1");
        $stmtG->bind_param("si", $sessionToken, $userId);
        $stmtG->execute();
        $guestCart = $stmtG->get_result()->fetch_assoc();
        $stmtG->close();

        if (!$guestCart) return;

        $guestCartId = (int)$guestCart['id'];
        $userCartId = self::getActiveCartId();

        // Move items
        $resItems = $db->query("SELECT * FROM cart_items WHERE cart_id = {$guestCartId}");
        if ($resItems) {
            while ($item = $resItems->fetch_assoc()) {
                self::addItem((int)$item['product_id'], (int)$item['quantity'], $item['variant_id'] ? (int)$item['variant_id'] : null);
            }
        }

        // Delete guest cart
        $db->query("DELETE FROM cart_items WHERE cart_id = {$guestCartId}");
        $db->query("DELETE FROM carts WHERE id = {$guestCartId}");
    }

    /**
     * Get all cart items for a user with full product + image details.
     */
    public static function getItemsForUser(int $userId): array {
        $db = Database::connect();

        $stmt = $db->prepare("
            SELECT
                ci.id            AS cart_item_id,
                ci.quantity,
                ci.unit_price,
                ci.added_at,
                p.id             AS product_id,
                p.name,
                p.slug,
                p.sku,
                p.base_price,
                p.sale_price,
                p.is_in_stock,
                p.average_rating,
                p.review_count,
                p.tax_rate,
                (SELECT pi.image_url FROM product_images pi
                 WHERE pi.product_id = p.id AND pi.is_primary = 1
                 ORDER BY pi.sort_order ASC LIMIT 1) AS image_url
            FROM cart_items ci
            INNER JOIN carts c ON c.id = ci.cart_id AND c.user_id = ?
            INNER JOIN products p ON p.id = ci.product_id AND p.deleted_at IS NULL
            ORDER BY ci.added_at DESC
        ");
        if (!$stmt) return [];
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($items as &$item) {
            $item['encrypted_cart_item_id'] = SecurityHelper::encryptId($item['cart_item_id']);
            $item['encrypted_product_id']   = SecurityHelper::encryptId($item['product_id']);
            $base = (float)$item['base_price'];
            $sale = (float)$item['sale_price'];
            $item['discount_pct'] = ($base > 0 && $sale < $base)
                ? (int)round(($base - $sale) / $base * 100)
                : 0;
            $item['line_total'] = (float)$item['unit_price'] * (int)$item['quantity'];
        }
        return $items;
    }

    /**
     * Update quantity of a cart item (owned by user).
     */
    public static function updateQty(int $userId, int $cartItemId, int $qty): bool {
        $db = Database::connect();
        if ($qty < 1) $qty = 1;
        if ($qty > 10) $qty = 10;

        $stmt = $db->prepare("
            UPDATE cart_items ci
            INNER JOIN carts c ON c.id = ci.cart_id AND c.user_id = ?
            SET ci.quantity = ?, ci.updated_at = NOW()
            WHERE ci.id = ?
        ");
        if (!$stmt) return false;
        $stmt->bind_param("iii", $userId, $qty, $cartItemId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Remove a single cart item (owned by user).
     */
    public static function removeItem(int $userId, int $cartItemId): bool {
        $db = Database::connect();
        $stmt = $db->prepare("
            DELETE ci FROM cart_items ci
            INNER JOIN carts c ON c.id = ci.cart_id AND c.user_id = ?
            WHERE ci.id = ?
        ");
        if (!$stmt) return false;
        $stmt->bind_param("ii", $userId, $cartItemId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
