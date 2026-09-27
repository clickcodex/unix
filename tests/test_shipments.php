<?php
session_start();
$_SESSION['admin_user_id'] = 50;
$_SESSION['admin_session_token'] = 'test_token';
$_SESSION['admin_user_name'] = 'Super Admin';
$_SESSION['admin_user_email'] = 'admin@clickcodex.in';
$_SESSION['admin_role_name'] = 'Super Admin';

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Models/Shipment.php';
use App\Models\Shipment;

// Simple test to fetch paginated shipments
$page = 1;
$filters = [];
$result = Shipment::getPaginatedShipments($page, 15, $filters);
print_r($result);
?>
