<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class Product {
    /**
     * Get paginated products list with filtering and search.
     */
    public static function getPaginatedProducts(
        int $page = 1,
        int $perPage = 10,
        string $category = '',
        string $status = '',
        string $stock = '',
        string $featured = '',
        string $search = ''
    ): array {
        $db = Database::connect();
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $where = ["1=1"];
        $params = [];
        $types = "";

        if ($status === 'deleted') {
            $where[] = "p.deleted_at IS NOT NULL";
        } else {
            $where[] = "p.deleted_at IS NULL";
            if ($status === 'active') {
                $where[] = "p.is_active = 1";
            } elseif ($status === 'inactive') {
                $where[] = "p.is_active = 0";
            }
        }

        if (!empty($category)) {
            if (is_numeric($category)) {
                $where[] = "p.category_id = ?";
                $params[] = (int)$category;
                $types .= "i";
            } else {
                $where[] = "c.name = ?";
                $params[] = $category;
                $types .= "s";
            }
        }

        if ($stock === 'in') {
            $where[] = "p.is_in_stock = 1";
        } elseif ($stock === 'out') {
            $where[] = "p.is_in_stock = 0";
        }

        if ($featured === 'yes') {
            $where[] = "p.is_featured = 1";
        } elseif ($featured === 'no') {
            $where[] = "p.is_featured = 0";
        }

        if (!empty($search)) {
            $sTerm = '%' . $search . '%';
            $where[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.uuid LIKE ?)";
            $params[] = $sTerm;
            $params[] = $sTerm;
            $params[] = $sTerm;
            $types .= "sss";
        }

        $whereClause = implode(" AND ", $where);

        // Count query
        $countSql = "
            SELECT COUNT(*) as total 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE {$whereClause}
        ";
        $stmtCount = $db->prepare($countSql);
        if (!empty($types)) {
            $stmtCount->bind_param($types, ...$params);
        }
        $stmtCount->execute();
        $totalCount = (int)($stmtCount->get_result()->fetch_assoc()['total'] ?? 0);
        $stmtCount->close();

        $totalPages = (int)ceil($totalCount / $perPage) ?: 1;

        // Main Query
        $sql = "
            SELECT p.id, p.uuid, p.name, p.slug, p.sku, p.category_id, p.base_price, p.sale_price,
                   p.cost_price, p.is_active, p.is_featured, p.is_in_stock, p.stock_qty, p.review_count,
                   p.average_rating, p.total_sold, p.created_at, p.deleted_at,
                   c.name as category_name,
                   (SELECT image_url FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as primary_image,
                   (SELECT COUNT(*) FROM product_variants WHERE product_id = p.id) as variant_count
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereClause}
            ORDER BY p.id DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $perPage;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        $products = [];
        while ($row = $res->fetch_assoc()) {
            $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
            $row['category_encrypted_id'] = SecurityHelper::encryptId($row['category_id']);
            $products[] = $row;
        }
        $stmt->close();

        return [
            'products' => $products,
            'totalCount' => $totalCount,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'perPage' => $perPage
        ];
    }

    /**
     * Get all products with optional filters and limit.
     */
    public static function getAllProducts(array $options = []): array {
        $db = Database::connect();
        $limit = isset($options['limit']) ? (int)$options['limit'] : 500;
        $where = ["p.deleted_at IS NULL"];

        if (!empty($options['status'])) {
            if ($options['status'] === 'active') {
                $where[] = "p.is_active = 1";
            } elseif ($options['status'] === 'inactive') {
                $where[] = "p.is_active = 0";
            }
        }

        if (!empty($options['category_id'])) {
            $catId = (int)$options['category_id'];
            $where[] = "p.category_id = {$catId}";
        }

        $whereClause = implode(" AND ", $where);
        $sql = "
            SELECT p.id, p.uuid, p.name, p.slug, p.sku, p.category_id, p.base_price, p.sale_price,
                   p.cost_price, p.is_active, p.is_featured, p.is_in_stock, p.stock_qty, p.review_count,
                   p.average_rating, p.total_sold, p.created_at, p.deleted_at,
                   c.name as category_name,
                   (SELECT image_url FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as primary_image,
                   (SELECT COUNT(*) FROM product_variants WHERE product_id = p.id) as variant_count
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereClause}
            ORDER BY p.name ASC
            LIMIT {$limit}
        ";

        $res = $db->query($sql);
        $products = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
                $row['category_encrypted_id'] = SecurityHelper::encryptId($row['category_id']);
                $products[] = $row;
            }
        }

        return [
            'products' => $products,
            'totalCount' => count($products)
        ];
    }

    /**
     * Get single product by raw ID.
     */
    public static function getProductById(int $id): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = ?
            LIMIT 1
        ");
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$product) return null;

        $product['encrypted_id'] = SecurityHelper::encryptId($product['id']);
        $product['category_encrypted_id'] = SecurityHelper::encryptId($product['category_id']);

        // Fetch Images
        $stmtImg = $db->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC");
        if ($stmtImg) {
            $stmtImg->bind_param("i", $id);
            $stmtImg->execute();
            $product['images'] = $stmtImg->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtImg->close();
        } else {
            $product['images'] = [];
        }

        // Fetch Videos
        $stmtVid = $db->prepare("SELECT * FROM product_videos WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
        if ($stmtVid) {
            $stmtVid->bind_param("i", $id);
            $stmtVid->execute();
            $product['videos'] = $stmtVid->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtVid->close();
        } else {
            $product['videos'] = [];
        }

        // Fetch Tags
        $stmtTag = $db->prepare("SELECT t.* FROM tags t JOIN product_tags pt ON t.id = pt.tag_id WHERE pt.product_id = ?");
        if ($stmtTag) {
            $stmtTag->bind_param("i", $id);
            $stmtTag->execute();
            $product['tags'] = $stmtTag->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtTag->close();
        } else {
            $product['tags'] = [];
        }

        // Fetch Specifications (Custom Attributes)
        $stmtSpec = $db->prepare("SELECT * FROM product_specifications WHERE product_id = ? ORDER BY sort_order ASC, id ASC");
        if ($stmtSpec) {
            $stmtSpec->bind_param("i", $id);
            $stmtSpec->execute();
            $product['specifications'] = $stmtSpec->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtSpec->close();
        } else {
            $product['specifications'] = [];
        }

        // Fetch Variants
        $product['variants'] = ProductVariant::getVariantsByProductId($id);

        return $product;
    }

    /**
     * Create a new product.
     */
    public static function createProduct(array $data): ?int {
        $db = Database::connect();
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $name = trim($data['name'] ?? '');
        $slug = trim($data['slug'] ?? '');
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
        }
        $sku = trim($data['sku'] ?? '');
        if (empty($sku)) {
            $sku = 'CC-PRD-' . strtoupper(substr(md5(uniqid()), 0, 6));
        }

        $categoryId = (int)($data['category_id'] ?? 1);
        $basePrice = (float)($data['base_price'] ?? 0);
        $salePrice = isset($data['sale_price']) && $data['sale_price'] !== '' ? (float)$data['sale_price'] : null;
        $costPrice = isset($data['cost_price']) && $data['cost_price'] !== '' ? (float)$data['cost_price'] : null;
        $shortDesc = $data['short_description'] ?? null;
        $desc = $data['description'] ?? null;
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        $isFeatured = isset($data['is_featured']) ? (int)$data['is_featured'] : 0;
        $isInStock = isset($data['is_in_stock']) ? (int)$data['is_in_stock'] : 1;

        $unitId = isset($data['unit_id']) && $data['unit_id'] !== '' ? (int)$data['unit_id'] : null;
        $unitValue = isset($data['unit_value']) && $data['unit_value'] !== '' ? (float)$data['unit_value'] : null;
        $weightGrams = isset($data['weight_grams']) && $data['weight_grams'] !== '' ? (int)$data['weight_grams'] : null;
        $lengthMm = isset($data['length_mm']) && $data['length_mm'] !== '' ? (int)$data['length_mm'] : null;
        $widthMm = isset($data['width_mm']) && $data['width_mm'] !== '' ? (int)$data['width_mm'] : null;
        $heightMm = isset($data['height_mm']) && $data['height_mm'] !== '' ? (int)$data['height_mm'] : null;

        $metaTitle = $data['meta_title'] ?? null;
        $metaDescription = $data['meta_description'] ?? null;
        $sortOrder = (int)($data['sort_order'] ?? 0);
        $currency = trim($data['currency'] ?? 'INR');
        $taxRate = (float)($data['tax_rate'] ?? 0.00);

        $stmt = $db->prepare("
            INSERT INTO products (uuid, category_id, name, slug, sku, short_description, description, base_price, sale_price, cost_price, is_active, is_featured, is_in_stock, unit_id, unit_value, weight_grams, length_mm, width_mm, height_mm, meta_title, meta_description, sort_order, currency, tax_rate, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        if (!$stmt) return null;

        $stmt->bind_param("sisssssdddiiiiiiiissiisd", $uuid, $categoryId, $name, $slug, $sku, $shortDesc, $desc, $basePrice, $salePrice, $costPrice, $isActive, $isFeatured, $isInStock, $unitId, $unitValue, $weightGrams, $lengthMm, $widthMm, $heightMm, $metaTitle, $metaDescription, $sortOrder, $currency, $taxRate);
        $res = $stmt->execute();
        $productId = $stmt->insert_id;
        $stmt->close();

        if ($res) {
            // Sync Product Images
            $imagesList = $data['images'] ?? (!empty($data['image_url']) ? [$data['image_url']] : []);
            if (!empty($imagesList)) {
                $db->query("DELETE FROM product_images WHERE product_id = {$productId}");
                $stmtImg = $db->prepare("INSERT INTO product_images (product_id, image_url, is_primary, sort_order) VALUES (?, ?, ?, ?)");
                if ($stmtImg) {
                    foreach ($imagesList as $sortIdx => $url) {
                        $isPrimary = ($sortIdx === 0) ? 1 : 0;
                        $stmtImg->bind_param("isii", $productId, $url, $isPrimary, $sortIdx);
                        $stmtImg->execute();
                    }
                    $stmtImg->close();
                }
            }

            // Sync Product Videos
            if (!empty($data['video_url'])) {
                $db->query("DELETE FROM product_videos WHERE product_id = {$productId}");
                $vidUrl = trim($data['video_url']);
                $stmtVid = $db->prepare("INSERT INTO product_videos (product_id, video_type, video_url, title) VALUES (?, 'upload', ?, 'Product Video')");
                if ($stmtVid) {
                    $stmtVid->bind_param("is", $productId, $vidUrl);
                    $stmtVid->execute();
                    $stmtVid->close();
                }
            }

            // Sync Tags
            if (isset($data['tag_ids']) && is_array($data['tag_ids'])) {
                $db->query("DELETE FROM product_tags WHERE product_id = {$productId}");
                $stmtTag = $db->prepare("INSERT INTO product_tags (product_id, tag_id) VALUES (?, ?)");
                if ($stmtTag) {
                    foreach ($data['tag_ids'] as $tagId) {
                        $tagIdInt = (int)$tagId;
                        if ($tagIdInt > 0) {
                            $stmtTag->bind_param("ii", $productId, $tagIdInt);
                            $stmtTag->execute();
                        }
                    }
                    $stmtTag->close();
                }
            }

            // Sync Custom Specifications
            if (isset($data['specifications']) && is_array($data['specifications'])) {
                $db->query("DELETE FROM product_specifications WHERE product_id = {$productId}");
                $stmtSpec = $db->prepare("INSERT INTO product_specifications (product_id, spec_group, spec_key, spec_value, sort_order) VALUES (?, ?, ?, ?, ?)");
                if ($stmtSpec) {
                    foreach ($data['specifications'] as $sIdx => $spec) {
                        $group = trim($spec['group'] ?? 'General');
                        $key = trim($spec['key'] ?? '');
                        $val = trim($spec['value'] ?? '');
                        if (!empty($key)) {
                            $stmtSpec->bind_param("isssi", $productId, $group, $key, $val, $sIdx);
                            $stmtSpec->execute();
                        }
                    }
                    $stmtSpec->close();
                }
            }
        }

        return $res ? $productId : null;
    }

    /**
     * Update an existing product.
     */
    public static function updateProduct(int $id, array $data): bool {
        $db = Database::connect();

        $name = trim($data['name'] ?? '');
        $categoryId = (int)($data['category_id'] ?? 1);
        $basePrice = (float)($data['base_price'] ?? 0);
        $salePrice = isset($data['sale_price']) && $data['sale_price'] !== '' ? (float)$data['sale_price'] : null;
        $costPrice = isset($data['cost_price']) && $data['cost_price'] !== '' ? (float)$data['cost_price'] : null;
        $sku = trim($data['sku'] ?? '');
        $slug = trim($data['slug'] ?? '');
        $shortDesc = $data['short_description'] ?? null;
        $desc = $data['description'] ?? null;
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        $isFeatured = isset($data['is_featured']) ? (int)$data['is_featured'] : 0;
        $isInStock = isset($data['is_in_stock']) ? (int)$data['is_in_stock'] : 1;

        $unitId = isset($data['unit_id']) && $data['unit_id'] !== '' ? (int)$data['unit_id'] : null;
        $unitValue = isset($data['unit_value']) && $data['unit_value'] !== '' ? (float)$data['unit_value'] : null;
        $weightGrams = isset($data['weight_grams']) && $data['weight_grams'] !== '' ? (int)$data['weight_grams'] : null;
        $lengthMm = isset($data['length_mm']) && $data['length_mm'] !== '' ? (int)$data['length_mm'] : null;
        $widthMm = isset($data['width_mm']) && $data['width_mm'] !== '' ? (int)$data['width_mm'] : null;
        $heightMm = isset($data['height_mm']) && $data['height_mm'] !== '' ? (int)$data['height_mm'] : null;

        $metaTitle = $data['meta_title'] ?? null;
        $metaDescription = $data['meta_description'] ?? null;
        $sortOrder = (int)($data['sort_order'] ?? 0);
        $currency = trim($data['currency'] ?? 'INR');
        $taxRate = (float)($data['tax_rate'] ?? 0.00);

        $stmt = $db->prepare("
            UPDATE products 
            SET name = ?, category_id = ?, base_price = ?, sale_price = ?, cost_price = ?, sku = ?, slug = ?, short_description = ?, description = ?, is_active = ?, is_featured = ?, is_in_stock = ?, unit_id = ?, unit_value = ?, weight_grams = ?, length_mm = ?, width_mm = ?, height_mm = ?, meta_title = ?, meta_description = ?, sort_order = ?, currency = ?, tax_rate = ?, updated_at = NOW()
            WHERE id = ?
        ");
        if (!$stmt) return false;

        $stmt->bind_param("sidddssssiiiiidiiiiissdi", $name, $categoryId, $basePrice, $salePrice, $costPrice, $sku, $slug, $shortDesc, $desc, $isActive, $isFeatured, $isInStock, $unitId, $unitValue, $weightGrams, $lengthMm, $widthMm, $heightMm, $metaTitle, $metaDescription, $sortOrder, $currency, $taxRate, $id);
        $res = $stmt->execute();
        $stmt->close();

        // Sync Product Images
        $imagesList = $data['images'] ?? (!empty($data['image_url']) ? [$data['image_url']] : []);
        $db->query("DELETE FROM product_images WHERE product_id = {$id}");
        if (!empty($imagesList)) {
            $stmtImg = $db->prepare("INSERT INTO product_images (product_id, image_url, is_primary, sort_order) VALUES (?, ?, ?, ?)");
            if ($stmtImg) {
                foreach ($imagesList as $sortIdx => $url) {
                    $isPrimary = ($sortIdx === 0) ? 1 : 0;
                    $stmtImg->bind_param("isii", $id, $url, $isPrimary, $sortIdx);
                    $stmtImg->execute();
                }
                $stmtImg->close();
            }
        }

        // Sync Product Videos
        if (isset($data['video_url'])) {
            $db->query("DELETE FROM product_videos WHERE product_id = {$id}");
            $vidUrl = trim($data['video_url']);
            if (!empty($vidUrl)) {
                $stmtVid = $db->prepare("INSERT INTO product_videos (product_id, video_type, video_url, title) VALUES (?, 'upload', ?, 'Product Video')");
                if ($stmtVid) {
                    $stmtVid->bind_param("is", $id, $vidUrl);
                    $stmtVid->execute();
                    $stmtVid->close();
                }
            }
        }

        // Sync Tags
        if (isset($data['tag_ids']) && is_array($data['tag_ids'])) {
            $db->query("DELETE FROM product_tags WHERE product_id = {$id}");
            $stmtTag = $db->prepare("INSERT INTO product_tags (product_id, tag_id) VALUES (?, ?)");
            if ($stmtTag) {
                foreach ($data['tag_ids'] as $tagId) {
                    $tagIdInt = (int)$tagId;
                    if ($tagIdInt > 0) {
                        $stmtTag->bind_param("ii", $id, $tagIdInt);
                        $stmtTag->execute();
                    }
                }
                $stmtTag->close();
            }
        }

        // Sync Custom Specifications
        if (isset($data['specifications']) && is_array($data['specifications'])) {
            $db->query("DELETE FROM product_specifications WHERE product_id = {$id}");
            $stmtSpec = $db->prepare("INSERT INTO product_specifications (product_id, spec_group, spec_key, spec_value, sort_order) VALUES (?, ?, ?, ?, ?)");
            if ($stmtSpec) {
                foreach ($data['specifications'] as $sIdx => $spec) {
                    $group = trim($spec['group'] ?? 'General');
                    $key = trim($spec['key'] ?? '');
                    $val = trim($spec['value'] ?? '');
                    if (!empty($key)) {
                        $stmtSpec->bind_param("isssi", $id, $group, $key, $val, $sIdx);
                        $stmtSpec->execute();
                    }
                }
                $stmtSpec->close();
            }
        }

        return $res;
    }

    /**
     * Toggle product active state.
     */
    public static function toggleActive(int $id): bool {
        $db = Database::connect();
        return $db->query("UPDATE products SET is_active = 1 - is_active WHERE id = {$id}");
    }

    /**
     * Toggle product stock state.
     */
    public static function toggleStock(int $id): bool {
        $db = Database::connect();
        return $db->query("UPDATE products SET is_in_stock = 1 - is_in_stock WHERE id = {$id}");
    }

    /**
     * Toggle product featured state.
     */
    public static function toggleFeatured(int $id): bool {
        $db = Database::connect();
        return $db->query("UPDATE products SET is_featured = 1 - is_featured WHERE id = {$id}");
    }

    /**
     * Soft delete product.
     */
    public static function softDeleteProduct(int $id): bool {
        $db = Database::connect();
        return $db->query("UPDATE products SET deleted_at = NOW() WHERE id = {$id}");
    }

    /**
     * Restore soft-deleted product.
     */
    public static function restoreProduct(int $id): bool {
        $db = Database::connect();
        return $db->query("UPDATE products SET deleted_at = NULL WHERE id = {$id}");
    }

    /**
     * Execute bulk actions on selected product IDs.
     */
    public static function bulkAction(array $productIds, string $action): bool {
        if (empty($productIds)) return false;
        $db = Database::connect();
        $idsStr = implode(',', array_map('intval', $productIds));

        switch ($action) {
            case 'activate':
                return $db->query("UPDATE products SET is_active = 1 WHERE id IN ({$idsStr})");
            case 'deactivate':
                return $db->query("UPDATE products SET is_active = 0 WHERE id IN ({$idsStr})");
            case 'stock-in':
                return $db->query("UPDATE products SET is_in_stock = 1 WHERE id IN ({$idsStr})");
            case 'stock-out':
                return $db->query("UPDATE products SET is_in_stock = 0 WHERE id IN ({$idsStr})");
            case 'featured':
                return $db->query("UPDATE products SET is_featured = 1 WHERE id IN ({$idsStr})");
            case 'unfeatured':
                return $db->query("UPDATE products SET is_featured = 0 WHERE id IN ({$idsStr})");
            case 'soft-delete':
                return $db->query("UPDATE products SET deleted_at = NOW() WHERE id IN ({$idsStr})");
            case 'restore':
                return $db->query("UPDATE products SET deleted_at = NULL WHERE id IN ({$idsStr})");
        }
        return false;
    }

    /**
     * Get KPI Summary Metrics for Products.
     */
    public static function getKpiMetrics(): array {
        $db = Database::connect();
        
        $totalActive = 0;
        $featuredCount = 0;
        $outOfStock = 0;
        $deletedCount = 0;
        $withVariants = 0;
        $valuationSum = 0.0;

        $res = $db->query("SELECT COUNT(*) as cnt FROM products WHERE is_active = 1 AND deleted_at IS NULL");
        if ($res && $row = $res->fetch_assoc()) $totalActive = (int)$row['cnt'];

        $res = $db->query("SELECT COUNT(*) as cnt FROM products WHERE is_featured = 1 AND deleted_at IS NULL");
        if ($res && $row = $res->fetch_assoc()) $featuredCount = (int)$row['cnt'];

        $res = $db->query("SELECT COUNT(*) as cnt FROM products WHERE is_in_stock = 0 AND deleted_at IS NULL");
        if ($res && $row = $res->fetch_assoc()) $outOfStock = (int)$row['cnt'];

        $res = $db->query("SELECT COUNT(*) as cnt FROM products WHERE deleted_at IS NOT NULL");
        if ($res && $row = $res->fetch_assoc()) $deletedCount = (int)$row['cnt'];

        $res = $db->query("SELECT COUNT(DISTINCT product_id) as cnt FROM product_variants");
        if ($res && $row = $res->fetch_assoc()) $withVariants = (int)$row['cnt'];

        $res = $db->query("SELECT COALESCE(SUM(COALESCE(sale_price, base_price)), 0) as val FROM products WHERE is_active = 1 AND deleted_at IS NULL");
        if ($res && $row = $res->fetch_assoc()) $valuationSum = (float)$row['val'];

        return [
            'totalActive' => $totalActive,
            'featuredCount' => $featuredCount,
            'outOfStock' => $outOfStock,
            'deletedCount' => $deletedCount,
            'withVariants' => $withVariants,
            'valuationSum' => $valuationSum
        ];
    }

    /**
     * Get list of all categories for dropdown filter.
     */
    public static function getAllCategories(): array {
        $db = Database::connect();
        $res = $db->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name ASC");
        $cats = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
                $cats[] = $row;
            }
        }
        return $cats;
    }
}
