<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
check_login();

$network_path_ttd = PATH_TTD;

if (isset($_GET['norek'])) {
    $norek = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', trim($_GET['norek']));
    if (!empty($norek)) {
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
}

http_response_code(404);
echo "File spesimen tanda tangan tidak ditemukan.";
