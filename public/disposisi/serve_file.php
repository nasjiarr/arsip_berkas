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

// Security check - ensure the path contains expected network path
if (strpos($file_path, '\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\') === false) {
    header("HTTP/1.0 403 Forbidden");
    exit("Access denied");
}

// Verify file exists
if (!file_exists($file_path)) {
    header("HTTP/1.0 404 Not Found");
    exit("File not found");
}

// Get file information
$file_info = pathinfo($file_path);
$file_size = filesize($file_path);

// Set appropriate headers for PDF files
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
header('Content-Length: ' . $file_size);
header('Cache-Control: public, must-revalidate, max-age=0');
header('Pragma: public');
header('X-Frame-Options: SAMEORIGIN');

// Output file contents
readfile($file_path);
exit;
