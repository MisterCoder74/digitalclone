<?php
/**
 * widget_vupload.php
 * Handles image uploads for the Skynet Digital Clone Widget
 * Saves images to the uploads/ directory and returns the URL
 */

header('Content-Type: application/json');

// Configuration
$targetDir = __DIR__ . '/uploads/';
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$maxFileSize = 20 * 1024 * 1024; // 20MB

// Create uploads directory if it doesn't exist
if (!is_dir($targetDir)) {
    if (!mkdir($targetDir, 0755, true)) {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to create uploads directory"
        ]);
        exit;
    }
}

// Check if file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => "File exceeds server's maximum upload size",
        UPLOAD_ERR_FORM_SIZE => "File exceeds the MAX_FILE_SIZE directive",
        UPLOAD_ERR_PARTIAL => "File was only partially uploaded",
        UPLOAD_ERR_NO_FILE => "No file was uploaded",
        UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder",
        UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
        UPLOAD_ERR_EXTENSION => "Upload blocked by extension",
    ];
    
    $errorCode = $_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE;
    $errorMsg = $errorMessages[$errorCode] ?? "Unknown upload error";
    
    echo json_encode([
        "status" => "error",
        "message" => $errorMsg
    ]);
    exit;
}

// Check file size
if ($_FILES['file']['size'] > $maxFileSize) {
    echo json_encode([
        "status" => "error",
        "message" => "File size exceeds 20MB limit"
    ]);
    exit;
}

// Get file info
$originalName = basename($_FILES['file']['name']);
$fileType = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

// Validate file type
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode([
        "status" => "error",
        "message" => "Only JPG, PNG, GIF, and WEBP files are allowed"
    ]);
    exit;
}

// Generate unique filename to avoid collisions
$uniqueId = uniqid('img_', true);
$newFileName = $uniqueId . '.' . $fileType;
$targetFile = $targetDir . $newFileName;

// Move uploaded file
if (move_uploaded_file($_FILES['file']['tmp_name'], $targetFile)) {
    // Return the relative URL for the uploaded file
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $fileUrl = $protocol . '://' . $host . $scriptDir . '/uploads/' . $newFileName;
    
    echo json_encode([
        "status" => "success",
        "url" => $fileUrl,
        "filename" => $newFileName,
        "originalName" => $originalName,
        "size" => $_FILES['file']['size']
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to save uploaded file"
    ]);
}
?>