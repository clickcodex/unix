<?php

namespace App\Controllers\Admin;

use App\Models\User;
use App\Models\UserSession;
use App\Models\LoginAttempt;

class AuthController {
    /**
     * Display the Admin Login page.
     */
    public function showLoginForm(): void {
        \App\Middleware\GuestAdminMiddleware::check();
        require_once __DIR__ . '/../../Views/admin/auth/login.php';
    }

    /**
     * Handle Admin Login submission.
     */
    public function login(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Accept JSON or form POST
        $isJson = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
        if ($isJson) {
            $input = json_decode(file_get_contents('php://input'), true) ?? [];
        } else {
            $input = $_POST;
        }

        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $remember = !empty($input['remember']);
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        if (empty($email) || empty($password)) {
            $this->jsonResponse(false, 'Email address and password are required.', 400);
            return;
        }

        // Rate limiting check: max 5 failed attempts in 15 mins
        $failedAttempts = LoginAttempt::countFailedAttempts($email, $ipAddress, 15);
        if ($failedAttempts >= 5) {
            $this->jsonResponse(false, 'Too many failed login attempts. Account locked for 15 minutes.', 429);
            return;
        }

        // Fetch user by email
        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            LoginAttempt::record($email, $ipAddress, false);
            $remaining = max(0, 5 - ($failedAttempts + 1));
            $this->jsonResponse(false, "Invalid credentials. {$remaining} attempt(s) remaining.", 401);
            return;
        }

        // Check if user is active
        if ((int)$user['is_active'] !== 1) {
            LoginAttempt::record($email, $ipAddress, false);
            $this->jsonResponse(false, 'Your account is deactivated. Please contact Super Admin.', 403);
            return;
        }

        // Check if user has Admin-level role
        if (!User::hasAdminAccess((int)$user['id'])) {
            LoginAttempt::record($email, $ipAddress, false);
            $this->jsonResponse(false, 'Access denied. Only Super Admin, Admin, or Staff accounts can access this portal.', 403);
            return;
        }

        // Authentication successful! Record success
        LoginAttempt::record($email, $ipAddress, true);

        // Generate session token
        $rawToken = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $rawToken);
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        // Store session token in DB (valid for 7 days or 30 days if remembered)
        $ttl = $remember ? (86400 * 30) : (86400 * 7);
        UserSession::createSession(
            (int)$user['id'],
            $tokenHash,
            'web',
            'Browser Session',
            $ipAddress,
            $userAgent,
            $ttl
        );

        // Set PHP session variables
        $_SESSION['admin_user_id'] = $user['id'];
        $_SESSION['admin_session_token'] = $rawToken;
        $_SESSION['admin_user_name'] = $user['name'];
        $_SESSION['admin_user_email'] = $user['email'];
        $_SESSION['admin_role_name'] = User::getPrimaryRoleName((int)$user['id']);

        // Set cookie if remember me checked
        if ($remember) {
            setcookie('cc_admin_remember_token', $rawToken, time() + $ttl, '/', '', false, true);
        }

        // Update last login & record audit log
        User::updateLastLogin((int)$user['id']);
        \App\Models\AuditLog::log('user.login', 'User', (int)$user['id'], null, ['email' => $user['email'], 'name' => $user['name']], (int)$user['id']);

        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $this->jsonResponse(true, 'Authentication successful! Redirecting to dashboard...', 200, [
            'redirect' => $baseUrl . '/admin/dashboard'
        ]);
    }

    /**
     * Handle Admin Logout.
     */
    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['admin_user_id'])) {
            \App\Models\AuditLog::log('user.logout', 'User', (int)$_SESSION['admin_user_id']);
        }

        $token = $_SESSION['admin_session_token'] ?? $_COOKIE['cc_admin_remember_token'] ?? null;
        if ($token) {
            UserSession::revokeToken(hash('sha256', $token));
        }

        if (isset($_COOKIE['cc_admin_remember_token'])) {
            setcookie('cc_admin_remember_token', '', time() - 3600, '/');
        }

        unset($_SESSION['admin_user_id'], $_SESSION['admin_session_token'], $_SESSION['admin_user_name'], $_SESSION['admin_user_email'], $_SESSION['admin_role_name']);
        session_destroy();

        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        header('Location: ' . $baseUrl . '/admin/login');
        exit;
    }

    private function jsonResponse(bool $success, string $message, int $statusCode = 200, array $extra = []): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $extra));
        exit;
    }
}
