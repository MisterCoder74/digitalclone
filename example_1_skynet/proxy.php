<?php
/**
 * OpenAI API Proxy for Skynet Widget
 * This script forwards requests to OpenAI while keeping the API key secure on the server.
 */

require_once 'api_config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => ['message' => 'Method Not Allowed. Use POST.']]);
    exit;
}

// Determine which OpenAI endpoint to use
$path = isset($_GET['path']) ? $_GET['path'] : '';
$openai_url = '';

if ($path === 'chat') {
    $openai_url = 'https://api.openai.com/v1/chat/completions';
} elseif ($path === 'embeddings') {
    $openai_url = 'https://api.openai.com/v1/embeddings';
} else {
    // If path is not specified, try to detect from the request body
    $input_raw = file_get_contents('php://input');
    $input = json_decode($input_raw, true);
    
    if (isset($input['messages'])) {
        $openai_url = 'https://api.openai.com/v1/chat/completions';
    } elseif (isset($input['input'])) {
        $openai_url = 'https://api.openai.com/v1/embeddings';
    } else {
        http_response_code(400);
        echo json_encode(['error' => ['message' => 'Invalid request. Specify path=chat or path=embeddings.']]);
        exit;
    }
}

// Get the API key from secure config
$apiKey = getOpenAIKey();

if (empty($apiKey)) {
    http_response_code(500);
    echo json_encode(['error' => ['message' => 'OpenAI API key not configured on server.']]);
    exit;
}

// Get the raw input
$input_raw = file_get_contents('php://input');

// Forward request to OpenAI
$ch = curl_init($openai_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $input_raw);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Handle errors
if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(['error' => ['message' => 'Proxy Error: ' . curl_error($ch)]]);
} else {
    http_response_code($httpCode);
    echo $response;
}

curl_close($ch);
?>
