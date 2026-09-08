<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
check_login();

// // Validasi role user yang bisa download
// if (!in_array($role, ['admin_dok', 'ti_admin'])) {
//     header('HTTP/1.0 403 Forbidden');
//     die('You are not allowed to download this file');
// }

$sop_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$sop_id) {
    die('Invalid request: Missing SOP ID');
}

try {
    // Query untuk mengambil informasi SOP
    $query = "SELECT nomor_sop, judul_sop, file_path FROM sop_table WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$sop_id]);
    $sop = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sop) {
        die('SOP not found');
    }

    $file_path = $sop['file_path'];

    if (!file_exists($file_path)) {
        die('File not found on server');
    }

    $file_size = filesize($file_path);
    $download_name = $sop['nomor_sop'] . ' - ' . $sop['judul_sop'] . '.pdf';
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
    error_log('Error downloading SOP: ' . $e->getMessage());
    die('Terjadi kesalahan saat mengunduh file. Silakan coba lagi nanti.');
}
