<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class UserAddress {

    /**
     * Get all saved addresses for a user.
     */
    public static function getAddresses(int $userId): array {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT * FROM user_addresses 
            WHERE user_id = ? 
            ORDER BY is_default DESC, id DESC
        ");
        if (!$stmt) return [];
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($items as &$item) {
            $item['encrypted_id'] = SecurityHelper::encryptId($item['id']);
        }
        return $items;
    }

    /**
     * Get single address by ID (owned by user).
     */
    public static function getAddressById(int $userId, int $addressId): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM user_addresses WHERE id = ? AND user_id = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("ii", $addressId, $userId);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($item) {
            $item['encrypted_id'] = SecurityHelper::encryptId($item['id']);
        }
        return $item ?: null;
    }

    /**
     * Clear default status for all user addresses.
     */
    private static function clearDefault(int $userId): void {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE user_addresses SET is_default = 0 WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
        }
    }

    /**
     * Create a new address for a user.
     */
    public static function createAddress(int $userId, array $data): array {
        $db = Database::connect();

        $label       = trim($data['label'] ?? 'Home');
        $name        = trim($data['recipient_name'] ?? '');
        $phone       = trim($data['phone'] ?? '');
        $line1       = trim($data['address_line1'] ?? '');
        $line2       = trim($data['address_line2'] ?? '');
        $city        = trim($data['city'] ?? '');
        $state       = trim($data['state'] ?? '');
        $country     = !empty($data['country']) ? trim($data['country']) : 'India';
        $pincode     = trim($data['postal_code'] ?? '');
        $isDefault   = !empty($data['is_default']) ? 1 : 0;

        if (empty($name) || empty($line1) || empty($city) || empty($state) || empty($pincode)) {
            return ['success' => false, 'message' => 'Please fill in all required fields.'];
        }

        // Check if user has no addresses yet -> make first one default
        $cnt = self::getCount($userId);
        if ($cnt === 0) {
            $isDefault = 1;
        }

        if ($isDefault) {
            self::clearDefault($userId);
        }

        $stmt = $db->prepare("
            INSERT INTO user_addresses 
            (user_id, label, recipient_name, phone, address_line1, address_line2, city, state, country, postal_code, is_default, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        if (!$stmt) return ['success' => false, 'message' => 'Database error.'];

        $stmt->bind_param("issssssssii", $userId, $label, $name, $phone, $line1, $line2, $city, $state, $country, $pincode, $isDefault);
        $ok = $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();

        return [
            'success' => $ok,
            'message' => $ok ? 'Address added successfully!' : 'Failed to add address.',
            'encrypted_id' => $ok ? SecurityHelper::encryptId($newId) : ''
        ];
    }

    /**
     * Update existing address.
     */
    public static function updateAddress(int $userId, int $addressId, array $data): array {
        $db = Database::connect();

        $existing = self::getAddressById($userId, $addressId);
        if (!$existing) {
            return ['success' => false, 'message' => 'Address not found.'];
        }

        $label     = trim($data['label'] ?? 'Home');
        $name      = trim($data['recipient_name'] ?? '');
        $phone     = trim($data['phone'] ?? '');
        $line1     = trim($data['address_line1'] ?? '');
        $line2     = trim($data['address_line2'] ?? '');
        $city      = trim($data['city'] ?? '');
        $state     = trim($data['state'] ?? '');
        $country   = !empty($data['country']) ? trim($data['country']) : 'India';
        $pincode   = trim($data['postal_code'] ?? '');
        $isDefault = !empty($data['is_default']) ? 1 : 0;

        if (empty($name) || empty($line1) || empty($city) || empty($state) || empty($pincode)) {
            return ['success' => false, 'message' => 'Please fill in all required fields.'];
        }

        if ($isDefault) {
            self::clearDefault($userId);
        }

        $stmt = $db->prepare("
            UPDATE user_addresses 
            SET label = ?, recipient_name = ?, phone = ?, address_line1 = ?, address_line2 = ?, 
                city = ?, state = ?, country = ?, postal_code = ?, is_default = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        if (!$stmt) return ['success' => false, 'message' => 'Database error.'];

        $stmt->bind_param("sssssssssiii", $label, $name, $phone, $line1, $line2, $city, $state, $country, $pincode, $isDefault, $addressId, $userId);
        $ok = $stmt->execute();
        $stmt->close();

        return ['success' => $ok, 'message' => $ok ? 'Address updated successfully!' : 'Failed to update address.'];
    }

    /**
     * Delete address by ID.
     */
    public static function deleteAddress(int $userId, int $addressId): bool {
        $db = Database::connect();

        $existing = self::getAddressById($userId, $addressId);
        if (!$existing) return false;

        $stmt = $db->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("ii", $addressId, $userId);
        $ok = $stmt->execute();
        $stmt->close();

        // If deleted address was default, set the newest remaining address as default
        if ($ok && !empty($existing['is_default'])) {
            $db->query("
                UPDATE user_addresses 
                SET is_default = 1 
                WHERE user_id = {$userId} 
                ORDER BY id DESC LIMIT 1
            ");
        }

        return $ok;
    }

    /**
     * Set selected address as default.
     */
    public static function setDefaultAddress(int $userId, int $addressId): bool {
        $db = Database::connect();

        $existing = self::getAddressById($userId, $addressId);
        if (!$existing) return false;

        self::clearDefault($userId);

        $stmt = $db->prepare("UPDATE user_addresses SET is_default = 1, updated_at = NOW() WHERE id = ? AND user_id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("ii", $addressId, $userId);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    /**
     * Count total addresses for a user.
     */
    public static function getCount(int $userId): int {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT COUNT(id) as cnt FROM user_addresses WHERE user_id = ?");
        if (!$stmt) return 0;
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int)($res['cnt'] ?? 0);
    }
}
