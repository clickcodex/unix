<?php

namespace App\Models;

use App\Config\Database;
use App\Helpers\SecurityHelper;

class Media {

    /**
     * Ensure media table exists in the database.
     */
    public static function ensureTableExists(): void {
        try {
            $db = Database::connect();
            $sql = "CREATE TABLE IF NOT EXISTS media (
                id              BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
                filename        VARCHAR(255)     NOT NULL,
                original_name   VARCHAR(255)     NOT NULL,
                mime_type       VARCHAR(100)     NOT NULL,
                size_bytes      BIGINT UNSIGNED  NOT NULL,
                width           SMALLINT UNSIGNED NULL,
                height          SMALLINT UNSIGNED NULL,
                alt_text        VARCHAR(255)     NULL,
                folder          VARCHAR(100)     NOT NULL DEFAULT 'general',
                storage_disk    VARCHAR(30)      NOT NULL DEFAULT 'local',
                storage_path    VARCHAR(500)     NOT NULL,
                public_url      VARCHAR(500)     NOT NULL,
                uploaded_by     BIGINT UNSIGNED  NULL,
                created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_media_uploader (uploaded_by),
                KEY idx_media_type     (mime_type),
                KEY idx_media_folder   (folder)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $db->query($sql);

            // Ensure columns alt_text and folder exist for older tables
            @$db->query("ALTER TABLE media ADD COLUMN IF NOT EXISTS alt_text VARCHAR(255) NULL AFTER height");
            @$db->query("ALTER TABLE media ADD COLUMN IF NOT EXISTS folder VARCHAR(100) NOT NULL DEFAULT 'general' AFTER alt_text");
        } catch (\Throwable $e) {
            error_log("Failed to create media table: " . $e->getMessage());
        }
    }

    /**
     * Calculate KPI metrics for the media library dashboard.
     */
    public static function getKpiMetrics(): array {
        self::ensureTableExists();
        $db = Database::connect();

        $res = $db->query("
            SELECT 
                COUNT(*) AS total_files,
                SUM(CASE WHEN mime_type LIKE 'image/%' THEN 1 ELSE 0 END) AS total_images,
                SUM(CASE WHEN mime_type LIKE 'application/%' OR mime_type LIKE 'text/%' THEN 1 ELSE 0 END) AS total_documents,
                SUM(CASE WHEN mime_type LIKE 'video/%' THEN 1 ELSE 0 END) AS total_videos,
                SUM(CASE WHEN mime_type LIKE 'audio/%' THEN 1 ELSE 0 END) AS total_audio,
                COALESCE(SUM(size_bytes), 0) AS total_size_bytes
            FROM media
        ");

        $row = $res ? $res->fetch_assoc() : [];
        $totalBytes = (int)($row['total_size_bytes'] ?? 0);

        return [
            'total_files' => (int)($row['total_files'] ?? 0),
            'total_images' => (int)($row['total_images'] ?? 0),
            'total_documents' => (int)($row['total_documents'] ?? 0),
            'total_videos' => (int)($row['total_videos'] ?? 0),
            'total_audio' => (int)($row['total_audio'] ?? 0),
            'total_size_bytes' => $totalBytes,
            'formatted_total_size' => self::formatBytes($totalBytes)
        ];
    }

    /**
     * Get paginated list of media files with filters.
     */
    public static function getPaginatedMedia(int $page = 1, int $perPage = 24, array $filters = []): array {
        self::ensureTableExists();
        $db = Database::connect();

        $where = [];
        $params = [];
        $types = '';

        // Filter by File Type Category
        if (!empty($filters['type'])) {
            switch ($filters['type']) {
                case 'image':
                    $where[] = "mime_type LIKE 'image/%'";
                    break;
                case 'document':
                    $where[] = "(mime_type LIKE 'application/%' OR mime_type LIKE 'text/%')";
                    break;
                case 'video':
                    $where[] = "mime_type LIKE 'video/%'";
                    break;
                case 'audio':
                    $where[] = "mime_type LIKE 'audio/%'";
                    break;
                case 'archive':
                    $where[] = "(mime_type LIKE '%zip%' OR mime_type LIKE '%tar%' OR mime_type LIKE '%rar%' OR mime_type LIKE '%7z%')";
                    break;
            }
        }

        // Filter by Folder
        if (!empty($filters['folder'])) {
            $where[] = "folder = ?";
            $params[] = $filters['folder'];
            $types .= 's';
        }

        // Search query
        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where[] = "(original_name LIKE ? OR filename LIKE ? OR alt_text LIKE ? OR folder LIKE ?)";
            $params = array_merge($params, [$s, $s, $s, $s]);
            $types .= 'ssss';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Count query
        $stmtCount = $db->prepare("SELECT COUNT(*) AS cnt FROM media {$whereClause}");
        if ($types && $stmtCount) {
            $stmtCount->bind_param($types, ...$params);
        }
        if ($stmtCount) {
            $stmtCount->execute();
            $totalCount = (int)$stmtCount->get_result()->fetch_assoc()['cnt'];
            $stmtCount->close();
        } else {
            $totalCount = 0;
        }

        $totalPages = max(1, (int)ceil($totalCount / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        // Sorting
        $orderBy = "id DESC";
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'oldest':
                    $orderBy = "id ASC";
                    break;
                case 'name_asc':
                    $orderBy = "original_name ASC";
                    break;
                case 'name_desc':
                    $orderBy = "original_name DESC";
                    break;
                case 'size_desc':
                    $orderBy = "size_bytes DESC";
                    break;
                case 'size_asc':
                    $orderBy = "size_bytes ASC";
                    break;
                case 'newest':
                default:
                    $orderBy = "id DESC";
                    break;
            }
        }

        $sql = "SELECT * FROM media {$whereClause} ORDER BY {$orderBy} LIMIT ? OFFSET ?";
        $fetchTypes = $types . 'ii';
        $fetchParams = array_merge($params, [$perPage, $offset]);

        $stmt = $db->prepare($sql);
        if ($fetchTypes && $stmt) {
            $stmt->bind_param($fetchTypes, ...$fetchParams);
        }

        $mediaList = [];
        if ($stmt) {
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $mediaList[] = self::formatMediaRow($row);
            }
            $stmt->close();
        }

        return [
            'media' => $mediaList,
            'totalCount' => $totalCount,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'perPage' => $perPage
        ];
    }

    /**
     * Get distinct folders list.
     */
    public static function getFoldersList(): array {
        self::ensureTableExists();
        $db = Database::connect();
        $res = $db->query("SELECT DISTINCT folder FROM media WHERE folder IS NOT NULL AND folder != '' ORDER BY folder ASC");
        $folders = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $folders[] = $row['folder'];
            }
        }
        if (!in_array('general', $folders)) {
            array_unshift($folders, 'general');
        }
        return array_unique($folders);
    }

    /**
     * Get single media record by ID.
     */
    public static function getMediaById(int $id): ?array {
        self::ensureTableExists();
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM media WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $row ? self::formatMediaRow($row) : null;
    }

    /**
     * Insert new media record.
     */
    public static function createMediaRecord(array $data): ?array {
        self::ensureTableExists();
        $db = Database::connect();

        $stmt = $db->prepare("
            INSERT INTO media 
            (filename, original_name, mime_type, size_bytes, width, height, alt_text, folder, storage_disk, storage_path, public_url, uploaded_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $width = $data['width'] ?? null;
        $height = $data['height'] ?? null;
        $altText = $data['alt_text'] ?? null;
        $folder = !empty($data['folder']) ? $data['folder'] : 'general';
        $storageDisk = $data['storage_disk'] ?? 'local';
        $uploadedBy = $data['uploaded_by'] ?? ($_SESSION['admin_user_id'] ?? null);

        $stmt->bind_param(
            "sssiiisssssi",
            $data['filename'],
            $data['original_name'],
            $data['mime_type'],
            $data['size_bytes'],
            $width,
            $height,
            $altText,
            $folder,
            $storageDisk,
            $data['storage_path'],
            $data['public_url'],
            $uploadedBy
        );

        if ($stmt->execute()) {
            $insertId = $stmt->insert_id;
            $stmt->close();
            return self::getMediaById($insertId);
        }

        $stmt->close();
        return null;
    }

    /**
     * Update metadata for a media item.
     */
    public static function updateMediaMetadata(int $id, string $originalName, ?string $altText, string $folder = 'general'): bool {
        self::ensureTableExists();
        $db = Database::connect();

        $stmt = $db->prepare("UPDATE media SET original_name = ?, alt_text = ?, folder = ? WHERE id = ?");
        $stmt->bind_param("sssi", $originalName, $altText, $folder, $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /**
     * Delete media item and its physical file from disk.
     */
    public static function deleteMedia(int $id): bool {
        self::ensureTableExists();
        $media = self::getMediaById($id);
        if (!$media) {
            return false;
        }

        // Unlink file if it exists locally
        if (!empty($media['storage_path']) && file_exists($media['storage_path'])) {
            @unlink($media['storage_path']);
        }

        $db = Database::connect();
        $stmt = $db->prepare("DELETE FROM media WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();

        return $success;
    }

    /**
     * Bulk delete array of media IDs.
     */
    public static function bulkDeleteMedia(array $ids): int {
        $deletedCount = 0;
        foreach ($ids as $id) {
            if (self::deleteMedia((int)$id)) {
                $deletedCount++;
            }
        }
        return $deletedCount;
    }

    /**
     * Utility method to format a media row with computed properties.
     */
    public static function formatMediaRow(array $row): array {
        $row['encrypted_id'] = SecurityHelper::encryptId($row['id']);
        $row['formatted_size'] = self::formatBytes((int)($row['size_bytes'] ?? 0));
        $row['is_image'] = str_pos_starts($row['mime_type'] ?? '', 'image/');
        $row['file_extension'] = strtolower(pathinfo($row['original_name'] ?? $row['filename'] ?? '', PATHINFO_EXTENSION));

        // Format and validate public_url
        if (!empty($row['public_url']) && !preg_match('#^https?://#i', $row['public_url'])) {
            $base = defined('BASE_URL') ? BASE_URL : '';
            $row['public_url'] = rtrim($base, '/') . '/' . ltrim($row['public_url'], '/');
        }

        return $row;
    }

    /**
     * Index existing uploaded files in public/uploads if not already registered in media table.
     */
    public static function syncExistingUploads(): void {
        try {
            self::ensureTableExists();
            $db = Database::connect();
            $baseDir = __DIR__ . '/../../public/uploads';
            if (!is_dir($baseDir)) return;

            $folders = ['products', 'branding', 'avatars'];
            $baseUrl = defined('BASE_URL') ? BASE_URL : '';

            foreach ($folders as $fld) {
                $dirPath = $baseDir . '/' . $fld;
                if (!is_dir($dirPath)) continue;

                $files = scandir($dirPath);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..' || is_dir($dirPath . '/' . $file)) continue;

                    $fullPath = $dirPath . '/' . $file;
                    $size = filesize($fullPath);
                    if ($size <= 0) continue;

                    $stmt = $db->prepare("SELECT id FROM media WHERE filename = ? LIMIT 1");
                    $stmt->bind_param("s", $file);
                    $stmt->execute();
                    $exists = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if (!$exists) {
                        $imgInfo = @getimagesize($fullPath);
                        $width = $imgInfo[0] ?? null;
                        $height = $imgInfo[1] ?? null;
                        $mime = $imgInfo['mime'] ?? (function_exists('mime_content_type') ? mime_content_type($fullPath) : 'application/octet-stream');
                        $pubUrl = $baseUrl . '/public/uploads/' . $fld . '/' . $file;
                        $relPath = 'public/uploads/' . $fld . '/' . $file;
                        $alt = pathinfo($file, PATHINFO_FILENAME);

                        $insertStmt = $db->prepare("
                            INSERT INTO media 
                            (filename, original_name, mime_type, size_bytes, width, height, alt_text, folder, storage_disk, storage_path, public_url, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'local', ?, ?, NOW())
                        ");
                        $insertStmt->bind_param("sssiisssss", $file, $file, $mime, $size, $width, $height, $alt, $fld, $relPath, $pubUrl);
                        $insertStmt->execute();
                        $insertStmt->close();
                    }
                }
            }
        } catch (\Throwable $e) {
            error_log("Failed to sync uploads: " . $e->getMessage());
        }
    }

    /**
     * Utility method to format byte size nicely (e.g. 1.5 MB).
     */
    public static function formatBytes(int $bytes, int $precision = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

// Helper polyfill function if not existing
if (!function_exists('str_pos_starts')) {
    function str_pos_starts(string $haystack, string $needle): bool {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}
