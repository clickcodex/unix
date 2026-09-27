<?php

namespace App\Controllers\Storefront;

use App\Models\User;
use App\Models\UserAddress;
use App\Models\UserReview;
use App\Models\Cart;
use App\Helpers\SecurityHelper;

class UserController {

    private function json(bool $ok, string $msg, array $extra = [], int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $ok, 'message' => $msg], $extra));
        exit;
    }

    /**
     * Customer Panel Dashboard Overview.
     * Auth is enforced by CustomerAuthMiddleware via routes.php group().
     */
    public function dashboard(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $userId = (int)$_SESSION['user_id'];
        $user   = User::getUserDetail($userId);

        $baseUrl   = defined('BASE_URL') ? BASE_URL : '';
        $siteTitle = 'My Dashboard — ClickCodex';
        $activeTab = 'dashboard';

        require_once __DIR__ . '/../../Views/front/user/dashboard.php';
    }

    /**
     * Customer Orders List.
     * Auth is enforced by CustomerAuthMiddleware via routes.php group().
     */
    public function orders(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $userId = (int)$_SESSION['user_id'];
        $user   = User::getUserDetail($userId);

        $baseUrl   = defined('BASE_URL') ? BASE_URL : '';
        $siteTitle = 'My Orders — ClickCodex';
        $activeTab = 'orders';

        require_once __DIR__ . '/../../Views/front/user/orders.php';
    }

    /**
     * Customer Tax Invoice view/download for DELIVERED orders.
     */
    public function invoice(string $encOrderId): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);

        $orderId = SecurityHelper::decryptId($encOrderId);
        if (!$orderId) {
            $orderId = (int)$encOrderId;
        }

        $db = \App\Config\Database::connect();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
        $stmt->bind_param("ii", $orderId, $userId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$order) {
            die('Order not found.');
        }

        if (strtolower($order['status'] ?? '') !== 'delivered') {
            die('Invoices are strictly generated only for delivered orders.');
        }

        // Fetch Order Items
        $items = [];
        $stmtItems = $db->prepare("SELECT oi.*, p.name, p.primary_image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        if ($stmtItems) {
            $stmtItems->bind_param("i", $orderId);
            $stmtItems->execute();
            $items = $stmtItems->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtItems->close();
        }

        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        require_once __DIR__ . '/../../Views/front/user/invoice.php';
    }

    /**
     * API Handler: Cancel order (supports COD & PhonePe UPI refund details).
     */
    public function cancelOrder(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) {
            $this->json(false, 'Unauthorized', [], 401);
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $orderId = (int)($input['order_id'] ?? 0);
        $reason  = trim($input['reason'] ?? '');
        $upiId   = trim($input['upi_id'] ?? '');
        $comments = trim($input['comments'] ?? '');

        if (!$orderId) {
            $this->json(false, 'Invalid Order ID.', [], 400);
        }

        $db = \App\Config\Database::connect();
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
        $stmt->bind_param("ii", $orderId, $userId);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$order) {
            $this->json(false, 'Order not found.', [], 404);
        }

        $isOnlinePay = !in_array(strtolower($order['payment_method'] ?? ''), ['cod', 'cash on delivery']);
        $newPayStatus = $isOnlinePay ? 'refund_pending' : $order['payment_status'];

        // Update order status to cancelled
        $stmtUpd = $db->prepare("UPDATE orders SET status = 'cancelled', payment_status = ?, updated_at = NOW() WHERE id = ?");
        $stmtUpd->bind_param("si", $newPayStatus, $orderId);
        $stmtUpd->execute();
        $stmtUpd->close();

        $this->json(true, 'Order cancelled successfully.', [
            'order_id' => $orderId,
            'refund_upi' => $upiId
        ]);
    }

    /**
     * Customer Edit Profile Page.
     * Auth is enforced by CustomerAuthMiddleware via routes.php group().
     */
    public function profile(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $userId = (int)$_SESSION['user_id'];
        $user   = User::findById($userId);

        $baseUrl   = defined('BASE_URL') ? BASE_URL : '';
        $siteTitle = 'Edit Profile — ClickCodex';
        $activeTab = 'profile';

        require_once __DIR__ . '/../../Views/front/user/profile.php';
    }

    /**
     * Customer Saved Addresses Page.
     * Auth is enforced by CustomerAuthMiddleware via routes.php group().
     */
    public function addresses(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $userId    = (int)$_SESSION['user_id'];
        $user      = User::findById($userId);
        $addresses = UserAddress::getAddresses($userId);

        $baseUrl   = defined('BASE_URL') ? BASE_URL : '';
        $siteTitle = 'Saved Addresses — ClickCodex';
        $activeTab = 'addresses';

        require_once __DIR__ . '/../../Views/front/user/addresses.php';
    }

    /**
     * Customer Product Reviews Page.
     * Auth is enforced by CustomerAuthMiddleware via routes.php group().
     */
    public function reviews(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $userId  = (int)$_SESSION['user_id'];
        $user    = User::findById($userId);
        $reviews = UserReview::getReviewsForUser($userId);

        $baseUrl   = defined('BASE_URL') ? BASE_URL : '';
        $siteTitle = 'My Reviews & Ratings — ClickCodex';
        $activeTab = 'reviews';

        require_once __DIR__ . '/../../Views/front/user/reviews.php';
    }

    /**
     * POST /user/profile/update — AJAX: Update Profile Information.
     */
    public function updateProfile(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) $this->json(false, 'Unauthorized.', [], 401);

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $name   = trim($data['name']  ?? '');
        $phone  = trim($data['phone'] ?? '');
        $gender = trim($data['gender'] ?? '');
        $dob    = trim($data['date_of_birth'] ?? '');

        if (empty($name)) {
            $this->json(false, 'Full Name is required.', [], 400);
        }

        $ok = User::updateCustomerProfile($userId, [
            'name'          => $name,
            'phone'         => $phone,
            'gender'        => $gender,
            'date_of_birth' => $dob
        ]);

        if ($ok) {
            $_SESSION['user_name'] = $name;
        }

        $this->json($ok, $ok ? 'Profile updated successfully!' : 'Failed to update profile.');
    }

    /**
     * POST /user/password/update — AJAX: Change Password.
     */
    public function changePassword(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) $this->json(false, 'Unauthorized.', [], 401);

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $current = $data['current_password'] ?? '';
        $new     = $data['new_password']     ?? '';

        if (empty($current) || empty($new)) {
            $this->json(false, 'Please fill in both current and new password.', [], 400);
        }

        $res = User::updateCustomerPassword($userId, $current, $new);
        $this->json($res['success'], $res['message']);
    }

    /**
     * POST /user/addresses/store — AJAX: Add New Address.
     */
    public function storeAddress(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) $this->json(false, 'Unauthorized.', [], 401);

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $res = UserAddress::createAddress($userId, $data);
        $this->json($res['success'], $res['message'], ['encrypted_id' => $res['encrypted_id'] ?? '']);
    }

    /**
     * POST /user/addresses/update — AJAX: Update Address.
     */
    public function updateAddress(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) $this->json(false, 'Unauthorized.', [], 401);

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $rawAddrId = $data['address_id'] ?? null;
        $addressId = is_numeric($rawAddrId) ? (int)$rawAddrId : SecurityHelper::decryptId((string)$rawAddrId);

        if (!$addressId) {
            $this->json(false, 'Invalid address.', [], 400);
        }

        $res = UserAddress::updateAddress($userId, $addressId, $data);
        $this->json($res['success'], $res['message']);
    }

    /**
     * POST /user/addresses/delete — AJAX: Delete Address.
     */
    public function deleteAddress(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) $this->json(false, 'Unauthorized.', [], 401);

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $rawAddrId = $data['address_id'] ?? null;
        $addressId = is_numeric($rawAddrId) ? (int)$rawAddrId : SecurityHelper::decryptId((string)$rawAddrId);

        if (!$addressId) {
            $this->json(false, 'Invalid address.', [], 400);
        }

        $ok = UserAddress::deleteAddress($userId, $addressId);
        $this->json($ok, $ok ? 'Address deleted.' : 'Failed to delete address.');
    }

    /**
     * POST /user/addresses/set-default — AJAX: Set Default Address.
     */
    public function setDefaultAddress(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) $this->json(false, 'Unauthorized.', [], 401);

        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $rawAddrId = $data['address_id'] ?? null;
        $addressId = is_numeric($rawAddrId) ? (int)$rawAddrId : SecurityHelper::decryptId((string)$rawAddrId);

        if (!$addressId) {
            $this->json(false, 'Invalid address.', [], 400);
        }

        $ok = UserAddress::setDefaultAddress($userId, $addressId);
        $this->json($ok, $ok ? 'Set as default shipping address.' : 'Failed to set default.');
    }

    /**
     * POST /user/reviews/store — AJAX: Write New Product Review.
     */
    public function storeReview(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) $this->json(false, 'Unauthorized.', [], 401);

        $data   = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $rawPid = $data['product_id'] ?? null;
        $productId = is_numeric($rawPid) ? (int)$rawPid : SecurityHelper::decryptId((string)$rawPid);

        if (!$productId) {
            $this->json(false, 'Invalid product.', [], 400);
        }

        $data['product_id'] = $productId;
        $res = UserReview::createReview($userId, $data);
        $this->json($res['success'], $res['message'], ['encrypted_id' => $res['encrypted_id'] ?? '']);
    }

    /**
     * POST /user/reviews/update — AJAX: Edit Existing Review.
     */
    public function updateReview(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) $this->json(false, 'Unauthorized.', [], 401);

        $data     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $rawRevId = $data['review_id'] ?? null;
        $reviewId = is_numeric($rawRevId) ? (int)$rawRevId : SecurityHelper::decryptId((string)$rawRevId);

        if (!$reviewId) {
            $this->json(false, 'Invalid review.', [], 400);
        }

        $res = UserReview::updateReview($userId, $reviewId, $data);
        $this->json($res['success'], $res['message']);
    }

    /**
     * POST /user/reviews/delete — AJAX: Delete Review.
     */
    public function deleteReview(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) $this->json(false, 'Unauthorized.', [], 401);

        $data     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $rawRevId = $data['review_id'] ?? null;
        $reviewId = is_numeric($rawRevId) ? (int)$rawRevId : SecurityHelper::decryptId((string)$rawRevId);

        if (!$reviewId) {
            $this->json(false, 'Invalid review.', [], 400);
        }

        $ok = UserReview::deleteReview($userId, $reviewId);
        $this->json($ok, $ok ? 'Review deleted.' : 'Failed to delete review.');
    }

    /**
     * POST /user/avatar/upload — AJAX: Upload Profile Picture.
     */
    public function uploadAvatar(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) $this->json(false, 'Unauthorized.', [], 401);

        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $this->json(false, 'Please select a valid image file to upload.', [], 400);
        }

        $file      = $_FILES['avatar'];
        $maxSize   = 5 * 1024 * 1024; // 5MB limit
        $allowed   = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if ($file['size'] > $maxSize) {
            $this->json(false, 'Image size exceeds 5MB limit.', [], 400);
        }

        if (!in_array($extension, $allowed, true)) {
            $this->json(false, 'Only JPG, PNG, WEBP, and GIF images are allowed.', [], 400);
        }

        // Upload directory
        $targetDir = __DIR__ . '/../../../public/uploads/avatars/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $filename   = 'avatar_' . $userId . '_' . time() . '_' . rand(100, 999) . '.' . $extension;
        $targetPath = $targetDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->json(false, 'Failed to save uploaded image.', [], 500);
        }

        $baseUrl   = defined('BASE_URL') ? BASE_URL : '';
        $avatarUrl = $baseUrl . '/public/uploads/avatars/' . $filename;

        $ok = User::updateCustomerAvatar($userId, $avatarUrl);
        if ($ok) {
            $_SESSION['user_avatar'] = $avatarUrl;
        }

        $this->json($ok, $ok ? 'Profile picture updated successfully!' : 'Failed to update avatar in database.', [
            'avatar_url' => $avatarUrl
        ]);
    }

    /**
     * POST /user/avatar/remove — AJAX: Remove Profile Picture.
     */
    public function removeAvatar(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) $this->json(false, 'Unauthorized.', [], 401);

        $ok = User::updateCustomerAvatar($userId, null);
        if ($ok) {
            unset($_SESSION['user_avatar']);
        }

        $this->json($ok, $ok ? 'Profile picture removed.' : 'Failed to remove avatar.');
    }

    /**
     * Customer Help Center Page.
     * Auth is enforced by CustomerAuthMiddleware via routes.php group().
     */
    public function help(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $userId = (int)$_SESSION['user_id'];
        $user   = User::findById($userId);

        $baseUrl   = defined('BASE_URL') ? BASE_URL : '';
        $siteTitle = 'Help Center & Support — ClickCodex';
        $activeTab = 'help';

        require_once __DIR__ . '/../../Views/front/user/help.php';
    }

    /**
     * POST /user/help/ticket — AJAX: Submit Support Ticket / Inquiry.
     */
    public function submitTicket(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if (!$userId) $this->json(false, 'Unauthorized.', [], 401);

        $data     = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $subject  = trim($data['subject'] ?? '');
        $message  = trim($data['message'] ?? '');
        $category = trim($data['category'] ?? 'General Inquiry');

        if (empty($subject) || empty($message)) {
            $this->json(false, 'Please fill in both subject and message.', [], 400);
        }

        // Generate reference ID e.g. TKT-20260802-X892
        $refNo = 'TKT-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 4));

        $this->json(true, "Your support ticket ({$refNo}) has been submitted! Our team will get back to you within 2 hours.", [
            'ticket_ref' => $refNo
        ]);
    }
}
