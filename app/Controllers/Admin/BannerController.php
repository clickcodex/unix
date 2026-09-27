<?php

namespace App\Controllers\Admin;

use App\Models\Banner;
use App\Helpers\SecurityHelper;

class BannerController {

    private function jsonResponse(bool $success, string $message, int $code = 200, array $extra = []): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
    }

    private function getRequestInput(): array {
        $json = json_decode(file_get_contents('php://input'), true);
        return is_array($json) ? $json : $_POST;
    }

    /**
     * Banners management dashboard view.
     */
    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'type'      => $_GET['type'] ?? '',
            'placement' => $_GET['placement'] ?? '',
            'status'    => $_GET['status'] ?? '',
            'search'    => $_GET['search'] ?? '',
        ];

        $result = Banner::getPaginatedBanners($page, 15, $filters);
        $kpis = Banner::getKpiMetrics();

        if (!empty($_GET['ajax'])) {
            $this->jsonResponse(true, 'Banners retrieved.', 200, [
                'data'       => $result['banners'],
                'pagination' => $result['pagination'],
                'kpis'       => $kpis,
            ]);
            return;
        }

        $banners = $result['banners'];
        $pagination = $result['pagination'];
        require __DIR__ . '/../../Views/admin/banners/index.php';
    }

    private function parseId($id): ?int {
        if (empty($id)) return null;
        if (is_numeric($id)) return (int)$id;
        return SecurityHelper::decryptId((string)$id);
    }

    /**
     * Fetch single banner detail.
     */
    public function detail(string $encryptedId): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $id = $this->parseId($encryptedId);
        if (!$id) {
            $this->jsonResponse(false, 'Invalid banner ID.', 400);
            return;
        }

        $banner = Banner::getBannerById($id);
        if (!$banner) {
            $this->jsonResponse(false, 'Banner not found.', 404);
            return;
        }

        $this->jsonResponse(true, 'Banner detail retrieved.', 200, ['banner' => $banner]);
    }

    /**
     * Store new banner.
     */
    public function store(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $title = trim($input['title'] ?? '');
        $imageUrl = trim($input['image_url'] ?? '');

        if (empty($title)) {
            $this->jsonResponse(false, 'Banner title is required.', 400);
            return;
        }

        if (empty($imageUrl)) {
            $this->jsonResponse(false, 'Banner image URL is required.', 400);
            return;
        }

        $bannerId = Banner::createBanner($input);
        if ($bannerId) {
            \App\Models\AuditLog::log('banner.created', 'Banner', $bannerId, null, ['title' => $title, 'type' => $input['banner_type'] ?? 'default']);
            $this->jsonResponse(true, 'Banner created successfully.', 200, [
                'encrypted_id' => SecurityHelper::encryptId($bannerId)
            ]);
        } else {
            $this->jsonResponse(false, 'Failed to create banner.', 400);
        }
    }

    /**
     * Update existing banner.
     */
    public function update(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $encryptedId = trim($input['banner_id'] ?? $input['id'] ?? '');

        $id = $this->parseId($encryptedId);
        if (!$id) {
            $this->jsonResponse(false, 'Invalid banner ID.', 400);
            return;
        }

        $oldBanner = Banner::getBannerById($id);
        $success = Banner::updateBanner($id, $input);
        if ($success) {
            \App\Models\AuditLog::log('banner.updated', 'Banner', $id, $oldBanner, $input);
            $this->jsonResponse(true, 'Banner updated successfully.');
        } else {
            $this->jsonResponse(false, 'Failed to update banner.', 400);
        }
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $encryptedId = trim($input['banner_id'] ?? $input['id'] ?? '');

        $id = $this->parseId($encryptedId);
        if (!$id) {
            $this->jsonResponse(false, 'Invalid banner ID.', 400);
            return;
        }

        $oldBanner = Banner::getBannerById($id);
        $success = Banner::toggleActive($id);
        if ($success) {
            \App\Models\AuditLog::log('banner.toggle_active', 'Banner', $id, ['is_active' => $oldBanner['is_active'] ?? null], ['is_active' => !empty($oldBanner['is_active']) ? 0 : 1]);
            $this->jsonResponse(true, 'Banner status toggled successfully.');
        } else {
            $this->jsonResponse(false, 'Failed to toggle status.', 400);
        }
    }

    /**
     * Delete banner.
     */
    public function delete(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $encryptedId = trim($input['banner_id'] ?? $input['id'] ?? '');

        $id = $this->parseId($encryptedId);
        if (!$id) {
            $this->jsonResponse(false, 'Invalid banner ID.', 400);
            return;
        }

        $oldBanner = Banner::getBannerById($id);
        $success = Banner::deleteBanner($id);
        if ($success) {
            \App\Models\AuditLog::log('banner.deleted', 'Banner', $id, $oldBanner, null);
            $this->jsonResponse(true, 'Banner deleted successfully.');
        } else {
            $this->jsonResponse(false, 'Failed to delete banner.', 400);
        }
    }
}
