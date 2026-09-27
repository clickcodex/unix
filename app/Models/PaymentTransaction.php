<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class PaymentTransaction {

    /**
     * Get KPI metrics for the payment transactions dashboard.
     */
    public static function getKpiMetrics(): array {
        $db = Database::connect();
        $row = $db->query("
            SELECT 
                COUNT(*) AS total_transactions,
                SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) AS total_revenue,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) AS success_count,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) AS failed_count,
                SUM(CASE WHEN status = 'refunded' THEN 1 ELSE 0 END) AS refunded_count,
                SUM(CASE WHEN status = 'pending' OR status = 'initiated' THEN 1 ELSE 0 END) AS pending_count
            FROM payment_transactions
        ")->fetch_assoc();

        // Gateway breakdown
        $gateways = $db->query("
            SELECT gateway, COUNT(*) AS cnt, SUM(amount) AS total_val
            FROM payment_transactions
            WHERE status = 'success'
            GROUP BY gateway
        ")->fetch_all(MYSQLI_ASSOC);

        $row['gateway_breakdown'] = $gateways;
        return $row ?: [];
    }

    /**
     * Get paginated payment transactions list with filters and search.
     */
    public static function getPaginatedPayments(int $page = 1, int $perPage = 15, array $filters = []): array {
        $db = Database::connect();

        $where = [];
        $params = [];
        $types = '';

        if (!empty($filters['status'])) {
            $where[] = 'pt.status = ?';
            $params[] = $filters['status'];
            $types .= 's';
        }

        if (!empty($filters['gateway'])) {
            $where[] = 'pt.gateway = ?';
            $params[] = $filters['gateway'];
            $types .= 's';
        }

        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where[] = '(pt.gateway_txn_id LIKE ? OR o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)';
            $params = array_merge($params, [$s, $s, $s, $s]);
            $types .= 'ssss';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmtCount = $db->prepare("
            SELECT COUNT(*) AS cnt 
            FROM payment_transactions pt
            LEFT JOIN orders o ON pt.order_id = o.id
            LEFT JOIN users u ON o.user_id = u.id
            {$whereClause}
        ");
        if ($types && $stmtCount) {
            $stmtCount->bind_param($types, ...$params);
        }
        $stmtCount->execute();
        $total = $stmtCount->get_result()->fetch_assoc()['cnt'];
        $stmtCount->close();

        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql = "
            SELECT pt.*, o.order_number, u.name AS user_name, u.email AS user_email, u.avatar_url AS user_avatar
            FROM payment_transactions pt
            LEFT JOIN orders o ON pt.order_id = o.id
            LEFT JOIN users u ON o.user_id = u.id
            {$whereClause}
            ORDER BY pt.created_at DESC
            LIMIT ? OFFSET ?
        ";
        $fetchTypes = $types . 'ii';
        $fetchParams = array_merge($params, [$perPage, $offset]);

        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($fetchTypes, ...$fetchParams);
            $stmt->execute();
            $payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            $payments = [];
        }

        foreach ($payments as &$p) {
            $p['encrypted_id'] = SecurityHelper::encryptId($p['id']);
            $p['order_encrypted_id'] = SecurityHelper::encryptId($p['order_id']);
        }

        return [
            'payments' => $payments,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => (int)$total,
                'total_pages' => $totalPages,
            ]
        ];
    }

    /**
     * Get transaction detail by ID.
     */
    public static function getPaymentById(int $id): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT pt.*, o.order_number, o.total_amount AS order_total, u.name AS user_name, u.email AS user_email
            FROM payment_transactions pt
            LEFT JOIN orders o ON pt.order_id = o.id
            LEFT JOIN users u ON o.user_id = u.id
            WHERE pt.id = ?
        ");
        if (!$stmt) return null;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $payment = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($payment) {
            $payment['encrypted_id'] = SecurityHelper::encryptId($payment['id']);
            $payment['order_encrypted_id'] = SecurityHelper::encryptId($payment['order_id']);
        }
        return $payment;
    }

    /**
     * Update payment transaction status (e.g. process refund, mark success/failed).
     */
    public static function updateStatus(int $id, string $status): bool {
        $db = Database::connect();
        $allowed = ['initiated', 'success', 'failed', 'refunded', 'pending'];
        if (!in_array($status, $allowed)) return false;

        $stmt = $db->prepare("UPDATE payment_transactions SET status = ?, processed_at = NOW() WHERE id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("si", $status, $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }
}
