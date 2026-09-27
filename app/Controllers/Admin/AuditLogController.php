<?php

namespace App\Controllers\Admin;

use App\Models\AuditLog;
use App\Middleware\AdminAuthMiddleware;
use App\Helpers\SecurityHelper;

class AuditLogController {

    /**
     * Display or return JSON for Audit Log Manager.
     */
    public function index(): void {
        AdminAuthMiddleware::check();

        $page = (int)($_GET['page'] ?? 1);
        $perPage = (int)($_GET['per_page'] ?? 20);
        $search = trim($_GET['search'] ?? '');
        $event = trim($_GET['event'] ?? '');
        $model = trim($_GET['model'] ?? '');
        $userId = !empty($_GET['user_id']) ? (int)$_GET['user_id'] : null;
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');

        $isAjax = (isset($_GET['ajax']) && $_GET['ajax'] == '1') || 
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        $filters = [
            'search' => $search,
            'event' => $event,
            'model' => $model,
            'user_id' => $userId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];

        $logData = AuditLog::getPaginatedLogs($page, $perPage, $filters);
        $kpiData = AuditLog::getKpiMetrics();
        $eventCategories = AuditLog::getEventCategories();

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $logData['logs'],
                'pagination' => [
                    'totalCount' => $logData['totalCount'],
                    'totalPages' => $logData['totalPages'],
                    'currentPage' => $logData['currentPage'],
                    'perPage' => $logData['perPage']
                ],
                'kpis' => $kpiData,
                'categories' => $eventCategories
            ]);
            exit;
        }

        $pageTitle = 'Audit & System Log Trail';
        $activeMenu = 'audit';

        require_once __DIR__ . '/../../Views/admin/audit/index.php';
    }

    /**
     * Get detail record of a single audit log entry via API.
     */
    public function detail($id): void {
        AdminAuthMiddleware::check();

        $numericId = is_numeric($id) ? (int)$id : SecurityHelper::decryptId((string)$id);
        if (!$numericId) {
            $this->respond(['success' => false, 'message' => 'Invalid audit log identifier.'], 404);
            return;
        }

        $logRecord = AuditLog::getLogById($numericId);
        if (!$logRecord) {
            $this->respond(['success' => false, 'message' => 'Audit log record not found.'], 404);
            return;
        }

        $this->respond([
            'success' => true,
            'log' => $logRecord
        ]);
    }

    /**
     * Export audit logs to CSV file.
     */
    public function export(): void {
        AdminAuthMiddleware::check();

        $search = trim($_GET['search'] ?? '');
        $event = trim($_GET['event'] ?? '');
        $model = trim($_GET['model'] ?? '');
        $userId = !empty($_GET['user_id']) ? (int)$_GET['user_id'] : null;
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');

        $filters = [
            'search' => $search,
            'event' => $event,
            'model' => $model,
            'user_id' => $userId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo
        ];

        // Fetch up to 5,000 records for export
        $logData = AuditLog::getPaginatedLogs(1, 5000, $filters);
        $logs = $logData['logs'];

        // Audit the export action itself!
        AuditLog::log('audit.exported', 'AuditLog', null, null, ['exported_count' => count($logs)]);

        $filename = "audit_log_export_" . date('Y_m_d_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Log ID', 'Timestamp', 'User ID', 'User Name', 'User Email', 'Event Action', 'Target Model', 'Model ID', 'IP Address', 'Old State Data', 'New State Data']);

        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                $log['created_at'],
                $log['user_id'] ?? 'System',
                $log['user_name'] ?? 'System / Anonymous',
                $log['user_email'] ?? 'N/A',
                $log['event'],
                $log['model'] ?? 'N/A',
                $log['model_id'] ?? 'N/A',
                $log['ip_address'],
                $log['old_data'] ?? '',
                $log['new_data'] ?? ''
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Send JSON response helper.
     */
    private function respond(array $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
