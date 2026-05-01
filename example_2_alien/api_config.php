<?php
// Secure configuration for OpenAI API
// This file stores the obfuscated API key and provide utility functions to decode it.

// Simple XOR key for obfuscation - for demo purposes
if (!defined('XOR_KEY')) {
    define('XOR_KEY', 'skynet-security-2024-secret-key');
}

/**
 * Encodes a string using XOR and Base64
 */
function encodeKey($key) {
    $encoded = '';
    for($i = 0; $i < strlen($key); $i++) {
        $encoded .= $key[$i] ^ XOR_KEY[$i % strlen(XOR_KEY)];
    }
    return base64_encode($encoded);
}

/**
 * Decodes a string using Base64 and XOR
 */
function decodeKey($encodedKey) {
    if (empty($encodedKey)) return '';
    $decoded = base64_decode($encodedKey);
    $key = '';
    for($i = 0; $i < strlen($decoded); $i++) {
        $key .= $decoded[$i] ^ XOR_KEY[$i % strlen(XOR_KEY)];
    }
    return $key;
}

// The raw API key should be encoded using encodeKey() and stored here.
// For security, the raw key is never exposed in plain text in this file.
// For this demo, we'll try to get it from environment if it exists, otherwise it's empty.
$OBFUSCATED_API_KEY = ''; 

/**
 * Retrieves the decoded OpenAI API key
 */
function getOpenAIKey() {
    global $OBFUSCATED_API_KEY;
    
    $key = decodeKey($OBFUSCATED_API_KEY);
    
    if (empty($key)) {
        // Fallback for demo environment: check environment variables
        $envKey = getenv('OPENAI_API_KEY');
        if ($envKey) {
            return $envKey;
        }
    }
    
    return $key;
}
?>
