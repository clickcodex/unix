<?php

namespace App\Controllers\Admin;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Helpers\SecurityHelper;

class ProductController {
    /**
     * Display or return JSON for Products Manager index.
     */
    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $page = (int)($_GET['page'] ?? 1);
        $category = trim($_GET['category'] ?? '');
        $status = trim($_GET['status'] ?? '');
        $stock = trim($_GET['stock'] ?? '');
        $featured = trim($_GET['featured'] ?? '');
        $search = trim($_GET['search'] ?? '');
        $isAjax = (isset($_GET['ajax']) && $_GET['ajax'] == '1') || 
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        $productData = Product::getPaginatedProducts($page, 10, $category, $status, $stock, $featured, $search);
        $kpiData = Product::getKpiMetrics();
        $categories = Product::getAllCategories();

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $productData['products'],
                'pagination' => [
                    'totalCount' => $productData['totalCount'],
                    'totalPages' => $productData['totalPages'],
                    'currentPage' => $productData['currentPage'],
                    'perPage' => $productData['perPage']
                ],
                'kpis' => $kpiData,
                'categories' => $categories
            ]);
            exit;
        }

        require_once __DIR__ . '/../../Views/admin/products/index.php';
    }

    /**
     * Render high-scale Product Create Page.
     */
    public function create(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $product = [];
        $categories = Product::getAllCategories();
        $units = $this->getUnitsList();
        $allTags = $this->getTagsList();
        require __DIR__ . '/../../Views/admin/products/edit.php';
    }

    private function parseProductId($id): ?int {
        if (empty($id)) return null;
        if (is_numeric($id)) return (int)$id;
        return SecurityHelper::decryptId((string)$id);
    }

    /**
     * Render high-scale Product Edit Page.
     */
    public function edit(string $encryptedId): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $productId = $this->parseProductId($encryptedId);
        if (!$productId) {
            header('Location: ' . BASE_URL . '/admin/products');
            exit;
        }

        $product = Product::getProductById($productId);
        if (!$product) {
            header('Location: ' . BASE_URL . '/admin/products');
            exit;
        }

        $categories = Product::getAllCategories();
        $units = $this->getUnitsList();
        $allTags = $this->getTagsList();
        require __DIR__ . '/../../Views/admin/products/edit.php';
    }

    private function getUnitsList(): array {
        $db = \App\Config\Database::connect();
        $res = $db->query("SELECT * FROM units ORDER BY id ASC");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    private function getTagsList(): array {
        $db = \App\Config\Database::connect();
        $res = $db->query("SELECT * FROM tags WHERE is_active = 1 ORDER BY name ASC");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Fetch single product details with encrypted or numeric ID.
     */
    public function detail(string $encryptedId): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $productId = $this->parseProductId($encryptedId);
        if (!$productId) {
            $this->jsonResponse(false, 'Invalid Product ID.', 400);
            return;
        }

        $product = Product::getProductById($productId);
        if (!$product) {
            $this->jsonResponse(false, 'Product not found.', 404);
            return;
        }

        $this->jsonResponse(true, 'Product details retrieved successfully.', 200, [
            'product' => $product
        ]);
    }

    /**
     * Store new product.
     */
    public function store(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $name = trim($input['name'] ?? '');

        if (empty($name)) {
            $this->jsonResponse(false, 'Product name is required.', 400);
            return;
        }

        if (!empty($input['category_encrypted_id'])) {
            $input['category_id'] = $this->parseProductId($input['category_encrypted_id']);
        }

        $productId = Product::createProduct($input);
        if ($productId) {
            \App\Models\AuditLog::log('product.created', 'Product', $productId, null, ['name' => $name, 'sku' => $input['sku'] ?? '']);
            $this->jsonResponse(true, 'Product created successfully.', 200, [
                'encrypted_id' => SecurityHelper::encryptId($productId)
            ]);
        } else {
            $this->jsonResponse(false, 'Failed to create product.', 400);
        }
    }

    /**
     * Update product.
     */
    public function update(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $input = $this->getRequestInput();
        $encryptedId = trim($input['product_id'] ?? $input['id'] ?? '');

        $productId = $this->parseProductId($encryptedId);
        if (!$productId) {
            $this->jsonResponse(false, 'Invalid Product ID.', 400);
            return;
        }

        if (!empty($input['category_encrypted_id'])) {
            $input['category_id'] = $this->parseProductId($input['category_encrypted_id']);
        }

        $oldProd = Product::getProductById($productId);
        $success = Product::updateProduct($productId, $input);
        if ($success) {
            \App\Models\AuditLog::log('product.updated', 'Product', $productId, $oldProd, $input);
            $this->jsonResponse(true, 'Product updated successfully.');
        } else {
            $this->jsonResponse(false, 'Failed to update product.', 400);
        }
    }

    /**
     * Toggle product active status.
     */
    public function toggleActive(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $productId = $this->parseProductId($input['product_id'] ?? $input['id'] ?? '');

        if (!$productId) {
            $this->jsonResponse(false, 'Invalid Product ID.', 400);
            return;
        }

        $res = Product::toggleActive($productId);
        if ($res) {
            \App\Models\AuditLog::log('product.toggle_active', 'Product', $productId);
        }
        $this->jsonResponse($res, $res ? 'Active state toggled successfully.' : 'Failed to toggle active state.');
    }

    /**
     * Toggle product stock status.
     */
    public function toggleStock(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $productId = $this->parseProductId($input['product_id'] ?? $input['id'] ?? '');

        if (!$productId) {
            $this->jsonResponse(false, 'Invalid Product ID.', 400);
            return;
        }

        $res = Product::toggleStock($productId);
        if ($res) {
            \App\Models\AuditLog::log('product.toggle_stock', 'Product', $productId);
        }
        $this->jsonResponse($res, $res ? 'Stock state toggled successfully.' : 'Failed to toggle stock state.');
    }

    /**
     * Toggle product featured status.
     */
    public function toggleFeatured(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $productId = $this->parseProductId($input['product_id'] ?? $input['id'] ?? '');

        if (!$productId) {
            $this->jsonResponse(false, 'Invalid Product ID.', 400);
            return;
        }

        $res = Product::toggleFeatured($productId);
        if ($res) {
            \App\Models\AuditLog::log('product.toggle_featured', 'Product', $productId);
        }
        $this->jsonResponse($res, $res ? 'Featured state toggled successfully.' : 'Failed to toggle featured state.');
    }

    /**
     * Soft delete product.
     */
    public function delete(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $productId = $this->parseProductId($input['product_id'] ?? $input['id'] ?? '');

        if (!$productId) {
            $this->jsonResponse(false, 'Invalid Product ID.', 400);
            return;
        }

        $oldProd = Product::getProductById($productId);
        $res = Product::softDeleteProduct($productId);
        if ($res) {
            \App\Models\AuditLog::log('product.deleted', 'Product', $productId, $oldProd, null);
        }
        $this->jsonResponse($res, $res ? 'Product soft-deleted successfully.' : 'Failed to delete product.');
    }

    /**
     * Restore soft-deleted product.
     */
    public function restore(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $productId = $this->parseProductId($input['product_id'] ?? $input['id'] ?? '');

        if (!$productId) {
            $this->jsonResponse(false, 'Invalid Product ID.', 400);
            return;
        }

        $res = Product::restoreProduct($productId);
        if ($res) {
            \App\Models\AuditLog::log('product.restored', 'Product', $productId);
        }
        $this->jsonResponse($res, $res ? 'Product restored successfully.' : 'Failed to restore product.');
    }

    /**
     * Execute bulk operations on products.
     */
    public function bulkAction(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $encryptedIds = $input['product_ids'] ?? [];
        $action = trim($input['action'] ?? '');

        if (!is_array($encryptedIds) || empty($encryptedIds) || empty($action)) {
            $this->jsonResponse(false, 'Selected products and action are required.', 400);
            return;
        }

        $rawIds = [];
        foreach ($encryptedIds as $encId) {
            $dec = SecurityHelper::decryptId($encId);
            if ($dec) $rawIds[] = $dec;
        }

        if (empty($rawIds)) {
            $this->jsonResponse(false, 'No valid product IDs provided.', 400);
            return;
        }

        $res = Product::bulkAction($rawIds, $action);
        if ($res) {
            \App\Models\AuditLog::log('product.bulk_action', 'Product', null, null, ['action' => $action, 'raw_ids' => $rawIds]);
        }
        $this->jsonResponse($res, $res ? "Bulk action '{$action}' applied successfully." : 'Failed to perform bulk action.');
    }

    /**
     * Product Variants Manager View / API.
     */
    public function variants(string $encryptedProductId): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $productId = SecurityHelper::decryptId($encryptedProductId);
        if (!$productId) {
            header('Location: ' . BASE_URL . '/admin/products');
            exit;
        }

        $product = Product::getProductById($productId);
        if (!$product) {
            header('Location: ' . BASE_URL . '/admin/products');
            exit;
        }

        $variants = ProductVariant::getVariantsByProductId($productId);
        $attributeGroups = ProductVariant::getAllAttributeGroupsWithValues();
        $isAjax = (isset($_GET['ajax']) && $_GET['ajax'] == '1') || 
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'product' => $product,
                'variants' => $variants,
                'attributeGroups' => $attributeGroups
            ]);
            exit;
        }

        require __DIR__ . '/../../Views/admin/products/variants.php';
    }

    /**
     * Store variant.
     */
    public function storeVariant(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $encryptedProductId = trim($input['product_id'] ?? '');

        $productId = SecurityHelper::decryptId($encryptedProductId);
        if (!$productId) {
            $this->jsonResponse(false, 'Invalid Product ID.', 400);
            return;
        }

        $variantId = ProductVariant::createVariant($productId, $input);
        if ($variantId) {
            $this->jsonResponse(true, 'Variant created successfully.', 200, [
                'encrypted_id' => SecurityHelper::encryptId($variantId)
            ]);
        } else {
            $this->jsonResponse(false, 'Failed to create variant.', 400);
        }
    }

    /**
     * Update variant.
     */
    public function updateVariant(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $encryptedVariantId = trim($input['variant_id'] ?? '');

        $variantId = SecurityHelper::decryptId($encryptedVariantId);
        if (!$variantId) {
            $this->jsonResponse(false, 'Invalid Variant ID.', 400);
            return;
        }

        $res = ProductVariant::updateVariant($variantId, $input);
        $this->jsonResponse($res, $res ? 'Variant updated successfully.' : 'Failed to update variant.');
    }

    /**
     * Delete variant.
     */
    public function deleteVariant(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $encryptedVariantId = trim($input['variant_id'] ?? '');

        $variantId = SecurityHelper::decryptId($encryptedVariantId);
        if (!$variantId) {
            $this->jsonResponse(false, 'Invalid Variant ID.', 400);
            return;
        }

        $res = ProductVariant::deleteVariant($variantId);
        $this->jsonResponse($res, $res ? 'Variant deleted successfully.' : 'Failed to delete variant.');
    }

    /**
     * Toggle variant active status.
     */
    public function toggleVariantActive(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $encryptedVariantId = trim($input['variant_id'] ?? '');

        $variantId = SecurityHelper::decryptId($encryptedVariantId);
        if (!$variantId) {
            $this->jsonResponse(false, 'Invalid Variant ID.', 400);
            return;
        }

        $res = ProductVariant::toggleVariantActive($variantId);
        $this->jsonResponse($res, $res ? 'Variant active status toggled.' : 'Failed to toggle variant status.');
    }

    /**
     * Handle Image and Video file uploads.
     */
    public function uploadMedia(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        if (empty($_FILES['file'])) {
            $this->jsonResponse(false, 'No file received by server.', 400);
            return;
        }

        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMsgs = [
                UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
                UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.',
                UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
                UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder on the server.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.'
            ];
            $msg = $errorMsgs[$file['error']] ?? ('Upload error code: ' . $file['error']);
            $this->jsonResponse(false, $msg, 400);
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'webm', 'ogg', 'mov', 'avi'];

        if (!in_array($ext, $allowed)) {
            $this->jsonResponse(false, 'Invalid file type. Allowed: JPG, PNG, WEBP, GIF, MP4, WEBM.', 400);
            return;
        }

        // Target directory in public/uploads/products/
        $uploadDir = __DIR__ . '/../../../public/uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = 'prod_' . uniqid() . '_' . time() . '.' . $ext;
        $targetPath = $uploadDir . $filename;

        $saved = false;
        if (is_uploaded_file($file['tmp_name'])) {
            $saved = move_uploaded_file($file['tmp_name'], $targetPath);
        } else {
            $saved = copy($file['tmp_name'], $targetPath);
        }

        if ($saved) {
            $fileUrl = BASE_URL . '/public/uploads/products/' . $filename;
            $this->jsonResponse(true, 'File uploaded successfully.', 200, [
                'url' => $fileUrl,
                'filename' => $filename,
                'is_video' => in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'avi'])
            ]);
        } else {
            $this->jsonResponse(false, 'Failed to save uploaded file to destination directory.', 500);
        }
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
