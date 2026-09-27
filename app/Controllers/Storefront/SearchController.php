<?php

namespace App\Controllers\Storefront;

use App\Config\Database;
use App\Helpers\SecurityHelper;
use App\Models\SearchLog;

class SearchController {

    /**
     * Render the Product Search Results & Listing Page.
     */
    public function index(): void {
        $q = trim($_GET['q'] ?? $_GET['query'] ?? $_GET['search'] ?? '');
        $categorySlug = trim($_GET['category'] ?? '');
        $sort = trim($_GET['sort'] ?? 'relevance');
        $minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : null;
        $maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : null;
        $minRating = isset($_GET['rating']) && $_GET['rating'] !== '' ? (float)$_GET['rating'] : null;
        $inStockOnly = !empty($_GET['in_stock']);
        $minDiscount = isset($_GET['discount']) && $_GET['discount'] !== '' ? (float)$_GET['discount'] : null;

        $db = Database::connect();

        // 1. Build SQL Where Conditions
        $where = ["p.is_active = 1", "p.deleted_at IS NULL"];
        $params = [];
        $types = "";

        if (!empty($q)) {
            $sTerm = '%' . $q . '%';
            $where[] = "(p.name LIKE ? OR p.short_description LIKE ? OR p.description LIKE ? OR c.name LIKE ? OR p.sku LIKE ?)";
            $params[] = $sTerm;
            $params[] = $sTerm;
            $params[] = $sTerm;
            $params[] = $sTerm;
            $params[] = $sTerm;
            $types .= "sssss";
        }

        if (!empty($categorySlug)) {
            $where[] = "(c.slug = ? OR c.name = ?)";
            $params[] = $categorySlug;
            $params[] = $categorySlug;
            $types .= "ss";
        }

        if ($minPrice !== null) {
            $where[] = "COALESCE(p.sale_price, p.base_price) >= ?";
            $params[] = $minPrice;
            $types .= "d";
        }

        if ($maxPrice !== null) {
            $where[] = "COALESCE(p.sale_price, p.base_price) <= ?";
            $params[] = $maxPrice;
            $types .= "d";
        }

        if ($minRating !== null && $minRating > 0) {
            $where[] = "p.average_rating >= ?";
            $params[] = $minRating;
            $types .= "d";
        }

        if ($inStockOnly) {
            $where[] = "p.is_in_stock = 1";
        }

        if ($minDiscount !== null && $minDiscount > 0) {
            $where[] = "(p.sale_price IS NOT NULL AND p.sale_price < p.base_price AND ((p.base_price - p.sale_price) / p.base_price * 100) >= ?)";
            $params[] = $minDiscount;
            $types .= "d";
        }

        $whereClause = implode(" AND ", $where);

        // 2. Sorting Order
        $orderBy = "p.id DESC";
        switch ($sort) {
            case 'price_low':
                $orderBy = "COALESCE(p.sale_price, p.base_price) ASC";
                break;
            case 'price_high':
                $orderBy = "COALESCE(p.sale_price, p.base_price) DESC";
                break;
            case 'rating':
                $orderBy = "p.average_rating DESC, p.review_count DESC";
                break;
            case 'newest':
                $orderBy = "p.created_at DESC";
                break;
            case 'popularity':
                $orderBy = "p.total_sold DESC, p.review_count DESC";
                break;
            case 'relevance':
                if (!empty($q)) {
                    $escapedQ = $db->real_escape_string($q);
                    $orderBy = "CASE WHEN p.name LIKE '" . $escapedQ . "%' THEN 1 ELSE 2 END, p.id DESC";
                } else {
                    $orderBy = "p.sort_order ASC, p.id DESC";
                }
                break;
        }

        // 3. Query Matching Products
        $sql = "
            SELECT p.id, p.uuid, p.name, p.slug, p.sku, p.short_description, p.base_price, p.sale_price,
                   p.is_in_stock, p.is_featured, p.average_rating, p.review_count, p.total_sold,
                   c.name as category_name, c.slug as category_slug,
                   (SELECT image_url FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as primary_image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereClause}
            ORDER BY {$orderBy}
            LIMIT 60
        ";

        $products = [];
        $stmt = $db->prepare($sql);
        if ($stmt) {
            if (!empty($types)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
                $products[] = $row;
            }
            $stmt->close();
        }

        $resultCount = count($products);

        // 4. Log the Search Query for Analytics
        if (!empty($q)) {
            SearchLog::logSearch($q, $resultCount);
        }

        // 5. Get All Categories for Filter Options
        $categories = [];
        $catRes = $db->query("SELECT id, name, slug, icon FROM categories WHERE is_active = 1 ORDER BY sort_order ASC, name ASC");
        if ($catRes) {
            while ($row = $catRes->fetch_assoc()) {
                $categories[] = $row;
            }
        }

        // Load View
        require_once __DIR__ . '/../../Views/front/search.php';
    }

    /**
     * AJAX Live Search Suggestions API (for header search autocomplete).
     */
    public function suggestions(): void {
        header('Content-Type: application/json');

        $q = trim($_GET['q'] ?? $_GET['query'] ?? '');
        if (empty($q) || mb_strlen($q) < 2) {
            echo json_encode(['success' => true, 'products' => [], 'categories' => []]);
            exit;
        }

        $db = Database::connect();
        $sTerm = '%' . $q . '%';

        // 1. Search Products (Limit 6)
        $sqlProd = "
            SELECT p.id, p.name, p.slug, p.base_price, p.sale_price, c.name as category_name,
                   (SELECT image_url FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as primary_image
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1 AND p.deleted_at IS NULL AND (p.name LIKE ? OR c.name LIKE ?)
            ORDER BY CASE WHEN p.name LIKE ? THEN 1 ELSE 2 END, p.id DESC
            LIMIT 6
        ";

        $products = [];
        $prefixTerm = $q . '%';
        $stmt = $db->prepare($sqlProd);
        if ($stmt) {
            $stmt->bind_param("sss", $sTerm, $sTerm, $prefixTerm);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
                $products[] = $row;
            }
            $stmt->close();
        }

        // 2. Search Categories (Limit 3)
        $sqlCat = "SELECT id, name, slug FROM categories WHERE is_active = 1 AND name LIKE ? ORDER BY name ASC LIMIT 3";
        $matchingCategories = [];
        $stmtCat = $db->prepare($sqlCat);
        if ($stmtCat) {
            $stmtCat->bind_param("s", $sTerm);
            $stmtCat->execute();
            $resCat = $stmtCat->get_result();
            while ($row = $resCat->fetch_assoc()) {
                $matchingCategories[] = $row;
            }
            $stmtCat->close();
        }

        // 3. Log search query in background
        SearchLog::logSearch($q, count($products));

        echo json_encode([
            'success'    => true,
            'query'      => $q,
            'products'   => $products,
            'categories' => $matchingCategories
        ]);
        exit;
    }
}
