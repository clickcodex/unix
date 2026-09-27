<?php

namespace App\Models;

use App\Config\Database;

class LoginAttempt {
    /**
     * Record a login attempt (success = 1 or 0).
     */
    public static function record(string $identifier, ?string $ipAddress, bool $success): bool {
        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO login_attempts (identifier, ip_address, success, attempted_at) VALUES (?, ?, ?, NOW())");
        if (!$stmt) {
            return false;
        }
        $successVal = $success ? 1 : 0;
        $stmt->bind_param("ssi", $identifier, $ipAddress, $successVal);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Count failed attempts for identifier/IP in the last N minutes.
     */
    public static function countFailedAttempts(string $identifier, ?string $ipAddress, int $minutes = 15): int {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT COUNT(*) as failed_count FROM login_attempts 
            WHERE (identifier = ? OR ip_address = ?) 
              AND success = 0 
              AND attempted_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)
        ");
        if (!$stmt) {
            return 0;
        }
        $stmt->bind_param("ssi", $identifier, $ipAddress, $minutes);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($res['failed_count'] ?? 0);
    }
}
