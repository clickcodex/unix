<?php
$dir = new RecursiveDirectoryIterator(__DIR__ . '/../app/Views/admin');
$iter = new RecursiveIteratorIterator($dir);
foreach ($iter as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $rel = str_replace(realpath(__DIR__ . '/../app/Views/admin'), '', $file->getRealPath());
        echo $rel . " (" . $file->getSize() . " bytes)\n";
    }
}
