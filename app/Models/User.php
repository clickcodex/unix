<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;
use Exception;

class User {
    /**
     * Find an active user by email address.
     */
    public static function findByEmail(string $email): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user ?: null;
    }

    /**
     * Find a user by primary ID.
     */
    public static function findById(int $id): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            $user['encrypted_id'] = SecurityHelper::encryptId($user['id']);
            $user['roles'] = self::getRoles($user['id']);
            $user['primary_role'] = self::getPrimaryRoleName($user['id']);
        }
        return $user ?: null;
    }

    /**
     * Get assigned roles for a given user ID.
     */
    public static function getRoles(int $userId): array {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT r.id, r.slug, r.name 
            FROM roles r 
            INNER JOIN user_roles ur ON r.id = ur.role_id 
            WHERE ur.user_id = ?
        ");
        if (!$stmt) return [];
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
        $stmt->close();
        return $roles;
    }

    /**
     * Get all available system roles.
     */
    public static function getAllRoles(): array {
        $db = Database::connect();
        $res = $db->query("SELECT * FROM roles ORDER BY id ASC");
        if (!$res) return [];
        $roles = [];
        while ($row = $res->fetch_assoc()) {
            $roles[] = $row;
        }
        return $roles;
    }

    /**
     * Get role slugs for a user.
     */
    public static function getRoleSlugs(int $userId): array {
        $roles = self::getRoles($userId);
        return array_column($roles, 'slug');
    }

    /**
     * Check if a user has Admin Panel access.
     */
    public static function hasAdminAccess(int $userId): bool {
        $slugs = self::getRoleSlugs($userId);
        $adminRoles = ['super_admin', 'admin', 'staff', 'manager'];
        foreach ($slugs as $slug) {
            if (in_array($slug, $adminRoles, true)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get primary role name for display.
     */
    public static function getPrimaryRoleName(int $userId): string {
        $roles = self::getRoles($userId);
        if (empty($roles)) {
            return 'Customer';
        }
        $priority = ['super_admin' => 1, 'admin' => 2, 'manager' => 3, 'staff' => 4, 'customer' => 5];
        usort($roles, function($a, $b) use ($priority) {
            $pA = $priority[$a['slug']] ?? 99;
            $pB = $priority[$b['slug']] ?? 99;
            return $pA <=> $pB;
        });
        return $roles[0]['name'] ?? 'Customer';
    }

    /**
     * Update last_login_at timestamp.
     */
    public static function updateLastLogin(int $userId): bool {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $userId);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * High-scale paginated user list with filters & stats.
     */
    public static function getPaginatedUsers(array $filters = []): array {
        $db = Database::connect();

        $page = max(1, (int)($filters['page'] ?? 1));
        $limit = max(5, min(100, (int)($filters['limit'] ?? 10)));
        $offset = ($page - 1) * $limit;

        $where = ["u.deleted_at IS NULL"];
        $params = [];
        $types = "";

        // Search query
        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $where[] = "(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ? OR u.uuid LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
            $types .= "ssss";
        }

        // Role filter
        if (!empty($filters['role']) && $filters['role'] !== 'all') {
            $roleSlug = trim($filters['role']);
            $where[] = "u.id IN (SELECT ur.user_id FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE r.slug = ?)";
            $params[] = $roleSlug;
            $types .= "s";
        }

        // Status filter
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'active') {
                $where[] = "u.is_active = 1";
            } elseif ($filters['status'] === 'inactive') {
                $where[] = "u.is_active = 0";
            } elseif ($filters['status'] === 'verified') {
                $where[] = "u.is_verified = 1";
            } elseif ($filters['status'] === 'unverified') {
                $where[] = "u.is_verified = 0";
            }
        }

        $whereSql = implode(" AND ", $where);

        // Sort ordering
        $sortColumn = "u.id";
        $sortDir = "DESC";
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'newest': $sortColumn = "u.id"; $sortDir = "DESC"; break;
                case 'oldest': $sortColumn = "u.id"; $sortDir = "ASC"; break;
                case 'name_asc': $sortColumn = "u.name"; $sortDir = "ASC"; break;
                case 'name_desc': $sortColumn = "u.name"; $sortDir = "DESC"; break;
                case 'orders_desc': $sortColumn = "COALESCE(os.total_orders, 0)"; $sortDir = "DESC"; break;
                case 'spent_desc': $sortColumn = "COALESCE(os.lifetime_value, 0)"; $sortDir = "DESC"; break;
            }
        }

        // Count Total
        $countSql = "
            SELECT COUNT(DISTINCT u.id) as total 
            FROM users u 
            LEFT JOIN v_user_order_summary os ON u.id = os.user_id 
            WHERE {$whereSql}
        ";
        $stmtCount = $db->prepare($countSql);
        if (!empty($params)) {
            $stmtCount->bind_param($types, ...$params);
        }
        $stmtCount->execute();
        $totalRecords = (int)$stmtCount->get_result()->fetch_assoc()['total'];
        $stmtCount->close();

        $totalPages = max(1, (int)ceil($totalRecords / $limit));

        // Fetch Paginated Rows
        $sql = "
            SELECT u.*, 
                   COALESCE(os.total_orders, 0) as total_orders,
                   COALESCE(os.lifetime_value, 0) as lifetime_value,
                   os.last_order_at
            FROM users u
            LEFT JOIN v_user_order_summary os ON u.id = os.user_id
            WHERE {$whereSql}
            ORDER BY {$sortColumn} {$sortDir}
            LIMIT ? OFFSET ?
        ";

        $typesLimit = $types . "ii";
        $paramsLimit = array_merge($params, [$limit, $offset]);

        $stmt = $db->prepare($sql);
        $stmt->bind_param($typesLimit, ...$paramsLimit);
        $stmt->execute();
        $res = $stmt->get_result();

        $users = [];
        while ($row = $res->fetch_assoc()) {
            $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
            $row['roles'] = self::getRoles((int)$row['id']);
            $row['primary_role'] = self::getPrimaryRoleName((int)$row['id']);
            $row['role_slugs'] = array_column($row['roles'], 'slug');
            $users[] = $row;
        }
        $stmt->close();

        return [
            'users' => $users,
            'pagination' => [
                'current_page' => $page,
                'limit' => $limit,
                'total_records' => $totalRecords,
                'total_pages' => $totalPages
            ]
        ];
    }

    /**
     * Get KPI Statistics for User Dashboard.
     */
    public static function getKpiMetrics(): array {
        $db = Database::connect();
        $totalRes = $db->query("SELECT COUNT(*) as cnt FROM users WHERE deleted_at IS NULL");
        $activeRes = $db->query("SELECT COUNT(*) as cnt FROM users WHERE is_active = 1 AND deleted_at IS NULL");
        $verifiedRes = $db->query("SELECT COUNT(*) as cnt FROM users WHERE is_verified = 1 AND deleted_at IS NULL");
        $vipRes = $db->query("SELECT COUNT(DISTINCT user_id) as cnt FROM v_user_order_summary WHERE lifetime_value >= 10000 OR total_orders >= 5");

        $total = $totalRes ? (int)$totalRes->fetch_assoc()['cnt'] : 0;
        $active = $activeRes ? (int)$activeRes->fetch_assoc()['cnt'] : 0;
        $verified = $verifiedRes ? (int)$verifiedRes->fetch_assoc()['cnt'] : 0;
        $vip = $vipRes ? (int)$vipRes->fetch_assoc()['cnt'] : 0;

        return [
            'total' => $total,
            'active' => $active,
            'verified' => $verified,
            'vip' => $vip
        ];
    }

    /**
     * Fetch complete user detail including addresses and recent sessions.
     */
    public static function getUserDetail(int $id): ?array {
        $user = self::findById($id);
        if (!$user) return null;

        $db = Database::connect();

        // Order Summary
        $stmtSummary = $db->prepare("SELECT * FROM v_user_order_summary WHERE user_id = ? LIMIT 1");
        if ($stmtSummary) {
            $stmtSummary->bind_param("i", $id);
            $stmtSummary->execute();
            $user['order_summary'] = $stmtSummary->get_result()->fetch_assoc() ?: [
                'total_orders' => 0,
                'lifetime_value' => 0,
                'last_order_at' => null
            ];
            $stmtSummary->close();
        }

        // Addresses
        $stmtAddr = $db->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC");
        if ($stmtAddr) {
            $stmtAddr->bind_param("i", $id);
            $stmtAddr->execute();
            $user['addresses'] = [];
            $resA = $stmtAddr->get_result();
            while ($rowA = $resA->fetch_assoc()) {
                $rowA['encrypted_id'] = SecurityHelper::encryptId($rowA['id']);
                $user['addresses'][] = $rowA;
            }
            $stmtAddr->close();
        } else {
            $user['addresses'] = [];
        }

        // Sessions
        $stmtSess = $db->prepare("SELECT * FROM user_sessions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
        if ($stmtSess) {
            $stmtSess->bind_param("i", $id);
            $stmtSess->execute();
            $user['sessions'] = [];
            $resS = $stmtSess->get_result();
            while ($rowS = $resS->fetch_assoc()) {
                $rowS['encrypted_id'] = SecurityHelper::encryptId($rowS['id']);
                $user['sessions'][] = $rowS;
            }
            $stmtSess->close();
        } else {
            $user['sessions'] = [];
        }

        // Full Order History with Order Items
        $stmtOrders = $db->prepare("SELECT *, total_amount as grand_total FROM orders WHERE user_id = ? ORDER BY id DESC");
        if ($stmtOrders) {
            $stmtOrders->bind_param("i", $id);
            $stmtOrders->execute();
            $resO = $stmtOrders->get_result();
            $user['orders'] = [];
            while ($rowO = $resO->fetch_assoc()) {
                $rowO['encrypted_id'] = SecurityHelper::encryptId($rowO['id']);
                $orderId = (int)$rowO['id'];

                // Fetch items for this order
                $rowO['items'] = [];
                $stmtItems = $db->prepare("
                    SELECT oi.*, COALESCE(oi.product_name, p.name, 'Product Item') as name, COALESCE(p.primary_image, '') as img, oi.quantity as qty
                    FROM order_items oi 
                    LEFT JOIN products p ON oi.product_id = p.id 
                    WHERE oi.order_id = ?
                ");
                if ($stmtItems) {
                    $stmtItems->bind_param("i", $orderId);
                    $stmtItems->execute();
                    $resItems = $stmtItems->get_result();
                    while ($itemRow = $resItems->fetch_assoc()) {
                        $itemRow['qty'] = (int)($itemRow['quantity'] ?? 1);
                        $itemRow['unit_price'] = (float)($itemRow['unit_price'] ?? 0);
                        $itemRow['total_price'] = (float)($itemRow['total_price'] ?? ($itemRow['unit_price'] * $itemRow['qty']));
                        $rowO['items'][] = $itemRow;
                    }
                    $stmtItems->close();
                }

                $user['orders'][] = $rowO;
            }
            $stmtOrders->close();
        } else {
            $user['orders'] = [];
        }

        // Customer Product Reviews
        $stmtRev = $db->prepare("
            SELECT r.*, p.name as product_name 
            FROM reviews r 
            LEFT JOIN products p ON r.product_id = p.id 
            WHERE r.user_id = ? 
            ORDER BY r.id DESC
        ");
        if ($stmtRev) {
            $stmtRev->bind_param("i", $id);
            $stmtRev->execute();
            $user['reviews'] = [];
            $resR = $stmtRev->get_result();
            while ($rowR = $resR->fetch_assoc()) {
                $rowR['encrypted_id'] = SecurityHelper::encryptId($rowR['id']);
                $rowR['encrypted_product_id'] = SecurityHelper::encryptId($rowR['product_id']);
                $user['reviews'][] = $rowR;
            }
            $stmtRev->close();
        } else {
            $user['reviews'] = [];
        }

        // User Audit Logs
        $stmtAudit = $db->prepare("
            SELECT * FROM audit_logs 
            WHERE user_id = ? OR (model = 'User' AND model_id = ?) 
            ORDER BY id DESC LIMIT 20
        ");
        if ($stmtAudit) {
            $stmtAudit->bind_param("ii", $id, $id);
            $stmtAudit->execute();
            $user['audit_trail'] = $stmtAudit->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtAudit->close();
        } else {
            $user['audit_trail'] = [];
        }

        return $user;
    }

    /**
     * Create a new user.
     */
    public static function createUser(array $data): ?int {
        $db = Database::connect();

        $email = strtolower(trim($data['email'] ?? ''));
        $name = trim($data['name'] ?? '');
        $phone = trim($data['phone'] ?? null);
        $gender = $data['gender'] ?? null;
        $dob = !empty($data['date_of_birth']) ? $data['date_of_birth'] : null;
        $avatarUrl = !empty($data['avatar_url']) ? trim($data['avatar_url']) : null;
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        $isVerified = isset($data['is_verified']) ? (int)$data['is_verified'] : 0;
        $roleId = (int)($data['role_id'] ?? 3); // 3 = customer

        $password = $data['password'] ?? 'User@123456';
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $stmt = $db->prepare("
            INSERT INTO users (uuid, name, email, phone, password_hash, avatar_url, gender, date_of_birth, is_active, is_verified, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        if (!$stmt) return null;

        $stmt->bind_param("ssssssssii", $uuid, $name, $email, $phone, $passwordHash, $avatarUrl, $gender, $dob, $isActive, $isVerified);
        $res = $stmt->execute();
        $userId = $stmt->insert_id;
        $stmt->close();

        if ($res && $userId) {
            // Assign Role
            $db->query("DELETE FROM user_roles WHERE user_id = {$userId}");
            $stmtRole = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            if ($stmtRole) {
                $stmtRole->bind_param("ii", $userId, $roleId);
                $stmtRole->execute();
                $stmtRole->close();
            }
        }

        return $res ? $userId : null;
    }

    /**
     * Update existing user details.
     */
    public static function updateUser(int $id, array $data): bool {
        $db = Database::connect();

        $name = trim($data['name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $phone = trim($data['phone'] ?? null);
        $gender = $data['gender'] ?? null;
        $dob = !empty($data['date_of_birth']) ? $data['date_of_birth'] : null;
        $avatarUrl = !empty($data['avatar_url']) ? trim($data['avatar_url']) : null;
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        $isVerified = isset($data['is_verified']) ? (int)$data['is_verified'] : 0;
        $roleId = (int)($data['role_id'] ?? 3);

        if (!empty($data['password'])) {
            $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);
            $stmt = $db->prepare("
                UPDATE users
                SET name = ?, email = ?, phone = ?, password_hash = ?, avatar_url = ?, gender = ?, date_of_birth = ?, is_active = ?, is_verified = ?, updated_at = NOW()
                WHERE id = ?
            ");
            if (!$stmt) return false;
            $stmt->bind_param("sssssssiii", $name, $email, $phone, $passwordHash, $avatarUrl, $gender, $dob, $isActive, $isVerified, $id);
        } else {
            $stmt = $db->prepare("
                UPDATE users
                SET name = ?, email = ?, phone = ?, avatar_url = ?, gender = ?, date_of_birth = ?, is_active = ?, is_verified = ?, updated_at = NOW()
                WHERE id = ?
            ");
            if (!$stmt) return false;
            $stmt->bind_param("ssssssiii", $name, $email, $phone, $avatarUrl, $gender, $dob, $isActive, $isVerified, $id);
        }

        $res = $stmt->execute();
        $stmt->close();

        // Update assigned role
        if ($res && $roleId > 0) {
            $db->query("DELETE FROM user_roles WHERE user_id = {$id}");
            $stmtRole = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            if ($stmtRole) {
                $stmtRole->bind_param("ii", $id, $roleId);
                $stmtRole->execute();
                $stmtRole->close();
            }
        }

        return $res;
    }

    /**
     * Toggle active state.
     */
    public static function toggleUserActive(int $id): bool {
        $db = Database::connect();
        return $db->query("UPDATE users SET is_active = 1 - is_active WHERE id = {$id}");
    }

    /**
     * Soft delete user.
     */
    public static function deleteUser(int $id): bool {
        $db = Database::connect();
        return $db->query("UPDATE users SET deleted_at = NOW() WHERE id = {$id}");
    }

    /**
     * Update customer profile info.
     */
    public static function updateCustomerProfile(int $userId, array $data): bool {
        $db = Database::connect();
        $name = trim($data['name'] ?? '');
        $phone = trim($data['phone'] ?? '');
        $gender = !empty($data['gender']) ? trim($data['gender']) : null;
        $dob = !empty($data['date_of_birth']) ? trim($data['date_of_birth']) : null;
        $avatarUrl = !empty($data['avatar_url']) ? trim($data['avatar_url']) : null;

        if (empty($name)) return false;

        $stmt = $db->prepare("
            UPDATE users 
            SET name = ?, phone = ?, gender = ?, date_of_birth = ?, avatar_url = COALESCE(?, avatar_url), updated_at = NOW()
            WHERE id = ? AND deleted_at IS NULL
        ");
        if (!$stmt) return false;

        $stmt->bind_param("sssssi", $name, $phone, $gender, $dob, $avatarUrl, $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    /**
     * Change customer password.
     */
    public static function updateCustomerPassword(int $userId, string $currentPassword, string $newPassword): array {
        $db = Database::connect();
        $user = self::findById($userId);
        if (!$user) return ['success' => false, 'message' => 'User account not found.'];

        // Verify current password
        $valid = password_verify($currentPassword, $user['password_hash']);
        if (!$valid && strpos($user['password_hash'], '$2y$') !== 0) {
            $valid = ($currentPassword === $user['password_hash']);
        }

        if (!$valid) {
            return ['success' => false, 'message' => 'Current password is incorrect.'];
        }

        if (strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'New password must be at least 6 characters.'];
        }

        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
        if (!$stmt) return ['success' => false, 'message' => 'Database error.'];
        $stmt->bind_param("si", $newHash, $userId);
        $ok = $stmt->execute();
        $stmt->close();

        return ['success' => $ok, 'message' => $ok ? 'Password updated successfully!' : 'Failed to update password.'];
    }

    /**
     * Update customer avatar URL.
     */
    public static function updateCustomerAvatar(int $userId, ?string $avatarUrl): bool {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE users SET avatar_url = ?, updated_at = NOW() WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("si", $avatarUrl, $userId);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}


