<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class Banner {

    /**
     * Get KPI metrics for the banners dashboard.
     */
    public static function getKpiMetrics(): array {
        $db = Database::connect();
        $row = $db->query("
            SELECT 
                COUNT(*) AS total_banners,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_banners,
                SUM(CASE WHEN banner_type = 'hero_slider' THEN 1 ELSE 0 END) AS hero_sliders,
                SUM(CASE WHEN banner_type = 'offer_banner' THEN 1 ELSE 0 END) AS offer_banners,
                SUM(CASE WHEN banner_type = 'category_banner' THEN 1 ELSE 0 END) AS category_banners,
                SUM(CASE WHEN banner_type = 'custom' THEN 1 ELSE 0 END) AS custom_banners
            FROM banners
        ")->fetch_assoc();

        return $row ?: [];
    }

    /**
     * Get paginated/filtered banners list.
     */
    public static function getPaginatedBanners(int $page = 1, int $perPage = 15, array $filters = []): array {
        $db = Database::connect();

        $where = [];
        $params = [];
        $types = '';

        if (!empty($filters['type'])) {
            $where[] = 'banner_type = ?';
            $params[] = $filters['type'];
            $types .= 's';
        }

        if (!empty($filters['placement'])) {
            $where[] = 'placement = ?';
            $params[] = $filters['placement'];
            $types .= 's';
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = 'is_active = ?';
            $params[] = (int)$filters['status'];
            $types .= 'i';
        }

        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where[] = '(title LIKE ? OR subtitle LIKE ? OR link_url LIKE ?)';
            $params = array_merge($params, [$s, $s, $s]);
            $types .= 'sss';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmtCount = $db->prepare("SELECT COUNT(*) AS cnt FROM banners {$whereClause}");
        if ($types && $stmtCount) {
            $stmtCount->bind_param($types, ...$params);
        }
        $stmtCount->execute();
        $total = $stmtCount->get_result()->fetch_assoc()['cnt'];
        $stmtCount->close();

        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM banners {$whereClause} ORDER BY sort_order ASC, id DESC LIMIT ? OFFSET ?";
        $fetchTypes = $types . 'ii';
        $fetchParams = array_merge($params, [$perPage, $offset]);

        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($fetchTypes, ...$fetchParams);
            $stmt->execute();
            $banners = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            $banners = [];
        }

        foreach ($banners as &$b) {
            $b['encrypted_id'] = SecurityHelper::encryptId($b['id']);
        }

        return [
            'banners' => $banners,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => (int)$total,
                'total_pages' => $totalPages,
            ]
        ];
    }

    /**
     * Get banner detail by ID.
     */
    public static function getBannerById(int $id): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM banners WHERE id = ?");
        if (!$stmt) return null;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $banner = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($banner) {
            $banner['encrypted_id'] = SecurityHelper::encryptId($banner['id']);
        }
        return $banner;
    }

    /**
     * Create a new banner.
     */
    public static function createBanner(array $data): ?int {
        $db = Database::connect();
        $stmt = $db->prepare("
            INSERT INTO banners (title, subtitle, image_url, mobile_image_url, link_url, link_target, banner_type, placement, sort_order, starts_at, ends_at, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        if (!$stmt) return null;

        $title = trim($data['title'] ?? '');
        $subtitle = $data['subtitle'] ?? null;
        $imageUrl = trim($data['image_url'] ?? '');
        $mobileImageUrl = !empty($data['mobile_image_url']) ? trim($data['mobile_image_url']) : null;
        $linkUrl = !empty($data['link_url']) ? trim($data['link_url']) : null;
        $linkTarget = in_array($data['link_target'] ?? '_self', ['_self', '_blank']) ? $data['link_target'] : '_self';
        $bannerType = $data['banner_type'] ?? 'hero_slider';
        $placement = !empty($data['placement']) ? trim($data['placement']) : 'homepage_top';
        $sortOrder = (int)($data['sort_order'] ?? 0);
        $startsAt = !empty($data['starts_at']) ? $data['starts_at'] : null;
        $endsAt = !empty($data['ends_at']) ? $data['ends_at'] : null;
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        $stmt->bind_param("ssssssssissi", $title, $subtitle, $imageUrl, $mobileImageUrl, $linkUrl, $linkTarget, $bannerType, $placement, $sortOrder, $startsAt, $endsAt, $isActive);
        $res = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();

        return $res ? $id : null;
    }

    /**
     * Update an existing banner.
     */
    public static function updateBanner(int $id, array $data): bool {
        $db = Database::connect();
        $stmt = $db->prepare("
            UPDATE banners SET title = ?, subtitle = ?, image_url = ?, mobile_image_url = ?, link_url = ?, link_target = ?, banner_type = ?, placement = ?, sort_order = ?, starts_at = ?, ends_at = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ");
        if (!$stmt) return false;

        $title = trim($data['title'] ?? '');
        $subtitle = $data['subtitle'] ?? null;
        $imageUrl = trim($data['image_url'] ?? '');
        $mobileImageUrl = !empty($data['mobile_image_url']) ? trim($data['mobile_image_url']) : null;
        $linkUrl = !empty($data['link_url']) ? trim($data['link_url']) : null;
        $linkTarget = in_array($data['link_target'] ?? '_self', ['_self', '_blank']) ? $data['link_target'] : '_self';
        $bannerType = $data['banner_type'] ?? 'hero_slider';
        $placement = !empty($data['placement']) ? trim($data['placement']) : 'homepage_top';
        $sortOrder = (int)($data['sort_order'] ?? 0);
        $startsAt = !empty($data['starts_at']) ? $data['starts_at'] : null;
        $endsAt = !empty($data['ends_at']) ? $data['ends_at'] : null;
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        $stmt->bind_param("ssssssssissii", $title, $subtitle, $imageUrl, $mobileImageUrl, $linkUrl, $linkTarget, $bannerType, $placement, $sortOrder, $startsAt, $endsAt, $isActive, $id);
        $res = $stmt->execute();
        $stmt->close();

        return $res;
    }

    /**
     * Toggle active status.
     */
    public static function toggleActive(int $id): bool {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE banners SET is_active = 1 - is_active WHERE id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Delete banner.
     */
    public static function deleteBanner(int $id): bool {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM banners WHERE id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }
}
