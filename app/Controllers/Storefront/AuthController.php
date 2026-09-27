<?php

namespace App\Controllers\Storefront;

use App\Config\Database;
use App\Helpers\SecurityHelper;
use App\Models\Cart;

class AuthController {

    private function jsonResponse(bool $success, string $message, array $extra = [], int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $extra));
        exit;
    }

    private function startSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Display Customer Dedicated Login / Register Page.
     */
    public function showLoginForm(): void {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $siteTitle = 'ClickCodex — Login, Register or Recover Account';
        require_once __DIR__ . '/../../Views/front/login.php';
    }

    /**
     * AJAX Customer Login.
     */
    public function login(): void {
        $this->startSession();

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? $_POST;

        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->jsonResponse(false, 'Please fill in both email and password.', [], 400);
        }

        $db = Database::connect();
        $stmt = $db->prepare("SELECT id, name, email, password_hash, is_active FROM users WHERE email = ? LIMIT 1");
        if (!$stmt) {
            $this->jsonResponse(false, 'Database connection error.', [], 500);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            $this->jsonResponse(false, 'Invalid email address or password.', [], 401);
        }

        if ((int)$user['is_active'] !== 1) {
            $this->jsonResponse(false, 'Your account is deactivated. Please contact support.', [], 403);
        }

        // Verify Password (BCrypt or legacy hash check)
        $valid = password_verify($password, $user['password_hash']);
        if (!$valid && strpos($user['password_hash'], '$2y$') !== 0) {
            $valid = ($password === $user['password_hash']);
        }

        if (!$valid) {
            $this->jsonResponse(false, 'Invalid email address or password.', [], 401);
        }

        // Login Success
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        // Merge Guest Cart into User Account
        Cart::mergeSessionCartToUser((int)$user['id']);

        $summary = Cart::getCartSummary();

        $this->jsonResponse(true, 'Welcome back, ' . $user['name'] . '!', [
            'user' => [
                'id' => (int)$user['id'],
                'encrypted_id' => SecurityHelper::encryptId($user['id']),
                'name' => $user['name'],
                'email' => $user['email']
            ],
            'cart_count' => $summary['item_count']
        ]);
    }

    /**
     * AJAX Customer Registration.
     */
    public function register(): void {
        $this->startSession();

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true) ?? $_POST;

        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $password = $data['password'] ?? '';

        if (empty($name) || empty($email) || empty($password)) {
            $this->jsonResponse(false, 'Please provide your name, email, and password.', [], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonResponse(false, 'Please enter a valid email address.', [], 400);
        }

        if (strlen($password) < 6) {
            $this->jsonResponse(false, 'Password must be at least 6 characters long.', [], 400);
        }

        $db = Database::connect();

        // Check if email already registered
        $stmtCheck = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmtCheck->bind_param("s", $email);
        $stmtCheck->execute();
        if ($stmtCheck->get_result()->fetch_assoc()) {
            $stmtCheck->close();
            $this->jsonResponse(false, 'An account with this email address already exists. Please login instead.', [], 409);
        }
        $stmtCheck->close();

        // Create new user
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $stmtIns = $db->prepare("INSERT INTO users (uuid, name, email, phone, password_hash, is_active, is_verified, created_at, updated_at) VALUES (?, ?, ?, ?, ?, 1, 1, NOW(), NOW())");
        if (!$stmtIns) {
            $this->jsonResponse(false, 'Failed to prepare user creation statement.', [], 500);
        }

        $stmtIns->bind_param("sssss", $uuid, $name, $email, $phone, $passwordHash);
        if (!$stmtIns->execute()) {
            $stmtIns->close();
            $this->jsonResponse(false, 'Failed to create customer account. Please try again.', [], 500);
        }

        $userId = $stmtIns->insert_id;
        $stmtIns->close();

        // Automatically Assign Customer Role (Role ID 2 if roles table exists)
        $db->query("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES ({$userId}, 2)");

        // Set Session
        $_SESSION['user_id'] = (int)$userId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;

        // Merge Guest Cart into New Account
        Cart::mergeSessionCartToUser((int)$userId);

        $summary = Cart::getCartSummary();

        $this->jsonResponse(true, 'Account created successfully! Welcome to ClickCodex, ' . $name . '!', [
            'user' => [
                'id' => (int)$userId,
                'encrypted_id' => SecurityHelper::encryptId($userId),
                'name' => $name,
                'email' => $email
            ],
            'cart_count' => $summary['item_count']
        ]);
    }

    /**
     * Get Current User Auth Status.
     */
    public function status(): void {
        $this->startSession();

        $userId = $_SESSION['user_id'] ?? null;
        $summary = Cart::getCartSummary();

        if (!$userId) {
            $this->jsonResponse(true, 'Guest user.', [
                'is_logged_in' => false,
                'user' => null,
                'cart_count' => $summary['item_count']
            ]);
        }

        $db = Database::connect();
        $stmt = $db->prepare("SELECT id, name, email, avatar_url FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);
            $this->jsonResponse(true, 'User not found.', [
                'is_logged_in' => false,
                'user' => null,
                'cart_count' => $summary['item_count']
            ]);
        }

        $user['encrypted_id'] = SecurityHelper::encryptId($user['id']);

        $this->jsonResponse(true, 'User session active.', [
            'is_logged_in' => true,
            'user' => $user,
            'cart_count' => $summary['item_count']
        ]);
    }

    /**
     * Customer Logout.
     */
    public function logout(): void {
        $this->startSession();
        unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email']);

        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        header('Location: ' . $baseUrl . '/');
        exit;
    }
}
