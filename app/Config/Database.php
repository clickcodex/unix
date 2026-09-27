<?php

namespace App\Config;

use mysqli;
use Exception;

class Database {
    public static function connect() {
        $host = getenv('DB_HOST');
        $user = getenv('DB_USER');
        $pass = getenv('DB_PASS');
        $name = getenv('DB_NAME');

        // Validate environment variables
        if (!$host || !$user || !$name) {
            throw new Exception("Database configuration is incomplete.");
        }

        // Suppress direct error output - handle manually
        if (function_exists('mysqli_report')) {
            @mysqli_report(MYSQLI_REPORT_OFF);
        }
        $conn = @new mysqli($host, $user, $pass, $name);

        if ($conn->connect_errno) {
            // Log error instead of displaying
            error_log("Database connection error: " . $conn->connect_error);
            throw new Exception("Unable to connect to the database. Please try again later.");
        }

        // Use secure charset
        if (!$conn->set_charset("utf8mb4")) {
            error_log("Charset setting failed: " . $conn->error);
            throw new Exception("Internal database configuration error.");
        }

        return $conn;
    }
}
?>