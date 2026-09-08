<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
check_login();

$sop_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$sop_id) {
    die('Invalid request');
}

// Query untuk mengambil file PDF
$query = "SELECT file_path FROM sop_table WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$sop_id]);
$sop = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sop || !file_exists($sop['file_path'])) {
    die('File not found');
}

// Output PDF
header('Content-Type: application/pdf');
readfile($sop['file_path']);
