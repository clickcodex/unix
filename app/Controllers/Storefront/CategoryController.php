<?php

namespace App\Controllers\Storefront;

use App\Models\Category;
use App\Models\Product;
use App\Models\Wishlist;
use App\Models\Cart;

class CategoryController {

    /**
     * Display Category Details Page.
     * GET /category/{slug} or /category/{id}
     */
    public function show(string $slug = ''): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($slug)) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/');
            exit;
        }

        $category = Category::getCategoryBySlugOrId($slug);

        if (!$category) {
            http_response_code(404);
            $baseUrl = defined('BASE_URL') ? BASE_URL : '';
            require_once __DIR__ . '/../../Views/errors/404.php';
            return;
        }

        $baseUrl   = defined('BASE_URL') ? BASE_URL : '';
        $siteTitle = (!empty($category['meta_title']) ? $category['meta_title'] : $category['name']) . ' — ClickCodex';
        $metaDesc  = !empty($category['meta_description']) ? $category['meta_description'] : $category['description'];

        // Get user wishlist items if logged in (for heart state)
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $userWishlistProductIds = [];
        if ($userId) {
            $wishlist = Wishlist::getOrCreate($userId);
            $wItems   = Wishlist::getItems((int)$wishlist['id'], $userId);
            $userWishlistProductIds = array_column($wItems, 'product_id');
        }

        require_once __DIR__ . '/../../Views/front/category.php';
    }

    /**
     * Display All Categories Page.
     * GET /categories
     */
    public function index(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $tree      = Category::getCategoryTree();
        $baseUrl   = defined('BASE_URL') ? BASE_URL : '';
        $siteTitle = 'All Product Categories — ClickCodex';

        require_once __DIR__ . '/../../Views/front/categories.php';
    }
}
