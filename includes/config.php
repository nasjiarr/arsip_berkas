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
    return !empty($file_path) && file_exists($_SERVER['DOCUMENT_ROOT'] . $file_path);
}

// Allowed file types
define('ALLOWED_TYPES', [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'image/gif'
]);
