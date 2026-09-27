<?php

namespace App\Controllers\Admin;

use App\Models\Role;
use App\Models\AuditLog;
use App\Helpers\SecurityHelper;

class RoleController {

    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $roles = Role::getAllRoles();
        $permissionsGrouped = Role::getAllPermissionsGrouped();

        require __DIR__ . '/../../Views/admin/roles/index.php';
    }

    public function getRolePermissions(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $encId = $_GET['role_id'] ?? '';
        $roleId = is_numeric($encId) ? (int)$encId : SecurityHelper::decryptId((string)$encId);

        if (!$roleId) {
            $this->jsonResponse(false, 'Invalid Role ID.', 400);
            return;
        }

        $permIds = Role::getRolePermissions($roleId);
        $this->jsonResponse(true, 'Permissions loaded.', 200, ['permission_ids' => $permIds]);
    }

    public function updatePermissions(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();

        $encId = $input['role_id'] ?? '';
        $roleId = is_numeric($encId) ? (int)$encId : SecurityHelper::decryptId((string)$encId);
        $permIds = $input['permissions'] ?? [];

        if (!$roleId) {
            $this->jsonResponse(false, 'Invalid Role ID.', 400);
            return;
        }

        $ok = Role::updateRolePermissions($roleId, $permIds);
        if ($ok) {
            AuditLog::log('role.permissions_updated', 'Role', $roleId, null, ['permission_count' => count($permIds)]);
        }

        $this->jsonResponse($ok, $ok ? 'Role permissions updated successfully.' : 'Failed to update permissions.');
    }

    public function storeRole(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $name = trim($input['name'] ?? '');
        $slug = strtolower(trim($input['slug'] ?? ''));

        if (empty($name) || empty($slug)) {
            $this->jsonResponse(false, 'Role name and slug are required.', 400);
            return;
        }

        $ok = Role::createRole($name, $slug);
        if ($ok) {
            AuditLog::log('role.created', 'Role', null, null, ['name' => $name, 'slug' => $slug]);
        }

        $this->jsonResponse($ok, $ok ? 'Role created successfully.' : 'Failed to create role.');
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
