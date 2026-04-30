<?php
/**
 * getMemory.php
 * Restituisce il contenuto di memoria_salvata.json
 */

$filename = 'memoria_salvata.json';

header('Content-Type: application/json');

if (file_exists($filename)) {
    echo file_get_contents($filename);
} else {
    echo json_encode([]);
}
?>
