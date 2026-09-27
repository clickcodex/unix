<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class Category {
    /**
     * Get all categories in flat format.
     */
    public static function getFlatCategories(): array {
        $db = Database::connect();
        $res = $db->query("SELECT * FROM categories ORDER BY sort_order ASC, name ASC");
        if (!$res) return [];

        $categories = [];
        while ($row = $res->fetch_assoc()) {
            $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
            $row['parent_encrypted_id'] = $row['parent_id'] ? SecurityHelper::encryptId($row['parent_id']) : null;
            $categories[] = $row;
        }

        return $categories;
    }

    /**
     * Build nested category tree hierarchy.
     */
    public static function getCategoryTree(): array {
        $flat = self::getFlatCategories();
        $db = Database::connect();

        // Get product counts per category
        $resCount = $db->query("SELECT category_id, COUNT(*) as cnt FROM products GROUP BY category_id");
        $productCounts = [];
        if ($resCount) {
            while ($row = $resCount->fetch_assoc()) {
                $productCounts[$row['category_id']] = (int)$row['cnt'];
            }
        }

        // Map items by ID
        $itemsByParent = [];
        foreach ($flat as &$cat) {
            $catId = (int)$cat['id'];
            $cat['product_count'] = $productCounts[$catId] ?? 0;
            $parentId = $cat['parent_id'] ? (int)$cat['parent_id'] : 0;
            $itemsByParent[$parentId][] = &$cat;
        }

        return self::buildTreeNodes($itemsByParent, 0, 1);
    }

    private static function buildTreeNodes(array &$itemsByParent, int $parentId, int $depth): array {
        if (empty($itemsByParent[$parentId])) {
            return [];
        }

        $branch = [];
        foreach ($itemsByParent[$parentId] as &$node) {
            $node['depth'] = $depth;
            $children = self::buildTreeNodes($itemsByParent, (int)$node['id'], $depth + 1);
            $node['children'] = $children;
            $node['children_count'] = count($children);
            $branch[] = $node;
        }

        return $branch;
    }

    /**
     * Get single category by ID with parent info and counts.
     */
    public static function getCategoryById(int $id): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT c.*, p.name as parent_name,
                   (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count,
                   (SELECT COUNT(*) FROM categories WHERE parent_id = c.id) as children_count
            FROM categories c
            LEFT JOIN categories p ON c.parent_id = p.id
            WHERE c.id = ?
            LIMIT 1
        ");
        if (!$stmt) return null;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $category = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$category) return null;

        $category['encrypted_id'] = SecurityHelper::encryptId($category['id']);
        $category['parent_encrypted_id'] = $category['parent_id'] ? SecurityHelper::encryptId($category['parent_id']) : null;
        return $category;
    }

    /**
     * Get Category KPI stats metrics.
     */
    public static function getKpiMetrics(): array {
        $db = Database::connect();
        $totalRes = $db->query("SELECT COUNT(*) as cnt FROM categories");
        $activeRes = $db->query("SELECT COUNT(*) as cnt FROM categories WHERE is_active = 1");
        $inactiveRes = $db->query("SELECT COUNT(*) as cnt FROM categories WHERE is_active = 0");

        $total = $totalRes ? (int)$totalRes->fetch_assoc()['cnt'] : 0;
        $active = $activeRes ? (int)$activeRes->fetch_assoc()['cnt'] : 0;
        $inactive = $inactiveRes ? (int)$inactiveRes->fetch_assoc()['cnt'] : 0;

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'maxDepth' => 3
        ];
    }

    /**
     * Create a new category.
     */
    public static function createCategory(array $data): ?int {
        $db = Database::connect();

        $name = trim($data['name'] ?? '');
        if (empty($name)) return null;

        $parentId = isset($data['parent_id']) && $data['parent_id'] > 0 ? (int)$data['parent_id'] : null;
        $slug = trim($data['slug'] ?? '');
        if (empty($slug)) {
            $slug = strtolower(preg_replace('/[^a-z0-9 -]/', '', str_replace(' ', '-', $name)));
        }

        $desc = $data['description'] ?? null;
        $imageUrl = $data['image_url'] ?? null;
        $icon = trim($data['icon'] ?? 'folder');
        if (empty($icon)) $icon = 'folder';
        $sortOrder = (int)($data['sort_order'] ?? 0);
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        $stmt = $db->prepare("
            INSERT INTO categories (parent_id, name, slug, description, image_url, icon, sort_order, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        if (!$stmt) return null;

        $stmt->bind_param("isssssii", $parentId, $name, $slug, $desc, $imageUrl, $icon, $sortOrder, $isActive);
        $res = $stmt->execute();
        $catId = $stmt->insert_id;
        $stmt->close();

        return $res ? $catId : null;
    }

    /**
     * Update existing category.
     */
    public static function updateCategory(int $id, array $data): bool {
        $db = Database::connect();

        $name = trim($data['name'] ?? '');
        if (empty($name)) return false;

        $parentId = isset($data['parent_id']) && $data['parent_id'] > 0 && (int)$data['parent_id'] !== $id ? (int)$data['parent_id'] : null;
        $slug = trim($data['slug'] ?? '');
        if (empty($slug)) {
            $slug = strtolower(preg_replace('/[^a-z0-9 -]/', '', str_replace(' ', '-', $name)));
        }

        $desc = $data['description'] ?? null;
        $imageUrl = $data['image_url'] ?? null;
        $icon = trim($data['icon'] ?? 'folder');
        if (empty($icon)) $icon = 'folder';
        $sortOrder = (int)($data['sort_order'] ?? 0);
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        $stmt = $db->prepare("
            UPDATE categories
            SET parent_id = ?, name = ?, slug = ?, description = ?, image_url = ?, icon = ?, sort_order = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ");
        if (!$stmt) return false;

        $stmt->bind_param("isssssiii", $parentId, $name, $slug, $desc, $imageUrl, $icon, $sortOrder, $isActive, $id);
        $res = $stmt->execute();
        $stmt->close();

        return $res;
    }

    /**
     * Toggle active state.
     */
    public static function toggleActive(int $id): bool {
        $db = Database::connect();
        return $db->query("UPDATE categories SET is_active = 1 - is_active WHERE id = {$id}");
    }

    /**
     * Delete category (reassign children to null and delete).
     */
    public static function deleteCategory(int $id): bool {
        $db = Database::connect();
        // Reassign children parent_id to NULL
        $db->query("UPDATE categories SET parent_id = NULL WHERE parent_id = {$id}");
        // Reassign products category_id to NULL or 1
        $db->query("UPDATE products SET category_id = 1 WHERE category_id = {$id}");
        return $db->query("DELETE FROM categories WHERE id = {$id}");
    }

    /**
     * Get single category by slug or ID with parent, children, and products.
     */
    public static function getCategoryBySlugOrId(string $identifier): ?array {
        $db = Database::connect();

        $decryptedId = SecurityHelper::decryptId($identifier);

        if ($decryptedId) {
            $stmt = $db->prepare("SELECT * FROM categories WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $decryptedId);
        } else if (is_numeric($identifier)) {
            $stmt = $db->prepare("SELECT * FROM categories WHERE id = ? LIMIT 1");
            $catId = (int)$identifier;
            $stmt->bind_param("i", $catId);
        } else {
            $stmt = $db->prepare("SELECT * FROM categories WHERE slug = ? LIMIT 1");
            $stmt->bind_param("s", $identifier);
        }

        if (!$stmt) return null;
        $stmt->execute();
        $cat = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$cat) return null;

        $cat['encrypted_id'] = SecurityHelper::encryptId($cat['id']);

        // Parent Category info
        if (!empty($cat['parent_id'])) {
            $stmtP = $db->prepare("SELECT id, name, slug FROM categories WHERE id = ? LIMIT 1");
            if ($stmtP) {
                $stmtP->bind_param("i", $cat['parent_id']);
                $stmtP->execute();
                $parent = $stmtP->get_result()->fetch_assoc();
                if ($parent) {
                    $parent['encrypted_id'] = SecurityHelper::encryptId($parent['id']);
                    $cat['parent'] = $parent;
                }
                $stmtP->close();
            }
        }

        // Subcategories
        $stmtSub = $db->prepare("
            SELECT c.*, (SELECT COUNT(*) FROM products WHERE category_id = c.id AND deleted_at IS NULL) as product_count 
            FROM categories c 
            WHERE c.parent_id = ? 
            ORDER BY c.sort_order ASC, c.name ASC
        ");
        if ($stmtSub) {
            $stmtSub->bind_param("i", $cat['id']);
            $stmtSub->execute();
            $subs = $stmtSub->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtSub->close();
            foreach ($subs as &$sub) {
                $sub['encrypted_id'] = SecurityHelper::encryptId($sub['id']);
            }
            $cat['children'] = $subs;
        } else {
            $cat['children'] = [];
        }

        // Gather category IDs (this category + subcategories)
        $catIds = [(int)$cat['id']];
        foreach ($cat['children'] as $child) {
            $catIds[] = (int)$child['id'];
        }

        $catIdsStr = implode(',', array_map('intval', $catIds));
        $sqlProd = "
            SELECT p.*, c.name as category_name, c.slug as category_slug,
                   (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.category_id IN ({$catIdsStr}) AND p.deleted_at IS NULL AND p.is_active = 1
            ORDER BY p.is_featured DESC, p.id DESC
        ";
        $resProd = $db->query($sqlProd);
        $products = [];
        if ($resProd) {
            while ($pRow = $resProd->fetch_assoc()) {
                $pRow['encrypted_id'] = SecurityHelper::encryptId($pRow['id']);
                $pRow['encrypted_product_id'] = $pRow['encrypted_id'];
                $pRow['discount_pct'] = (!empty($pRow['base_price']) && !empty($pRow['sale_price']) && $pRow['base_price'] > $pRow['sale_price'])
                    ? round((($pRow['base_price'] - $pRow['sale_price']) / $pRow['base_price']) * 100)
                    : 0;
                $products[] = $pRow;
            }
        }
        $cat['products'] = $products;
        $cat['total_products'] = count($products);

        return $cat;
    }
}
