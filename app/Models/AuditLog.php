<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;
use Exception;

class AuditLog {

    /**
     * Ensure audit_logs database table exists.
     */
    public static function ensureTableExists(): void {
        try {
            $db = Database::connect();
            $sql = "CREATE TABLE IF NOT EXISTS audit_logs (
                id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
                user_id         BIGINT UNSIGNED  NULL,
                event           VARCHAR(150)     NOT NULL,
                model           VARCHAR(100)     NULL,
                model_id        BIGINT UNSIGNED  NULL,
                old_data        JSON             NULL,
                new_data        JSON             NULL,
                ip_address      VARCHAR(45)      NULL,
                user_agent      VARCHAR(255)     NULL,
                created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_audit_user          (user_id),
                KEY idx_audit_model         (model, model_id),
                KEY idx_audit_event         (event),
                KEY idx_audit_created       (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $db->query($sql);

            // Add user_agent column if missing in pre-existing database table
            $colCheck = $db->query("SHOW COLUMNS FROM audit_logs LIKE 'user_agent'");
            if ($colCheck && $colCheck->num_rows === 0) {
                $db->query("ALTER TABLE audit_logs ADD COLUMN user_agent VARCHAR(255) NULL AFTER ip_address");
            }
        } catch (\Throwable $e) {
            error_log("Failed to create or update audit_logs table: " . $e->getMessage());
        }
    }

    /**
     * Record an audit log entry in the database.
     * 
     * @param string $event Event identifier string (e.g., 'user.login', 'setting.updated', 'product.deleted')
     * @param string|null $model Target model name (e.g., 'Product', 'Order', 'Setting')
     * @param int|null $modelId Target model ID
     * @param mixed $oldData Array or JSON serializable data prior to action
     * @param mixed $newData Array or JSON serializable data after action
     * @param int|null $userId User ID executing the action (defaults to current session admin ID)
     * @param string|null $ipAddress Client IP address (defaults to current request IP)
     * @return bool True if successfully logged
     */
    public static function log(
        string $event,
        ?string $model = null,
        ?int $modelId = null,
        $oldData = null,
        $newData = null,
        ?int $userId = null,
        ?string $ipAddress = null
    ): bool {
        self::ensureTableExists();

        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, event, model, model_id, old_data, new_data, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            if (!$stmt) {
                error_log("AuditLog prepare failed: " . $db->error);
                return false;
            }

            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }

            $currentUserId = $userId ?: ($_SESSION['admin_user_id'] ?? null);
            $currentIp = $ipAddress ?: ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 250);

            $oldJson = $oldData !== null ? (is_string($oldData) ? $oldData : json_encode($oldData, JSON_UNESCAPED_UNICODE)) : null;
            $newJson = $newData !== null ? (is_string($newData) ? $newData : json_encode($newData, JSON_UNESCAPED_UNICODE)) : null;

            $stmt->bind_param(
                "ississss",
                $currentUserId,
                $event,
                $model,
                $modelId,
                $oldJson,
                $newJson,
                $currentIp,
                $userAgent
            );

            $success = $stmt->execute();
            if (!$success) {
                error_log("AuditLog execute failed: " . $stmt->error);
            }
            $stmt->close();
            return $success;
        } catch (\Throwable $e) {
            error_log("Audit log insertion failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get KPI metrics for the audit log dashboard.
     */
    public static function getKpiMetrics(): array {
        self::ensureTableExists();
        $db = Database::connect();

        $res = $db->query("
            SELECT 
                COUNT(*) AS total_logs,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today_logs,
                COUNT(DISTINCT user_id) AS total_users,
                SUM(CASE WHEN event LIKE '%.delete%' OR event LIKE '%.deactivate%' THEN 1 ELSE 0 END) AS security_actions,
                SUM(CASE WHEN event LIKE 'setting%' OR event LIKE 'system%' THEN 1 ELSE 0 END) AS system_actions
            FROM audit_logs
        ");

        return ($res && $row = $res->fetch_assoc()) ? $row : [
            'total_logs' => 0,
            'today_logs' => 0,
            'total_users' => 0,
            'security_actions' => 0,
            'system_actions' => 0
        ];
    }

    /**
     * Get distinct list of event categories for filter dropdown.
     */
    public static function getEventCategories(): array {
        self::ensureTableExists();
        $db = Database::connect();

        $res = $db->query("SELECT DISTINCT event FROM audit_logs ORDER BY event ASC");
        $events = [];

        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $events[] = $row['event'];
            }
        }

        return $events;
    }

    /**
     * Get paginated audit logs with search and filtering.
     */
    public static function getPaginatedLogs(int $page = 1, int $perPage = 20, array $filters = []): array {
        self::ensureTableExists();
        $db = Database::connect();

        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $where = ["1=1"];
        $params = [];
        $types = "";

        if (!empty($filters['search'])) {
            $s = '%' . trim($filters['search']) . '%';
            $where[] = "(a.event LIKE ? OR a.model LIKE ? OR a.ip_address LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
            $params = array_merge($params, [$s, $s, $s, $s, $s]);
            $types .= "sssss";
        }

        if (!empty($filters['event'])) {
            $where[] = "a.event = ?";
            $params[] = trim($filters['event']);
            $types .= "s";
        }

        if (!empty($filters['model'])) {
            $where[] = "a.model = ?";
            $params[] = trim($filters['model']);
            $types .= "s";
        }

        if (!empty($filters['user_id'])) {
            $where[] = "a.user_id = ?";
            $params[] = (int)$filters['user_id'];
            $types .= "i";
        }

        if (!empty($filters['date_from'])) {
            $where[] = "a.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
            $types .= "s";
        }

        if (!empty($filters['date_to'])) {
            $where[] = "a.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
            $types .= "s";
        }

        $whereSql = implode(" AND ", $where);

        // Count total matching records
        $countSql = "
            SELECT COUNT(*) AS total 
            FROM audit_logs a 
            LEFT JOIN users u ON a.user_id = u.id 
            WHERE {$whereSql}
        ";
        $stmtCount = $db->prepare($countSql);
        if (!empty($types) && $stmtCount) {
            $stmtCount->bind_param($types, ...$params);
        }

        $totalCount = 0;
        if ($stmtCount) {
            $stmtCount->execute();
            $res = $stmtCount->get_result()->fetch_assoc();
            $totalCount = (int)($res['total'] ?? 0);
            $stmtCount->close();
        }

        $totalPages = max(1, (int)ceil($totalCount / $perPage));

        // Fetch paginated records
        $dataSql = "
            SELECT a.*, u.name AS user_name, u.email AS user_email
            FROM audit_logs a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE {$whereSql}
            ORDER BY a.id DESC
            LIMIT ? OFFSET ?
        ";

        $fetchTypes = $types . "ii";
        $fetchParams = array_merge($params, [$perPage, $offset]);

        $stmtData = $db->prepare($dataSql);
        if ($fetchTypes && $stmtData) {
            $stmtData->bind_param($fetchTypes, ...$fetchParams);
        }

        $logs = [];
        if ($stmtData) {
            $stmtData->execute();
            $result = $stmtData->get_result();

            while ($row = $result->fetch_assoc()) {
                $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
                $row['old_data_parsed'] = !empty($row['old_data']) ? json_decode($row['old_data'], true) : null;
                $row['new_data_parsed'] = !empty($row['new_data']) ? json_decode($row['new_data'], true) : null;
                $logs[] = $row;
            }
            $stmtData->close();
        }

        return [
            'logs' => $logs,
            'totalCount' => $totalCount,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'perPage' => $perPage
        ];
    }

    /**
     * Get single audit log detail by ID.
     */
    public static function getLogById(int $id): ?array {
        self::ensureTableExists();
        $db = Database::connect();

        $stmt = $db->prepare("
            SELECT a.*, u.name AS user_name, u.email AS user_email
            FROM audit_logs a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.id = ? LIMIT 1
        ");

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
            $row['old_data_parsed'] = !empty($row['old_data']) ? json_decode($row['old_data'], true) : null;
            $row['new_data_parsed'] = !empty($row['new_data']) ? json_decode($row['new_data'], true) : null;
        }

        return $row ?: null;
    }

    /**
     * Delete old audit logs before a specific date.
     */
    public static function purgeLogsOlderThan(int $days = 90): int {
        self::ensureTableExists();
        $db = Database::connect();

        $stmt = $db->prepare("DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        if (!$stmt) {
            return 0;
        }

        $stmt->bind_param("i", $days);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        return $affected;
    }
}
