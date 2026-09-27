<?php

namespace App\Controllers\Admin;

use App\Models\Category;
use App\Helpers\SecurityHelper;

class CategoryController {
    /**
     * Display Categories Manager page or return JSON tree.
     */
    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $categoryTree = Category::getCategoryTree();
        $flatCategories = Category::getFlatCategories();
        $kpiData = Category::getKpiMetrics();

        $isAjax = (isset($_GET['ajax']) && $_GET['ajax'] == '1') || 
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'tree' => $categoryTree,
                'categories' => $flatCategories,
                'kpis' => $kpiData
            ]);
            exit;
        }

        require_once __DIR__ . '/../../Views/admin/categories/index.php';
    }

    private function parseId($id): ?int {
        if (empty($id)) return null;
        if (is_numeric($id)) return (int)$id;
        return SecurityHelper::decryptId((string)$id);
    }

    /**
     * Fetch single category detail.
     */
    public function detail(string $encryptedId): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $catId = $this->parseId($encryptedId);
        if (!$catId) {
            $this->jsonResponse(false, 'Invalid Category ID.', 400);
            return;
        }

        $category = Category::getCategoryById($catId);
        if (!$category) {
            $this->jsonResponse(false, 'Category not found.', 404);
            return;
        }

        $this->jsonResponse(true, 'Category details fetched.', 200, [
            'category' => $category
        ]);
    }

    /**
     * Store new category.
     */
    public function store(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $name = trim($input['name'] ?? '');

        if (empty($name)) {
            $this->jsonResponse(false, 'Category name is required.', 400);
            return;
        }

        if (!empty($input['parent_encrypted_id'])) {
            $input['parent_id'] = $this->parseId($input['parent_encrypted_id']);
        }

        $catId = Category::createCategory($input);
        if ($catId) {
            \App\Models\AuditLog::log('category.created', 'Category', $catId, null, ['name' => $name]);
            $this->jsonResponse(true, 'Category created successfully.', 200, [
                'encrypted_id' => SecurityHelper::encryptId($catId)
            ]);
        } else {
            $this->jsonResponse(false, 'Failed to create category.', 400);
        }
    }

    /**
     * Update category.
     */
    public function update(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $encryptedId = trim($input['category_id'] ?? $input['id'] ?? '');

        $catId = $this->parseId($encryptedId);
        if (!$catId) {
            $this->jsonResponse(false, 'Invalid Category ID.', 400);
            return;
        }

        if (!empty($input['parent_encrypted_id'])) {
            $input['parent_id'] = $this->parseId($input['parent_encrypted_id']);
        }

        $oldCat = Category::getCategoryById($catId);
        $success = Category::updateCategory($catId, $input);
        if ($success) {
            \App\Models\AuditLog::log('category.updated', 'Category', $catId, $oldCat, $input);
            $this->jsonResponse(true, 'Category updated successfully.');
        } else {
            $this->jsonResponse(false, 'Failed to update category.', 400);
        }
    }

    /**
     * Toggle active state.
     */
    public function toggleActive(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $encryptedId = trim($input['category_id'] ?? $input['id'] ?? '');

        $catId = $this->parseId($encryptedId);
        if (!$catId) {
            $this->jsonResponse(false, 'Invalid Category ID.', 400);
            return;
        }

        $res = Category::toggleActive($catId);
        if ($res) {
            \App\Models\AuditLog::log('category.toggle_active', 'Category', $catId);
        }
        $this->jsonResponse($res, $res ? 'Category status toggled successfully.' : 'Failed to toggle category status.');
    }

    /**
     * Delete category.
     */
    public function delete(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $encryptedId = trim($input['category_id'] ?? $input['id'] ?? '');

        $catId = $this->parseId($encryptedId);
        if (!$catId) {
            $this->jsonResponse(false, 'Invalid Category ID.', 400);
            return;
        }

        $oldCat = Category::getCategoryById($catId);
        $res = Category::deleteCategory($catId);
        if ($res) {
            \App\Models\AuditLog::log('category.deleted', 'Category', $catId, $oldCat, null);
        }
        $this->jsonResponse($res, $res ? 'Category deleted successfully.' : 'Failed to delete category.');
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
