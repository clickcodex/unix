<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=unix;charset=utf8mb4', 'root', '');
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Total tables in database 'unix': " . count($tables) . "\n";
    foreach ($tables as $t) {
        echo "- " . $t . "\n";
    }
} catch (Exception $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
}
