<?php

namespace App\Middleware;

use App\Models\User;

class CustomerAuthMiddleware {

    /**
     * Check if a customer is authenticated.
     * Mirrors the same pattern as AdminAuthMiddleware::check().
     * Called via $router->group(fn() => CustomerAuthMiddleware::check(), ...) in routes.php.
     */
    public static function check(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user_id'] ?? null;

        // No session — redirect to customer login
        if (!$userId) {
            self::redirectToLogin();
            return;
        }

        // Validate the user still exists and is active in DB
        $user = User::findById((int)$userId);

        if (!$user || (int)$user['is_active'] !== 1) {
            self::logoutAndRedirect();
            return;
        }

        // Refresh session metadata on every protected request
        $_SESSION['user_id']    = (int)$user['id'];
        $_SESSION['user_name']  = $user['name'];
        $_SESSION['user_email'] = $user['email'];
    }

    /**
     * Redirect unauthenticated visitor to the customer login page.
     */
    private static function redirectToLogin(): void {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';

        // Preserve the requested URL so we can redirect back after login
        $requestedUri = $_SERVER['REQUEST_URI'] ?? '';
        if ($requestedUri && strpos($requestedUri, '/login') === false) {
            $baseUri = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            $relative = ($baseUri !== '/' && strpos($requestedUri, $baseUri) === 0)
                ? substr($requestedUri, strlen($baseUri))
                : $requestedUri;
            $_SESSION['login_redirect'] = ltrim($relative, '/');
        }

        header('Location: ' . $baseUrl . '/login');
        exit;
    }

    /**
     * Clear customer session and redirect to login.
     */
    private static function logoutAndRedirect(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        unset(
            $_SESSION['user_id'],
            $_SESSION['user_name'],
            $_SESSION['user_email']
        );

        self::redirectToLogin();
    }
}
