<?php
session_start();
require_once 'db_config.php';

// Validate file ID
if (!isset($_GET['file_id']) || !is_numeric($_GET['file_id'])) {
    header('HTTP/1.0 400 Bad Request');
    die('Invalid file request');
}

$file_id = intval($_GET['file_id']);

// Get file information from database
$stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
$stmt->execute([$file_id]);
$file = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$file) {
    header('Location: 404.php');
    exit;
}

$file_path = $file['file_path'];
$original_name = $file['original_name'] ?: $file['file_name'];

// Validate file existence
if (!file_exists($file_path)) {
    header('Location: 404.php');
    exit;
}

// Check if view mode is requested
$view_mode = isset($_GET['view']) && $_GET['view'] === 'true';

// Get file mime type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $file_path);
finfo_close($finfo);

// Set appropriate headers
header('Content-Type: ' . $mime_type);
header('Content-Length: ' . filesize($file_path));

if (!$view_mode) {
    // Force download
    header('Content-Disposition: attachment; filename="' . $original_name . '"');
} else {
    // Display in browser
    header('Content-Disposition: inline; filename="' . $original_name . '"');
}

// Disable output buffering
if (ob_get_level()) {
    ob_end_clean();
}

// Stream file
readfile($file_path);
exit;