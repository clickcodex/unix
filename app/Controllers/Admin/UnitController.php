<?php

namespace App\Controllers\Admin;

use App\Models\Unit;
use App\Models\AuditLog;
use App\Helpers\SecurityHelper;

class UnitController {

    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $units = Unit::getAll();
        require __DIR__ . '/../../Views/admin/units/index.php';
    }

    public function store(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();

        $ok = Unit::create($input);
        if ($ok) {
            AuditLog::log('unit.created', 'Unit', null, null, ['name' => $input['name'] ?? '']);
        }
        $this->jsonResponse($ok, $ok ? 'Unit created successfully.' : 'Failed to create unit.');
    }

    public function update(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $encId = $input['unit_id'] ?? $input['id'] ?? '';
        $id = is_numeric($encId) ? (int)$encId : SecurityHelper::decryptId((string)$encId);

        if (!$id) {
            $this->jsonResponse(false, 'Invalid Unit ID.', 400);
            return;
        }

        $ok = Unit::update($id, $input);
        if ($ok) {
            AuditLog::log('unit.updated', 'Unit', $id);
        }
        $this->jsonResponse($ok, $ok ? 'Unit updated successfully.' : 'Failed to update unit.');
    }

    public function delete(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $encId = $input['unit_id'] ?? $input['id'] ?? '';
        $id = is_numeric($encId) ? (int)$encId : SecurityHelper::decryptId((string)$encId);

        if (!$id) {
            $this->jsonResponse(false, 'Invalid Unit ID.', 400);
            return;
        }

        $ok = Unit::delete($id);
        if ($ok) {
            AuditLog::log('unit.deleted', 'Unit', $id);
        }
        $this->jsonResponse($ok, $ok ? 'Unit deleted successfully.' : 'Failed to delete unit.');
    }

    private function getRequestInput(): array {
        $isJson = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
        return $isJson ? (json_decode(file_get_contents('php://input'), true) ?? []) : $_POST;
    }

    private function jsonResponse(bool $success, string $message, int $statusCode = 200, array $extra = []): void {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message
        ], $extra));
        exit;
    }
}
