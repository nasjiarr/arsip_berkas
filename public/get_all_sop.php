<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
$offset = ($page - 1) * $limit;

// Query untuk mengambil data SOP dengan pagination
$query = "SELECT * FROM sop_table ORDER BY tahun_disahkan DESC, nomor_sop DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk menghitung total data
$countQuery = "SELECT COUNT(*) as total FROM sop_table";
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute();
$totalData = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

$totalPages = ceil($totalData / $limit);

echo json_encode([
    'results' => $results,
    'totalPages' => $totalPages,
    'currentPage' => $page
]);
