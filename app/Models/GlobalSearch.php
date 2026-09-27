<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class GlobalSearch {

    /**
     * Perform global multi-entity search across products, orders, users, categories, coupons, banners, media, and audit logs.
     */
    public static function searchAll(string $query, int $limitPerType = 5): array {
        $q = trim($query);
        if (empty($q)) {
            return [
                'products' => [],
                'orders' => [],
                'users' => [],
                'categories' => [],
                'coupons' => [],
                'banners' => [],
                'media' => [],
                'audit_logs' => [],
                'total_count' => 0
            ];
        }

        $db = Database::connect();
        $like = "%{$q}%";

        $results = [
            'products' => self::searchProducts($db, $like, $limitPerType),
            'orders' => self::searchOrders($db, $like, $limitPerType),
            'users' => self::searchUsers($db, $like, $limitPerType),
            'categories' => self::searchCategories($db, $like, $limitPerType),
            'coupons' => self::searchCoupons($db, $like, $limitPerType),
            'banners' => self::searchBanners($db, $like, $limitPerType),
            'media' => self::searchMedia($db, $like, $limitPerType),
            'audit_logs' => self::searchAuditLogs($db, $like, $limitPerType),
        ];

        $totalCount = 0;
        foreach ($results as $items) {
            $totalCount += count($items);
        }
        $results['total_count'] = $totalCount;

        return $results;
    }

    private static function searchProducts($db, string $like, int $limit): array {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $sql = "SELECT id, name, sku, base_price, is_active FROM products WHERE (name LIKE ? OR sku LIKE ? OR short_description LIKE ?) AND deleted_at IS NULL ORDER BY id DESC LIMIT ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("sssi", $like, $like, $like, $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
            $row['url'] = $baseUrl . '/admin/products/edit/' . $row['encrypted_id'];
            $list[] = $row;
        }
        $stmt->close();
        return $list;
    }

    private static function searchOrders($db, string $like, int $limit): array {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $sql = "SELECT id, order_number, grand_total, status, payment_status, created_at FROM orders WHERE (order_number LIKE ? OR status LIKE ? OR shipping_address LIKE ?) ORDER BY id DESC LIMIT ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("sssi", $like, $like, $like, $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
            $row['url'] = $baseUrl . '/admin/orders?search=' . urlencode($row['order_number']);
            $list[] = $row;
        }
        $stmt->close();
        return $list;
    }

    private static function searchUsers($db, string $like, int $limit): array {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $sql = "SELECT id, name, email, phone, is_active FROM users WHERE (name LIKE ? OR email LIKE ? OR phone LIKE ?) AND deleted_at IS NULL ORDER BY id DESC LIMIT ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("sssi", $like, $like, $like, $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
            $row['url'] = $baseUrl . '/admin/users?search=' . urlencode($row['email']);
            $list[] = $row;
        }
        $stmt->close();
        return $list;
    }

    private static function searchCategories($db, string $like, int $limit): array {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $sql = "SELECT id, name, slug, is_active FROM categories WHERE (name LIKE ? OR slug LIKE ?) ORDER BY id DESC LIMIT ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("ssi", $like, $like, $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
            $row['url'] = $baseUrl . '/admin/categories?search=' . urlencode($row['name']);
            $list[] = $row;
        }
        $stmt->close();
        return $list;
    }

    private static function searchCoupons($db, string $like, int $limit): array {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $sql = "SELECT id, code, discount_type, discount_value, is_active FROM coupons WHERE (code LIKE ? OR description LIKE ?) ORDER BY id DESC LIMIT ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("ssi", $like, $like, $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
            $row['url'] = $baseUrl . '/admin/coupons?search=' . urlencode($row['code']);
            $list[] = $row;
        }
        $stmt->close();
        return $list;
    }

    private static function searchBanners($db, string $like, int $limit): array {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $sql = "SELECT id, title, banner_type, image_url, is_active FROM banners WHERE (title LIKE ? OR banner_type LIKE ?) ORDER BY id DESC LIMIT ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("ssi", $like, $like, $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
            $row['url'] = $baseUrl . '/admin/banners?search=' . urlencode($row['title']);
            $list[] = $row;
        }
        $stmt->close();
        return $list;
    }

    private static function searchMedia($db, string $like, int $limit): array {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $sql = "SELECT id, original_name, mime_type, public_url FROM media WHERE (original_name LIKE ? OR alt_text LIKE ?) ORDER BY id DESC LIMIT ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("ssi", $like, $like, $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
            $row['url'] = $baseUrl . '/admin/media?search=' . urlencode($row['original_name']);
            $list[] = $row;
        }
        $stmt->close();
        return $list;
    }

    private static function searchAuditLogs($db, string $like, int $limit): array {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $sql = "SELECT id, event, model, model_id, created_at FROM audit_logs WHERE (event LIKE ? OR model LIKE ?) ORDER BY id DESC LIMIT ?";
        $stmt = $db->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("ssi", $like, $like, $limit);
        $stmt->execute();
        $res = $stmt->get_result();
        $list = [];
        while ($row = $res->fetch_assoc()) {
            $row['url'] = $baseUrl . '/admin/audit?search=' . urlencode($row['event']);
            $list[] = $row;
        }
        $stmt->close();
        return $list;
    }
}
