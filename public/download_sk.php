<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
check_login();

// // Validasi role user yang bisa download
// if (!in_array($role, ['admin_dok', 'ti_admin'])) {
//     header('HTTP/1.0 403 Forbidden');
//     die('You are not allowed to download this file');
// }

$sk_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$sk_id) {
    die('Invalid request: Missing SK ID');
}

try {
    // Query untuk mengambil informasi SK
    $query = "SELECT nomor_sk, judul_sk, file_path FROM sk_table WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$sk_id]);
    $sk = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sk) {
        die('SK not found');
    }

    $file_path = $sk['file_path'];

    if (!file_exists($file_path)) {
        die('File not found on server');
    }

    $file_size = filesize($file_path);
    $download_name = $sk['nomor_sk'] . ' - ' . $sk['judul_sk'] . '.pdf';
    $download_name = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $download_name);

    // Set header untuk download
    header('Content-Type: application/pdf');
    header('Content-Length: ' . $file_size);
    header('Content-Disposition: attachment; filename="' . $download_name . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Output file
    readfile($file_path);
    exit;
} catch (Exception $e) {
    error_log('Error downloading SK: ' . $e->getMessage());
    die('Terjadi kesalahan saat mengunduh file. Silakan coba lagi nanti.');
}
