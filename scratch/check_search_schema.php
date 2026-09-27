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
    $res = $db->query("DESCRIBE search_history");
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
