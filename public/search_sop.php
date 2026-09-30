<?php
// ponytail: paginated search with SQL-indexed substring matching; upgrade to FULLTEXT when table exceeds 50k rows.
require_once '../includes/auth.php';
require_once '../includes/functions.php';
check_login();

header('Content-Type: application/json');

$searchTerm = trim($_GET['term'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = max(1, min(50, (int)($_GET['limit'] ?? 5)));
$offset = ($page - 1) * $limit;

if (empty($searchTerm)) {
    echo json_encode([
        'results'     => [],
        'suggestions' => [],
        'currentPage' => 1,
        'totalPages'  => 0,
        'total'       => 0
    ]);
    exit;
}

$likeParam = '%' . $searchTerm . '%';

// 1. Total count query
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM sop_table WHERE LOWER(judul_sop) LIKE LOWER(:term) OR LOWER(nomor_sop) LIKE LOWER(:term)");
$countStmt->execute([':term' => $likeParam]);
$total = (int)$countStmt->fetchColumn();
$totalPages = (int)ceil($total / $limit);

// 2. Paginated results query
$query = "SELECT * FROM sop_table 
          WHERE LOWER(judul_sop) LIKE LOWER(:term) OR LOWER(nomor_sop) LIKE LOWER(:term) 
          ORDER BY tahun_disahkan DESC, nomor_sop DESC 
          LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':term', $likeParam, PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Fast SQL-bounded suggestions (top 5 matching titles)
$sugStmt = $pdo->prepare("SELECT DISTINCT judul_sop FROM sop_table WHERE LOWER(judul_sop) LIKE LOWER(:term) LIMIT 5");
$sugStmt->execute([':term' => $likeParam]);
$suggestions = $sugStmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode([
    'results'     => $results,
    'suggestions' => $suggestions,
    'currentPage' => $page,
    'totalPages'  => $totalPages,
    'total'       => $total
]);
