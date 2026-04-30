<?php
/**
 * widget_log_chat.php
 * Logs chat interactions to widget_chat_logs.json with flock for concurrency safety
 * Records: IP, timestamp, user text, image URL, and persona response
 */

header('Content-Type: application/json');

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid JSON data"]);
    exit;
}

// Get client IP
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Sanitize data
$userText = isset($data['userText']) ? trim($data['userText']) : '';
$imageUrl = isset($data['imageUrl']) ? trim($data['imageUrl']) : '';
$response = isset($data['response']) ? trim($data['response']) : '';
$timestamp = isset($data['timestamp']) ? $data['timestamp'] : date('c');
$persona = isset($data['persona']) ? trim($data['persona']) : 'Unknown';

// Build log entry
$logEntry = [
    'timestamp' => $timestamp,
    'ip' => $clientIP,
    'persona' => $persona,
    'userText' => $userText,
    'imageUrl' => $imageUrl !== '' ? $imageUrl : null,
    'response' => $response,
    'type' => $imageUrl !== '' ? 'image_analysis' : 'text_chat'
];

// Log file path
$logFile = __DIR__ . '/widget_chat_logs.json';

// Ensure log file exists with valid JSON array
if (!file_exists($logFile)) {
    file_put_contents($logFile, "[]\n", LOCK_EX);
}

// Open file with exclusive lock using flock
$fp = fopen($logFile, 'c+');
if (!$fp) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to open log file"]);
    exit;
}

// Acquire exclusive lock (blocks until available)
if (!flock($fp, LOCK_EX)) {
    fclose($fp);
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to acquire lock on log file"]);
    exit;
}

// Read existing content
$content = '';
while (!feof($fp)) {
    $content .= fread($fp, 8192);
}

// Parse existing logs
$logs = [];
if ($content !== '') {
    $decoded = json_decode($content, true);
    if (is_array($decoded)) {
        $logs = $decoded;
    }
}

// Add new entry
$logs[] = $logEntry;

// Keep only last 10000 entries to prevent file from growing too large
if (count($logs) > 10000) {
    $logs = array_slice($logs, -10000);
}

// Truncate and rewrite
ftruncate($fp, 0);
rewind($fp);

// Write with pretty print
$jsonOutput = json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
fwrite($fp, $jsonOutput);
fflush($fp);

// Release lock and close
flock($fp, LOCK_UN);
fclose($fp);

echo json_encode([
    "status" => "success",
    "message" => "Chat logged successfully",
    "entries" => count($logs)
]);
?>