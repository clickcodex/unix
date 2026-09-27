<?php

namespace App\Controllers\Storefront;

use App\Config\Database;
use App\Models\Category;
use App\Models\Product;
use App\Helpers\SecurityHelper;

class SitemapController {

    /**
     * GET /sitemap.xml — Dynamic XML Sitemap Generator for Google / Bing Search Engines.
     */
    public function index(): void {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/xml; charset=utf-8');

        $baseUrl = defined('BASE_URL') ? BASE_URL : (isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : '');
        $db      = Database::connect();

        $urls = [];

        // 1. Static Pages
        $staticPages = [
            ['loc' => $baseUrl . '/',          'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => $baseUrl . '/categories', 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl . '/offers',     'priority' => '0.9', 'changefreq' => 'daily'],
            ['loc' => $baseUrl . '/about',      'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl . '/contact',    'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl . '/faq',        'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl . '/terms',      'priority' => '0.3', 'changefreq' => 'yearly'],
            ['loc' => $baseUrl . '/privacy',    'priority' => '0.3', 'changefreq' => 'yearly'],
        ];
        foreach ($staticPages as $sp) {
            $urls[] = array_merge($sp, ['lastmod' => date('Y-m-d')]);
        }

        // 2. Dynamic Categories
        $categories = Category::getFlatCategories();
        foreach ($categories as $cat) {
            if (isset($cat['is_active']) && (int)$cat['is_active'] === 0) continue;
            $slug   = !empty($cat['slug']) ? $cat['slug'] : $cat['id'];
            $urls[] = [
                'loc'        => $baseUrl . '/category/' . htmlspecialchars($slug),
                'priority'   => '0.8',
                'changefreq' => 'weekly',
                'lastmod'    => !empty($cat['updated_at']) ? date('Y-m-d', strtotime($cat['updated_at'])) : date('Y-m-d'),
            ];
        }

        // 3. Dynamic Active Products
        $resProd = $db->query("
            SELECT id, slug, updated_at, created_at 
            FROM products 
            WHERE is_active = 1 AND deleted_at IS NULL
            ORDER BY id DESC
            LIMIT 2000
        ");
        if ($resProd) {
            while ($p = $resProd->fetch_assoc()) {
                $encId  = SecurityHelper::encryptId($p['id']);
                $slug   = !empty($p['slug']) ? $p['slug'] : $encId;
                $lastmodDate = !empty($p['updated_at']) ? $p['updated_at'] : $p['created_at'];
                $urls[] = [
                    'loc'        => $baseUrl . '/product/' . htmlspecialchars($slug),
                    'priority'   => '0.9',
                    'changefreq' => 'daily',
                    'lastmod'    => date('Y-m-d', strtotime($lastmodDate ?: 'now')),
                ];
            }
        }

        // 4. Dynamic Active Offers
        $resOffers = $db->query("
            SELECT id, updated_at, created_at 
            FROM offers 
            WHERE is_active = 1
            ORDER BY id DESC
        ");
        if ($resOffers) {
            while ($o = $resOffers->fetch_assoc()) {
                $encId  = SecurityHelper::encryptId($o['id']);
                $lastmodDate = !empty($o['updated_at']) ? $o['updated_at'] : $o['created_at'];
                $urls[] = [
                    'loc'        => $baseUrl . '/offer/' . $encId,
                    'priority'   => '0.8',
                    'changefreq' => 'daily',
                    'lastmod'    => date('Y-m-d', strtotime($lastmodDate ?: 'now')),
                ];
            }
        }

        // Output XML
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            echo "  <url>\n";
            echo "    <loc>" . htmlspecialchars($u['loc']) . "</loc>\n";
            echo "    <lastmod>" . htmlspecialchars($u['lastmod']) . "</lastmod>\n";
            echo "    <changefreq>" . htmlspecialchars($u['changefreq']) . "</changefreq>\n";
            echo "    <priority>" . htmlspecialchars($u['priority']) . "</priority>\n";
            echo "  </url>\n";
        }
        echo '</urlset>';
        exit;
    }

    /**
     * GET /robots.txt — Robots.txt generator for search crawlers.
     */
    public function robots(): void {
        if (ob_get_length()) ob_clean();
        header('Content-Type: text/plain; charset=utf-8');

        $baseUrl = defined('BASE_URL') ? BASE_URL : (isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : '');

        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: /admin/\n";
        echo "Disallow: /admin\n";
        echo "Disallow: /cart\n";
        echo "Disallow: /checkout\n";
        echo "Disallow: /user/\n";
        echo "Disallow: /login\n";
        echo "Disallow: /register\n\n";
        echo "Sitemap: " . $baseUrl . "/sitemap.xml\n";
        exit;
    }
}
