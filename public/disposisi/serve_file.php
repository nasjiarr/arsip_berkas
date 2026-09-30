<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Check if user is logged in
check_login();

// Validate and sanitize the file path
if (!isset($_GET['path']) || empty($_GET['path'])) {
    header("HTTP/1.0 404 Not Found");
    exit("File not found");
}

$file_path = urldecode($_GET['path']);
$normalized = str_replace('/', '\\', $file_path);

// Security check - ensure path starts strictly with the network share and prevents directory traversal
if (strpos($normalized, '\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\') !== 0 || str_contains($normalized, '..')) {
    header("HTTP/1.0 403 Forbidden");
    exit("Access denied");
}

$ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
$mime_map = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
];

if (!array_key_exists($ext, $mime_map)) {
    header("HTTP/1.0 403 Forbidden");
    exit("File type not allowed");
}

// Verify file exists
if (!file_exists($file_path)) {
    header("HTTP/1.0 404 Not Found");
    exit("File not found");
}

// Get file information
$file_size = filesize($file_path);

// Set appropriate headers based on verified extension
header('Content-Type: ' . $mime_map[$ext]);
header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
header('Content-Length: ' . $file_size);
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: public');
header('X-Frame-Options: SAMEORIGIN');

// Output file contents
readfile($file_path);
exit;
