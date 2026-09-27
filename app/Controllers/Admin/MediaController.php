<?php

namespace App\Controllers\Admin;

use App\Models\Media;
use App\Helpers\SecurityHelper;

class MediaController {

    /**
     * Display or return JSON for Media Library Manager.
     */
    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 24);
        $type = trim($_GET['type'] ?? '');
        $folder = trim($_GET['folder'] ?? '');
        $sort = trim($_GET['sort'] ?? 'newest');
        $search = trim($_GET['search'] ?? '');
        
        $isAjax = (isset($_GET['ajax']) && $_GET['ajax'] == '1') || 
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        $filters = [
            'type' => $type,
            'folder' => $folder,
            'sort' => $sort,
            'search' => $search
        ];

        $mediaData = Media::getPaginatedMedia($page, $perPage, $filters);
        $kpis = Media::getKpiMetrics();
        $kpiData = $kpis;
        $folders = Media::getFoldersList();

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $mediaData['media'],
                'pagination' => [
                    'totalCount' => $mediaData['totalCount'],
                    'totalPages' => $mediaData['totalPages'],
                    'currentPage' => $mediaData['currentPage'],
                    'perPage' => $mediaData['perPage']
                ],
                'kpis' => $kpiData,
                'folders' => $folders
            ]);
            exit;
        }

        require_once __DIR__ . '/../../Views/admin/media/index.php';
    }

    /**
     * Handle single/multiple file uploads via AJAX or Form submission.
     */
    public function upload(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        $folder = trim($_POST['folder'] ?? 'general');
        $folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $folder);
        if (empty($folder)) $folder = 'general';

        $files = [];
        if (isset($_FILES['files'])) {
            $f = $_FILES['files'];
            if (is_array($f['name'])) {
                for ($i = 0; $i < count($f['name']); $i++) {
                    if (!empty($f['name'][$i]) && $f['error'][$i] === UPLOAD_ERR_OK) {
                        $files[] = [
                            'name' => $f['name'][$i],
                            'type' => $f['type'][$i],
                            'tmp_name' => $f['tmp_name'][$i],
                            'error' => $f['error'][$i],
                            'size' => $f['size'][$i]
                        ];
                    }
                }
            }
        } elseif (isset($_FILES['file'])) {
            if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
                $files[] = $_FILES['file'];
            }
        }

        if (empty($files)) {
            echo json_encode(['success' => false, 'message' => 'No valid files uploaded or file size exceeds PHP upload limits.']);
            exit;
        }

        $uploadedResults = [];
        $errors = [];

        // Destination base dir: public/uploads/media/YYYY/MM
        $year = date('Y');
        $month = date('m');
        $relativeDir = "uploads/media/{$year}/{$month}";
        $uploadDir = __DIR__ . "/../../../public/{$relativeDir}";

        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $baseUrl = defined('BASE_URL') ? BASE_URL : '';

        foreach ($files as $file) {
            $originalName = basename($file['name']);
            $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            $fileSize = (int)$file['size'];

            // Allowed extensions & mime validation
            $allowedExtensions = [
                // Images
                'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif', 'ico',
                // Documents
                'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv',
                // Archives & Audio/Video
                'zip', 'rar', '7z', 'mp3', 'wav', 'mp4', 'webm', 'mov'
            ];

            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors[] = "File '{$originalName}' has an unallowed file extension (.{$fileExtension}).";
                continue;
            }

            // Max file size: 50MB
            if ($fileSize > 50 * 1024 * 1024) {
                $errors[] = "File '{$originalName}' exceeds maximum size of 50MB.";
                continue;
            }

            // Safe unique file name
            $cleanName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
            $uniqueFilename = $cleanName . '_' . time() . '_' . substr(bin2hex(random_bytes(4)), 0, 8) . '.' . $fileExtension;
            $targetPath = $uploadDir . '/' . $uniqueFilename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $mimeType = $file['type'] ?: mime_content_type($targetPath);
                $width = null;
                $height = null;

                // Extract image dimensions if image
                if (strpos($mimeType, 'image/') === 0 && $fileExtension !== 'svg') {
                    $imgSize = @getimagesize($targetPath);
                    if ($imgSize) {
                        $width = $imgSize[0];
                        $height = $imgSize[1];
                    }
                }

                $publicUrl = $baseUrl . '/public/' . $relativeDir . '/' . $uniqueFilename;

                $recordData = [
                    'filename' => $uniqueFilename,
                    'original_name' => $originalName,
                    'mime_type' => $mimeType,
                    'size_bytes' => $fileSize,
                    'width' => $width,
                    'height' => $height,
                    'alt_text' => pathinfo($originalName, PATHINFO_FILENAME),
                    'folder' => $folder,
                    'storage_disk' => 'local',
                    'storage_path' => realpath($targetPath) ?: $targetPath,
                    'public_url' => $publicUrl,
                    'uploaded_by' => $_SESSION['admin_user_id'] ?? null
                ];

                $createdMedia = Media::createMediaRecord($recordData);
                if ($createdMedia) {
                    $uploadedResults[] = $createdMedia;
                } else {
                    $errors[] = "Failed to save database record for '{$originalName}'.";
                }
            } else {
                $errors[] = "Failed to move uploaded file '{$originalName}' to storage.";
            }
        }

        if (count($uploadedResults) > 0) {
            \App\Models\AuditLog::log('media.uploaded', 'Media', null, null, ['count' => count($uploadedResults)]);
        }

        echo json_encode([
            'success' => count($uploadedResults) > 0,
            'message' => count($uploadedResults) > 0 
                ? count($uploadedResults) . ' file(s) uploaded successfully.' 
                : 'Upload failed.',
            'uploaded' => $uploadedResults,
            'errors' => $errors
        ]);
        exit;
    }

    /**
     * Get single media detail by encrypted or numeric ID.
     */
    public function detail(string $encryptedId): void {
        \App\Middleware\AdminAuthMiddleware::check();
        header('Content-Type: application/json');

        $mediaId = SecurityHelper::decryptId($encryptedId);
        if (!$mediaId && is_numeric($encryptedId)) {
            $mediaId = (int)$encryptedId;
        }

        if (!$mediaId) {
            echo json_encode(['success' => false, 'message' => 'Invalid media ID.']);
            exit;
        }

        $media = Media::getMediaById($mediaId);
        if (!$media) {
            echo json_encode(['success' => false, 'message' => 'Media file not found.']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => $media
        ]);
        exit;
    }

    /**
     * Update media metadata (original name, alt text, folder).
     */
    public function update(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        $encryptedId = trim($_POST['id'] ?? $_POST['encrypted_id'] ?? '');
        $mediaId = SecurityHelper::decryptId($encryptedId);
        if (!$mediaId && is_numeric($encryptedId)) {
            $mediaId = (int)$encryptedId;
        }

        if (!$mediaId) {
            echo json_encode(['success' => false, 'message' => 'Invalid media asset ID.']);
            exit;
        }

        $originalName = trim($_POST['original_name'] ?? '');
        $altText = trim($_POST['alt_text'] ?? '');
        $folder = trim($_POST['folder'] ?? 'general');

        if (empty($originalName)) {
            echo json_encode(['success' => false, 'message' => 'Original filename cannot be empty.']);
            exit;
        }

        $oldMedia = Media::getMediaById($mediaId);
        $success = Media::updateMediaMetadata($mediaId, $originalName, $altText, $folder);

        if ($success) {
            \App\Models\AuditLog::log('media.updated', 'Media', $mediaId, $oldMedia, ['original_name' => $originalName, 'alt_text' => $altText, 'folder' => $folder]);
            $updatedMedia = Media::getMediaById($mediaId);
            echo json_encode([
                'success' => true,
                'message' => 'Media metadata updated successfully.',
                'data' => $updatedMedia
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to update media metadata.'
            ]);
        }
        exit;
    }

    /**
     * Delete media item.
     */
    public function delete(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        $encryptedId = trim($_POST['id'] ?? $_POST['encrypted_id'] ?? '');
        $mediaId = SecurityHelper::decryptId($encryptedId);
        if (!$mediaId && is_numeric($encryptedId)) {
            $mediaId = (int)$encryptedId;
        }

        if (!$mediaId) {
            echo json_encode(['success' => false, 'message' => 'Invalid media ID.']);
            exit;
        }

        $oldMedia = Media::getMediaById($mediaId);
        $success = Media::deleteMedia($mediaId);

        if ($success) {
            \App\Models\AuditLog::log('media.deleted', 'Media', $mediaId, $oldMedia, null);
            echo json_encode([
                'success' => true,
                'message' => 'Media item deleted successfully.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to delete media item or item no longer exists.'
            ]);
        }
        exit;
    }

    /**
     * Bulk delete media items.
     */
    public function bulkDelete(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
            exit;
        }

        $idsInput = $_POST['ids'] ?? [];
        if (!is_array($idsInput) || empty($idsInput)) {
            echo json_encode(['success' => false, 'message' => 'No items selected for deletion.']);
            exit;
        }

        $decryptedIds = [];
        foreach ($idsInput as $idVal) {
            $decId = SecurityHelper::decryptId($idVal);
            if (!$decId && is_numeric($idVal)) {
                $decId = (int)$idVal;
            }
            if ($decId) {
                $decryptedIds[] = $decId;
            }
        }

        if (empty($decryptedIds)) {
            echo json_encode(['success' => false, 'message' => 'Invalid media item IDs.']);
            exit;
        }

        $deletedCount = Media::bulkDeleteMedia($decryptedIds);

        echo json_encode([
            'success' => true,
            'message' => "Successfully deleted {$deletedCount} media file(s).",
            'deleted_count' => $deletedCount
        ]);
        exit;
    }

    /**
     * API for Modal Media Picker in other admin features (Products, Banners, Categories).
     */
    public function pickerApi(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        header('Content-Type: application/json');

        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 18);
        $type = trim($_GET['type'] ?? 'image'); // Default to images for pickers
        $search = trim($_GET['search'] ?? '');

        $mediaData = Media::getPaginatedMedia($page, $perPage, [
            'type' => $type,
            'search' => $search,
            'sort' => 'newest'
        ]);

        echo json_encode([
            'success' => true,
            'data' => $mediaData['media'],
            'pagination' => [
                'totalCount' => $mediaData['totalCount'],
                'totalPages' => $mediaData['totalPages'],
                'currentPage' => $mediaData['currentPage'],
                'perPage' => $mediaData['perPage']
            ]
        ]);
        exit;
    }
}
