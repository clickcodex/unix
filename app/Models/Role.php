<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class Role {

    public static function getAllRoles(): array {
        $db = Database::connect();
        $res = $db->query("
            SELECT r.*, COUNT(ur.user_id) as user_count 
            FROM roles r 
            LEFT JOIN user_roles ur ON ur.role_id = r.id 
            GROUP BY r.id 
            ORDER BY r.id ASC
        ");
        $roles = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
                $roles[] = $row;
            }
        }
        return $roles;
    }

    public static function getAllPermissionsGrouped(): array {
        $db = Database::connect();
        $res = $db->query("SELECT * FROM permissions ORDER BY module ASC, name ASC");
        $grouped = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
                $mod = $row['module'] ?: 'general';
                $grouped[$mod][] = $row;
            }
        }
        return $grouped;
    }

    public static function getRolePermissions(int $roleId): array {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
        if (!$stmt) return [];
        $stmt->bind_param("i", $roleId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return array_column($res, 'permission_id');
    }

    public static function createRole(string $name, string $slug): bool {
        $db = Database::connect();
        $stmt = $db->prepare("INSERT INTO roles (name, slug) VALUES (?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param("ss", $name, $slug);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    public static function updateRolePermissions(int $roleId, array $permissionIds): bool {
        $db = Database::connect();
        // Clear existing permissions for role
        $db->query("DELETE FROM role_permissions WHERE role_id = {$roleId}");

        if (empty($permissionIds)) return true;

        $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
        if (!$stmt) return false;

        foreach ($permissionIds as $pid) {
            $pidInt = (int)$pid;
            if ($pidInt > 0) {
                $stmt->bind_param("ii", $roleId, $pidInt);
                $stmt->execute();
            }
        }
        $stmt->close();
        return true;
    }
}
