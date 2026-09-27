<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class UserReview {

    /**
     * Get all reviews created by a user with product details & primary image.
     */
    public static function getReviewsForUser(int $userId): array {
        $db = Database::connect();

        $stmt = $db->prepare("
            SELECT 
                r.id AS review_id,
                r.product_id,
                r.order_item_id,
                r.rating,
                r.title,
                r.body,
                r.status,
                r.is_verified_purchase,
                r.helpful_count,
                r.created_at,
                r.updated_at,
                p.name AS product_name,
                p.slug AS product_slug,
                p.base_price,
                p.sale_price,
                (SELECT pi.image_url FROM product_images pi 
                 WHERE pi.product_id = p.id AND pi.is_primary = 1 
                 ORDER BY pi.sort_order ASC LIMIT 1) AS product_image
            FROM reviews r
            INNER JOIN products p ON p.id = r.product_id AND p.deleted_at IS NULL
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
        ");
        if (!$stmt) return [];
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($items as &$item) {
            $item['encrypted_id']         = SecurityHelper::encryptId($item['review_id']);
            $item['encrypted_review_id']  = $item['encrypted_id'];
            $item['encrypted_product_id'] = SecurityHelper::encryptId($item['product_id']);
        }
        return $items;
    }

    /**
     * Get single review by ID (owned by user).
     */
    public static function getReviewById(int $userId, int $reviewId): ?array {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM reviews WHERE id = ? AND user_id = ? LIMIT 1");
        if (!$stmt) return null;
        $stmt->bind_param("ii", $reviewId, $userId);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($item) {
            $item['encrypted_id']         = SecurityHelper::encryptId($item['id']);
            $item['encrypted_product_id'] = SecurityHelper::encryptId($item['product_id']);
        }
        return $item ?: null;
    }

    /**
     * Recalculate and update average rating & review count for a product.
     */
    public static function recalculateProductRating(int $productId): void {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT COUNT(id) as cnt, AVG(rating) as avg_rating 
            FROM reviews 
            WHERE product_id = ? AND status = 'approved'
        ");
        if (!$stmt) return;
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $count = (int)($res['cnt'] ?? 0);
        $avg   = round((float)($res['avg_rating'] ?? 0), 2);

        $stmtUpd = $db->prepare("UPDATE products SET average_rating = ?, review_count = ? WHERE id = ?");
        if ($stmtUpd) {
            $stmtUpd->bind_param("dii", $avg, $count, $productId);
            $stmtUpd->execute();
            $stmtUpd->close();
        }
    }

    /**
     * Submit a new review for a product.
     */
    public static function createReview(int $userId, array $data): array {
        $db = Database::connect();

        $productId = (int)($data['product_id'] ?? 0);
        $rating    = max(1, min(5, (int)($data['rating'] ?? 5)));
        $title     = trim($data['title'] ?? '');
        $body      = trim($data['body'] ?? '');

        if (!$productId) {
            return ['success' => false, 'message' => 'Product is required.'];
        }
        if (empty($title) || empty($body)) {
            return ['success' => false, 'message' => 'Please provide a title and review text.'];
        }

        // Check if user already reviewed this product
        $stmtCheck = $db->prepare("SELECT id FROM reviews WHERE user_id = ? AND product_id = ? LIMIT 1");
        if ($stmtCheck) {
            $stmtCheck->bind_param("ii", $userId, $productId);
            $stmtCheck->execute();
            if ($stmtCheck->get_result()->fetch_assoc()) {
                $stmtCheck->close();
                return ['success' => false, 'message' => 'You have already reviewed this product. You can edit your existing review.'];
            }
            $stmtCheck->close();
        }

        // Check if user has purchased this product
        $isVerified = 0;
        $orderItemId = null;
        $stmtOrder = $db->prepare("
            SELECT oi.id FROM order_items oi
            INNER JOIN orders o ON o.id = oi.order_id
            WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'delivered'
            ORDER BY o.created_at DESC LIMIT 1
        ");
        if ($stmtOrder) {
            $stmtOrder->bind_param("ii", $userId, $productId);
            $stmtOrder->execute();
            $ordRow = $stmtOrder->get_result()->fetch_assoc();
            if ($ordRow) {
                $isVerified = 1;
                $orderItemId = (int)$ordRow['id'];
            }
            $stmtOrder->close();
        }

        // Check system settings for review auto-approval
        $autoApprove = 1;
        $resSet = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'review_auto_approve' LIMIT 1");
        if ($resSet && $rowSet = $resSet->fetch_assoc()) {
            $autoApprove = (int)$rowSet['setting_value'];
        }
        $status = $autoApprove ? 'approved' : 'pending';

        $stmt = $db->prepare("
            INSERT INTO reviews 
            (product_id, user_id, order_item_id, rating, title, body, status, is_verified_purchase, helpful_count, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW())
        ");
        if (!$stmt) return ['success' => false, 'message' => 'Database error.'];

        $stmt->bind_param("iiiisssi", $productId, $userId, $orderItemId, $rating, $title, $body, $status, $isVerified);
        $ok = $stmt->execute();
        $newId = $stmt->insert_id;
        $stmt->close();

        if ($ok && $status === 'approved') {
            self::recalculateProductRating($productId);
        }

        return [
            'success' => $ok,
            'message' => $ok ? 'Review submitted successfully!' : 'Failed to submit review.',
            'encrypted_id' => $ok ? SecurityHelper::encryptId($newId) : ''
        ];
    }

    /**
     * Update an existing review.
     */
    public static function updateReview(int $userId, int $reviewId, array $data): array {
        $db = Database::connect();

        $existing = self::getReviewById($userId, $reviewId);
        if (!$existing) {
            return ['success' => false, 'message' => 'Review not found.'];
        }

        $rating = max(1, min(5, (int)($data['rating'] ?? 5)));
        $title  = trim($data['title'] ?? '');
        $body   = trim($data['body'] ?? '');

        if (empty($title) || empty($body)) {
            return ['success' => false, 'message' => 'Please fill in title and review body.'];
        }

        $stmt = $db->prepare("
            UPDATE reviews 
            SET rating = ?, title = ?, body = ?, updated_at = NOW()
            WHERE id = ? AND user_id = ?
        ");
        if (!$stmt) return ['success' => false, 'message' => 'Database error.'];

        $stmt->bind_param("issii", $rating, $title, $body, $reviewId, $userId);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            self::recalculateProductRating((int)$existing['product_id']);
        }

        return ['success' => $ok, 'message' => $ok ? 'Review updated successfully!' : 'Failed to update review.'];
    }

    /**
     * Delete a review.
     */
    public static function deleteReview(int $userId, int $reviewId): bool {
        $db = Database::connect();

        $existing = self::getReviewById($userId, $reviewId);
        if (!$existing) return false;

        $stmt = $db->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
        if (!$stmt) return false;

        $stmt->bind_param("ii", $reviewId, $userId);
        $ok = $stmt->execute();
        $stmt->close();

        if ($ok) {
            self::recalculateProductRating((int)$existing['product_id']);
        }

        return $ok;
    }
}
