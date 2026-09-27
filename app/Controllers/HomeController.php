<?php

namespace App\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Helpers\SecurityHelper;

class HomeController {

    /**
     * Display Storefront Home Page.
     */
    public function index(): void {
        $baseUrl = defined('BASE_URL') ? BASE_URL : '';

        // Fetch categories for navbar and sidebar
        $flatCategories = Category::getFlatCategories();
        $activeCategories = array_filter($flatCategories, fn($c) => !isset($c['is_active']) || (int)$c['is_active'] === 1);

        // Fetch products for sliders and grids
        $featuredResult = Product::getPaginatedProducts(1, 8, '', 'active', '', 'yes');
        $allProductsResult = Product::getPaginatedProducts(1, 16, '', 'active');

        $featuredProducts = $featuredResult['products'] ?? [];
        $allProducts = $allProductsResult['products'] ?? [];

        $db = \App\Config\Database::connect();
        
        // Fetch active offers for hero slider
        $resHeroOffers = $db->query("
            SELECT * FROM offers 
            WHERE is_active = 1 
              AND (start_date IS NULL OR start_date <= NOW()) 
              AND (end_date IS NULL OR end_date >= NOW())
            ORDER BY created_at DESC
            LIMIT 5
        ");
        $heroOffers = $resHeroOffers ? $resHeroOffers->fetch_all(MYSQLI_ASSOC) : [];
        foreach ($heroOffers as &$ho) {
            $ho['encrypted_id'] = SecurityHelper::encryptId($ho['id']);
        }

        // Fetch hero slider banners
        $resHeroBanners = $db->query("
            SELECT * FROM banners 
            WHERE is_active = 1 AND banner_type IN ('hero_slider', 'offer_banner')
            ORDER BY sort_order ASC
            LIMIT 5
        ");
        $heroBanners = $resHeroBanners ? $resHeroBanners->fetch_all(MYSQLI_ASSOC) : [];

        // Branding & Site Settings
        $siteTitle = Setting::get('site_name', 'ClickCodex — Shop Electronics, Fashion, Home & More Online');
        $siteLogo = Setting::get('logo_url') ?: Setting::get('site_logo', '');
        $siteFavicon = Setting::get('favicon_url') ?: Setting::get('site_favicon', '');
        $supportPhone = Setting::get('support_phone', '+91 98765 43210');
        $announcementText = Setting::get('announcement_text', 'Mega Sale is Live! Up to 70% OFF on Top Tech & Fashion');

        require_once __DIR__ . '/../Views/front/home.php';
    }
}