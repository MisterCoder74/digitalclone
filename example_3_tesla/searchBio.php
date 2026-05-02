<?php
/**
 * searchBio.php
 * Ricerca semantica nell'indice della biografia
 */

require_once 'api_config.php';

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$query = $data['query'] ?? '';
$apiKey = getOpenAIKey();

if (!$query || !$apiKey) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Query is required and API Key must be configured on server"]);
    exit;
}

$indexFile = __DIR__ . '/biography_index.json';
if (!file_exists($indexFile)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Biography index not found. Please run indexBio.php first."]);
    exit;
}

// 1. Get embedding for the query
$ch = curl_init('https://api.openai.com/v1/embeddings');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: ' . 'Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'model' => 'text-embedding-3-small',
    'input' => $query,
    'dimensions' => 512
]));

$response = curl_exec($ch);
if (curl_errno($ch)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "OpenAI API call failed"]);
    exit;
}
curl_close($ch);

$resData = json_decode($response, true);
if (!isset($resData['data'][0]['embedding'])) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to get embedding for query"]);
    exit;
}
$queryVector = $resData['data'][0]['embedding'];

// 2. Load index
$index = json_decode(file_get_contents($indexFile), true);

// 3. Calculate similarity
function cosineSimilarity($u, $v) {
    $dotProduct = 0;
    $uMagnitude = 0;
    $vMagnitude = 0;
    $count = count($u);
    for ($i = 0; $i < $count; $i++) {
        $dotProduct += $u[$i] * $v[$i];
        $uMagnitude += $u[$i] * $u[$i];
        $vMagnitude += $v[$i] * $v[$i];
    }
    $uMagnitude = sqrt($uMagnitude);
    $vMagnitude = sqrt($vMagnitude);
    if ($uMagnitude == 0 || $vMagnitude == 0) {
        return 0;
    }
    return $dotProduct / ($uMagnitude * $vMagnitude);
}

$matches = [];
foreach ($index as $item) {
    $similarity = cosineSimilarity($queryVector, $item['vector']);
    $matches[] = [
        "file" => $item['file'],
        "text" => $item['text'],
        "similarity" => $similarity
    ];
}

// 4. Sort and return top 3
usort($matches, function($a, $b) {
    return $b['similarity'] <=> $a['similarity'];
});

$topMatches = array_slice($matches, 0, 3);

echo json_encode($topMatches);
?>
