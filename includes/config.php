<?php
// ponytail: stdlib config with env overrides; upgrade to vlucas/phpdotenv if multi-environment files (.env) are required.

// Database configuration
$host = getenv('DB_HOST') ?: 'localhost';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
$database = getenv('DB_NAME') ?: 'arsip_berkas';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => true, // Required for multiple named parameter reuse in LIKE search
    ]);
} catch (PDOException $e) {
    error_log('Database connection error: ' . $e->getMessage());
    die('Koneksi database gagal. Silakan hubungi administrator.');
}

// Storage configuration (UNC Network Share / Local directory)
define('STORAGE_BASE_PATH', getenv('STORAGE_BASE_PATH') ?: '\\\\172.16.34.5\\ftp\\');

function get_storage_path($subfolder = '')
{
    $base = rtrim(STORAGE_BASE_PATH, '\\/') . DIRECTORY_SEPARATOR;
    return empty($subfolder) ? $base : $base . trim($subfolder, '\\/') . DIRECTORY_SEPARATOR;
}

define('PATH_DISPOSISI', get_storage_path('DISPOSISI SURAT'));
define('PATH_BERKAS_KREDIT', get_storage_path('BERKAS KREDIT'));
define('PATH_TTD', get_storage_path('TTD'));
define('PATH_SK', get_storage_path('SK'));
define('PATH_SOP', get_storage_path('SOP'));

// Backwards compatibility alias
define('NETWORK_PATH', rtrim(PATH_BERKAS_KREDIT, '\\/'));

// Local upload paths
define('UPLOAD_DIR', ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/uploads/');
define('UPLOAD_URL', '/uploads/');

// Allowed file types
define('ALLOWED_TYPES', [
    'application/pdf',
    'image/jpeg',
    'image/png',
    'image/gif'
]);

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

// ponytail: dynamic disposisi URL resolver via serve_file.php; handles relative, UNC, and custom storage paths.
if (!function_exists('getFileUrl')) {
    function getFileUrl($file_path)
    {
        if (empty($file_path)) {
            return '';
        }
        $prefix = 'serve_file.php';
        if (file_exists('disposisi/serve_file.php')) {
            $prefix = 'disposisi/serve_file.php';
        } elseif (file_exists('../public/disposisi/serve_file.php')) {
            $prefix = '../public/disposisi/serve_file.php';
        } elseif (file_exists('../disposisi/serve_file.php')) {
            $prefix = '../disposisi/serve_file.php';
        }
        return $prefix . '?path=' . urlencode($file_path);
    }
}

function handle_system_error(Throwable $e, $user_msg = 'Terjadi kesalahan pada sistem. Silakan coba lagi nanti.')
{
    error_log('[ERROR] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    return $user_msg;
}
