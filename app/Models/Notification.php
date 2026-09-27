<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;
use Exception;

class Notification {

    /**
     * Ensure notifications database table exists.
     */
    public static function ensureTableExists(): void {
        try {
            $db = Database::connect();
            $sql = "CREATE TABLE IF NOT EXISTS notifications (
                id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
                user_id         BIGINT UNSIGNED  NULL,
                type            VARCHAR(100)     NOT NULL,
                channel         ENUM('in_app','email','sms','push') NOT NULL DEFAULT 'in_app',
                title           VARCHAR(200)     NULL,
                body            TEXT             NULL,
                data            JSON             NULL,
                is_read         TINYINT(1)       NOT NULL DEFAULT 0,
                read_at         DATETIME         NULL,
                sent_at         DATETIME         NULL,
                created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_notif_user          (user_id, is_read),
                KEY idx_notif_type          (type),
                KEY idx_notif_created       (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $db->query($sql);
        } catch (\Throwable $e) {
            error_log("Failed to create notifications table: " . $e->getMessage());
        }
    }

    /**
     * Create / Send a new notification entry.
     */
    public static function createNotification(array $data): ?int {
        self::ensureTableExists();
        $db = Database::connect();

        $userId = !empty($data['user_id']) ? (int)$data['user_id'] : null;
        $type = trim($data['type'] ?? 'system_notice');
        $channel = in_array($data['channel'] ?? '', ['in_app', 'email', 'sms', 'push']) ? $data['channel'] : 'in_app';
        $title = trim($data['title'] ?? 'Notification');
        $body = trim($data['body'] ?? '');
        $payload = !empty($data['data']) ? (is_string($data['data']) ? $data['data'] : json_encode($data['data'], JSON_UNESCAPED_UNICODE)) : null;

        $stmt = $db->prepare("
            INSERT INTO notifications (user_id, type, channel, title, body, data, is_read, sent_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 0, NOW(), NOW())
        ");
        if (!$stmt) return null;

        $stmt->bind_param("isssss", $userId, $type, $channel, $title, $body, $payload);
        $res = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        return $res ? $id : null;
    }

    /**
     * Fetch paginated notification records with filters.
     */
    public static function getPaginatedNotifications(int $page = 1, int $limit = 15, array $filters = []): array {
        self::ensureTableExists();
        $db = Database::connect();

        $offset = ($page - 1) * $limit;
        $whereClause = "WHERE 1=1";
        $params = [];
        $types = "";

        if (!empty($filters['channel']) && $filters['channel'] !== 'all') {
            $whereClause .= " AND n.channel = ?";
            $params[] = $filters['channel'];
            $types .= "s";
        }

        if (isset($filters['status']) && $filters['status'] !== '' && $filters['status'] !== 'all') {
            if ($filters['status'] === 'unread') {
                $whereClause .= " AND n.is_read = 0";
            } elseif ($filters['status'] === 'read') {
                $whereClause .= " AND n.is_read = 1";
            }
        }

        if (!empty($filters['search'])) {
            $searchTerm = "%" . trim($filters['search']) . "%";
            $whereClause .= " AND (n.title LIKE ? OR n.body LIKE ? OR n.type LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $types .= "sssss";
        }

        // Count Total
        $countSql = "SELECT COUNT(*) as total FROM notifications n LEFT JOIN users u ON n.user_id = u.id {$whereClause}";
        $stmtCount = $db->prepare($countSql);
        if ($stmtCount && !empty($types)) {
            $stmtCount->bind_param($types, ...$params);
        }
        if ($stmtCount) {
            $stmtCount->execute();
            $totalResult = $stmtCount->get_result()->fetch_assoc();
            $totalItems = (int)($totalResult['total'] ?? 0);
            $stmtCount->close();
        } else {
            $totalItems = 0;
        }

        // Fetch Data
        $dataSql = "
            SELECT n.*, u.name as user_name, u.email as user_email
            FROM notifications n
            LEFT JOIN users u ON n.user_id = u.id
            {$whereClause}
            ORDER BY n.created_at DESC
            LIMIT ? OFFSET ?
        ";
        $paramsWithLimit = array_merge($params, [$limit, $offset]);
        $typesWithLimit = $types . "ii";

        $stmtData = $db->prepare($dataSql);
        if ($stmtData && !empty($typesWithLimit)) {
            $stmtData->bind_param($typesWithLimit, ...$paramsWithLimit);
        }

        $notifications = [];
        if ($stmtData) {
            $stmtData->execute();
            $res = $stmtData->get_result();
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
                $row['data_decoded'] = !empty($row['data']) ? json_decode($row['data'], true) : null;
                $notifications[] = $row;
            }
            $stmtData->close();
        }

        $totalPages = ceil($totalItems / $limit);

        return [
            'notifications' => $notifications,
            'pagination' => [
                'current_page' => $page,
                'total_pages'  => max(1, $totalPages),
                'total_items'  => $totalItems,
                'limit'        => $limit
            ]
        ];
    }

    /**
     * Get KPI Metrics for Notifications Dashboard.
     */
    public static function getKpiMetrics(): array {
        self::ensureTableExists();
        $db = Database::connect();

        $unreadCount = 0;
        $totalCount = 0;
        $inAppCount = 0;
        $emailCount = 0;
        $todayCount = 0;

        $res1 = $db->query("SELECT COUNT(*) as cnt FROM notifications WHERE is_read = 0");
        if ($res1) $unreadCount = (int)($res1->fetch_assoc()['cnt'] ?? 0);

        $res2 = $db->query("SELECT COUNT(*) as cnt FROM notifications");
        if ($res2) $totalCount = (int)($res2->fetch_assoc()['cnt'] ?? 0);

        $res3 = $db->query("SELECT COUNT(*) as cnt FROM notifications WHERE channel = 'in_app'");
        if ($res3) $inAppCount = (int)($res3->fetch_assoc()['cnt'] ?? 0);

        $res4 = $db->query("SELECT COUNT(*) as cnt FROM notifications WHERE channel = 'email'");
        if ($res4) $emailCount = (int)($res4->fetch_assoc()['cnt'] ?? 0);

        $res5 = $db->query("SELECT COUNT(*) as cnt FROM notifications WHERE DATE(created_at) = CURDATE()");
        if ($res5) $todayCount = (int)($res5->fetch_assoc()['cnt'] ?? 0);

        return [
            'unread' => $unreadCount,
            'total' => $totalCount,
            'in_app' => $inAppCount,
            'email' => $emailCount,
            'today' => $todayCount
        ];
    }

    /**
     * Get Top Unread Notifications for Top Header Dropdown.
     */
    public static function getTopUnreadForAdmin(int $limit = 5): array {
        self::ensureTableExists();
        $db = Database::connect();

        $sql = "SELECT id, type, channel, title, body, created_at, is_read FROM notifications ORDER BY is_read ASC, created_at DESC LIMIT ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) return ['count' => 0, 'items' => []];

        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $res = $stmt->get_result();

        $items = [];
        while ($row = $res->fetch_assoc()) {
            $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
            $row['time_ago'] = self::formatTimeAgo($row['created_at']);
            $items[] = $row;
        }
        $stmt->close();

        $unreadRes = $db->query("SELECT COUNT(*) as cnt FROM notifications WHERE is_read = 0");
        $unreadCount = $unreadRes ? (int)($unreadRes->fetch_assoc()['cnt'] ?? 0) : 0;

        return [
            'count' => $unreadCount,
            'items' => $items
        ];
    }

    /**
     * Mark single notification as read.
     */
    public static function markAsRead(int $id): bool {
        self::ensureTableExists();
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Mark all notifications as read.
     */
    public static function markAllAsRead(): bool {
        self::ensureTableExists();
        $db = Database::connect();
        return (bool)$db->query("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE is_read = 0");
    }

    /**
     * Delete notification item.
     */
    public static function deleteNotification(int $id): bool {
        self::ensureTableExists();
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM notifications WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Bulk Action on Notifications (mark_read, mark_unread, delete).
     */
    public static function bulkAction(array $ids, string $action): bool {
        if (empty($ids)) return false;
        self::ensureTableExists();
        $db = Database::connect();

        $sanitizedIds = array_map('intval', array_filter($ids, 'is_numeric'));
        if (empty($sanitizedIds)) return false;
        $idList = implode(',', $sanitizedIds);

        switch ($action) {
            case 'mark_read':
                return (bool)$db->query("UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id IN ({$idList})");
            case 'mark_unread':
                return (bool)$db->query("UPDATE notifications SET is_read = 0, read_at = NULL WHERE id IN ({$idList})");
            case 'delete':
                return (bool)$db->query("DELETE FROM notifications WHERE id IN ({$idList})");
            default:
                return false;
        }
    }

    /**
     * Broadcast notification to specified group or users.
     */
    public static function sendBroadcast(array $data): int {
        self::ensureTableExists();
        $db = Database::connect();

        $targetGroup = $data['target'] ?? 'all';
        $title = trim($data['title'] ?? 'System Announcement');
        $body = trim($data['body'] ?? '');
        $channel = in_array($data['channel'] ?? '', ['in_app', 'email', 'sms', 'push']) ? $data['channel'] : 'in_app';
        $type = trim($data['type'] ?? 'system_broadcast');

        $users = [];
        if ($targetGroup === 'customers') {
            $res = $db->query("SELECT id FROM users WHERE is_active = 1");
            if ($res) $users = array_column($res->fetch_all(MYSQLI_ASSOC), 'id');
        } elseif ($targetGroup === 'admins') {
            $res = $db->query("SELECT id FROM users WHERE is_active = 1 LIMIT 5");
            if ($res) $users = array_column($res->fetch_all(MYSQLI_ASSOC), 'id');
        } else {
            // All users or general broadcast
            $res = $db->query("SELECT id FROM users WHERE is_active = 1");
            if ($res) $users = array_column($res->fetch_all(MYSQLI_ASSOC), 'id');
        }

        $sentCount = 0;
        if (!empty($users)) {
            $stmt = $db->prepare("INSERT INTO notifications (user_id, type, channel, title, body, is_read, sent_at, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW(), NOW())");
            if ($stmt) {
                foreach ($users as $uId) {
                    $uIdInt = (int)$uId;
                    $stmt->bind_param("issss", $uIdInt, $type, $channel, $title, $body);
                    if ($stmt->execute()) $sentCount++;
                }
                $stmt->close();
            }
        } else {
            // General system notification (user_id = null)
            $nullUser = null;
            $stmt = $db->prepare("INSERT INTO notifications (user_id, type, channel, title, body, is_read, sent_at, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW(), NOW())");
            if ($stmt) {
                $stmt->bind_param("issss", $nullUser, $type, $channel, $title, $body);
                if ($stmt->execute()) $sentCount = 1;
                $stmt->close();
            }
        }

        return $sentCount;
    }

    private static function formatTimeAgo(string $datetime): string {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . 'm ago';
        if ($diff < 86400) return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        return date('M j, Y', $time);
    }
}
