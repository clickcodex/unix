<?php
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
foreach ($_ENV as $key => $value) putenv("$key=$value");

use App\Config\Database;
$db = Database::connect();

$res = $db->query("DESCRIBE products");
echo "=== PRODUCTS ===\n";
while ($row = $res->fetch_assoc()) echo $row['Field'] . " (" . $row['Type'] . ")\n";

try {
    $res2 = $db->query("DESCRIBE v_products_active");
    echo "=== V_PRODUCTS_ACTIVE ===\n";
    while ($row = $res2->fetch_assoc()) echo $row['Field'] . " (" . $row['Type'] . ")\n";
} catch (\Throwable $e) {
    echo "v_products_active not found\n";
}
