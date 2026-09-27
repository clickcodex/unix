<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class Tag {

    public static function getAll(): array {
        $db = Database::connect();
        $res = $db->query("
            SELECT t.*, COUNT(pt.product_id) as product_count 
            FROM tags t 
            LEFT JOIN product_tags pt ON pt.tag_id = t.id 
            GROUP BY t.id 
            ORDER BY t.name ASC
        ");
        $tags = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
                $tags[] = $row;
            }
        }
        return $tags;
    }

    public static function create(array $data): bool {
        $db = Database::connect();
        $name     = trim($data['name'] ?? '');
        $slug     = !empty($data['slug']) ? trim($data['slug']) : self::slugify($name);
        $tagType  = trim($data['tag_type'] ?? 'custom');
        $colorHex = trim($data['color_hex'] ?? '#4F46E5');
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        if (empty($name)) return false;

        $stmt = $db->prepare("INSERT INTO tags (name, slug, tag_type, color_hex, is_active) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param("ssssi", $name, $slug, $tagType, $colorHex, $isActive);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    public static function update(int $id, array $data): bool {
        $db = Database::connect();
        $name     = trim($data['name'] ?? '');
        $slug     = !empty($data['slug']) ? trim($data['slug']) : self::slugify($name);
        $tagType  = trim($data['tag_type'] ?? 'custom');
        $colorHex = trim($data['color_hex'] ?? '#4F46E5');
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        if (empty($name)) return false;

        $stmt = $db->prepare("UPDATE tags SET name = ?, slug = ?, tag_type = ?, color_hex = ?, is_active = ? WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("ssssii", $name, $slug, $tagType, $colorHex, $isActive, $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    public static function toggleActive(int $id): bool {
        $db = Database::connect();
        return $db->query("UPDATE tags SET is_active = 1 - is_active WHERE id = {$id}");
    }

    public static function delete(int $id): bool {
        $db = Database::connect();
        $db->query("DELETE FROM product_tags WHERE tag_id = {$id}");
        $stmt = $db->prepare("DELETE FROM tags WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    private static function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text ?: 'tag-' . time());
    }
}
