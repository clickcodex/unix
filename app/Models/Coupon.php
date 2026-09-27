<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class Coupon {

    /**
     * Get KPI metrics for coupons & offers dashboard.
     */
    public static function getKpiMetrics(): array {
        $db = Database::connect();

        $couponStats = $db->query("
            SELECT 
                COUNT(*) AS total_coupons,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) AS active_coupons,
                SUM(used_count) AS total_redemptions,
                SUM(CASE WHEN ends_at IS NOT NULL AND ends_at < NOW() THEN 1 ELSE 0 END) AS expired_coupons
            FROM coupons
        ")->fetch_assoc();

        $offerStats = $db->query("
            SELECT 
                COUNT(*) AS total_offers,
                SUM(CASE WHEN is_active = 1 AND starts_at <= NOW() AND (ends_at IS NULL OR ends_at >= NOW()) THEN 1 ELSE 0 END) AS active_offers
            FROM offers
        ")->fetch_assoc();

        return array_merge($couponStats, $offerStats);
    }

    /**
     * Get paginated coupons with filters.
     */
    public static function getPaginatedCoupons(int $page = 1, int $perPage = 15, array $filters = []): array {
        $db = Database::connect();

        $where = [];
        $params = [];
        $types = '';

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $where[] = 'c.is_active = 1';
            } elseif ($filters['status'] === 'inactive') {
                $where[] = 'c.is_active = 0';
            } elseif ($filters['status'] === 'expired') {
                $where[] = '(c.ends_at IS NOT NULL AND c.ends_at < NOW())';
            }
        }

        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where[] = '(c.code LIKE ? OR c.description LIKE ?)';
            $params[] = $s;
            $params[] = $s;
            $types .= 'ss';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmtC = $db->prepare("SELECT COUNT(*) AS cnt FROM coupons c {$whereClause}");
        if ($types && $stmtC) {
            $stmtC->bind_param($types, ...$params);
        }
        $stmtC->execute();
        $total = $stmtC->get_result()->fetch_assoc()['cnt'];
        $stmtC->close();

        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT c.* FROM coupons c {$whereClause} ORDER BY c.created_at DESC LIMIT ? OFFSET ?";
        $fetchTypes = $types . 'ii';
        $fetchParams = array_merge($params, [$perPage, $offset]);

        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($fetchTypes, ...$fetchParams);
            $stmt->execute();
            $coupons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            $coupons = [];
        }

        foreach ($coupons as &$c) {
            $c['encrypted_id'] = SecurityHelper::encryptId($c['id']);
        }

        return [
            'coupons' => $coupons,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => (int)$total,
                'total_pages' => $totalPages,
            ]
        ];
    }

    /**
     * Get a single coupon by ID.
     */
    public static function getCouponById(int $id): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM coupons WHERE id = ?");
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $coupon = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($coupon) {
            $coupon['encrypted_id'] = SecurityHelper::encryptId($coupon['id']);
        }
        return $coupon;
    }

    /**
     * Create a new coupon.
     */
    public static function createCoupon(array $data): ?int {
        $db = Database::connect();
        $stmt = $db->prepare("
            INSERT INTO coupons (code, description, discount_type, discount_value, min_order_value, max_discount, usage_limit, per_user_limit, starts_at, ends_at, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        if (!$stmt) return null;

        $code = strtoupper(trim($data['code'] ?? ''));
        $desc = $data['description'] ?? null;
        $discType = $data['discount_type'] ?? 'percentage';
        $discVal = (float)($data['discount_value'] ?? 0);
        $minOrder = isset($data['min_order_value']) && $data['min_order_value'] !== '' ? (float)$data['min_order_value'] : null;
        $maxDisc = isset($data['max_discount']) && $data['max_discount'] !== '' ? (float)$data['max_discount'] : null;
        $usageLimit = isset($data['usage_limit']) && $data['usage_limit'] !== '' ? (int)$data['usage_limit'] : null;
        $perUser = (int)($data['per_user_limit'] ?? 1);
        $startsAt = !empty($data['starts_at']) ? $data['starts_at'] : null;
        $endsAt = !empty($data['ends_at']) ? $data['ends_at'] : null;
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        $stmt->bind_param("sssdddiissi", $code, $desc, $discType, $discVal, $minOrder, $maxDisc, $usageLimit, $perUser, $startsAt, $endsAt, $isActive);
        $res = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        return $res ? $id : null;
    }

    /**
     * Update an existing coupon.
     */
    public static function updateCoupon(int $id, array $data): bool {
        $db = Database::connect();
        $stmt = $db->prepare("
            UPDATE coupons SET code = ?, description = ?, discount_type = ?, discount_value = ?, min_order_value = ?, max_discount = ?, usage_limit = ?, per_user_limit = ?, starts_at = ?, ends_at = ?, is_active = ?
            WHERE id = ?
        ");
        if (!$stmt) return false;

        $code = strtoupper(trim($data['code'] ?? ''));
        $desc = $data['description'] ?? null;
        $discType = $data['discount_type'] ?? 'percentage';
        $discVal = (float)($data['discount_value'] ?? 0);
        $minOrder = isset($data['min_order_value']) && $data['min_order_value'] !== '' ? (float)$data['min_order_value'] : null;
        $maxDisc = isset($data['max_discount']) && $data['max_discount'] !== '' ? (float)$data['max_discount'] : null;
        $usageLimit = isset($data['usage_limit']) && $data['usage_limit'] !== '' ? (int)$data['usage_limit'] : null;
        $perUser = (int)($data['per_user_limit'] ?? 1);
        $startsAt = !empty($data['starts_at']) ? $data['starts_at'] : null;
        $endsAt = !empty($data['ends_at']) ? $data['ends_at'] : null;
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        $stmt->bind_param("sssdddiiissi", $code, $desc, $discType, $discVal, $minOrder, $maxDisc, $usageLimit, $perUser, $startsAt, $endsAt, $isActive, $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Toggle coupon active status.
     */
    public static function toggleActive(int $id): bool {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE coupons SET is_active = 1 - is_active WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Delete a coupon.
     */
    public static function deleteCoupon(int $id): bool {
        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM coupons WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    // ===========================================================================
    // OFFERS
    // ===========================================================================

    /**
     * Get all offers with applicability info.
     */
    public static function getAllOffers(): array {
        $db = Database::connect();
        $res = $db->query("SELECT o.*, 
            (SELECT GROUP_CONCAT(CONCAT(oa.scope, ':', COALESCE(oa.ref_id, 'ALL')) SEPARATOR ', ') FROM offer_applicability oa WHERE oa.offer_id = o.id) AS applicability_summary
            FROM offers o ORDER BY o.created_at DESC");
        $offers = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

        foreach ($offers as &$o) {
            $o['encrypted_id'] = SecurityHelper::encryptId($o['id']);
        }
        return $offers;
    }

    /**
     * Get single offer by ID.
     */
    public static function getOfferById(int $id): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM offers WHERE id = ?");
        if (!$stmt) return null;
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $offer = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($offer) {
            $offer['encrypted_id'] = SecurityHelper::encryptId($offer['id']);
            // Fetch applicability
            $stmtA = $db->prepare("SELECT * FROM offer_applicability WHERE offer_id = ?");
            if ($stmtA) {
                $stmtA->bind_param("i", $id);
                $stmtA->execute();
                $offer['applicability'] = $stmtA->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmtA->close();
            }
        }
        return $offer;
    }

    /**
     * Create a new offer.
     */
    public static function createOffer(array $data): ?int {
        $db = Database::connect();
        $stmt = $db->prepare("
            INSERT INTO offers (name, description, offer_type, discount_value, min_order_value, max_discount_cap, starts_at, ends_at, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        if (!$stmt) return null;

        $name = trim($data['name'] ?? '');
        $desc = $data['description'] ?? null;
        $offerType = $data['offer_type'] ?? 'percentage';
        $discVal = (float)($data['discount_value'] ?? 0);
        $minOrder = isset($data['min_order_value']) && $data['min_order_value'] !== '' ? (float)$data['min_order_value'] : null;
        $maxCap = isset($data['max_discount_cap']) && $data['max_discount_cap'] !== '' ? (float)$data['max_discount_cap'] : null;
        $startsAt = !empty($data['starts_at']) ? $data['starts_at'] : date('Y-m-d H:i:s');
        $endsAt = !empty($data['ends_at']) ? $data['ends_at'] : null;
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        $stmt->bind_param("sssdddssi", $name, $desc, $offerType, $discVal, $minOrder, $maxCap, $startsAt, $endsAt, $isActive);
        $res = $stmt->execute();
        $offerId = $stmt->insert_id;
        $stmt->close();

        if ($res && !empty($data['applicability']) && is_array($data['applicability'])) {
            self::syncOfferApplicability($offerId, $data['applicability']);
        }

        return $res ? $offerId : null;
    }

    /**
     * Update an existing offer.
     */
    public static function updateOffer(int $id, array $data): bool {
        $db = Database::connect();
        $stmt = $db->prepare("
            UPDATE offers SET name = ?, description = ?, offer_type = ?, discount_value = ?, min_order_value = ?, max_discount_cap = ?, starts_at = ?, ends_at = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ");
        if (!$stmt) return false;

        $name = trim($data['name'] ?? '');
        $desc = $data['description'] ?? null;
        $offerType = $data['offer_type'] ?? 'percentage';
        $discVal = (float)($data['discount_value'] ?? 0);
        $minOrder = isset($data['min_order_value']) && $data['min_order_value'] !== '' ? (float)$data['min_order_value'] : null;
        $maxCap = isset($data['max_discount_cap']) && $data['max_discount_cap'] !== '' ? (float)$data['max_discount_cap'] : null;
        $startsAt = !empty($data['starts_at']) ? $data['starts_at'] : date('Y-m-d H:i:s');
        $endsAt = !empty($data['ends_at']) ? $data['ends_at'] : null;
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        $stmt->bind_param("sssdddssii", $name, $desc, $offerType, $discVal, $minOrder, $maxCap, $startsAt, $endsAt, $isActive, $id);
        $res = $stmt->execute();
        $stmt->close();

        if ($res && isset($data['applicability'])) {
            self::syncOfferApplicability($id, $data['applicability']);
        }

        return $res;
    }

    /**
     * Sync offer applicability scopes.
     */
    private static function syncOfferApplicability(int $offerId, array $applicability): void {
        $db = Database::connect();
        $db->query("DELETE FROM offer_applicability WHERE offer_id = {$offerId}");

        $stmt = $db->prepare("INSERT INTO offer_applicability (offer_id, scope, ref_id) VALUES (?, ?, ?)");
        if ($stmt) {
            foreach ($applicability as $a) {
                $scope = $a['scope'] ?? 'all';
                $refId = !empty($a['ref_id']) ? (int)$a['ref_id'] : null;
                $stmt->bind_param("isi", $offerId, $scope, $refId);
                $stmt->execute();
            }
            $stmt->close();
        }
    }

    /**
     * Toggle offer active status.
     */
    public static function toggleOfferActive(int $id): bool {
        $db = Database::connect();
        $stmt = $db->prepare("UPDATE offers SET is_active = 1 - is_active WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Get list of uppercase coupon codes already used by a user in active/completed orders.
     */
    public static function getUserUsedCouponCodes(int $userId): array {
        if (!$userId) return [];
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT DISTINCT UPPER(coupon_code) AS code 
            FROM orders 
            WHERE user_id = ? 
              AND coupon_code IS NOT NULL 
              AND coupon_code != ''
              AND status NOT IN ('cancelled', 'refunded')
        ");
        if (!$stmt) return [];
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $used = [];
        while ($row = $res->fetch_assoc()) {
            if (!empty($row['code'])) {
                $used[] = strtoupper($row['code']);
            }
        }
        $stmt->close();
        return $used;
    }

    /**
     * Check if a specific user has already used a coupon code (1-time limit).
     */
    public static function hasUserUsedCoupon(int $userId, string $code): bool {
        if (!$userId || empty($code)) return false;
        $db = Database::connect();
        $code = strtoupper(trim($code));
        $stmt = $db->prepare("
            SELECT COUNT(*) AS cnt 
            FROM orders 
            WHERE user_id = ? 
              AND UPPER(coupon_code) = ? 
              AND status NOT IN ('cancelled', 'refunded')
        ");
        if (!$stmt) return false;
        $stmt->bind_param("is", $userId, $code);
        $stmt->execute();
        $cnt = (int)($stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
        $stmt->close();
        return $cnt > 0;
    }
}
