<?php

namespace App\Controllers\Admin;

use App\Models\Notification;
use App\Models\AuditLog;
use App\Helpers\SecurityHelper;

class NotificationController {

    private function parseId($id): ?int {
        if (empty($id)) return null;
        if (is_numeric($id)) return (int)$id;
        return SecurityHelper::decryptId((string)$id);
    }

    private function jsonResponse(bool $success, string $message, int $code = 200, array $extra = []): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
        exit;
    }

    private function getRequestInput(): array {
        $json = json_decode(file_get_contents('php://input'), true);
        return is_array($json) ? $json : $_POST;
    }

    /**
     * Display Notification Management Page or return AJAX JSON response.
     */
    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $filters = [
            'search'  => trim($_GET['search'] ?? ''),
            'channel' => trim($_GET['channel'] ?? 'all'),
            'status'  => trim($_GET['status'] ?? 'all'),
            'page'    => max(1, (int)($_GET['page'] ?? 1)),
            'limit'   => max(5, min(100, (int)($_GET['limit'] ?? 15)))
        ];

        $result = Notification::getPaginatedNotifications($filters['page'], $filters['limit'], $filters);
        $kpis = Notification::getKpiMetrics();

        $isAjax = (!empty($_GET['ajax']) && $_GET['ajax'] == '1') || 
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($isAjax) {
            $this->jsonResponse(true, 'Notifications fetched.', 200, [
                'notifications' => $result['notifications'],
                'pagination'    => $result['pagination'],
                'kpis'          => $kpis
            ]);
        }

        $notifications = $result['notifications'];
        $pagination = $result['pagination'];

        require_once __DIR__ . '/../../Views/admin/notifications/index.php';
    }

    /**
     * Top header unread notification feed API (AJAX).
     */
    public function unreadFeed(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $limit = max(1, min(20, (int)($_GET['limit'] ?? 5)));
        $feed = Notification::getTopUnreadForAdmin($limit);

        $this->jsonResponse(true, 'Feed loaded.', 200, [
            'count' => $feed['count'],
            'items' => $feed['items']
        ]);
    }

    /**
     * Mark single notification as read.
     */
    public function markAsRead(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $id = $this->parseId($input['notification_id'] ?? $input['id'] ?? '');

        if (!$id) {
            $this->jsonResponse(false, 'Invalid notification ID.', 400);
        }

        $success = Notification::markAsRead($id);
        if ($success) {
            AuditLog::log('notification.read', 'Notification', $id);
            $this->jsonResponse(true, 'Notification marked as read.');
        } else {
            $this->jsonResponse(false, 'Failed to mark notification as read.', 400);
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $success = Notification::markAllAsRead();
        if ($success) {
            AuditLog::log('notification.mark_all_read', 'Notification', null);
            $this->jsonResponse(true, 'All notifications marked as read.');
        } else {
            $this->jsonResponse(false, 'Failed to mark all as read or all already read.', 400);
        }
    }

    /**
     * Delete notification entry.
     */
    public function delete(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $id = $this->parseId($input['notification_id'] ?? $input['id'] ?? '');

        if (!$id) {
            $this->jsonResponse(false, 'Invalid notification ID.', 400);
        }

        $success = Notification::deleteNotification($id);
        if ($success) {
            AuditLog::log('notification.deleted', 'Notification', $id);
            $this->jsonResponse(true, 'Notification deleted successfully.');
        } else {
            $this->jsonResponse(false, 'Failed to delete notification.', 400);
        }
    }

    /**
     * Bulk Action on notifications.
     */
    public function bulkAction(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();
        $action = trim($input['action'] ?? '');
        $rawIds = $input['notification_ids'] ?? $input['ids'] ?? [];

        if (!is_array($rawIds) || empty($rawIds) || empty($action)) {
            $this->jsonResponse(false, 'Notifications and action selection are required.', 400);
        }

        $ids = [];
        foreach ($rawIds as $raw) {
            $parsed = $this->parseId($raw);
            if ($parsed) $ids[] = $parsed;
        }

        if (empty($ids)) {
            $this->jsonResponse(false, 'No valid notification IDs selected.', 400);
        }

        $success = Notification::bulkAction($ids, $action);
        if ($success) {
            AuditLog::log('notification.bulk_action', 'Notification', null, null, ['action' => $action, 'count' => count($ids)]);
            $this->jsonResponse(true, "Bulk action '{$action}' completed successfully.");
        } else {
            $this->jsonResponse(false, 'Failed to perform bulk action.', 400);
        }
    }

    /**
     * Send Broadcast / Announcement to users/admins.
     */
    public function sendBroadcast(): void {
        \App\Middleware\AdminAuthMiddleware::check();
        $input = $this->getRequestInput();

        $title = trim($input['title'] ?? '');
        $body = trim($input['body'] ?? '');
        $target = trim($input['target'] ?? 'all');
        $channel = trim($input['channel'] ?? 'in_app');

        if (empty($title) || empty($body)) {
            $this->jsonResponse(false, 'Broadcast Title and Message Body are required.', 400);
        }

        $count = Notification::sendBroadcast($input);
        if ($count > 0) {
            AuditLog::log('notification.broadcast_sent', 'Notification', null, null, [
                'title' => $title,
                'target' => $target,
                'channel' => $channel,
                'recipients_count' => $count
            ]);
            $this->jsonResponse(true, "Broadcast dispatched successfully to {$count} recipient(s).", 200, ['sent_count' => $count]);
        } else {
            $this->jsonResponse(false, 'Failed to send broadcast or no active recipients found.', 400);
        }
    }
}
