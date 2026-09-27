<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class Review {

    /**
     * Get KPI metrics for the reviews dashboard.
     */
    public static function getKpiMetrics(): array {
        $db = Database::connect();
        $row = $db->query("
            SELECT 
                COUNT(*) AS total_reviews,
                ROUND(AVG(rating), 1) AS avg_rating,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved_count,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected_count,
                SUM(is_verified_purchase) AS verified_count,
                SUM(helpful_count) AS total_helpful
            FROM reviews
        ")->fetch_assoc();

        // Rating distribution
        $dist = $db->query("
            SELECT rating, COUNT(*) AS cnt FROM reviews GROUP BY rating ORDER BY rating DESC
        ")->fetch_all(MYSQLI_ASSOC);

        $ratingDist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($dist as $d) {
            $ratingDist[(int)$d['rating']] = (int)$d['cnt'];
        }

        $row['rating_distribution'] = $ratingDist;
        return $row;
    }

    /**
     * Get paginated reviews with filters, search, and sorting.
     */
    public static function getPaginatedReviews(int $page = 1, int $perPage = 15, array $filters = []): array {
        $db = Database::connect();

        $where = [];
        $params = [];
        $types = '';

        // Status filter
        if (!empty($filters['status'])) {
            $where[] = 'r.status = ?';
            $params[] = $filters['status'];
            $types .= 's';
        }

        // Rating filter
        if (!empty($filters['rating'])) {
            $where[] = 'r.rating = ?';
            $params[] = (int)$filters['rating'];
            $types .= 'i';
        }

        // Verified purchase filter
        if (isset($filters['verified']) && $filters['verified'] !== '') {
            $where[] = 'r.is_verified_purchase = ?';
            $params[] = (int)$filters['verified'];
            $types .= 'i';
        }

        // Search
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $where[] = '(r.title LIKE ? OR r.body LIKE ? OR u.name LIKE ? OR p.name LIKE ?)';
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            $types .= 'ssss';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Sorting
        $sortField = $filters['sort'] ?? 'r.created_at';
        $sortDir = ($filters['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        $allowedSorts = ['r.created_at', 'r.rating', 'r.helpful_count', 'r.status', 'p.name', 'u.name'];
        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'r.created_at';
        }

        // Count total
        $countSql = "SELECT COUNT(*) AS cnt FROM reviews r LEFT JOIN products p ON r.product_id = p.id LEFT JOIN users u ON r.user_id = u.id {$whereClause}";
        $stmtCount = $db->prepare($countSql);
        if ($types && $stmtCount) {
            $stmtCount->bind_param($types, ...$params);
        }
        $stmtCount->execute();
        $total = $stmtCount->get_result()->fetch_assoc()['cnt'];
        $stmtCount->close();

        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        // Fetch reviews
        $sql = "
            SELECT r.*, 
                   p.name AS product_name, p.slug AS product_slug,
                   u.name AS user_name, u.email AS user_email, u.avatar_url AS user_avatar,
                   (SELECT COUNT(*) FROM review_images ri WHERE ri.review_id = r.id) AS image_count
            FROM reviews r
            LEFT JOIN products p ON r.product_id = p.id
            LEFT JOIN users u ON r.user_id = u.id
            {$whereClause}
            ORDER BY {$sortField} {$sortDir}
            LIMIT ? OFFSET ?
        ";

        $fetchTypes = $types . 'ii';
        $fetchParams = array_merge($params, [$perPage, $offset]);

        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($fetchTypes, ...$fetchParams);
            $stmt->execute();
            $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        } else {
            $reviews = [];
        }

        foreach ($reviews as &$rev) {
            $rev['encrypted_id'] = SecurityHelper::encryptId($rev['id']);
            $rev['product_encrypted_id'] = SecurityHelper::encryptId($rev['product_id']);
            $rev['user_encrypted_id'] = SecurityHelper::encryptId($rev['user_id']);
        }

        return [
            'reviews' => $reviews,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => (int)$total,
                'total_pages' => $totalPages,
            ]
        ];
    }

    /**
     * Get full review detail including images.
     */
    public static function getReviewDetail(int $id): ?array {
        $db = Database::connect();

        $stmt = $db->prepare("
            SELECT r.*, 
                   p.name AS product_name, p.slug AS product_slug, p.sku AS product_sku,
                   u.name AS user_name, u.email AS user_email, u.avatar_url AS user_avatar, u.phone AS user_phone,
                   oi.quantity AS order_quantity
            FROM reviews r
            LEFT JOIN products p ON r.product_id = p.id
            LEFT JOIN users u ON r.user_id = u.id
            LEFT JOIN order_items oi ON r.order_item_id = oi.id
            WHERE r.id = ?
        ");
        if (!$stmt) return null;

        $stmt->bind_param("i", $id);
        $stmt->execute();
        $review = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$review) return null;

        $review['encrypted_id'] = SecurityHelper::encryptId($review['id']);

        // Fetch images
        $stmtImg = $db->prepare("SELECT * FROM review_images WHERE review_id = ? ORDER BY sort_order ASC");
        if ($stmtImg) {
            $stmtImg->bind_param("i", $id);
            $stmtImg->execute();
            $review['images'] = $stmtImg->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtImg->close();
        } else {
            $review['images'] = [];
        }

        return $review;
    }

    /**
     * Update review status (approve/reject/pending).
     */
    public static function updateStatus(int $id, string $status): bool {
        $db = Database::connect();
        $allowed = ['pending', 'approved', 'rejected'];
        if (!in_array($status, $allowed)) return false;

        $stmt = $db->prepare("UPDATE reviews SET status = ? WHERE id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("si", $status, $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Delete a review and its images.
     */
    public static function deleteReview(int $id): bool {
        $db = Database::connect();
        $db->query("DELETE FROM review_images WHERE review_id = {$id}");
        $stmt = $db->prepare("DELETE FROM reviews WHERE id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("i", $id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }

    /**
     * Bulk update review statuses.
     */
    public static function bulkUpdateStatus(array $ids, string $status): int {
        $db = Database::connect();
        $allowed = ['pending', 'approved', 'rejected'];
        if (!in_array($status, $allowed) || empty($ids)) return 0;

        $count = 0;
        $stmt = $db->prepare("UPDATE reviews SET status = ? WHERE id = ?");
        if ($stmt) {
            foreach ($ids as $id) {
                $idInt = (int)$id;
                $stmt->bind_param("si", $status, $idInt);
                if ($stmt->execute()) $count++;
            }
            $stmt->close();
        }
        return $count;
    }
}
