<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
check_login();

$sop_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$sop_id) {
    die('Invalid request');
}

// Query untuk mengambil file PDF
$query = "SELECT file_path, nomor_sop FROM sop_table WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$sop_id]);
$sop = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sop || !file_exists($sop['file_path'])) {
    die('File not found');
}

$is_download = !empty($_GET['download']);
$filename = basename($sop['file_path']);
$mime = mime_content_type($sop['file_path']) ?: 'application/pdf';

header('Content-Type: ' . $mime);
header('Content-Disposition: ' . ($is_download ? 'attachment' : 'inline') . '; filename="' . $filename . '"');
header('Content-Length: ' . filesize($sop['file_path']));
readfile($sop['file_path']);
exit;
