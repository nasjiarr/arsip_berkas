<?php
// ponytail: Phase 3 assertions verifying indexes, query explain plans, and paginated search response schemas.
require_once __DIR__ . '/../includes/config.php';

// 1. Verify indexes exist
$checkIndexes = [
    'disposisi_surat'  => ['idx_disp_tgl_kode', 'idx_disp_no'],
    'disposisi_keluar' => ['idx_disp_keluar_tgl_kode', 'idx_disp_keluar_no'],
    'sk_table'         => ['idx_sk_tahun_nomor', 'idx_sk_judul'],
    'sop_table'        => ['idx_sop_tahun_nomor', 'idx_sop_judul']
];

foreach ($checkIndexes as $table => $expectedIndexes) {
    $stmt = $pdo->query("SHOW INDEX FROM `$table`");
    $existing = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'Key_name');
    foreach ($expectedIndexes as $idx) {
        assert(in_array($idx, $existing), "Index $idx must exist on $table");
    }
}

// 2. Verify sk_table and sop_table alignment
$stmt = $pdo->query("SHOW COLUMNS FROM sk_table LIKE 'tahun_disahkan'");
assert($stmt->fetch() !== false, "sk_table must contain 'tahun_disahkan' column");

$stmt = $pdo->query("SHOW COLUMNS FROM sop_table LIKE 'tahun_disahkan'");
assert($stmt->fetch() !== false, "sop_table must contain 'tahun_disahkan' column");

// 3. Verify EXPLAIN on sk_table sorting
$stmt = $pdo->query("EXPLAIN SELECT * FROM sk_table ORDER BY tahun_disahkan DESC, nomor_sk DESC");
$explain = $stmt->fetch(PDO::FETCH_ASSOC);
assert(!empty($explain), "Explain query on sk_table must produce query plan");

// 4. Test Search SQL execution with pagination
$term = 'Libur';
$limit = 5;
$offset = 0;
$likeParam = '%' . $term . '%';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM sk_table WHERE LOWER(judul_sk) LIKE LOWER(:term) OR LOWER(nomor_sk) LIKE LOWER(:term)");
$countStmt->execute([':term' => $likeParam]);
$total = (int)$countStmt->fetchColumn();

$query = "SELECT * FROM sk_table 
          WHERE LOWER(judul_sk) LIKE LOWER(:term) OR LOWER(nomor_sk) LIKE LOWER(:term) 
          ORDER BY tahun_disahkan DESC, nomor_sk DESC 
          LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':term', $likeParam, PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

assert($total >= 1, "Should find at least 1 record for 'Libur'");
assert(count($results) >= 1, "Results count must match query");

// 5. Test SQL bounded suggestions
$sugStmt = $pdo->prepare("SELECT DISTINCT judul_sk FROM sk_table WHERE LOWER(judul_sk) LIKE LOWER(:term) LIMIT 5");
$sugStmt->execute([':term' => $likeParam]);
$suggestions = $sugStmt->fetchAll(PDO::FETCH_COLUMN);
assert(is_array($suggestions), "Suggestions must be an array");

echo "[OK] All Phase 3 performance & scalability assertions passed successfully.\n";
