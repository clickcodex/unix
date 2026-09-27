<?php
require_once __DIR__ . '/vendor/autoload.php';
 $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
 $dotenv->load();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

foreach ($_ENV as $key => $value) {
    putenv("$key=$value");
}

/* =============================================================
   TEMPORARY FIX FOR LOCALHOST REDIRECTS
   -------------------------------------------------------------
   When running on localhost, absolute URLs like "/login"
   redirect to http://localhost/login instead of your project folder.
   This block automatically detects your local base path and fixes it.
   -------------------------------------------------------------
   Remove or comment this block when you move project online.
   ============================================================= */
if (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    define('BASE_URL', $protocol . '://' . $host . $basePath);
} else {
    // On live server (will work normally)
    define('BASE_URL', '');
}
require_once __DIR__ . '/app/routes.php';
?>