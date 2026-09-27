<?php
session_start();
$_SESSION['admin_user_id'] = 50;
$_SESSION['admin_session_token'] = 'test_token';
$_SESSION['admin_user_name'] = 'Super Admin';
$_SESSION['admin_user_email'] = 'admin@clickcodex.in';
$_SESSION['admin_role_name'] = 'Super Admin';

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Helpers/SecurityHelper.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
foreach ($_ENV as $k => $v) {
    putenv("$k=$v");
}

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/unix');
}

use App\Helpers\SecurityHelper;

$tokenHash = hash('sha256', 'test_token');
\App\Models\UserSession::createSession(50, $tokenHash, 'web', 'CLI Test', '127.0.0.1', 'CLI');

$controller = new \App\Controllers\Admin\ProductController();
$encProductId = SecurityHelper::encryptId(1);

ob_start();
$controller->variants($encProductId);
$output = ob_get_clean();

echo "Product Variants View Render Results:\n";
echo "- Output length: " . strlen($output) . " bytes\n";
echo "- Contains Product Name: " . (strpos($output, 'iPhone 15 Pro Max') !== false ? 'YES' : 'NO') . "\n";
echo "- Contains vProductEncryptedId: " . (strpos($output, 'vProductEncryptedId') !== false ? 'YES' : 'NO') . "\n";
