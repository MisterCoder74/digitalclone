<?php
/**
 * widget_save_bio.php
 * Saves text content to the bio/ directory for the knowledgebase
 */

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid JSON data"]);
    exit;
}

if (!isset($data['filename']) || !isset($data['content'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing filename or content"]);
    exit;
}

$filename = basename($data['filename']);
if (!preg_match('/^[a-zA-Z0-9_\-]+\.txt$/', $filename)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid filename. Use only alphanumeric characters, underscores, and hyphens."]);
    exit;
}

$bioDir = __DIR__ . '/bio/';
if (!is_dir($bioDir)) {
    if (!mkdir($bioDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to create bio directory"]);
        exit;
    }
}

$filePath = $bioDir . $filename;
$content = $data['content'];

// Truncate if too long (60k chars limit like the main system)
if (strlen($content) > 60000) {
    $content = substr($content, 0, 60000) . "\n\n[Content truncated...]";
}

if (file_put_contents($filePath, $content) !== false) {
    echo json_encode([
        "status" => "success",
        "message" => "File saved successfully",
        "filename" => $filename,
        "size" => strlen($content),
        "path" => $filePath
    ]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to save file"]);
}
?>