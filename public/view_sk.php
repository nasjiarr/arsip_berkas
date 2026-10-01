<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
check_login();

$sk_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$sk_id) {
    die('Invalid request');
}

// Query untuk mengambil file PDF
$query = "SELECT file_path, nomor_sk FROM sk_table WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$sk_id]);
$sk = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sk || !file_exists($sk['file_path'])) {
    die('File not found');
}

$is_download = !empty($_GET['download']);
$filename = basename($sk['file_path']);
$mime = mime_content_type($sk['file_path']) ?: 'application/pdf';

header('Content-Type: ' . $mime);
header('Content-Disposition: ' . ($is_download ? 'attachment' : 'inline') . '; filename="' . $filename . '"');
header('Content-Length: ' . filesize($sk['file_path']));
readfile($sk['file_path']);
exit;
