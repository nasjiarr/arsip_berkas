<?php
// ponytail: paginated search with SQL-indexed substring matching; upgrade to FULLTEXT when table exceeds 50k rows.
require_once '../includes/auth.php';
require_once '../includes/functions.php';
check_login();

if (!headers_sent()) {
    header('Content-Type: application/json');
}

$searchTerm = trim($_GET['term'] ?? '');
$year = !empty($_GET['year']) ? (int)$_GET['year'] : null;
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = max(1, min(50, (int)($_GET['limit'] ?? 10)));
$offset = ($page - 1) * $limit;

if (empty($searchTerm) && empty($year)) {
    echo json_encode([
        'results'     => [],
        'suggestions' => [],
        'currentPage' => 1,
        'totalPages'  => 0,
        'total'       => 0
    ]);
    exit;
}

$conditions = [];
$params = [];

if (!empty($searchTerm)) {
    $conditions[] = "(LOWER(judul_sk) LIKE LOWER(:term) OR LOWER(nomor_sk) LIKE LOWER(:term))";
    $params[':term'] = '%' . $searchTerm . '%';
}
if (!empty($year)) {
    $conditions[] = "tahun_disahkan = :year";
    $params[':year'] = $year;
}

$whereSql = "WHERE " . implode(" AND ", $conditions);

// 1. Total count query
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM sk_table $whereSql");
foreach ($params as $k => $v) {
    $countStmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$countStmt->execute();
$total = (int)$countStmt->fetchColumn();
$totalPages = (int)ceil($total / $limit);

// 2. Paginated results query
$query = "SELECT * FROM sk_table 
          $whereSql 
          ORDER BY tahun_disahkan DESC, nomor_sk DESC 
          LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
foreach ($params as $k => $v) {
    $stmt->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 3. Fast SQL-bounded suggestions
$suggestions = [];
if (!empty($searchTerm)) {
    $sugStmt = $pdo->prepare("SELECT DISTINCT judul_sk FROM sk_table WHERE LOWER(judul_sk) LIKE LOWER(:term) LIMIT 5");
    $sugStmt->execute([':term' => '%' . $searchTerm . '%']);
    $suggestions = $sugStmt->fetchAll(PDO::FETCH_COLUMN);
}

echo json_encode([
    'results'     => $results,
    'suggestions' => $suggestions,
    'currentPage' => $page,
    'totalPages'  => $totalPages,
    'total'       => $total
]);
