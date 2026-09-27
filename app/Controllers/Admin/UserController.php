<?php

namespace App\Controllers\Admin;

use App\Models\User;
use App\Helpers\SecurityHelper;

class UserController {
    /**
     * Display Users Manager page or return AJAX JSON response.
     */
    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $filters = [
            'search' => trim($_GET['search'] ?? ''),
            'role' => trim($_GET['role'] ?? 'all'),
            'status' => trim($_GET['status'] ?? 'all'),
            'sort' => trim($_GET['sort'] ?? 'newest'),
            'page' => max(1, (int)($_GET['page'] ?? 1)),
            'limit' => max(5, min(100, (int)($_GET['limit'] ?? 10)))
        ];

        $userData = User::getPaginatedUsers($filters);
        $kpis = User::getKpiMetrics();
        $roles = User::getAllRoles();

        $isAjax = (isset($_GET['ajax']) && $_GET['ajax'] == '1') || 
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'users' => $userData['users'],
                'pagination' => $userData['pagination'],
                'kpis' => $kpis,
                'roles' => $roles
            ]);
            exit;
        }

        $users = $userData['users'];
        $pagination = $userData['pagination'];

        require_once __DIR__ . '/../../Views/admin/users/index.php';
    }

    private function parseUserId($id): ?int {
        if (empty($id)) return null;
        if (is_numeric($id)) return (int)$id;
        return SecurityHelper::decryptId((string)$id);
    }

    /**
     * Render Dedicated Full Page User Profile & Activity View.
     */
    public function view(string $encryptedId): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $userId = $this->parseUserId($encryptedId);
        if (!$userId) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/users');
            exit;
        }

        $user = User::getUserDetail($userId);
        if (!$user) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/admin/users');
            exit;
        }

        require_once __DIR__ . '/../../Views/admin/users/view.php';
    }

    /**
     * Fetch detailed user profile JSON.
     */
    public function detail(string $encryptedId): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $userId = $this->parseUserId($encryptedId);
        if (!$userId) {
            $this->jsonResponse(false, 'Invalid User ID.', 400);
            return;
        }

        $user = User::getUserDetail($userId);
        if (!$user) {
            $this->jsonResponse(false, 'User not found.', 404);
            return;
        }

        $this->jsonResponse(true, 'User details fetched.', 200, [
            'user' => $user
        ]);
    }

    /**
     * Create a new user account.
     */
    public function store(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $email = strtolower(trim($input['email'] ?? ''));
        $name = trim($input['name'] ?? '');

        if (empty($email) || empty($name)) {
            $this->jsonResponse(false, 'Name and Email are required.', 400);
            return;
        }

        // Check if email already exists
        $existing = User::findByEmail($email);
        if ($existing) {
            $this->jsonResponse(false, 'Email address is already registered.', 400);
            return;
        }

        $userId = User::createUser($input);
        if ($userId) {
            \App\Models\AuditLog::log('user.created', 'User', $userId, null, ['name' => $name, 'email' => $email]);
            $this->jsonResponse(true, 'User created successfully.', 200, [
                'encrypted_id' => SecurityHelper::encryptId($userId)
            ]);
        } else {
            $this->jsonResponse(false, 'Failed to create user.', 400);
        }
    }

    /**
     * Update user details.
     */
    public function update(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $encryptedId = trim($input['user_id'] ?? $input['id'] ?? '');

        $userId = $this->parseUserId($encryptedId);
        if (!$userId) {
            $this->jsonResponse(false, 'Invalid User ID.', 400);
            return;
        }

        $oldUser = User::getUserDetail($userId);
        $success = User::updateUser($userId, $input);
        if ($success) {
            \App\Models\AuditLog::log('user.updated', 'User', $userId, $oldUser, $input);
            $this->jsonResponse(true, 'User updated successfully.');
        } else {
            $this->jsonResponse(false, 'Failed to update user.', 400);
        }
    }

    /**
     * Toggle active state.
     */
    public function toggleActive(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $encryptedId = trim($input['user_id'] ?? $input['id'] ?? '');

        $userId = $this->parseUserId($encryptedId);
        if (!$userId) {
            $this->jsonResponse(false, 'Invalid User ID.', 400);
            return;
        }

        $res = User::toggleUserActive($userId);
        if ($res) {
            \App\Models\AuditLog::log('user.toggle_active', 'User', $userId);
        }
        $this->jsonResponse($res, $res ? 'User status toggled successfully.' : 'Failed to toggle user status.');
    }

    /**
     * Soft delete user account.
     */
    public function delete(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $encryptedId = trim($input['user_id'] ?? $input['id'] ?? '');

        $userId = $this->parseUserId($encryptedId);
        if (!$userId) {
            $this->jsonResponse(false, 'Invalid User ID.', 400);
            return;
        }

        $oldUser = User::getUserDetail($userId);
        $res = User::deleteUser($userId);
        if ($res) {
            \App\Models\AuditLog::log('user.deleted', 'User', $userId, $oldUser, null);
        }
        $this->jsonResponse($res, $res ? 'User account deleted successfully.' : 'Failed to delete user.');
    }

    private function getRequestInput(): array {
        $json = json_decode(file_get_contents('php://input'), true);
        if (is_array($json) && !empty($json)) return $json;
        return $_POST;
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
