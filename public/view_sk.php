<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
check_login();

$sk_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$sk_id) {
    die('Invalid request');
}

// Query untuk mengambil file PDF
$query = "SELECT file_path FROM sk_table WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$sk_id]);
$sk = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sk || !file_exists($sk['file_path'])) {
    die('File not found');
}


// Output PDF
header('Content-Type: application/pdf');
readfile($sk['file_path']);
