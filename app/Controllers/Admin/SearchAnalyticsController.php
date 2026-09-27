<?php

namespace App\Controllers\Admin;

use App\Middleware\AdminAuthMiddleware;
use App\Models\SearchLog;

class SearchAnalyticsController {

    /**
     * Render the Admin Search Analytics & Insights Dashboard.
     */
    public function index(): void {
        AdminAuthMiddleware::check();

        $analytics = SearchLog::getAnalytics();

        require_once __DIR__ . '/../../Views/admin/search_analytics.php';
    }
}
