<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class Wishlist {

    /**
     * Get or create the default wishlist for a user.
     */
    public static function getOrCreate(int $userId): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM wishlists WHERE user_id = ? ORDER BY id ASC LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $wl = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($wl) return $wl;

        // Create default wishlist
        $stmt2 = $db->prepare("INSERT INTO wishlists (user_id, name, is_public, created_at) VALUES (?, 'My Wishlist', 0, NOW())");
        if (!$stmt2) return null;
        $stmt2->bind_param("i", $userId);
        $stmt2->execute();
        $id = $stmt2->insert_id;
        $stmt2->close();

        return ['id' => $id, 'user_id' => $userId, 'name' => 'My Wishlist', 'is_public' => 0];
    }

    /**
     * Get all wishlists for a user with item counts.
     */
    public static function getAllForUser(int $userId): array {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT w.*, COUNT(wi.id) as item_count
            FROM wishlists w
            LEFT JOIN wishlist_items wi ON wi.wishlist_id = w.id
            WHERE w.user_id = ?
            GROUP BY w.id
            ORDER BY w.id ASC
        ");
        if (!$stmt) return [];
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    /**
     * Get items in a wishlist with full product details + primary image.
     */
    public static function getItems(int $wishlistId, int $userId): array {
        $db = Database::connect();
        // Verify wishlist belongs to user
        $check = $db->prepare("SELECT id FROM wishlists WHERE id = ? AND user_id = ? LIMIT 1");
        if (!$check) return [];
        $check->bind_param("ii", $wishlistId, $userId);
        $check->execute();
        if (!$check->get_result()->fetch_assoc()) { $check->close(); return []; }
        $check->close();

        $stmt = $db->prepare("
            SELECT
                wi.id            AS wishlist_item_id,
                wi.added_at,
                p.id             AS product_id,
                p.name,
                p.slug,
                p.sku,
                p.sale_price,
                p.base_price,
                p.is_in_stock,
                p.average_rating,
                p.review_count,
                p.wishlist_count,
                (SELECT pi.image_url FROM product_images pi
                 WHERE pi.product_id = p.id AND pi.is_primary = 1
                 ORDER BY pi.sort_order ASC LIMIT 1) AS image_url
            FROM wishlist_items wi
            INNER JOIN products p ON p.id = wi.product_id AND p.deleted_at IS NULL
            WHERE wi.wishlist_id = ?
            ORDER BY wi.added_at DESC
        ");
        if (!$stmt) return [];
        $stmt->bind_param("i", $wishlistId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Add encrypted IDs and discount calculation
        foreach ($items as &$item) {
            $item['encrypted_wishlist_item_id'] = SecurityHelper::encryptId($item['wishlist_item_id']);
            $item['encrypted_id']               = $item['encrypted_wishlist_item_id'];
            $item['encrypted_product_id']       = SecurityHelper::encryptId($item['product_id']);
            $base = (float)$item['base_price'];
            $sale = (float)$item['sale_price'];
            $item['discount_pct'] = ($base > 0 && $sale < $base)
                ? (int)round(($base - $sale) / $base * 100)
                : 0;
        }
        return $items;
    }

    /**
     * Toggle a product in a user's default wishlist (Add if not present, Remove if already present).
     */
    public static function toggleItem(int $userId, int $productId): array {
        $db = Database::connect();

        $wl = self::getOrCreate($userId);
        if (!$wl) return ['success' => false, 'message' => 'Could not access wishlist.'];

        $wishlistId = (int)$wl['id'];

        // Check if already in wishlist
        $chk = $db->prepare("SELECT id FROM wishlist_items WHERE wishlist_id = ? AND product_id = ? LIMIT 1");
        if (!$chk) return ['success' => false, 'message' => 'Database error.'];
        $chk->bind_param("ii", $wishlistId, $productId);
        $chk->execute();
        $existing = $chk->get_result()->fetch_assoc();
        $chk->close();

        if ($existing) {
            // REMOVE from wishlist
            $delId = (int)$existing['id'];
            $stmtD = $db->prepare("DELETE FROM wishlist_items WHERE id = ?");
            if ($stmtD) {
                $stmtD->bind_param("i", $delId);
                $stmtD->execute();
                $stmtD->close();
                $db->query("UPDATE products SET wishlist_count = GREATEST(0, wishlist_count - 1) WHERE id = {$productId}");
            }
            return [
                'success' => true,
                'action'  => 'removed',
                'message' => 'Removed from Wishlist.',
                'already' => false
            ];
        }

        // ADD to wishlist
        $stmt = $db->prepare("INSERT INTO wishlist_items (wishlist_id, product_id, added_at) VALUES (?, ?, NOW())");
        if (!$stmt) return ['success' => false, 'message' => 'Failed to add item.'];
        $stmt->bind_param("ii", $wishlistId, $productId);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $db->query("UPDATE products SET wishlist_count = wishlist_count + 1 WHERE id = {$productId}");
        }

        return $ok
            ? ['success' => true, 'action' => 'added', 'message' => 'Saved to Wishlist!', 'already' => false]
            : ['success' => false, 'message' => 'Failed to save item.'];
    }

    /**
     * Add a product to a user's default wishlist (Alias to toggleItem for compatibility).
     */
    public static function addItem(int $userId, int $productId): array {
        return self::toggleItem($userId, $productId);
    }

    /**
     * Remove a product from a user's wishlist by wishlist_item_id or product_id.
     */
    public static function removeItem(int $userId, int $wishlistItemId): bool {
        $db = Database::connect();

        // Get product_id before deleting
        $sel = $db->prepare("
            SELECT wi.id, wi.product_id FROM wishlist_items wi
            INNER JOIN wishlists w ON w.id = wi.wishlist_id
            WHERE (wi.id = ? OR wi.product_id = ?) AND w.user_id = ? LIMIT 1
        ");
        if (!$sel) return false;
        $sel->bind_param("iii", $wishlistItemId, $wishlistItemId, $userId);
        $sel->execute();
        $row = $sel->get_result()->fetch_assoc();
        $sel->close();

        if (!$row) return false;
        $actualItemId = (int)$row['id'];
        $pid          = (int)$row['product_id'];

        $stmt = $db->prepare("DELETE FROM wishlist_items WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $actualItemId);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            $db->query("UPDATE products SET wishlist_count = GREATEST(0, wishlist_count - 1) WHERE id = {$pid}");
        }

        return $ok;
    }

    /**
     * Check if a product is in a user's wishlist.
     */
    public static function isWishlisted(int $userId, int $productId): bool {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT wi.id FROM wishlist_items wi
            INNER JOIN wishlists w ON w.id = wi.wishlist_id
            WHERE w.user_id = ? AND wi.product_id = ? LIMIT 1
        ");
        if (!$stmt) return false;
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $found = (bool)$stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $found;
    }

    /**
     * Get total wishlist item count for a user (for badge display).
     */
    public static function getCount(int $userId): int {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT COUNT(wi.id) as cnt FROM wishlist_items wi
            INNER JOIN wishlists w ON w.id = wi.wishlist_id
            WHERE w.user_id = ?
        ");
        if (!$stmt) return 0;
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($row['cnt'] ?? 0);
    }

    /**
     * Move a wishlist item to cart (adds to cart then removes from wishlist).
     */
    public static function moveToCart(int $userId, int $wishlistItemId): array {
        $db = Database::connect();

        // Get product info
        $sel = $db->prepare("
            SELECT wi.id AS wid, wi.product_id, p.sale_price, p.is_in_stock
            FROM wishlist_items wi
            INNER JOIN wishlists w ON w.id = wi.wishlist_id
            INNER JOIN products p ON p.id = wi.product_id
            WHERE (wi.id = ? OR wi.product_id = ?) AND w.user_id = ? LIMIT 1
        ");
        if (!$sel) return ['success' => false, 'message' => 'Error accessing wishlist.'];
        $sel->bind_param("iii", $wishlistItemId, $wishlistItemId, $userId);
        $sel->execute();
        $item = $sel->get_result()->fetch_assoc();
        $sel->close();

        if (!$item) return ['success' => false, 'message' => 'Item not found in wishlist.'];
        if (!(int)$item['is_in_stock']) return ['success' => false, 'message' => 'Product is out of stock.'];

        // Add to cart via Cart model
        $cartResult = Cart::addItem((int)$item['product_id'], 1);
        if (!$cartResult['success']) return $cartResult;

        // Remove from wishlist
        self::removeItem($userId, (int)$item['wid']);

        $summary = Cart::getCartSummary();
        return ['success' => true, 'message' => 'Moved to cart!', 'cart_count' => $summary['item_count']];
    }
}
