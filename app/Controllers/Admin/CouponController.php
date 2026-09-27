<?php

namespace App\Controllers\Admin;

use App\Models\Coupon;
use App\Helpers\SecurityHelper;

class CouponController {

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
     * Coupons & Offers listing page.
     */
    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'status' => $_GET['status'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        $result = Coupon::getPaginatedCoupons($page, 15, $filters);
        $kpis = Coupon::getKpiMetrics();
        $offers = Coupon::getAllOffers();

        if (!empty($_GET['ajax'])) {
            $this->jsonResponse(true, 'Data loaded', 200, [
                'coupons'    => $result['coupons'],
                'pagination' => $result['pagination'],
                'offers'     => $offers,
                'kpis'       => $kpis,
            ]);
            return;
        }

        $coupons = $result['coupons'];
        $pagination = $result['pagination'];
        require __DIR__ . '/../../Views/admin/coupons/index.php';
    }

    /**
     * Get coupon detail.
     */
    public function couponDetail(string $encryptedId): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $id = SecurityHelper::decryptId($encryptedId);
        if (!$id) { $this->jsonResponse(false, 'Invalid ID.', 400); return; }

        $coupon = Coupon::getCouponById($id);
        if (!$coupon) { $this->jsonResponse(false, 'Coupon not found.', 404); return; }

        $this->jsonResponse(true, 'Coupon loaded.', 200, ['coupon' => $coupon]);
    }

    /**
     * Store new coupon.
     */
    public function storeCoupon(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        if (empty(trim($input['code'] ?? ''))) {
            $this->jsonResponse(false, 'Coupon code is required.', 400);
            return;
        }

        $id = Coupon::createCoupon($input);
        if ($id) {
            \App\Models\AuditLog::log('coupon.created', 'Coupon', $id, null, ['code' => $input['code'] ?? '']);
            $this->jsonResponse(true, 'Coupon created successfully.', 200, ['encrypted_id' => SecurityHelper::encryptId($id)]);
        } else {
            $this->jsonResponse(false, 'Failed to create coupon.', 400);
        }
    }

    /**
     * Update coupon.
     */
    public function updateCoupon(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $id = SecurityHelper::decryptId($input['coupon_id'] ?? '');
        if (!$id) { $this->jsonResponse(false, 'Invalid coupon ID.', 400); return; }

        $oldCoupon = Coupon::getCouponById($id);
        $success = Coupon::updateCoupon($id, $input);
        if ($success) {
            \App\Models\AuditLog::log('coupon.updated', 'Coupon', $id, $oldCoupon, $input);
        }
        $this->jsonResponse($success, $success ? 'Coupon updated successfully.' : 'Failed to update coupon.', $success ? 200 : 400);
    }

    /**
     * Toggle coupon active.
     */
    public function toggleCouponActive(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $id = SecurityHelper::decryptId($input['coupon_id'] ?? '');
        if (!$id) { $this->jsonResponse(false, 'Invalid coupon ID.', 400); return; }

        $success = Coupon::toggleActive($id);
        if ($success) {
            \App\Models\AuditLog::log('coupon.toggle_active', 'Coupon', $id);
        }
        $this->jsonResponse($success, $success ? 'Coupon status toggled.' : 'Failed.', $success ? 200 : 400);
    }

    /**
     * Delete coupon.
     */
    public function deleteCoupon(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $id = SecurityHelper::decryptId($input['coupon_id'] ?? '');
        if (!$id) { $this->jsonResponse(false, 'Invalid coupon ID.', 400); return; }

        $oldCoupon = Coupon::getCouponById($id);
        $success = Coupon::deleteCoupon($id);
        if ($success) {
            \App\Models\AuditLog::log('coupon.deleted', 'Coupon', $id, $oldCoupon, null);
        }
        $this->jsonResponse($success, $success ? 'Coupon deleted successfully.' : 'Failed.', $success ? 200 : 400);
    }

    // ===========================================================================
    // OFFERS
    // ===========================================================================

    public function offerDetail(string $encryptedId): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $id = SecurityHelper::decryptId($encryptedId);
        if (!$id) { $this->jsonResponse(false, 'Invalid ID.', 400); return; }

        $offer = Coupon::getOfferById($id);
        if (!$offer) { $this->jsonResponse(false, 'Offer not found.', 404); return; }

        $this->jsonResponse(true, 'Offer loaded.', 200, ['offer' => $offer]);
    }

    public function storeOffer(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        if (empty(trim($input['name'] ?? ''))) {
            $this->jsonResponse(false, 'Offer name is required.', 400);
            return;
        }

        $id = Coupon::createOffer($input);
        if ($id) {
            \App\Models\AuditLog::log('offer.created', 'Offer', $id, null, ['name' => $input['name'] ?? '']);
            $this->jsonResponse(true, 'Offer created successfully.', 200, ['encrypted_id' => SecurityHelper::encryptId($id)]);
        } else {
            $this->jsonResponse(false, 'Failed to create offer.', 400);
        }
    }

    public function updateOffer(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $id = SecurityHelper::decryptId($input['offer_id'] ?? '');
        if (!$id) { $this->jsonResponse(false, 'Invalid offer ID.', 400); return; }

        $oldOffer = Coupon::getOfferById($id);
        $success = Coupon::updateOffer($id, $input);
        if ($success) {
            \App\Models\AuditLog::log('offer.updated', 'Offer', $id, $oldOffer, $input);
        }
        $this->jsonResponse($success, $success ? 'Offer updated successfully.' : 'Failed to update offer.', $success ? 200 : 400);
    }

    public function toggleOfferActive(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $id = SecurityHelper::decryptId($input['offer_id'] ?? '');
        if (!$id) { $this->jsonResponse(false, 'Invalid offer ID.', 400); return; }

        $success = Coupon::toggleOfferActive($id);
        if ($success) {
            \App\Models\AuditLog::log('offer.toggle_active', 'Offer', $id);
        }
        $this->jsonResponse($success, $success ? 'Offer status toggled.' : 'Failed.', $success ? 200 : 400);
    }

    public function deleteOffer(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $id = SecurityHelper::decryptId($input['offer_id'] ?? '');
        if (!$id) { $this->jsonResponse(false, 'Invalid offer ID.', 400); return; }

        $oldOffer = Coupon::getOfferById($id);
        $success = Coupon::deleteOffer($id);
        if ($success) {
            \App\Models\AuditLog::log('offer.deleted', 'Offer', $id, $oldOffer, null);
        }
        $this->jsonResponse($success, $success ? 'Offer deleted successfully.' : 'Failed.', $success ? 200 : 400);
    }
}
