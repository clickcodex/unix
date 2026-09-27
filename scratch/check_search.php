<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
foreach ($_ENV as $key => $value) {
    putenv("$key=$value");
}

use App\Config\Database;

try {
    $db = Database::connect();
    $res = $db->query("SHOW TABLES");
    while ($row = $res->fetch_array()) {
        echo $row[0] . "\n";
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
