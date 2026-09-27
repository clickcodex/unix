<?php

namespace App\Models;

use App\Config\Database;

class UserSession {
    /**
     * Create a new session token in the database.
     */
    public static function createSession(
        int $userId,
        string $tokenHash,
        string $deviceType = 'web',
        ?string $deviceName = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        int $ttlSeconds = 86400 * 7 // Default 7 days
    ): bool {
        $db = Database::connect();
        $expiresAt = date('Y-m-d H:i:s', time() + $ttlSeconds);

        $stmt = $db->prepare("
            INSERT INTO user_sessions (user_id, token_hash, device_name, device_type, ip_address, user_agent, expires_at)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("issssss", $userId, $tokenHash, $deviceName, $deviceType, $ipAddress, $userAgent, $expiresAt);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    /**
     * Validate session token hash. Returns session row if valid and unexpired, null otherwise.
     */
    public static function validateToken(string $tokenHash): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT * FROM user_sessions 
            WHERE token_hash = ? AND revoked_at IS NULL AND expires_at > NOW() 
            LIMIT 1
        ");
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param("s", $tokenHash);
        $stmt->execute();
        $res = $stmt->get_result();
        $session = $res->fetch_assoc();
        $stmt->close();
        return $session ?: null;
    }

    /**
     * Revoke a specific session token hash.
     */
    public static function revokeToken(string $tokenHash): bool {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE user_sessions SET revoked_at = NOW() WHERE token_hash = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("s", $tokenHash);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Revoke all active sessions for a user.
     */
    public static function revokeAllUserSessions(int $userId): bool {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE user_sessions SET revoked_at = NOW() WHERE user_id = ? AND revoked_at IS NULL");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $userId);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }
}
