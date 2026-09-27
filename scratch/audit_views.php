<?php
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

$controllerDir = __DIR__ . '/../app/Controllers';
$dir = new RecursiveDirectoryIterator($controllerDir);
$iter = new RecursiveIteratorIterator($dir);

$missingViews = [];

foreach ($iter as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getRealPath());
        // match view('view_name' or view("view_name"
        preg_match_all("/view\s*\(\s*['\"]([^'\"]+)['\"]/i", $content, $matches);
        foreach ($matches[1] as $v) {
            $viewPath = __DIR__ . '/../app/Views/' . $v . '.php';
            if (!file_exists($viewPath)) {
                $missingViews[] = "In " . $file->getFilename() . " -> view('$v') => $viewPath not found";
            }
        }
    }
}

echo "--- MISSING VIEWS REFERENCED IN CONTROLLERS (" . count($missingViews) . ") ---\n";
foreach ($missingViews as $mv) echo " - $mv\n";
