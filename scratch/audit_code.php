<?php
$routesContent = file_get_contents(__DIR__ . '/../app/routes.php');
preg_match_all('/\$router->(get|post|put|delete)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*\)/i', $routesContent, $matches, PREG_SET_ORDER);

echo "Total Defined Routes Matched: " . count($matches) . "\n\n";

$missingControllers = [];
$missingMethods = [];

// Autoload helper
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

foreach ($matches as $m) {
    $httpMethod = $m[1];
    $routePath  = $m[2];
    $handler    = $m[3];

    if (strpos($handler, '@') === false) continue;
    list($controllerName, $methodName) = explode('@', $handler);
    $controllerClass = "App\\Controllers\\" . str_replace('/', '\\', $controllerName);
    $filePath = __DIR__ . '/../app/Controllers/' . $controllerName . '.php';

    if (!file_exists($filePath)) {
        $missingControllers[] = "$routePath -> $controllerName ($filePath not found)";
    } else {
        if (!class_exists($controllerClass)) {
            $missingControllers[] = "Class $controllerClass not found in $filePath";
        } elseif (!method_exists($controllerClass, $methodName)) {
            $missingMethods[] = "$routePath -> $controllerClass::$methodName() missing";
        }
    }
}

echo "--- MISSING CONTROLLERS (" . count($missingControllers) . ") ---\n";
foreach ($missingControllers as $mc) echo " - $mc\n";

echo "\n--- MISSING METHODS (" . count($missingMethods) . ") ---\n";
foreach ($missingMethods as $mm) echo " - $mm\n";
