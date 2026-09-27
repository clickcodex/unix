<?php

namespace App\Controllers\Admin;

use App\Models\Tag;
use App\Models\AuditLog;
use App\Helpers\SecurityHelper;

class TagController {

    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $tags = Tag::getAll();
        require __DIR__ . '/../../Views/admin/tags/index.php';
    }

    public function store(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();

        $ok = Tag::create($input);
        if ($ok) {
            AuditLog::log('tag.created', 'Tag', null, null, ['name' => $input['name'] ?? '']);
        }
        $this->jsonResponse($ok, $ok ? 'Tag created successfully.' : 'Failed to create tag.');
    }

    public function update(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $encId = $input['tag_id'] ?? $input['id'] ?? '';
        $id = is_numeric($encId) ? (int)$encId : SecurityHelper::decryptId((string)$encId);

        if (!$id) {
            $this->jsonResponse(false, 'Invalid Tag ID.', 400);
            return;
        }

        $ok = Tag::update($id, $input);
        if ($ok) {
            AuditLog::log('tag.updated', 'Tag', $id);
        }
        $this->jsonResponse($ok, $ok ? 'Tag updated successfully.' : 'Failed to update tag.');
    }

    public function toggleActive(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $encId = $input['tag_id'] ?? $input['id'] ?? '';
        $id = is_numeric($encId) ? (int)$encId : SecurityHelper::decryptId((string)$encId);

        if (!$id) {
            $this->jsonResponse(false, 'Invalid Tag ID.', 400);
            return;
        }

        $ok = Tag::toggleActive($id);
        if ($ok) {
            AuditLog::log('tag.toggle_active', 'Tag', $id);
        }
        $this->jsonResponse($ok, $ok ? 'Tag active status toggled.' : 'Failed to toggle status.');
    }

    public function delete(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $encId = $input['tag_id'] ?? $input['id'] ?? '';
        $id = is_numeric($encId) ? (int)$encId : SecurityHelper::decryptId((string)$encId);

        if (!$id) {
            $this->jsonResponse(false, 'Invalid Tag ID.', 400);
            return;
        }

        $ok = Tag::delete($id);
        if ($ok) {
            AuditLog::log('tag.deleted', 'Tag', $id);
        }
        $this->jsonResponse($ok, $ok ? 'Tag deleted successfully.' : 'Failed to delete tag.');
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
