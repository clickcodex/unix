<?php

namespace App\Controllers\Storefront;

use App\Models\Product;
use App\Models\Category;
use App\Models\Wishlist;
use App\Helpers\SecurityHelper;

class ProductController {

    /**
     * Display Product Detail Page.
     * GET /product/{id} or /product/{slug}
     */
    public function show(string $identifier = ''): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($identifier)) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/');
            exit;
        }

        $productId = is_numeric($identifier) ? (int)$identifier : SecurityHelper::decryptId($identifier);
        $product   = null;

        if ($productId) {
            $product = Product::getProductById($productId);
        } else {
            // Find by slug
            $db = \App\Config\Database::connect();
            $stmt = $db->prepare("SELECT id FROM products WHERE slug = ? AND deleted_at IS NULL LIMIT 1");
            if ($stmt) {
                $stmt->bind_param("s", $identifier);
                $stmt->execute();
                $res = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($res) {
                    $product = Product::getProductById((int)$res['id']);
                }
            }
        }

        if (!$product || empty($product['is_active'])) {
            http_response_code(404);
            $baseUrl = defined('BASE_URL') ? BASE_URL : '';
            require_once __DIR__ . '/../../Views/errors/404.php';
            return;
        }

        $baseUrl   = defined('BASE_URL') ? BASE_URL : '';
        $siteTitle = (!empty($product['meta_title']) ? $product['meta_title'] : $product['name']) . ' — ClickCodex';
        $metaDesc  = !empty($product['meta_description']) ? $product['meta_description'] : $product['short_description'];

        // Related products in same category
        $relatedProducts = [];
        if (!empty($product['category_id'])) {
            $db = \App\Config\Database::connect();
            $stmtRel = $db->prepare("
                SELECT p.*, (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
                FROM products p
                WHERE p.category_id = ? AND p.id != ? AND p.deleted_at IS NULL AND p.is_active = 1
                LIMIT 4
            ");
            if ($stmtRel) {
                $stmtRel->bind_param("ii", $product['category_id'], $product['id']);
                $stmtRel->execute();
                $relatedProducts = $stmtRel->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmtRel->close();
                foreach ($relatedProducts as &$rp) {
                    $rp['encrypted_id'] = SecurityHelper::encryptId($rp['id']);
                    $rp['discount_pct'] = (!empty($rp['base_price']) && !empty($rp['sale_price']) && $rp['base_price'] > $rp['sale_price'])
                        ? round((($rp['base_price'] - $rp['sale_price']) / $rp['base_price']) * 100)
                        : 0;
                }
            }
        }

        // Fetch approved reviews for product
        $db = \App\Config\Database::connect();
        $stmtRev = $db->prepare("
            SELECT r.*, u.name as user_name, u.avatar_url
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.id
            WHERE r.product_id = ? AND r.status = 'approved'
            ORDER BY r.created_at DESC
            LIMIT 20
        ");
        $reviews = [];
        if ($stmtRev) {
            $stmtRev->bind_param("i", $product['id']);
            $stmtRev->execute();
            $reviews = $stmtRev->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtRev->close();
        }

        // Rating Breakdown Counts
        $ratingCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $stmtStar = $db->prepare("SELECT rating, COUNT(*) as cnt FROM reviews WHERE product_id = ? AND status = 'approved' GROUP BY rating");
        if ($stmtStar) {
            $stmtStar->bind_param("i", $product['id']);
            $stmtStar->execute();
            $resStar = $stmtStar->get_result();
            while ($row = $resStar->fetch_assoc()) {
                $r = (int)$row['rating'];
                if (isset($ratingCounts[$r])) {
                    $ratingCounts[$r] = (int)$row['cnt'];
                }
            }
            $stmtStar->close();
        }

        // Check if in user wishlist
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $inWishlist = false;
        if ($userId) {
            $wishlist = Wishlist::getOrCreate($userId);
            $wItems   = Wishlist::getItems((int)$wishlist['id'], $userId);
            $wPids    = array_column($wItems, 'product_id');
            $inWishlist = in_array((int)$product['id'], $wPids, true);
        }

        require_once __DIR__ . '/../../Views/front/product_detail.php';
    }
}
