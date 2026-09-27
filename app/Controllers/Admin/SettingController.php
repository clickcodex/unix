<?php

namespace App\Controllers\Admin;

use App\Models\Setting;
use App\Models\Media;
use App\Middleware\AdminAuthMiddleware;

class SettingController {

    /**
     * Default settings dictionary with group, label, type, and default values.
     */
    private static array $defaultDefinitions = [
        // General Group
        'site_name' => ['default' => 'ClickCodex Store', 'type' => 'string', 'group' => 'general', 'label' => 'Store Name', 'public' => 1],
        'site_tagline' => ['default' => 'Premium Digital & Electronic Commerce Marketplace', 'type' => 'string', 'group' => 'general', 'label' => 'Tagline / Slogan', 'public' => 1],
        'contact_email' => ['default' => 'support@clickcodex.in', 'type' => 'string', 'group' => 'general', 'label' => 'Support Email', 'public' => 1],
        'contact_phone' => ['default' => '+91 98765 43210', 'type' => 'string', 'group' => 'general', 'label' => 'Support Phone', 'public' => 1],
        'store_address' => ['default' => 'Tech Hub Tower, Floor 4, Suite 402, Bengaluru, KA - 560001', 'type' => 'string', 'group' => 'general', 'label' => 'Store Physical Address', 'public' => 1],
        'maintenance_mode' => ['default' => false, 'type' => 'boolean', 'group' => 'general', 'label' => 'Maintenance Mode', 'public' => 0],

        // Branding Group
        'logo_url' => ['default' => '/uploads/branding/logo_1785644220_68f2fedd.jpeg', 'type' => 'string', 'group' => 'branding', 'label' => 'Store Logo URL', 'public' => 1],
        'favicon_url' => ['default' => '', 'type' => 'string', 'group' => 'branding', 'label' => 'Favicon URL', 'public' => 1],
        'footer_copyright' => ['default' => '© 2026 ClickCodex Inc. All rights reserved.', 'type' => 'string', 'group' => 'branding', 'label' => 'Footer Copyright Text', 'public' => 1],

        // SEO & Meta Group
        'meta_title' => ['default' => 'ClickCodex — Online Shopping for Electronics & Tech', 'type' => 'string', 'group' => 'seo', 'label' => 'Default Meta Title', 'public' => 1],
        'meta_description' => ['default' => 'Discover premium tech products, gadgets, apparel and accessories at unbeatable prices on ClickCodex.', 'type' => 'string', 'group' => 'seo', 'label' => 'Meta Description', 'public' => 1],
        'meta_keywords' => ['default' => 'ecommerce, online store, gadgets, electronics, shop', 'type' => 'string', 'group' => 'seo', 'label' => 'Meta Keywords', 'public' => 1],
        'google_analytics_id' => ['default' => 'G-XXXXXXXXXX', 'type' => 'string', 'group' => 'seo', 'label' => 'Google Analytics ID', 'public' => 1],

        // Currency, Shipping & Taxes Group
        'currency' => ['default' => 'INR', 'type' => 'string', 'group' => 'shipping', 'label' => 'Default Currency Code', 'public' => 1],
        'currency_symbol' => ['default' => '₹', 'type' => 'string', 'group' => 'shipping', 'label' => 'Currency Symbol', 'public' => 1],
        'tax_rate' => ['default' => 18.0, 'type' => 'decimal', 'group' => 'shipping', 'label' => 'Tax Rate (%)', 'public' => 1],
        'tax_inclusive' => ['default' => false, 'type' => 'boolean', 'group' => 'shipping', 'label' => 'Product Prices Tax Inclusive', 'public' => 1],
        'free_shipping_above' => ['default' => 999.0, 'type' => 'decimal', 'group' => 'shipping', 'label' => 'Free Shipping Minimum Threshold', 'public' => 1],
        'default_shipping_fee' => ['default' => 99.0, 'type' => 'decimal', 'group' => 'shipping', 'label' => 'Flat Shipping Fee', 'public' => 1],

        // Orders & Reviews Policy Group
        'order_prefix' => ['default' => 'ORD', 'type' => 'string', 'group' => 'orders', 'label' => 'Order Number Prefix', 'public' => 0],
        'low_stock_threshold' => ['default' => 5, 'type' => 'integer', 'group' => 'orders', 'label' => 'Low Stock Warning Threshold', 'public' => 0],
        'review_auto_approve' => ['default' => false, 'type' => 'boolean', 'group' => 'reviews', 'label' => 'Auto-Approve Customer Reviews', 'public' => 0],

        // Mail / SMTP Config Group
        'smtp_host' => ['default' => 'smtp.mailtrap.io', 'type' => 'string', 'group' => 'mail', 'label' => 'SMTP Host', 'public' => 0],
        'smtp_port' => ['default' => 2525, 'type' => 'integer', 'group' => 'mail', 'label' => 'SMTP Port', 'public' => 0],
        'smtp_username' => ['default' => '', 'type' => 'string', 'group' => 'mail', 'label' => 'SMTP Username', 'public' => 0],
        'smtp_password' => ['default' => '', 'type' => 'string', 'group' => 'mail', 'label' => 'SMTP Password', 'public' => 0],
        'smtp_encryption' => ['default' => 'tls', 'type' => 'string', 'group' => 'mail', 'label' => 'Mail Encryption', 'public' => 0],
        'mail_from_address' => ['default' => 'noreply@clickcodex.in', 'type' => 'string', 'group' => 'mail', 'label' => 'Mail From Address', 'public' => 0],
        'mail_from_name' => ['default' => 'ClickCodex Notifications', 'type' => 'string', 'group' => 'mail', 'label' => 'Mail From Name', 'public' => 0],

        // Payment & PhonePe UPI Config Group
        'upi_vpa_id' => ['default' => 'clickcodex@ybl', 'type' => 'string', 'group' => 'payment', 'label' => 'Merchant UPI VPA ID', 'public' => 1],
        'upi_merchant_name' => ['default' => 'ClickCodex Marketplace', 'type' => 'string', 'group' => 'payment', 'label' => 'UPI Merchant Name', 'public' => 1],
        'phonepe_merchant_id' => ['default' => 'PGTESTPAYUAT', 'type' => 'string', 'group' => 'payment', 'label' => 'PhonePe Merchant ID (MID)', 'public' => 0],
        'phonepe_salt_key' => ['default' => '099eb0cd-02fa-4e2d-73a6-5746e40465af', 'type' => 'string', 'group' => 'payment', 'label' => 'PhonePe Salt Key', 'public' => 0],
        'phonepe_salt_index' => ['default' => 1, 'type' => 'integer', 'group' => 'payment', 'label' => 'PhonePe Salt Index', 'public' => 0],
        'phonepe_env' => ['default' => 'sandbox', 'type' => 'string', 'group' => 'payment', 'label' => 'PhonePe Environment', 'public' => 0],
    ];

    /**
     * Display the Website Settings control panel page.
     */
    public function index(): void {
        AdminAuthMiddleware::check();

        // Auto-heal stored branding URLs in DB missing /public/
        try {
            $db = \App\Config\Database::connect();
            $db->query("UPDATE settings SET setting_value = REPLACE(setting_value, '/unix/uploads/', '/unix/public/uploads/') WHERE setting_key IN ('logo_url', 'favicon_url') AND setting_value LIKE '%/unix/uploads/%'");
            $db->query("UPDATE settings SET setting_value = REPLACE(setting_value, 'uploads/branding/', 'public/uploads/branding/') WHERE setting_key IN ('logo_url', 'favicon_url') AND setting_value LIKE '%uploads/branding/%' AND setting_value NOT LIKE '%public/uploads/branding/%'");
            Setting::clearCache();
        } catch (\Throwable $e) {}

        // Load existing settings from DB
        $allSettings = Setting::getAll();

        // Merge defaults for missing keys
        $settings = [];
        foreach (self::$defaultDefinitions as $key => $def) {
            $settings[$key] = array_key_exists($key, $allSettings) ? $allSettings[$key] : $def['default'];
        }

        $pageTitle = 'Website & Store Settings';
        $activeMenu = 'settings';

        require_once __DIR__ . '/../../Views/admin/settings/index.php';
    }

    /**
     * Handle update form submissions for Website Settings (including file uploads).
     */
    public function update(): void {
        AdminAuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respond(['success' => false, 'message' => 'Invalid request method.'], 405);
            return;
        }

        $isJson = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
        $input = $isJson ? (json_decode(file_get_contents('php://input'), true) ?? []) : $_POST;
        $targetGroup = trim($input['group_name'] ?? '');

        // 1. Process File Uploads (logo_file, favicon_file) if uploaded
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
            $logoUrl = self::processBrandingUpload($_FILES['logo_file'], 'logo');
            if ($logoUrl) {
                $input['logo_url'] = $logoUrl;
            }
        }
        if (isset($_FILES['favicon_file']) && $_FILES['favicon_file']['error'] === UPLOAD_ERR_OK) {
            $faviconUrl = self::processBrandingUpload($_FILES['favicon_file'], 'favicon');
            if ($faviconUrl) {
                $input['favicon_url'] = $faviconUrl;
            }
        }

        // 2. Save settings
        foreach (self::$defaultDefinitions as $key => $def) {
            if (!empty($targetGroup) && $def['group'] !== $targetGroup) {
                continue;
            }

            if ($def['type'] === 'boolean') {
                $val = !empty($input[$key]) ? true : false;
            } else {
                $val = $input[$key] ?? $def['default'];
            }

            Setting::set(
                $key,
                $val,
                $def['type'],
                $def['group'],
                $def['label'],
                (bool)$def['public']
            );
        }

        // Record audit event for settings update
        \App\Models\AuditLog::log('setting.updated', 'Setting', null, null, ['group' => $targetGroup ?: 'all']);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['flash_success'] = 'Website settings updated successfully!';

        if ($isJson) {
            $this->respond([
                'success' => true,
                'message' => 'Settings saved successfully!',
                'logo_url' => Setting::get('logo_url'),
                'favicon_url' => Setting::get('favicon_url')
            ]);
            return;
        }

        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        header('Location: ' . $baseUrl . '/admin/settings');
        exit;
    }

    /**
     * Standalone AJAX Endpoint to upload Logo or Favicon dynamically.
     */
    public function uploadBranding(): void {
        AdminAuthMiddleware::check();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respond(['success' => false, 'message' => 'Invalid request method.'], 405);
            return;
        }

        $type = trim($_POST['branding_type'] ?? 'logo'); // 'logo' or 'favicon'
        if (!in_array($type, ['logo', 'favicon'])) {
            $type = 'logo';
        }

        $fileKey = isset($_FILES['file']) ? 'file' : ($type . '_file');
        if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
            $this->respond(['success' => false, 'message' => 'No valid file uploaded.'], 400);
            return;
        }

        $publicUrl = self::processBrandingUpload($_FILES[$fileKey], $type);
        if (!$publicUrl) {
            $this->respond(['success' => false, 'message' => 'Failed to process branding upload. Unsupported file type or folder permission issue.'], 400);
            return;
        }

        // Update settings in database
        $settingKey = $type . '_url';
        $label = $type === 'logo' ? 'Store Logo URL' : 'Favicon URL';
        Setting::set($settingKey, $publicUrl, 'string', 'branding', $label, true);

        $this->respond([
            'success' => true,
            'message' => ucfirst($type) . ' uploaded successfully!',
            'type' => $type,
            'url' => $publicUrl
        ]);
    }

    /**
     * Process branding file upload, save to public/uploads/branding/ directory, and register in Media library.
     */
    private static function processBrandingUpload(array $file, string $type): ?string {
        $originalName = basename($file['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        $allowedExtensions = ['png', 'jpg', 'jpeg', 'svg', 'webp', 'gif', 'ico'];
        if (!in_array($ext, $allowedExtensions)) {
            return null;
        }

        // Relative and physical target directory
        $relativeDir = "uploads/branding";
        $uploadDir = __DIR__ . "/../../../public/{$relativeDir}";

        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $filename = "{$type}_" . time() . "_" . substr(md5(uniqid()), 0, 8) . ".{$ext}";
        $targetFile = "{$uploadDir}/{$filename}";

        if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
            return null;
        }

        $baseUrl = defined('BASE_URL') ? BASE_URL : '';
        $publicUrl = "{$baseUrl}/public/{$relativeDir}/{$filename}";

        // Register in Media Library database if Media model available
        try {
            $imgDimensions = @getimagesize($targetFile);
            $width = $imgDimensions ? $imgDimensions[0] : null;
            $height = $imgDimensions ? $imgDimensions[1] : null;
            $mimeType = $file['type'] ?: "image/{$ext}";

            Media::createMediaRecord([
                'filename' => $filename,
                'original_name' => $originalName,
                'mime_type' => $mimeType,
                'size_bytes' => (int)$file['size'],
                'width' => $width,
                'height' => $height,
                'alt_text' => "Store " . ucfirst($type),
                'folder' => 'branding',
                'storage_disk' => 'local',
                'storage_path' => "{$relativeDir}/{$filename}",
                'public_url' => $publicUrl
            ]);
        } catch (\Throwable $e) {
            error_log("Failed to register branding media record: " . $e->getMessage());
        }

        return $publicUrl;
    }

    /**
     * Send JSON response helper.
     */
    private function respond(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
