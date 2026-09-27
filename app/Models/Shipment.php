<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class Shipment {

    /**
     * Get KPI metrics for the shipments dashboard.
     */
    public static function getKpiMetrics(): array {
        $db = Database::connect();
        $row = $db->query("
            SELECT 
                COUNT(*) AS total_shipments,
                SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS delivered_count,
                SUM(CASE WHEN status = 'in_transit' OR status = 'shipped' THEN 1 ELSE 0 END) AS in_transit_count,
                SUM(CASE WHEN status = 'out_for_delivery' THEN 1 ELSE 0 END) AS out_for_delivery_count,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN status = 'failed_attempt' OR status = 'returned' THEN 1 ELSE 0 END) AS failed_count
            FROM shipments
        ")->fetch_assoc();

        return $row ?: [];
    }

    /**
     * Get paginated shipments list with filters and search.
     */
    public static function getPaginatedShipments(int $page = 1, int $perPage = 15, array $filters = []): array {
        $db = Database::connect();

        $where = [];
        $params = [];
        $types = '';

        if (!empty($filters['status'])) {
            $where[] = 's.status = ?';
            $params[] = $filters['status'];
            $types .= 's';
        }

        if (!empty($filters['carrier'])) {
            $where[] = 's.carrier_name = ?';
            $params[] = $filters['carrier'];
            $types .= 's';
        }

        if (!empty($filters['search'])) {
            $st = '%' . $filters['search'] . '%';
            $where[] = '(s.tracking_number LIKE ? OR s.carrier_name LIKE ? OR o.order_number LIKE ? OR u.name LIKE ?)';
            $params = array_merge($params, [$st, $st, $st, $st]);
            $types .= 'ssss';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmtCount = $db->prepare("
            SELECT COUNT(*) AS cnt 
            FROM shipments s
            LEFT JOIN orders o ON s.order_id = o.id
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
            SELECT s.*, o.order_number, o.shipping_address, u.name AS user_name, u.email AS user_email
            FROM shipments s
            LEFT JOIN orders o ON s.order_id = o.id
            LEFT JOIN users u ON o.user_id = u.id
            {$whereClause}
            ORDER BY s.updated_at DESC, s.id DESC
            LIMIT ? OFFSET ?
        ";
        $fetchTypes = $types . 'ii';
        $fetchParams = array_merge($params, [$perPage, $offset]);

        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($fetchTypes, ...$fetchParams);
            $stmt->execute();
            $shipments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            $shipments = [];
        }

        foreach ($shipments as &$shp) {
            $shp['encrypted_id'] = SecurityHelper::encryptId($shp['id']);
            $shp['order_encrypted_id'] = SecurityHelper::encryptId($shp['order_id']);
        }

        return [
            'shipments' => $shipments,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => (int)$total,
                'total_pages' => $totalPages,
            ]
        ];
    }

    /**
     * Get shipment detail by ID.
     */
    public static function getShipmentById(int $id): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT s.*, o.order_number, o.shipping_name, o.shipping_phone, o.shipping_address, u.email AS user_email
            FROM shipments s
            LEFT JOIN orders o ON s.order_id = o.id
            LEFT JOIN users u ON o.user_id = u.id
            WHERE s.id = ?
        ");
        if (!$stmt) return null;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $shipment = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($shipment) {
            $shipment['encrypted_id'] = SecurityHelper::encryptId($shipment['id']);
            $shipment['order_encrypted_id'] = SecurityHelper::encryptId($shipment['order_id']);
        }
        return $shipment;
    }

    /**
     * Update shipment carrier tracking or status.
     */
    public static function updateShipment(int $id, array $data): bool {
        $db = Database::connect();
        $stmt = $db->prepare("
            UPDATE shipments 
            SET carrier_name = ?, tracking_number = ?, tracking_url = ?, status = ?, estimated_delivery = ?, shipped_at = ?, delivered_at = ?, updated_at = NOW()
            WHERE id = ?
        ");
        if (!$stmt) return false;

        $carrier = trim($data['carrier_name'] ?? '');
        $trackingNum = trim($data['tracking_number'] ?? '');
        $trackingUrl = !empty($data['tracking_url']) ? trim($data['tracking_url']) : null;
        $status = trim($data['status'] ?? 'pending');
        $estDelivery = !empty($data['estimated_delivery']) ? $data['estimated_delivery'] : null;
        $shippedAt = !empty($data['shipped_at']) ? $data['shipped_at'] : null;
        $deliveredAt = ($status === 'delivered') ? ($data['delivered_at'] ?? date('Y-m-d H:i:s')) : null;

        $stmt->bind_param("sssssssi", $carrier, $trackingNum, $trackingUrl, $status, $estDelivery, $shippedAt, $deliveredAt, $id);
        $res = $stmt->execute();
        $stmt->close();

        return $res;
    }

    /**
     * Create shipment record.
     */
    public static function createShipment(array $data): ?int {
        $db = Database::connect();
        $stmt = $db->prepare("
            INSERT INTO shipments (order_id, carrier_name, tracking_number, tracking_url, status, estimated_delivery, shipped_at, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW(), NOW())
        ");
        if (!$stmt) return null;

        $orderId = (int)$data['order_id'];
        $carrier = trim($data['carrier_name'] ?? 'Delhivery');
        $trackingNum = trim($data['tracking_number'] ?? '');
        $trackingUrl = !empty($data['tracking_url']) ? trim($data['tracking_url']) : null;
        $status = trim($data['status'] ?? 'in_transit');
        $estDelivery = !empty($data['estimated_delivery']) ? $data['estimated_delivery'] : null;

        $stmt->bind_param("isssss", $orderId, $carrier, $trackingNum, $trackingUrl, $status, $estDelivery);
        $res = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        return $res ? $id : null;
    }
}
