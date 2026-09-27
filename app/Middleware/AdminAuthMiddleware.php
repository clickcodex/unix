<?php

namespace App\Middleware;

use App\Models\User;
use App\Models\UserSession;

class AdminAuthMiddleware {
    public static function check(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['admin_user_id'] ?? null;
        $token = $_SESSION['admin_session_token'] ?? $_COOKIE['cc_admin_remember_token'] ?? null;

        if (!$userId || !$token) {
            self::redirectToLogin();
            return;
        }

        // Validate session token in DB
        $tokenHash = hash('sha256', $token);
        $sessionData = UserSession::validateToken($tokenHash);

        if (!$sessionData || (int)$sessionData['user_id'] !== (int)$userId) {
            self::logoutAndRedirect();
            return;
        }

        // Validate active status & admin role
        $user = User::findById((int)$userId);
        if (!$user || (int)$user['is_active'] !== 1 || !User::hasAdminAccess((int)$userId)) {
            self::logoutAndRedirect();
            return;
        }

        // Populate session metadata for convenience
        $_SESSION['admin_user_name'] = $user['name'];
        $_SESSION['admin_user_email'] = $user['email'];
        $_SESSION['admin_role_name'] = User::getPrimaryRoleName((int)$userId);
    }

    private static function redirectToLogin(): void {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        header('Location: ' . $baseUrl . '/admin/login');
        exit;
    }

    private static function logoutAndRedirect(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['admin_session_token'])) {
            UserSession::revokeToken(hash('sha256', $_SESSION['admin_session_token']));
        }
        if (isset($_COOKIE['cc_admin_remember_token'])) {
            UserSession::revokeToken(hash('sha256', $_COOKIE['cc_admin_remember_token']));
            setcookie('cc_admin_remember_token', '', time() - 3600, '/');
        }

        unset($_SESSION['admin_user_id'], $_SESSION['admin_session_token'], $_SESSION['admin_user_name'], $_SESSION['admin_user_email'], $_SESSION['admin_role_name']);

        self::redirectToLogin();
    }
}
