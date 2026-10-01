<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
check_login();

if (!headers_sent()) {
    header('Content-Type: application/json');
}

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = max(1, min(50, (int)($_GET['limit'] ?? 10)));
$offset = ($page - 1) * $limit;
$year = !empty($_GET['year']) ? (int)$_GET['year'] : null;

$whereClause = $year ? "WHERE tahun_disahkan = :year" : "";

// Query untuk mengambil data SK dengan pagination
$query = "SELECT * FROM sk_table $whereClause ORDER BY tahun_disahkan DESC, nomor_sk DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
if ($year) {
    $stmt->bindValue(':year', $year, PDO::PARAM_INT);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Query untuk menghitung total data
$countQuery = "SELECT COUNT(*) as total FROM sk_table $whereClause";
$countStmt = $pdo->prepare($countQuery);
if ($year) {
    $countStmt->bindValue(':year', $year, PDO::PARAM_INT);
}
$countStmt->execute();
$totalData = (int)$countStmt->fetch(PDO::FETCH_ASSOC)['total'];

// Available Years
$yearsStmt = $pdo->query("SELECT DISTINCT tahun_disahkan FROM sk_table WHERE tahun_disahkan IS NOT NULL AND tahun_disahkan > 0 ORDER BY tahun_disahkan DESC");
$years = $yearsStmt->fetchAll(PDO::FETCH_COLUMN);

$totalPages = (int)ceil($totalData / $limit);

echo json_encode([
    'results' => $results,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'total' => $totalData,
    'years' => $years
]);
