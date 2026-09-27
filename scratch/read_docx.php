<?php
$zip = new ZipArchive();
if ($zip->open(__DIR__ . '/../storage/Ecommerce_Project_Document.docx') === true) {
    $content = $zip->getFromName('word/document.xml');
    $zip->close();
    $content = str_replace(['</w:p>', '</w:tr>', '</w:tc>'], ["\n", "\n", "\t"], $content);
    file_put_contents(__DIR__ . '/docx_full.txt', mb_convert_encoding(strip_tags($content), 'UTF-8', 'UTF-8'));
}
