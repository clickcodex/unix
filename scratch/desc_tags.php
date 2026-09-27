<?php
$pdo = new PDO('mysql:host=localhost;dbname=unix;charset=utf8mb4', 'root', '');
$colsTags = $pdo->query("DESCRIBE tags")->fetchAll(PDO::FETCH_ASSOC);
$colsProdTags = $pdo->query("DESCRIBE product_tags")->fetchAll(PDO::FETCH_ASSOC);
echo "TAGS:\n" . json_encode($colsTags, JSON_PRETTY_PRINT) . "\n\nPRODUCT_TAGS:\n" . json_encode($colsProdTags, JSON_PRETTY_PRINT);
