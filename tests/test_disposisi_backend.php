<?php
// Test Suite: Disposisi Backend - Category Filter & Mapping Alignment
echo "=== Running Disposisi Category Filter & Mapping Tests ===\n";

$base_dir = dirname(__DIR__);
require_once $base_dir . '/includes/config.php';

// 1. Verify syntax of modified files
$files = [
    $base_dir . '/public/disposisi/disposisi.php',
    $base_dir . '/public/disposisi/disposisi_keluar.php',
    $base_dir . '/public/disposisi/export_excel.php',
    $base_dir . '/public/disposisi/export_excel_keluar.php',
];

foreach ($files as $f) {
    exec("php -l " . escapeshellarg($f), $out, $code);
    assert($code === 0, basename($f) . " must have valid syntax");
}
echo "PASS: All 4 modified disposisi files have valid syntax.\n";

// 2. Test Category BI query matching both '1', '001', and kategori_id = 1
$stmt_bi = $pdo->query("SELECT COUNT(*) FROM disposisi_surat WHERE (kode IN ('1', '001') OR kategori_id = 1)");
$count_bi = (int)$stmt_bi->fetchColumn();
assert($count_bi >= 2, "BI filter must match records with '001' / id 1 (found: $count_bi)");
echo "PASS: BI category query matches $count_bi records correctly.\n";

// 3. Test Category OJK query matching '2', '002', and kategori_id = 2
$stmt_ojk = $pdo->query("SELECT COUNT(*) FROM disposisi_surat WHERE (kode IN ('2', '002') OR kategori_id = 2)");
$count_ojk = (int)$stmt_ojk->fetchColumn();
assert($count_ojk >= 0, "OJK filter query executes cleanly (found: $count_ojk)");
echo "PASS: OJK category query executes cleanly ($count_ojk records).\n";

// 4. Test Category UMUM query matching non-BI and non-OJK
$stmt_umum = $pdo->query("SELECT COUNT(*) FROM disposisi_surat WHERE (kode NOT IN ('1', '2', '001', '002') AND (kategori_id NOT IN (1, 2) OR kategori_id IS NULL))");
$count_umum = (int)$stmt_umum->fetchColumn();
assert($count_umum >= 1, "UMUM filter query must match other records (found: $count_umum)");
echo "PASS: UMUM category query matches $count_umum records correctly.\n";

// Total check
$stmt_all = $pdo->query("SELECT COUNT(*) FROM disposisi_surat");
$count_all = (int)$stmt_all->fetchColumn();
assert(($count_bi + $count_ojk + $count_umum) === $count_all, "Partition BI + OJK + UMUM (" . ($count_bi + $count_ojk + $count_umum) . ") must equal total ($count_all)");
echo "PASS: Full partition BI + OJK + UMUM equals total records in database.\n";

// 5. Test Disposisi Keluar Category Partition
$stmt_k_bi = $pdo->query("SELECT COUNT(*) FROM disposisi_keluar WHERE (kode IN ('1', '001') OR kategori_id = 1)");
$k_bi = (int)$stmt_k_bi->fetchColumn();
$stmt_k_ojk = $pdo->query("SELECT COUNT(*) FROM disposisi_keluar WHERE (kode IN ('2', '002') OR kategori_id = 2)");
$k_ojk = (int)$stmt_k_ojk->fetchColumn();
$stmt_k_umum = $pdo->query("SELECT COUNT(*) FROM disposisi_keluar WHERE (kode NOT IN ('1', '2', '001', '002') AND (kategori_id NOT IN (1, 2) OR kategori_id IS NULL))");
$k_umum = (int)$stmt_k_umum->fetchColumn();
$stmt_k_all = $pdo->query("SELECT COUNT(*) FROM disposisi_keluar");
$k_all = (int)$stmt_k_all->fetchColumn();
assert(($k_bi + $k_ojk + $k_umum) === $k_all, "Disposisi keluar partition matches total");
echo "PASS: Disposisi Keluar partition matches total records ($k_all).\n";

// 6. Test Search Query Parameter in Export URL
$disp_code = file_get_contents($base_dir . '/public/disposisi/disposisi.php');
assert(strpos($disp_code, 'search=') !== false, 'disposisi.php must include search in export_url');

$disp_k_code = file_get_contents($base_dir . '/public/disposisi/disposisi_keluar.php');
assert(strpos($disp_k_code, 'search=') !== false, 'disposisi_keluar.php must include search in export_url');

$export_code = file_get_contents($base_dir . '/public/disposisi/export_excel.php');
assert(strpos($export_code, "\$_GET['search']") !== false, 'export_excel.php must handle search param');

$export_k_code = file_get_contents($base_dir . '/public/disposisi/export_excel_keluar.php');
assert(strpos($export_k_code, "\$_GET['search']") !== false, 'export_excel_keluar.php must handle search param');
echo "PASS: Search synchronization across disposisi views and Excel export verified.\n";

// 7. Test Export Excel Query with Search parameter simulated
$search_term = '1112';
$search_like = '%' . $search_term . '%';
$stmt_srch = $pdo->prepare("SELECT COUNT(*) FROM disposisi_surat WHERE (nomer_surat LIKE ? OR perihal LIKE ? OR dari LIKE ? OR instruksi LIKE ? OR diteruskan LIKE ?)");
$stmt_srch->execute([$search_like, $search_like, $search_like, $search_like, $search_like]);
$srch_count = (int)$stmt_srch->fetchColumn();
assert($srch_count >= 1, "Simulated search must find matching records for '1112' (found: $srch_count)");
echo "PASS: Search parameterized query in export executed successfully ($srch_count records).\n";

// 8. Test Automatic Sequence Renumbering upon Delete
$del_code = file_get_contents($base_dir . '/public/disposisi/delete_disposisi.php');
assert(strpos($del_code, "updateNomorUrut(\$pdo, 'disposisi_surat')") !== false, 'delete_disposisi must call updateNomorUrut');

$del_k_code = file_get_contents($base_dir . '/public/disposisi/delete_disposisi_keluar.php');
assert(strpos($del_k_code, "updateNomorUrut(\$pdo, 'disposisi_keluar')") !== false, 'delete_disposisi_keluar must call updateNomorUrut');
echo "PASS: delete handlers include updateNomorUrut calls.\n";

// 9. Functional verification of renumbering in transaction
require_once $base_dir . '/public/disposisi/update_nomor.php';
$pdo->beginTransaction();
// Insert dummy record to create gap
$stmt_dummy = $pdo->prepare("INSERT INTO disposisi_surat (no, kode, kategori_id, tanggal_surat, tanggal_masuk, nomer_surat, dari, perihal, instruksi, diteruskan)
                             VALUES (999, '001', 1, '2026-01-01', '2026-01-01', 'TEST/GAP', 'Tester', 'Gap Test', 'None', 'None')");
$stmt_dummy->execute();
$dummy_id = $pdo->lastInsertId();

// Run updateNomorUrut
$renumber_ok = updateNomorUrut($pdo, 'disposisi_surat');
assert($renumber_ok === true, 'updateNomorUrut must execute without errors');

// Verify numbers are strictly consecutive 1..N
$rows = $pdo->query("SELECT no FROM disposisi_surat ORDER BY no ASC")->fetchAll(PDO::FETCH_COLUMN);
$expected = 1;
foreach ($rows as $no_val) {
    assert((int)$no_val === $expected, "Agenda number must be consecutive without gaps (expected: $expected, got: $no_val)");
    $expected++;
}
$pdo->rollBack();
echo "PASS: Transactional sequence gap closure verified (strictly consecutive 1..N).\n";

// 10. Test Role Authorization Harmonization (sekre & ti_admin)
$role_guarded_files = [
    'add_disposisi.php' => $base_dir . '/public/disposisi/add_disposisi.php',
    'add_disposisi_keluar.php' => $base_dir . '/public/disposisi/add_disposisi_keluar.php',
    'edit_disposisi.php' => $base_dir . '/public/disposisi/edit_disposisi.php',
    'edit_disposisi_keluar.php' => $base_dir . '/public/disposisi/edit_disposisi_keluar.php',
    'delete_disposisi.php' => $base_dir . '/public/disposisi/delete_disposisi.php',
    'delete_disposisi_keluar.php' => $base_dir . '/public/disposisi/delete_disposisi_keluar.php',
    'export_excel.php' => $base_dir . '/public/disposisi/export_excel.php',
    'export_excel_keluar.php' => $base_dir . '/public/disposisi/export_excel_keluar.php',
];

foreach ($role_guarded_files as $name => $path) {
    $code = file_get_contents($path);
    assert(strpos($code, "'ti_admin'") !== false, "$name must allow ti_admin role access");
    assert(strpos($code, "'sekre'") !== false, "$name must allow sekre role access");
}

$disp_content = file_get_contents($base_dir . '/public/disposisi/disposisi.php');
assert(strpos($disp_content, "(\$role === 'sekre' || \$role === 'ti_admin')") !== false, 'disposisi.php header buttons must allow ti_admin');

$disp_k_content = file_get_contents($base_dir . '/public/disposisi/disposisi_keluar.php');
assert(strpos($disp_k_content, "(\$role === 'sekre' || \$role === 'ti_admin')") !== false, 'disposisi_keluar.php header buttons must allow ti_admin');
echo "PASS: Role harmonization verified across all disposisi mutation handlers and view actions.\n";

// 11. Test Standardized Dynamic getFileUrl Resolver
assert(getFileUrl('') === '', 'Empty path URL must be empty string');
$unc_url = getFileUrl('\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\test.pdf');
assert(str_contains($unc_url, 'serve_file.php?path='), 'UNC path must resolve to serve_file.php');

$custom_url = getFileUrl('D:\\custom_storage\\agenda_surat_2026.pdf');
assert(str_contains($custom_url, 'serve_file.php?path='), 'Non-standard path must safely resolve to serve_file.php without depending on hardcoded DISPOSISI SURAT substring');

assert(strpos($disp_content, 'function getFileUrl') === false, 'disposisi.php must not have duplicate local getFileUrl');
assert(strpos($disp_k_content, 'function getFileUrl') === false, 'disposisi_keluar.php must not have duplicate local getFileUrl');
echo "PASS: Standardized dynamic getFileUrl resolver verified.\n";

echo "ALL DISPOSISI TESTS (CATEGORY, EXPORT SEARCH, AUTO RENUMBER, ROLE HARMONIZATION, DYNAMIC FILE URL) PASSED (100%)\n";
