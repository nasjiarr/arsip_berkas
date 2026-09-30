<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'arsip_berkas';


try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Aktifkan mode error
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// Network path
define('NETWORK_PATH', '\\172.16.34.5\ftp\BERKAS KREDIT');

// Define upload paths
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/uploads/');
define('UPLOAD_URL', '/uploads/');

// Helper functions
function isValidFile($file_path)
{
    if (empty($file_path)) {
        return false;
    }
    if (str_starts_with($file_path, '\\\\') || preg_match('/^[a-zA-Z]:[\\\\\/]/', $file_path)) {
        return file_exists($file_path);
    }
    $doc_root = $_SERVER['DOCUMENT_ROOT'] ?? '';
    return (!empty($doc_root) && file_exists($doc_root . $file_path)) || file_exists($file_path);
}

if (!function_exists('getFileUrl')) {
    function getFileUrl($file_path)
    {
        if (empty($file_path)) {
            return '';
        }
        if (str_contains($file_path, 'DISPOSISI SURAT')) {
            $prefix = file_exists('serve_file.php') ? 'serve_file.php' : (file_exists('disposisi/serve_file.php') ? 'disposisi/serve_file.php' : '../disposisi/serve_file.php');
            return $prefix . '?path=' . urlencode($file_path);
        }
        return $file_path;
    }
}

// Allowed file types
define('ALLOWED_TYPES', [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'image/gif'
]);
