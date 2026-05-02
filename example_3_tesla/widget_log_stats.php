<?php
/**
 * widget_log_stats.php
 * Returns statistics about the chat log file
 */

header('Content-Type: application/json');

$logFile = __DIR__ . '/widget_chat_logs.json';

if (!file_exists($logFile)) {
    echo json_encode([
        'status' => 'success',
        'count' => 0,
        'total' => 0
    ]);
    exit;
}

$content = file_get_contents($logFile);
$data = json_decode($content, true);

if (!is_array($data)) {
    echo json_encode([
        'status' => 'success',
        'count' => 0,
        'total' => 0
    ]);
    exit;
}

echo json_encode([
    'status' => 'success',
    'count' => count($data),
    'total' => count($data),
    'fileSize' => filesize($logFile)
]);
?>