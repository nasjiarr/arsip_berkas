<?php
require_once '../includes/auth.php';
check_login();

$network_path_ttd = '\\\\172.16.34.5\\ftp\\TTD\\';

if (isset($_GET['norek'])) {
    $norek = trim($_GET['norek']);
    $jpg_files_ttd = glob($network_path_ttd . $norek . "*.jpg");
    if (!empty($jpg_files_ttd)) {
        $jpg_file_ttd = $jpg_files_ttd[0]; // Ambil file pertama yang cocok
        if (is_readable($jpg_file_ttd)) {
            header("Content-Type: image/jpeg");
            readfile($jpg_file_ttd);
            exit;
        }
    }
}

http_response_code(404);
echo "File spesimen tanda tangan tidak ditemukan.";
