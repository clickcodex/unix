<?php

namespace App\Middleware;

use App\Models\User;
use App\Models\UserSession;

class GuestAdminMiddleware {
    public static function check(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['admin_user_id'] ?? null;
        $token = $_SESSION['admin_session_token'] ?? $_COOKIE['cc_admin_remember_token'] ?? null;

        if ($userId && $token) {
            $tokenHash = hash('sha256', $token);
            $sessionData = UserSession::validateToken($tokenHash);
            if ($sessionData && (int)$sessionData['user_id'] === (int)$userId && User::hasAdminAccess((int)$userId)) {
                $baseUrl = defined('BASE_URL') ? BASE_URL : '';
                header('Location: ' . $baseUrl . '/admin/dashboard');
                exit;
            }
        }
    }
}
