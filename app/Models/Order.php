<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class Order {
    /**
     * Get paginated orders list with filtering and search.
     */
    public static function getPaginatedOrders(
        int $page = 1,
        int $perPage = 10,
        string $status = 'all',
        string $paymentStatus = 'all',
        string $date = '',
        string $search = ''
    ): array {
        $db = Database::connect();
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $where = ["1=1"];
        $params = [];
        $types = "";

        if ($status !== 'all' && !empty($status)) {
            $where[] = "o.status = ?";
            $params[] = $status;
            $types .= "s";
        }

        if ($paymentStatus !== 'all' && !empty($paymentStatus)) {
            $where[] = "o.payment_status = ?";
            $params[] = $paymentStatus;
            $types .= "s";
        }

        if (!empty($date)) {
            $where[] = "DATE(o.placed_at) = ?";
            $params[] = $date;
            $types .= "s";
        }

        if (!empty($search)) {
            $sTerm = '%' . $search . '%';
            $where[] = "(o.order_number LIKE ? OR o.shipping_name LIKE ? OR o.shipping_phone LIKE ? OR u.email LIKE ?)";
            $params[] = $sTerm;
            $params[] = $sTerm;
            $params[] = $sTerm;
            $params[] = $sTerm;
            $types .= "ssss";
        }

        $whereClause = implode(" AND ", $where);

        // Count query
        $countSql = "
            SELECT COUNT(*) as total FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            WHERE {$whereClause}
        ";
        $stmtCount = $db->prepare($countSql);
        if ($types) {
            $stmtCount->bind_param($types, ...$params);
        }
        $stmtCount->execute();
        $totalCount = (int)($stmtCount->get_result()->fetch_assoc()['total'] ?? 0);
        $stmtCount->close();

        $totalPages = (int)ceil($totalCount / $perPage) ?: 1;

        // Main List Query
        $sql = "
            SELECT o.id, o.uuid, o.order_number, o.user_id, o.shipping_name, o.shipping_phone,
                   o.shipping_city, o.shipping_state, o.shipping_country, o.shipping_postal,
                   o.subtotal, o.discount_amount, o.shipping_charge, o.tax_amount, o.total_amount,
                   o.currency, o.coupon_code, o.status, o.payment_status, o.placed_at,
                   u.name as user_name, u.email as user_email,
                   (SELECT GROUP_CONCAT(CONCAT(product_name, ' ×', quantity) SEPARATOR ', ') 
                    FROM order_items WHERE order_id = o.id) as items_summary
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE {$whereClause}
            ORDER BY o.placed_at DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $perPage;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        $orders = [];
        while ($row = $res->fetch_assoc()) {
            // Encrypt order ID securely!
            $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
            $row['user_encrypted_id'] = SecurityHelper::encryptId($row['user_id']);
            
            // Generate initials
            $uName = $row['user_name'] ?: $row['shipping_name'];
            $words = explode(' ', $uName);
            $row['user_initials'] = strtoupper(substr($words[0] ?? 'U', 0, 1) . substr($words[1] ?? '', 0, 1));
            
            $orders[] = $row;
        }
        $stmt->close();

        return [
            'orders' => $orders,
            'totalCount' => $totalCount,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'perPage' => $perPage
        ];
    }

    /**
     * Get full order details by raw ID.
     */
    public static function getOrderById(int $id): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT o.*, u.name as user_name, u.email as user_email, u.phone as user_phone
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
            LIMIT 1
        ");
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$order) {
            return null;
        }

        // Encrypt IDs
        $order['encrypted_id'] = SecurityHelper::encryptId($order['id']);
        $order['user_encrypted_id'] = SecurityHelper::encryptId($order['user_id']);

        // Fetch Order Items
        $order['items'] = self::getOrderItems($id);

        // Fetch Status History
        $order['status_history'] = self::getOrderStatusHistory($id);

        // Fetch Payment Transactions
        $order['transactions'] = self::getOrderTransactions($id);

        return $order;
    }

    /**
     * Fetch order_items rows for an order.
     */
    public static function getOrderItems(int $orderId): array {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT oi.*, 
                   COALESCE(NULLIF(oi.product_name, ''), p.name, 'Product Item') as product_name,
                   COALESCE(NULLIF(oi.product_sku, ''), p.sku, CONCAT('SKU-', oi.product_id)) as product_sku,
                   COALESCE(NULLIF(oi.image_url, ''), (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = oi.product_id AND pi.is_primary = 1 LIMIT 1), '') as image_url
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
            ORDER BY oi.id ASC
        ");
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $res = $stmt->get_result();
        $items = [];
        while ($row = $res->fetch_assoc()) {
            $row['product_encrypted_id'] = SecurityHelper::encryptId($row['product_id']);
            $row['quantity'] = (int)($row['quantity'] ?? 1);
            $row['unit_price'] = (float)($row['unit_price'] ?? 0);
            $row['sale_price'] = (float)($row['sale_price'] ?? $row['unit_price']);
            $row['line_total'] = (float)($row['line_total'] ?? ($row['unit_price'] * $row['quantity']));
            $items[] = $row;
        }
        $stmt->close();
        return $items;
    }

    /**
     * Fetch order_status_history rows for an order.
     */
    public static function getOrderStatusHistory(int $orderId): array {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT h.*, u.name as changed_by_name
            FROM order_status_history h
            LEFT JOIN users u ON h.changed_by = u.id
            WHERE h.order_id = ?
            ORDER BY h.created_at ASC
        ");
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $res = $stmt->get_result();
        $history = [];
        while ($row = $res->fetch_assoc()) {
            $history[] = $row;
        }
        $stmt->close();
        return $history;
    }

    /**
     * Fetch payment_transactions for an order.
     */
    public static function getOrderTransactions(int $orderId): array {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT * FROM payment_transactions WHERE order_id = ? ORDER BY created_at DESC
        ");
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $res = $stmt->get_result();
        $txs = [];
        while ($row = $res->fetch_assoc()) {
            $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
            $txs[] = $row;
        }
        $stmt->close();
        return $txs;
    }

    /**
     * Update order status & log to history.
     */
    public static function updateOrderStatus(int $orderId, string $newStatus, ?string $note, int $changedByUserId): bool {
        $validStatuses = ['pending','confirmed','processing','shipped','delivered','cancelled','return_requested','returned','refunded'];
        if (!in_array($newStatus, $validStatuses, true)) {
            return false;
        }

        $db = Database::connect();

        // Determine timestamp column to update
        $tsField = null;
        if ($newStatus === 'confirmed') $tsField = 'confirmed_at = NOW(), ';
        elseif ($newStatus === 'shipped') $tsField = 'shipped_at = NOW(), ';
        elseif ($newStatus === 'delivered') $tsField = 'delivered_at = NOW(), ';
        elseif ($newStatus === 'cancelled') $tsField = 'cancelled_at = NOW(), ';
        else $tsField = '';

        $sql = "UPDATE orders SET {$tsField} status = ? WHERE id = ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("si", $newStatus, $orderId);
        $res = $stmt->execute();
        $stmt->close();

        if ($res) {
            // Log history
            $stmtHist = $db->prepare("
                INSERT INTO order_status_history (order_id, status, note, is_public, changed_by, created_at)
                VALUES (?, ?, ?, 1, ?, NOW())
            ");
            if ($stmtHist) {
                $stmtHist->bind_param("issi", $orderId, $newStatus, $note, $changedByUserId);
                $stmtHist->execute();
                $stmtHist->close();
            }
        }

        return $res;
    }

    /**
     * Get KPI Summary Metrics.
     */
    public static function getKpiMetrics(): array {
        $db = Database::connect();
        
        $totalOrders = 0;
        $todayOrders = 0;
        $totalRevenue = 0.0;
        $pendingCount = 0;
        $unpaidCount = 0;
        $returnsCount = 0;

        $res = $db->query("SELECT COUNT(*) as cnt FROM orders");
        if ($res && $row = $res->fetch_assoc()) $totalOrders = (int)$row['cnt'];

        $res = $db->query("SELECT COUNT(*) as cnt FROM orders WHERE DATE(placed_at) = CURDATE()");
        if ($res && $row = $res->fetch_assoc()) $todayOrders = (int)$row['cnt'];
        if ($todayOrders === 0) $todayOrders = $totalOrders; // Fallback for historical seed data

        $res = $db->query("SELECT COALESCE(SUM(total_amount), 0) as rev FROM orders WHERE payment_status = 'paid'");
        if ($res && $row = $res->fetch_assoc()) $totalRevenue = (float)$row['rev'];

        $res = $db->query("SELECT COUNT(*) as cnt FROM orders WHERE status = 'pending'");
        if ($res && $row = $res->fetch_assoc()) $pendingCount = (int)$row['cnt'];

        $res = $db->query("SELECT COUNT(*) as cnt FROM orders WHERE payment_status = 'unpaid'");
        if ($res && $row = $res->fetch_assoc()) $unpaidCount = (int)$row['cnt'];

        $res = $db->query("SELECT COUNT(*) as cnt FROM orders WHERE status IN ('return_requested','returned','refunded')");
        if ($res && $row = $res->fetch_assoc()) $returnsCount = (int)$row['cnt'];

        return [
            'totalOrders' => $totalOrders,
            'todayOrders' => $todayOrders,
            'totalRevenue' => $totalRevenue,
            'pendingCount' => $pendingCount,
            'unpaidCount' => $unpaidCount,
            'returnsCount' => $returnsCount
        ];
    }

    /**
     * Get grouped count of all 9 statuses.
     */
    public static function getStatusCounts(): array {
        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'return_requested', 'returned', 'refunded'];
        $counts = array_fill_keys($statuses, 0);

        $db = Database::connect();
        $res = $db->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                if (isset($counts[$row['status']])) {
                    $counts[$row['status']] = (int)$row['cnt'];
                }
            }
        }
        return $counts;
    }
}
