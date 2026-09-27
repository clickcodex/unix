<?php
$pdo = new PDO('mysql:host=localhost;dbname=unix;charset=utf8mb4', 'root', '');
$cols = $pdo->query("DESCRIBE inventory_movements")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($cols, JSON_PRETTY_PRINT);
