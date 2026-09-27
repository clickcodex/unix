<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class Unit {

    public static function getAll(): array {
        $db = Database::connect();
        $res = $db->query("SELECT * FROM units ORDER BY unit_type ASC, name ASC");
        $units = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
                $units[] = $row;
            }
        }
        return $units;
    }

    public static function getById(int $id): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM units WHERE id = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($row) {
            $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
        }
        return $row ?: null;
    }

    public static function create(array $data): bool {
        $db = Database::connect();
        $name     = trim($data['name'] ?? '');
        $symbol   = trim($data['symbol'] ?? '');
        $unitType = trim($data['unit_type'] ?? 'count');

        if (empty($name) || empty($symbol)) return false;

        $stmt = $db->prepare("INSERT INTO units (name, symbol, unit_type) VALUES (?, ?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param("sss", $name, $symbol, $unitType);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    public static function update(int $id, array $data): bool {
        $db = Database::connect();
        $name     = trim($data['name'] ?? '');
        $symbol   = trim($data['symbol'] ?? '');
        $unitType = trim($data['unit_type'] ?? 'count');

        if (empty($name) || empty($symbol)) return false;

        $stmt = $db->prepare("UPDATE units SET name = ?, symbol = ?, unit_type = ? WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("sssi", $name, $symbol, $unitType, $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    public static function delete(int $id): bool {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM units WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }
}
