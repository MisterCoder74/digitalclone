<?php
/**
 * indexBio.php
 * Genera biography_index.json dalla cartella /bio
 * Richiede apiKey via POST o GET per le chiamate a OpenAI
 * Estrae anche BirthDate e ZodiacalSign per aggiornare persona_details.json
 */

require_once 'api_config.php';

header('Content-Type: application/json');

$apiKey = getOpenAIKey();

if (!$apiKey) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "OpenAI API Key is not configured on server"]);
    exit;
}

$bioDir = __DIR__ . '/bio/';
$outputFile = __DIR__ . '/biography_index.json';
$personaFile = __DIR__ . '/persona_details.json';

// Zodiac mapping: zodiac sign by date range (month, day)
function getZodiacSign($day, $month) {
    $zodiacSigns = [
        ['name' => 'Capricorn', 'start' => [1, 1], 'end' => [1, 19]],
        ['name' => 'Aquarius', 'start' => [1, 20], 'end' => [2, 18]],
        ['name' => 'Pisces', 'start' => [2, 19], 'end' => [3, 20]],
        ['name' => 'Aries', 'start' => [3, 21], 'end' => [4, 19]],
        ['name' => 'Taurus', 'start' => [4, 20], 'end' => [5, 20]],
        ['name' => 'Gemini', 'start' => [5, 21], 'end' => [6, 20]],
        ['name' => 'Cancer', 'start' => [6, 21], 'end' => [7, 22]],
        ['name' => 'Leo', 'start' => [7, 23], 'end' => [8, 22]],
        ['name' => 'Virgo', 'start' => [8, 23], 'end' => [9, 22]],
        ['name' => 'Libra', 'start' => [9, 23], 'end' => [10, 22]],
        ['name' => 'Scorpio', 'start' => [10, 23], 'end' => [11, 21]],
        ['name' => 'Sagittarius', 'start' => [11, 22], 'end' => [12, 21]],
        ['name' => 'Capricorn', 'start' => [12, 22], 'end' => [12, 31]]
    ];
    
    foreach ($zodiacSigns as $sign) {
        $startMonth = $sign['start'][0];
        $startDay = $sign['start'][1];
        $endMonth = $sign['end'][0];
        $endDay = $sign['end'][1];
        
        if (($month == $startMonth && $day >= $startDay) || ($month == $endMonth && $day <= $endDay)) {
            return $sign['name'];
        }
    }
    return 'Unknown';
}

// Parse date like "born on July 10, 1856" or "July 10, 1856" or Italian "nato il 14 marzo 1985"
function parseDate($text) {
    $months = [
        'gennaio' => 1, 'febbraio' => 2, 'marzo' => 3, 'aprile' => 4,
        'maggio' => 5, 'giugno' => 6, 'luglio' => 7, 'agosto' => 8,
        'settembre' => 9, 'ottobre' => 10, 'novembre' => 11, 'dicembre' => 12,
        'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4,
        'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8,
        'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12
    ];
    
    // Pattern: "born on July 10, 1856" or "nato il 14 marzo 1985"
    if (preg_match('/(?:born\s+on|nato\s+il)\s+(\w+)\s+(\d{1,2}),?\s+(\d{4})/i', $text, $matches)) {
        $monthName = strtolower($matches[1]);
        $day = (int)$matches[2];
        $year = (int)$matches[3];
        if (isset($months[$monthName])) {
            return sprintf('%04d-%02d-%02d', $year, $months[$monthName], $day);
        }
    }

    // Pattern: "July 10, 1856"
    if (preg_match('/(\w+)\s+(\d{1,2}),?\s+(\d{4})/i', $text, $matches)) {
        $monthName = strtolower($matches[1]);
        $day = (int)$matches[2];
        $year = (int)$matches[3];
        if (isset($months[$monthName])) {
            return sprintf('%04d-%02d-%02d', $year, $months[$monthName], $day);
        }
    }

    // Pattern: "10 July 1856"
    if (preg_match('/(\d{1,2})\s+(\w+)\s+(\d{4})/i', $text, $matches)) {
        $day = (int)$matches[1];
        $monthName = strtolower($matches[2]);
        $year = (int)$matches[3];
        if (isset($months[$monthName])) {
            return sprintf('%04d-%02d-%02d', $year, $months[$monthName], $day);
        }
    }
    
    return null;
}

// Extract zodiac sign from text like "del segno dei Pesci" or "Zodiac sign: Cancer"
function extractZodiacSign($text) {
    $zodiacMapping = [
        'ariete' => 'Aries', 'aries' => 'Aries',
        'toro' => 'Taurus', 'taurus' => 'Taurus',
        'gemelli' => 'Gemini', 'gemini' => 'Gemini',
        'cancro' => 'Cancer', 'cancer' => 'Cancer',
        'leone' => 'Leo', 'leo' => 'Leo',
        'vergine' => 'Virgo', 'virgo' => 'Virgo',
        'bilancia' => 'Libra', 'libra' => 'Libra',
        'scorpione' => 'Scorpio', 'scorpio' => 'Scorpio',
        'sagittario' => 'Sagittarius', 'sagittarius' => 'Sagittarius',
        'capricorno' => 'Capricorn', 'capricorn' => 'Capricorn',
        'acquario' => 'Aquarius', 'aquarius' => 'Aquarius',
        'pesci' => 'Pisces', 'pisces' => 'Pisces'
    ];
    
    $text = strtolower($text);
    
    foreach ($zodiacMapping as $key => $english) {
        if (preg_match('/\b' . preg_quote($key, '/') . '\b/i', $text)) {
            return $english;
        }
    }
    
    return null;
}

if (!is_dir($bioDir)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Directory /bio/ not found"]);
    exit;
}

$files = glob($bioDir . '*.txt');
$index = [];
$allBioContent = '';
$extractedBirthDate = null;
$extractedZodiacSign = null;

foreach ($files as $file) {
    $content = file_get_contents($file);
    if (!$content) continue;

    $fileName = basename($file);
    $allBioContent .= "\n\n" . $content;
    
    // Extract birth date from this file if not already found
    if ($extractedBirthDate === null) {
        $extractedBirthDate = parseDate($content);
    }
    
    // Extract zodiac sign from this file if not already found
    if ($extractedZodiacSign === null) {
        $extractedZodiacSign = extractZodiacSign($content);
    }
    
    // If we have a birth date but no zodiac sign, calculate it
    if ($extractedBirthDate !== null && $extractedZodiacSign === null) {
        $dateParts = explode('-', $extractedBirthDate);
        if (count($dateParts) === 3) {
            $extractedZodiacSign = getZodiacSign((int)$dateParts[2], (int)$dateParts[1]);
        }
    }
    
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

// Update persona_details.json with extracted BirthDate and ZodiacalSign
$personaUpdated = false;
if ($extractedBirthDate !== null || $extractedZodiacSign !== null) {
    if (file_exists($personaFile)) {
        $personaData = json_decode(file_get_contents($personaFile), true);
        if ($personaData && isset($personaData['Persona']) && isset($personaData['Persona'][0])) {
            if ($extractedBirthDate !== null) {
                $personaData['Persona'][0]['BirthDate'] = $extractedBirthDate;
                // Also update BirthDay
                $dateParts = explode('-', $extractedBirthDate);
                if (count($dateParts) === 3) {
                    $personaData['Persona'][0]['BirthDay'] = (int)$dateParts[2];
                }
            }
            if ($extractedZodiacSign !== null) {
                $personaData['Persona'][0]['ZodiacalSign'] = $extractedZodiacSign;
            }
            
            if (file_put_contents($personaFile, json_encode($personaData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
                $personaUpdated = true;
            }
        }
    }
}

if (file_put_contents($outputFile, json_encode($index, JSON_UNESCAPED_UNICODE))) {
    $response = [
        "status" => "success", 
        "message" => "biography_index.json generated", 
        "files_processed" => count($index)
    ];
    
    if ($personaUpdated) {
        $response["persona_updated"] = true;
        if ($extractedBirthDate) {
            $response["birth_date"] = $extractedBirthDate;
        }
        if ($extractedZodiacSign) {
            $response["zodiac_sign"] = $extractedZodiacSign;
        }
    }
    
    echo json_encode($response);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to write biography_index.json"]);
}
?>
