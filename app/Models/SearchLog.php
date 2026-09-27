<?php

namespace App\Models;

use App\Config\Database;

class SearchLog {

    /**
     * Record a search query into search_history table.
     */
    public static function logSearch(string $query, int $resultCount = 0, ?int $userId = null): void {
        $query = trim($query);
        if (empty($query) || mb_strlen($query) < 2) {
            return;
        }

        try {
            $db = Database::connect();
            $sessionToken = session_id() ?: null;
            $userId = $userId ?? ($_SESSION['user_id'] ?? null);

            $stmt = $db->prepare("
                INSERT INTO search_history (user_id, session_token, query, result_count, searched_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
            if ($stmt) {
                $stmt->bind_param("isis", $userId, $sessionToken, $query, $resultCount);
                $stmt->execute();
                $stmt->close();
            }
        } catch (\Throwable $e) {
            error_log("Search log error: " . $e->getMessage());
        }
    }

    /**
     * Get search analytics metrics for admin dashboard.
     */
    public static function getAnalytics(): array {
        $db = Database::connect();

        // 1. Total searches
        $totalSearches = 0;
        $res = $db->query("SELECT COUNT(*) as total FROM search_history");
        if ($res && $row = $res->fetch_assoc()) $totalSearches = (int)$row['total'];

        // 2. Unique search terms
        $uniqueQueries = 0;
        $res = $db->query("SELECT COUNT(DISTINCT LOWER(query)) as total FROM search_history");
        if ($res && $row = $res->fetch_assoc()) $uniqueQueries = (int)$row['total'];

        // 3. Zero result searches count
        $zeroResultCount = 0;
        $res = $db->query("SELECT COUNT(*) as total FROM search_history WHERE result_count = 0");
        if ($res && $row = $res->fetch_assoc()) $zeroResultCount = (int)$row['total'];

        // 4. Searches today
        $searchesToday = 0;
        $res = $db->query("SELECT COUNT(*) as total FROM search_history WHERE DATE(searched_at) = CURDATE()");
        if ($res && $row = $res->fetch_assoc()) $searchesToday = (int)$row['total'];

        // 5. Top 10 searched queries
        $topQueries = [];
        $res = $db->query("
            SELECT query, COUNT(*) as search_count, MAX(searched_at) as last_searched, ROUND(AVG(result_count)) as avg_results
            FROM search_history
            GROUP BY LOWER(query)
            ORDER BY search_count DESC
            LIMIT 10
        ");
        if ($res) {
            while ($row = $res->fetch_assoc()) $topQueries[] = $row;
        }

        // 6. Searches with 0 results (unfound products)
        $zeroResultsList = [];
        $res = $db->query("
            SELECT query, COUNT(*) as search_count, MAX(searched_at) as last_searched
            FROM search_history
            WHERE result_count = 0
            GROUP BY LOWER(query)
            ORDER BY search_count DESC
            LIMIT 10
        ");
        if ($res) {
            while ($row = $res->fetch_assoc()) $zeroResultsList[] = $row;
        }

        // 7. Recent searches log
        $recentLog = [];
        $res = $db->query("
            SELECT sh.*, u.name as user_name, u.email as user_email
            FROM search_history sh
            LEFT JOIN users u ON sh.user_id = u.id
            ORDER BY sh.id DESC
            LIMIT 15
        ");
        if ($res) {
            while ($row = $res->fetch_assoc()) $recentLog[] = $row;
        }

        return [
            'totalSearches'   => $totalSearches,
            'uniqueQueries'   => $uniqueQueries,
            'zeroResultCount' => $zeroResultCount,
            'searchesToday'   => $searchesToday,
            'topQueries'      => $topQueries,
            'zeroResultsList' => $zeroResultsList,
            'recentLog'       => $recentLog
        ];
    }
}
