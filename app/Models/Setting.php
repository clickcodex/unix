<?php

namespace App\Models;

use App\Config\Database;
use Exception;

class Setting {

    /**
     * In-memory cache for loaded settings during a single request lifecycle.
     */
    private static array $cache = [];

    /**
     * Clear or reset the in-memory cache.
     */
    public static function clearCache(): void {
        self::$cache = [];
    }

    /**
     * Retrieve a setting value by key.
     * 
     * @param string $key Setting key name
     * @param mixed $default Fallback value if setting key does not exist
     * @return mixed Casted setting value or default
     */
    public static function get(string $key, $default = null) {
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }

        $db = Database::connect();
        $stmt = $db->prepare("SELECT setting_value, value_type FROM settings WHERE setting_key = ? LIMIT 1");
        if (!$stmt) {
            return $default;
        }

        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $castedValue = self::castValue($row['setting_value'], $row['value_type']);
            self::$cache[$key] = $castedValue;
            $stmt->close();
            return $castedValue;
        }

        $stmt->close();
        return $default;
    }

    /**
     * Check if a setting key exists in the database.
     */
    public static function has(string $key): bool {
        if (array_key_exists($key, self::$cache)) {
            return true;
        }

        $db = Database::connect();
        $stmt = $db->prepare("SELECT id FROM settings WHERE setting_key = ? LIMIT 1");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $key);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res->num_rows > 0;
        $stmt->close();

        return $exists;
    }

    /**
     * Get all settings as key => value pairs, optionally filtered by group.
     */
    public static function getAll(?string $group = null): array {
        $db = Database::connect();

        if (!empty($group)) {
            $stmt = $db->prepare("SELECT setting_key, setting_value, value_type FROM settings WHERE group_name = ? ORDER BY id ASC");
            $stmt->bind_param("s", $group);
        } else {
            $stmt = $db->prepare("SELECT setting_key, setting_value, value_type FROM settings ORDER BY id ASC");
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $settings = [];

        while ($row = $res->fetch_assoc()) {
            $val = self::castValue($row['setting_value'], $row['value_type']);
            $settings[$row['setting_key']] = $val;
            self::$cache[$row['setting_key']] = $val;
        }

        $stmt->close();
        return $settings;
    }

    /**
     * Get settings belonging to a specific group.
     */
    public static function getByGroup(string $group): array {
        return self::getAll($group);
    }

    /**
     * Get all public settings (is_public = 1) for frontend exposure.
     */
    public static function getPublicSettings(): array {
        $db = Database::connect();
        $res = $db->query("SELECT setting_key, setting_value, value_type FROM settings WHERE is_public = 1 ORDER BY id ASC");

        $settings = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $val = self::castValue($row['setting_value'], $row['value_type']);
                $settings[$row['setting_key']] = $val;
                self::$cache[$row['setting_key']] = $val;
            }
        }

        return $settings;
    }

    /**
     * Get detailed setting records grouped by group_name.
     */
    public static function getGroupedSettings(): array {
        $db = Database::connect();
        $res = $db->query("SELECT * FROM settings ORDER BY group_name ASC, id ASC");

        $grouped = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $group = $row['group_name'] ?: 'general';
                $row['parsed_value'] = self::castValue($row['setting_value'], $row['value_type']);
                $grouped[$group][] = $row;
            }
        }

        return $grouped;
    }

    /**
     * Set or update a single setting by key.
     */
    public static function set(
        string $key,
        $value,
        ?string $type = null,
        ?string $group = null,
        ?string $label = null,
        ?bool $isPublic = null
    ): bool {
        $db = Database::connect();

        // Check if key already exists
        $existing = self::getRecordByKey($key);

        if ($existing) {
            // Updating existing record
            $valueType = $type ?: $existing['value_type'];
            $groupName = $group !== null ? $group : $existing['group_name'];
            $settingLabel = $label !== null ? $label : $existing['label'];
            $publicStatus = $isPublic !== null ? ($isPublic ? 1 : 0) : (int)$existing['is_public'];

            $storageValue = self::formatForStorage($value, $valueType);

            $stmt = $db->prepare("
                UPDATE settings 
                SET setting_value = ?, value_type = ?, group_name = ?, label = ?, is_public = ?, updated_at = NOW() 
                WHERE setting_key = ?
            ");
            $stmt->bind_param("ssssis", $storageValue, $valueType, $groupName, $settingLabel, $publicStatus, $key);
            $success = $stmt->execute();
            $stmt->close();

            if ($success) {
                self::$cache[$key] = self::castValue($storageValue, $valueType);
            }

            return $success;
        } else {
            // Inserting new record
            $valueType = $type ?: self::inferValueType($value);
            $groupName = $group ?: 'general';
            $settingLabel = $label ?: ucwords(str_replace('_', ' ', $key));
            $publicStatus = $isPublic ? 1 : 0;

            $storageValue = self::formatForStorage($value, $valueType);

            $stmt = $db->prepare("
                INSERT INTO settings (setting_key, setting_value, value_type, group_name, label, is_public, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->bind_param("sssssi", $key, $storageValue, $valueType, $groupName, $settingLabel, $publicStatus);
            $success = $stmt->execute();
            $stmt->close();

            if ($success) {
                self::$cache[$key] = self::castValue($storageValue, $valueType);
            }

            return $success;
        }
    }

    /**
     * Update multiple setting key-value pairs at once.
     * 
     * @param array $settings Key-value array of settings to update
     * @return bool True if all updates succeeded
     */
    public static function updateBulk(array $settings): bool {
        $db = Database::connect();
        $db->begin_transaction();

        try {
            foreach ($settings as $key => $val) {
                $existing = self::getRecordByKey($key);
                if ($existing) {
                    $type = $existing['value_type'];
                    $storageValue = self::formatForStorage($val, $type);

                    $stmt = $db->prepare("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
                    $stmt->bind_param("ss", $storageValue, $key);
                    $stmt->execute();
                    $stmt->close();

                    self::$cache[$key] = self::castValue($storageValue, $type);
                } else {
                    self::set($key, $val);
                }
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            return false;
        }
    }

    /**
     * Delete a setting key.
     */
    public static function delete(string $key): bool {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM settings WHERE setting_key = ?");
        $stmt->bind_param("s", $key);
        $success = $stmt->execute();
        $stmt->close();

        if ($success) {
            unset(self::$cache[$key]);
        }

        return $success;
    }

    /**
     * Fetch raw setting record array from database by key.
     */
    public static function getRecordByKey(string $key): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM settings WHERE setting_key = ? LIMIT 1");
        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("s", $key);
        $stmt->execute();
        $res = $stmt->get_result();
        $record = $res->fetch_assoc();
        $stmt->close();

        return $record ?: null;
    }

    /**
     * Cast string representation from DB to actual PHP data type.
     */
    private static function castValue(?string $val, string $type) {
        if ($val === null) {
            return null;
        }

        switch ($type) {
            case 'boolean':
                return filter_var($val, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int)$val;
            case 'decimal':
                return (float)$val;
            case 'json':
                $decoded = json_decode($val, true);
                return json_last_error() === JSON_ERROR_NONE ? $decoded : $val;
            case 'string':
            default:
                return (string)$val;
        }
    }

    /**
     * Format PHP value into DB string storage format based on value_type.
     */
    private static function formatForStorage($val, string $type): ?string {
        if ($val === null) {
            return null;
        }

        switch ($type) {
            case 'boolean':
                if (is_bool($val)) {
                    return $val ? 'true' : 'false';
                }
                return filter_var($val, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
            case 'json':
                return is_string($val) ? $val : json_encode($val, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            case 'integer':
                return (string)((int)$val);
            case 'decimal':
                return (string)((float)$val);
            case 'string':
            default:
                return (string)$val;
        }
    }

    /**
     * Infer DB value_type based on PHP variable type.
     */
    private static function inferValueType($val): string {
        if (is_bool($val)) {
            return 'boolean';
        }
        if (is_int($val)) {
            return 'integer';
        }
        if (is_float($val)) {
            return 'decimal';
        }
        if (is_array($val) || is_object($val)) {
            return 'json';
        }
        return 'string';
    }
}
