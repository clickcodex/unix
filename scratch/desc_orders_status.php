<?php
$pdo = new PDO('mysql:host=localhost;dbname=unix;charset=utf8mb4', 'root', '');
$cols = $pdo->query("DESCRIBE orders")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    if ($c['Field'] === 'status' || $c['Field'] === 'payment_status') {
        echo $c['Field'] . ": " . $c['Type'] . "\n";
    }
}
