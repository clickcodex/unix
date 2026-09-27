<?php

namespace App\Controllers\Storefront;

use App\Config\Database;
use App\Models\Coupon;
use App\Models\Banner;
use App\Helpers\SecurityHelper;

class OfferController {

    public function index(): void {
        $db = Database::connect();

        // ── Auth State ────────────────────────────────────────────────
        $isLoggedIn = !empty($_SESSION['user_id']);
        $userId     = $isLoggedIn ? (int)$_SESSION['user_id'] : 0;

        // ── User Purchase Stats (only if logged in) ───────────────────
        $userStats = [
            'order_count'   => 0,
            'total_spent'   => 0.0,
            'tier'          => 'new',       // new | bronze | silver | gold | platinum
            'tier_label'    => 'New Member',
        ];

        if ($isLoggedIn) {
            $stmt = $db->prepare("
                SELECT COUNT(*) as order_count,
                       COALESCE(SUM(total_amount), 0) as total_spent
                FROM orders
                WHERE user_id = ?
                  AND payment_status = 'paid'
                  AND status NOT IN ('cancelled', 'returned', 'refunded')
            ");
            if ($stmt) {
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                $userStats['order_count'] = (int)($row['order_count'] ?? 0);
                $userStats['total_spent'] = (float)($row['total_spent'] ?? 0);
            }

            // Determine loyalty tier by spend
            $spent = $userStats['total_spent'];
            if ($spent >= 50000) {
                $userStats['tier']       = 'platinum';
                $userStats['tier_label'] = 'Platinum Member';
            } elseif ($spent >= 15000) {
                $userStats['tier']       = 'gold';
                $userStats['tier_label'] = 'Gold Member';
            } elseif ($spent >= 5000) {
                $userStats['tier']       = 'silver';
                $userStats['tier_label'] = 'Silver Member';
            } elseif ($spent >= 999) {
                $userStats['tier']       = 'bronze';
                $userStats['tier_label'] = 'Bronze Member';
            } else {
                $userStats['tier']       = 'new';
                $userStats['tier_label'] = 'New Member';
            }
        }

        // ── Coupons segmented by tier ────────────────────────────────
        // We fetch ALL active non-expired coupons and split into:
        //   $publicCoupons  – shown blurred to guests (no code revealed)
        //   $myCoupons      – unlocked coupons matching user's tier
        //   $lockedCoupons  – coupons the user hasn't qualified for yet

        $allCoupons = [];
        if ($isLoggedIn) {
            // Fetch all active coupons for logged-in users
            $resCoupons = $db->query("
                SELECT * FROM coupons
                WHERE is_active = 1
                  AND (ends_at IS NULL OR ends_at >= NOW())
                ORDER BY created_at DESC
                LIMIT 20
            ");
            $allCoupons = $resCoupons ? $resCoupons->fetch_all(MYSQLI_ASSOC) : [];
        } else {
            // Guests only see count/teaser — fetch minimal info (no code)
            $resCoupons = $db->query("
                SELECT id, discount_type, discount_value, description, min_order_value, ends_at
                FROM coupons
                WHERE is_active = 1
                  AND (ends_at IS NULL OR ends_at >= NOW())
                ORDER BY created_at DESC
                LIMIT 8
            ");
            $allCoupons = $resCoupons ? $resCoupons->fetch_all(MYSQLI_ASSOC) : [];
        }

        // Segment coupons by 1-time usage & user purchase tier (min_order_value)
        $myCoupons     = [];
        $lockedCoupons = [];
        $usedCoupons   = [];

        if ($isLoggedIn) {
            $usedCouponCodes = Coupon::getUserUsedCouponCodes($userId);

            foreach ($allCoupons as $c) {
                $codeUpper = strtoupper(trim($c['code'] ?? ''));
                $minOrder  = (float)($c['min_order_value'] ?? 0);

                // 1. Already used by this user (1-time use limit)
                if (in_array($codeUpper, $usedCouponCodes, true)) {
                    $c['already_used'] = true;
                    $usedCoupons[]     = $c;
                }
                // 2. Unlocked based on purchase history
                elseif ($userStats['total_spent'] >= $minOrder) {
                    $c['unlocked'] = true;
                    $myCoupons[]   = $c;
                }
                // 3. Locked based on purchase history
                else {
                    $c['unlocked']     = false;
                    $c['spend_needed'] = $minOrder - $userStats['total_spent'];
                    $lockedCoupons[]   = $c;
                }
            }
        }

        // ── Active Offers ─────────────────────────────────────────────
        $resOffers = $db->query("
            SELECT * FROM offers 
            WHERE is_active = 1 
              AND (start_date IS NULL OR start_date <= NOW()) 
              AND (end_date IS NULL OR end_date >= NOW())
            ORDER BY created_at DESC
        ");
        $offers = $resOffers ? $resOffers->fetch_all(MYSQLI_ASSOC) : [];

        // ── Offer Banners ─────────────────────────────────────────────
        $resBanners = $db->query("
            SELECT * FROM banners 
            WHERE is_active = 1 AND banner_type IN ('offer_banner', 'category_banner', 'hero_slider', 'custom')
            ORDER BY sort_order ASC
            LIMIT 6
        ");
        $banners = $resBanners ? $resBanners->fetch_all(MYSQLI_ASSOC) : [];

        // ── Discounted Products ───────────────────────────────────────
        $resProducts = $db->query("
            SELECT p.id, p.name, p.slug, p.base_price, p.sale_price, p.is_featured,
                   p.is_in_stock, p.average_rating, p.review_count, p.total_sold,
                   c.name as category_name,
                   (SELECT image_url FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as main_image_url
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1
              AND p.deleted_at IS NULL
              AND p.sale_price IS NOT NULL
              AND p.sale_price > 0
              AND p.sale_price < p.base_price
            ORDER BY ((p.base_price - p.sale_price) / p.base_price) DESC
            LIMIT 16
        ");
        $offerProducts = [];
        if ($resProducts) {
            while ($row = $resProducts->fetch_assoc()) {
                $row['encrypted_id']     = SecurityHelper::encryptId($row['id']);
                $row['discount_percent'] = ($row['base_price'] > 0)
                    ? round((($row['base_price'] - $row['sale_price']) / $row['base_price']) * 100)
                    : 0;
                $offerProducts[] = $row;
            }
        }

        $flashDeals   = array_slice($offerProducts, 0, 4);
        $moreProducts = array_slice($offerProducts, 4);

        // ── Tier definitions for view ──────────────────────────────────
        $tiers = [
            'new'      => ['label' => 'New Member',      'color' => 'slate',  'icon' => 'person',       'min_spend' => 0,     'max_spend' => 999],
            'bronze'   => ['label' => 'Bronze Member',   'color' => 'orange', 'icon' => 'workspace_premium', 'min_spend' => 999,   'max_spend' => 5000],
            'silver'   => ['label' => 'Silver Member',   'color' => 'blue',   'icon' => 'workspace_premium', 'min_spend' => 5000,  'max_spend' => 15000],
            'gold'     => ['label' => 'Gold Member',     'color' => 'amber',  'icon' => 'workspace_premium', 'min_spend' => 15000, 'max_spend' => 50000],
            'platinum' => ['label' => 'Platinum Member', 'color' => 'purple', 'icon' => 'diamond',       'min_spend' => 50000, 'max_spend' => null],
        ];

        $siteTitle       = 'Exclusive Offers & Coupon Deals — ClickCodex';
        $metaDescription = 'Explore active promo codes, seasonal sales, discounts, and exclusive coupon codes for your shopping.';

        require __DIR__ . '/../../Views/front/offers.php';
    }

    /**
     * Display Single Offer Detail Page with its specific products.
     * GET /offer/{id}
     */
    public function show(string $id = ''): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $db = Database::connect();

        $rawId   = $id;
        $offerId = is_numeric($rawId) ? (int)$rawId : SecurityHelper::decryptId($rawId);

        $offer = null;
        if ($offerId) {
            $offer = Coupon::getOfferById($offerId);
        }

        if (!$offer && !empty($rawId)) {
            // Try to find offer by slug or title match in DB
            $slugClean = str_replace('-', ' ', $rawId);
            $stmtS = $db->prepare("SELECT * FROM offers WHERE title LIKE ? OR name LIKE ? LIMIT 1");
            if ($stmtS) {
                $searchPattern = '%' . $slugClean . '%';
                $stmtS->bind_param("ss", $searchPattern, $searchPattern);
                $stmtS->execute();
                $offer = $stmtS->get_result()->fetch_assoc();
                $stmtS->close();
            }
        }

        // If offer not found in DB or empty, create a dynamic offer object based on requested slug/id
        if (!$offer) {
            $offerTitle = !empty($rawId) ? ucwords(str_replace('-', ' ', $rawId)) : 'The Big Tech & Style Sale';
            if (strpos(strtolower($offerTitle), 'offer') === false && strpos(strtolower($offerTitle), 'sale') === false) {
                $offerTitle .= ' Offer';
            }
            $offer = [
                'id'             => $offerId ?: 1,
                'encrypted_id'   => $rawId ?: SecurityHelper::encryptId(1),
                'title'          => $offerTitle,
                'name'           => $offerTitle,
                'description'    => 'Enjoy exclusive promotional discounts and savings automatically applied to eligible products.',
                'offer_type'     => 'Special Offer',
                'discount_value' => 50,
                'min_order_value'=> 499,
                'starts_at'      => date('Y-m-d H:i:s'),
                'ends_at'        => date('Y-m-d H:i:s', strtotime('+7 days')),
                'is_active'      => 1,
                'banner_image'   => null,
            ];
        }

        // Fetch products applicable to this offer
        $categoryIds = [];
        $productIds  = [];
        if (!empty($offer['applicability'])) {
            foreach ($offer['applicability'] as $app) {
                if ($app['scope'] === 'category' && !empty($app['ref_id'])) {
                    $categoryIds[] = (int)$app['ref_id'];
                } elseif ($app['scope'] === 'product' && !empty($app['ref_id'])) {
                    $productIds[] = (int)$app['ref_id'];
                }
            }
        }

        $where = ["p.is_active = 1 AND p.deleted_at IS NULL"];

        if (!empty($categoryIds)) {
            $inClause = implode(',', array_map('intval', $categoryIds));
            $where[]  = "p.category_id IN ({$inClause})";
        } elseif (!empty($productIds)) {
            $inClause = implode(',', array_map('intval', $productIds));
            $where[]  = "p.id IN ({$inClause})";
        } else {
            // Default: fetch products with sale price or featured products
            $where[]  = "((p.sale_price IS NOT NULL AND p.sale_price > 0) OR p.is_featured = 1 OR 1=1)";
        }

        $whereClause = implode(" AND ", $where);
        $sql = "
            SELECT p.id, p.name, p.slug, p.base_price, p.sale_price, p.is_featured,
                   p.is_in_stock, p.average_rating, p.review_count, p.total_sold,
                   c.name as category_name,
                   (SELECT image_url FROM product_images WHERE product_id = p.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as main_image_url
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE {$whereClause}
            ORDER BY p.is_featured DESC, p.id DESC
            LIMIT 24
        ";

        $resProducts = $db->query($sql);
        $offerProducts = [];
        if ($resProducts) {
            while ($row = $resProducts->fetch_assoc()) {
                $row['encrypted_id']     = SecurityHelper::encryptId($row['id']);
                $basePrice               = (float)$row['base_price'];
                $salePrice               = (!empty($row['sale_price']) && $row['sale_price'] < $basePrice) ? (float)$row['sale_price'] : $basePrice * 0.8;
                $row['effective_price']  = $salePrice;
                $row['discount_percent'] = ($basePrice > 0) ? round((($basePrice - $salePrice) / $basePrice) * 100) : 20;
                $offerProducts[]         = $row;
            }
        }

        // Available coupons
        $couponsResult = Coupon::getPaginatedCoupons(1, 6, ['status' => 'active']);
        $coupons       = $couponsResult['coupons'] ?? [];

        $baseUrl   = defined('BASE_URL') ? BASE_URL : '';
        $siteTitle = ($offer['title'] ?? $offer['name']) . ' — ClickCodex Offers';

        require __DIR__ . '/../../Views/front/offer_detail.php';
    }
}
