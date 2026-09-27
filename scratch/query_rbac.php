<?php
$pdo = new PDO('mysql:host=localhost;dbname=unix;charset=utf8mb4', 'root', '');
$roles = $pdo->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);
$perms = $pdo->query("SELECT * FROM permissions LIMIT 20")->fetchAll(PDO::FETCH_ASSOC);
echo "ROLES:\n" . json_encode($roles, JSON_PRETTY_PRINT) . "\n\nPERMISSIONS SAMPLE:\n" . json_encode($perms, JSON_PRETTY_PRINT);
