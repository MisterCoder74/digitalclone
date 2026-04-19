<?php
/**
 * indexBio.php
 * Genera biography_index.json dalla cartella /bio
 * Richiede apiKey via POST o GET per le chiamate a OpenAI
 */

header('Content-Type: application/json');

$apiKey = $_REQUEST['apiKey'] ?? '';

if (!$apiKey) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "OpenAI API Key is required"]);
    exit;
}

$bioDir = __DIR__ . '/bio/';
$outputFile = __DIR__ . '/biography_index.json';

if (!is_dir($bioDir)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Directory /bio/ not found"]);
    exit;
}

$files = glob($bioDir . '*.txt');
$index = [];

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (!$content) continue;

    $fileName = basename($file);
    
    // Call OpenAI Embeddings API
    $ch = curl_init('https://api.openai.com/v1/embeddings');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: ' . 'Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'model' => 'text-embedding-3-small',
        'input' => $content,
        'dimensions' => 512
    ]));

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        continue; // Skip this file if error
    }
    curl_close($ch);

    $data = json_decode($response, true);
    if (isset($data['data'][0]['embedding'])) {
        $vector = $data['data'][0]['embedding'];
        // Round to 5 decimal places
        $vector = array_map(function($v) {
            return round($v, 5);
        }, $vector);

        $index[] = [
            "file" => $fileName,
            "text" => $content,
            "vector" => $vector
        ];
    }
}

if (file_put_contents($outputFile, json_encode($index, JSON_UNESCAPED_UNICODE))) {
    echo json_encode(["status" => "success", "message" => "biography_index.json generated", "files_processed" => count($index)]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to write biography_index.json"]);
}
?>
