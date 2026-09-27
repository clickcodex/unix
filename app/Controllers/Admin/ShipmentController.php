<?php

namespace App\Controllers\Admin;

use App\Models\Shipment;
use App\Helpers\SecurityHelper;

class ShipmentController {

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
     * Shipments management listing page.
     */
    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $filters = [
            'status'  => $_GET['status'] ?? '',
            'carrier' => $_GET['carrier'] ?? '',
            'search'  => $_GET['search'] ?? '',
        ];

        $result = Shipment::getPaginatedShipments($page, 15, $filters);
        $kpis = Shipment::getKpiMetrics();

        if (!empty($_GET['ajax'])) {
            $this->jsonResponse(true, 'Shipments retrieved.', 200, [
                'data'       => $result['shipments'],
                'pagination' => $result['pagination'],
                'kpis'       => $kpis,
            ]);
            return;
        }

        $shipments = $result['shipments'];
        $pagination = $result['pagination'];
        require __DIR__ . '/../../Views/admin/shipments/index.php';
    }

    /**
     * Fetch shipment detail.
     */
    public function detail(string $encryptedId): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $id = SecurityHelper::decryptId($encryptedId);
        if (!$id) {
            $this->jsonResponse(false, 'Invalid shipment ID.', 400);
            return;
        }

        $shipment = Shipment::getShipmentById($id);
        if (!$shipment) {
            $this->jsonResponse(false, 'Shipment record not found.', 404);
            return;
        }

        $this->jsonResponse(true, 'Shipment detail retrieved.', 200, ['shipment' => $shipment]);
    }

    /**
     * Update shipment carrier tracking or status.
     */
    public function update(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $encryptedId = trim($input['shipment_id'] ?? '');

        $id = SecurityHelper::decryptId($encryptedId);
        if (!$id) {
            $this->jsonResponse(false, 'Invalid shipment ID.', 400);
            return;
        }

        $oldShipment = Shipment::getShipmentById($id);
        $success = Shipment::updateShipment($id, $input);
        if ($success) {
            \App\Models\AuditLog::log('shipment.updated', 'Shipment', $id, $oldShipment, $input);
            $this->jsonResponse(true, 'Shipment tracking updated successfully.');
        } else {
            $this->jsonResponse(false, 'Failed to update shipment.', 400);
        }
    }
}
