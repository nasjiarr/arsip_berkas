<?php
// get_pdf.php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
check_login();

$network_paths = [
    'kredit' => '\\\\172.16.34.5\\ftp\\BERKAS KREDIT\\',
    'sk' => '\\\\172.16.34.5\\ftp\\SK\\',
    'sop' => '\\\\172.16.34.5\\ftp\\SOP\\'
];

$role = $_SESSION['user']['role'];

if (isset($_GET['norek'])) {
    $norek = trim($_GET['norek']);
    $pdf_file = null;

    // Search in all paths
    foreach ($network_paths as $path) {
        $pdf_files = glob($path . $norek . "*.pdf");
        if (!empty($pdf_files)) {
            $pdf_file = $pdf_files[0];
            break;
        }
    }

    if ($pdf_file && file_exists($pdf_file)) {
        if (isset($_GET['download'])) {
            if ($role === 'adminkredit' || $role === 'ti_admin' || $role === 'admin_dok') {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($pdf_file) . '"');
                header('Cache-Control: public, must-revalidate, max-age=0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($pdf_file));
                readfile($pdf_file);
                exit;
            } else {
                header('HTTP/1.0 403 Forbidden');
                echo 'Akses ditolak. Anda tidak memiliki izin untuk mengunduh file.';
                exit;
            }
        } else {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($pdf_file) . '"');
            header('Cache-Control: public, must-revalidate, max-age=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($pdf_file));
            readfile($pdf_file);
            exit;
        }
    }
}

header('HTTP/1.0 404 Not Found');
echo 'File tidak ditemukan.';
