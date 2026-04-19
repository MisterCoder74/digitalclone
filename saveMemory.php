<?php
ini_set('serialize_precision', -1);
/**
 * saveMemory.php
 * Riceve un oggetto memoria via POST e lo aggiunge a memoria_salvata.json
 */

header('Content-Type: application/json');

// Leggi il corpo della richiesta
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Dati non validi"]);
    exit;
}

$filename = 'memoria_salvata.json';

// Carica la memoria esistente
$memoria = [];
if (file_exists($filename)) {
    $json = file_get_contents($filename);
    $memoria = json_decode($json, true);
    if ($memoria === null) {
        $memoria = [];
    }
}

// Aggiungi il nuovo record se non esiste già un record identico (stesso testo)
$exists = false;
foreach ($memoria as $item) {
    if (isset($item['testo']) && $item['testo'] === $data['testo']) {
        $exists = true;
        break;
    }
}

if (!$exists) {
    $memoria[] = $data;
}

// Arrotonda TUTTI i vettori per evitare precisioni eccessive (corregge anche record esistenti)
foreach ($memoria as &$item) {
    if (isset($item['vettore']) && is_array($item['vettore'])) {
        foreach ($item['vettore'] as &$v) {
            $v = round((float)$v, 5);
        }
    }
}

// Forza la precisione di serializzazione per i float
ini_set('serialize_precision', -1);

// Salva su file
if (file_put_contents($filename, json_encode($memoria, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
    if ($exists) {
        echo json_encode(["status" => "success", "message" => "Memoria aggiornata"]);
    } else {
        echo json_encode(["status" => "success", "message" => "Memoria salvata"]);
    }
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Errore nel salvataggio su file"]);
}
?>