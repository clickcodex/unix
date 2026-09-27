<?php

namespace App\Controllers\Admin;

use App\Models\Review;
use App\Helpers\SecurityHelper;

class ReviewController {

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
     * Reviews listing page with AJAX support.
     */
    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'status'   => $_GET['status'] ?? '',
            'rating'   => $_GET['rating'] ?? '',
            'verified' => $_GET['verified'] ?? '',
            'search'   => $_GET['search'] ?? '',
            'sort'     => $_GET['sort'] ?? 'r.created_at',
            'dir'      => $_GET['dir'] ?? 'DESC',
        ];

        $result = Review::getPaginatedReviews($page, 15, $filters);
        $kpis = Review::getKpiMetrics();

        if (!empty($_GET['ajax'])) {
            $this->jsonResponse(true, 'Reviews loaded', 200, [
                'data'       => $result['reviews'],
                'pagination' => $result['pagination'],
                'kpis'       => $kpis,
            ]);
            return;
        }

        $reviews = $result['reviews'];
        $pagination = $result['pagination'];
        require __DIR__ . '/../../Views/admin/reviews/index.php';
    }

    private function parseId($id): ?int {
        if (empty($id)) return null;
        if (is_numeric($id)) return (int)$id;
        return SecurityHelper::decryptId((string)$id);
    }

    /**
     * Get single review detail (AJAX).
     */
    public function detail(string $encryptedId): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $id = $this->parseId($encryptedId);
        if (!$id) {
            $this->jsonResponse(false, 'Invalid review ID.', 400);
            return;
        }

        $review = Review::getReviewDetail($id);
        if (!$review) {
            $this->jsonResponse(false, 'Review not found.', 404);
            return;
        }

        $this->jsonResponse(true, 'Review detail loaded.', 200, ['review' => $review]);
    }

    /**
     * Update review status (approve/reject/pending).
     */
    public function updateStatus(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $id = $this->parseId($input['id'] ?? $input['review_id'] ?? '');
        $newStatus = $input['status'] ?? '';

        if (!$id) {
            $this->jsonResponse(false, 'Invalid review ID.', 400);
            return;
        }

        $success = Review::updateStatus($id, $newStatus);
        if ($success) {
            \App\Models\AuditLog::log('review.status_updated', 'Review', $id, null, ['status' => $newStatus]);
            $this->jsonResponse(true, 'Review status updated to ' . $newStatus . '.');
        } else {
            $this->jsonResponse(false, 'Failed to update review status.', 400);
        }
    }

    /**
     * Delete a review permanently.
     */
    public function delete(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $encryptedId = $input['review_id'] ?? '';

        $id = SecurityHelper::decryptId($encryptedId);
        if (!$id) {
            $this->jsonResponse(false, 'Invalid review ID.', 400);
            return;
        }

        $success = Review::deleteReview($id);
        if ($success) {
            \App\Models\AuditLog::log('review.deleted', 'Review', $id);
            $this->jsonResponse(true, 'Review deleted successfully.');
        } else {
            $this->jsonResponse(false, 'Failed to delete review.', 400);
        }
    }

    /**
     * Bulk action on reviews (approve_all, reject_all, delete_all).
     */
    public function bulkAction(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $action = $input['action'] ?? '';
        $encryptedIds = $input['ids'] ?? [];

        $ids = [];
        foreach ($encryptedIds as $eid) {
            $decrypted = SecurityHelper::decryptId($eid);
            if ($decrypted) $ids[] = $decrypted;
        }

        if (empty($ids)) {
            $this->jsonResponse(false, 'No valid reviews selected.', 400);
            return;
        }

        $count = 0;
        switch ($action) {
            case 'approve_all':
                $count = Review::bulkUpdateStatus($ids, 'approved');
                $msg = "{$count} reviews approved.";
                break;
            case 'reject_all':
                $count = Review::bulkUpdateStatus($ids, 'rejected');
                $msg = "{$count} reviews rejected.";
                break;
            case 'delete_all':
                foreach ($ids as $id) {
                    if (Review::deleteReview($id)) $count++;
                }
                $msg = "{$count} reviews deleted.";
                break;
            default:
                $this->jsonResponse(false, 'Unknown action.', 400);
                return;
        }

        \App\Models\AuditLog::log('review.bulk_action', 'Review', null, null, ['action' => $action, 'count' => $count]);
        $this->jsonResponse(true, $msg);
    }
}
