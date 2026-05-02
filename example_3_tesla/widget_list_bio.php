<?php
/**
 * widget_list_bio.php
 * Lists all files in the bio/ directory for the knowledgebase
 */

header('Content-Type: application/json');

$bioDir = __DIR__ . '/bio/';
$files = [];

if (is_dir($bioDir)) {
    $items = glob($bioDir . '*.txt');
    foreach ($items as $item) {
        $files[] = [
            'name' => basename($item),
            'path' => $item,
            'size' => filesize($item),
            'modified' => date('Y-m-d H:i:s', filemtime($item))
        ];
    }
}

echo json_encode([
    'status' => 'success',
    'count' => count($files),
    'files' => $files
]);
?>