<?php

namespace App\Controllers\Admin;

use App\Models\GlobalSearch;
use App\Models\AuditLog;

class SearchController {

    private function jsonResponse(bool $success, string $message, int $code = 200, array $extra = []): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => $success, 'message' => $message], $extra));
        exit;
    }

    /**
     * Render Full Global Search Results Page.
     */
    public function index(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $query = trim($_GET['q'] ?? $_GET['query'] ?? $_GET['search'] ?? '');
        $results = GlobalSearch::searchAll($query, 10);

        if (!empty($query)) {
            AuditLog::log('search.executed', 'GlobalSearch', null, null, ['query' => $query, 'total_results' => $results['total_count']]);
        }

        $isAjax = (!empty($_GET['ajax']) && $_GET['ajax'] == '1') || 
                  (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

        if ($isAjax) {
            $this->jsonResponse(true, 'Search results fetched.', 200, [
                'query'   => $query,
                'results' => $results
            ]);
        }

        require_once __DIR__ . '/../../Views/admin/search/index.php';
    }

    /**
     * Live Autocomplete API for top header search bar.
     */
    public function api(): void {
        \App\Middleware\AdminAuthMiddleware::check();

        $query = trim($_GET['q'] ?? $_GET['query'] ?? '');
        $limit = max(1, min(20, (int)($_GET['limit'] ?? 4)));

        $results = GlobalSearch::searchAll($query, $limit);

        $this->jsonResponse(true, 'Search API results loaded.', 200, [
            'query'       => $query,
            'total_count' => $results['total_count'],
            'results'     => $results
        ]);
    }
}
