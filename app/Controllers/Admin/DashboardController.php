<?php

namespace App\Controllers\Admin;

use App\Config\Database;
use Exception;

class DashboardController {
    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $db = Database::connect();

        // 1. KPI Cards Data
        $totalRevenue = 0.0;
        $ordersToday = 0;
        $outOfStockCount = 0;
        $pendingReviewsCount = 0;
        $avgRating = 0.0;

        $res = $db->query("SELECT COALESCE(SUM(total_amount), 0) as rev FROM orders WHERE payment_status = 'paid'");
        if ($res && $row = $res->fetch_assoc()) {
            $totalRevenue = (float)$row['rev'];
        }

        $res = $db->query("SELECT COUNT(*) as cnt FROM orders WHERE DATE(placed_at) = CURDATE()");
        if ($res && $row = $res->fetch_assoc()) {
            $ordersToday = (int)$row['cnt'];
        }
        // Fallback for demo if placed_at dates are historical
        if ($ordersToday === 0) {
            $res = $db->query("SELECT COUNT(*) as cnt FROM orders");
            if ($res && $row = $res->fetch_assoc()) {
                $ordersToday = (int)$row['cnt'];
            }
        }

        $res = $db->query("SELECT COUNT(*) as cnt FROM products WHERE is_in_stock = 0 AND deleted_at IS NULL");
        if ($res && $row = $res->fetch_assoc()) {
            $outOfStockCount = (int)$row['cnt'];
        }

        $res = $db->query("SELECT COUNT(*) as cnt FROM reviews WHERE status = 'pending'");
        if ($res && $row = $res->fetch_assoc()) {
            $pendingReviewsCount = (int)$row['cnt'];
        }

        $res = $db->query("SELECT COALESCE(AVG(rating), 0) as avg_r FROM reviews WHERE status = 'approved'");
        if ($res && $row = $res->fetch_assoc()) {
            $avgRating = round((float)$row['avg_r'], 1);
        }

        // 2. Order Status Breakdown (9 statuses)
        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'return_requested', 'returned', 'refunded'];
        $statusCounts = array_fill_keys($statuses, 0);

        $res = $db->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                if (isset($statusCounts[$row['status']])) {
                    $statusCounts[$row['status']] = (int)$row['cnt'];
                }
            }
        }
        $totalOrdersSum = array_sum($statusCounts) ?: 1;

        // 3. Quick Metadata Counts
        $activeUsers = 0;
        $activeProducts = 0;
        $activeCategories = 0;
        $wishlistCount = 0;
        $activeCarts = 0;
        $activeOffers = 0;

        $res = $db->query("SELECT COUNT(*) as cnt FROM users WHERE is_active = 1 AND deleted_at IS NULL");
        if ($res && $row = $res->fetch_assoc()) $activeUsers = (int)$row['cnt'];

        $res = $db->query("SELECT COUNT(*) as cnt FROM products WHERE is_active = 1 AND deleted_at IS NULL");
        if ($res && $row = $res->fetch_assoc()) $activeProducts = (int)$row['cnt'];

        $res = $db->query("SELECT COUNT(*) as cnt FROM categories WHERE is_active = 1");
        if ($res && $row = $res->fetch_assoc()) $activeCategories = (int)$row['cnt'];

        $res = $db->query("SELECT COUNT(*) as cnt FROM wishlist_items");
        if ($res && $row = $res->fetch_assoc()) $wishlistCount = (int)$row['cnt'];

        $res = $db->query("SELECT COUNT(*) as cnt FROM carts");
        if ($res && $row = $res->fetch_assoc()) $activeCarts = (int)$row['cnt'];

        $res = $db->query("SELECT COUNT(*) as cnt FROM offers WHERE is_active = 1");
        if ($res && $row = $res->fetch_assoc()) $activeOffers = (int)$row['cnt'];

        // 4. Recent Orders (limit 8)
        $recentOrders = [];
        $res = $db->query("
            SELECT o.id, o.order_number, o.total_amount, o.status, o.payment_status, o.placed_at,
                   u.name as user_name, u.id as user_id,
                   (SELECT GROUP_CONCAT(CONCAT(product_name, ' × ', quantity) SEPARATOR ', ') 
                    FROM order_items WHERE order_id = o.id) as items_summary
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.placed_at DESC
            LIMIT 8
        ");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $recentOrders[] = $row;
            }
        }

        // 5. Featured/Top Products (limit 6)
        $topProducts = [];
        $res = $db->query("
            SELECT p.id, p.name, p.sku, p.base_price, p.sale_price, p.is_in_stock, p.average_rating, p.review_count, p.total_sold,
                   c.name as category_name,
                   (SELECT image_url FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, id ASC LIMIT 1) as image_url
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.deleted_at IS NULL
            ORDER BY p.total_sold DESC, p.id DESC
            LIMIT 6
        ");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $topProducts[] = $row;
            }
        }

        // 6. Recent Reviews (limit 5)
        $recentReviews = [];
        $res = $db->query("
            SELECT r.id, r.rating, r.title, r.body, r.status, r.is_verified_purchase, r.helpful_count, r.created_at,
                   u.name as user_name, u.id as user_id,
                   p.name as product_name
            FROM reviews r
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN products p ON r.product_id = p.id
            ORDER BY r.created_at DESC
            LIMIT 5
        ");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $recentReviews[] = $row;
            }
        }

        // 7. Payment Transactions (limit 8)
        $recentTransactions = [];
        $res = $db->query("
            SELECT pt.id, pt.gateway, pt.gateway_txn_id, pt.amount, pt.currency, pt.status, pt.processed_at, pt.created_at,
                   o.order_number, u.name as user_name
            FROM payment_transactions pt
            LEFT JOIN orders o ON pt.order_id = o.id
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY pt.created_at DESC
            LIMIT 8
        ");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $recentTransactions[] = $row;
            }
        }

        // 8. Gateway Summary Totals
        $successTxTotal = 0.0;
        $refundedTxTotal = 0.0;
        $failedTxTotal = 0.0;
        $pendingTxTotal = 0.0;

        $res = $db->query("SELECT status, COALESCE(SUM(amount), 0) as amt FROM payment_transactions GROUP BY status");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                if ($row['status'] === 'success') $successTxTotal = (float)$row['amt'];
                if ($row['status'] === 'refunded') $refundedTxTotal = (float)$row['amt'];
                if ($row['status'] === 'failed') $failedTxTotal = (float)$row['amt'];
                if ($row['status'] === 'pending' || $row['status'] === 'initiated') $pendingTxTotal += (float)$row['amt'];
            }
        }

        $dashboardData = [
            'totalRevenue' => $totalRevenue,
            'ordersToday' => $ordersToday,
            'outOfStockCount' => $outOfStockCount,
            'pendingReviewsCount' => $pendingReviewsCount,
            'avgRating' => $avgRating,
            'statusCounts' => $statusCounts,
            'totalOrdersSum' => $totalOrdersSum,
            'activeUsers' => $activeUsers,
            'activeProducts' => $activeProducts,
            'activeCategories' => $activeCategories,
            'wishlistCount' => $wishlistCount,
            'activeCarts' => $activeCarts,
            'activeOffers' => $activeOffers,
            'recentOrders' => $recentOrders,
            'topProducts' => $topProducts,
            'recentReviews' => $recentReviews,
            'recentTransactions' => $recentTransactions,
            'successTxTotal' => $successTxTotal,
            'refundedTxTotal' => $refundedTxTotal,
            'failedTxTotal' => $failedTxTotal,
            'pendingTxTotal' => $pendingTxTotal,
        ];

        require_once __DIR__ . '/../../Views/admin/dashboard/index.php';
    }
}
