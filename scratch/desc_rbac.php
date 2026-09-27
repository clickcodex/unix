<?php
$pdo = new PDO('mysql:host=localhost;dbname=unix;charset=utf8mb4', 'root', '');
$colsRoles = $pdo->query("DESCRIBE roles")->fetchAll(PDO::FETCH_ASSOC);
$colsPerms = $pdo->query("DESCRIBE permissions")->fetchAll(PDO::FETCH_ASSOC);
$colsRolePerms = $pdo->query("DESCRIBE role_permissions")->fetchAll(PDO::FETCH_ASSOC);
echo "ROLES:\n" . json_encode($colsRoles, JSON_PRETTY_PRINT) . 
     "\n\nPERMISSIONS:\n" . json_encode($colsPerms, JSON_PRETTY_PRINT) . 
     "\n\nROLE_PERMISSIONS:\n" . json_encode($colsRolePerms, JSON_PRETTY_PRINT);
