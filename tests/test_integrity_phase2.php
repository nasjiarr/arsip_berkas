<?php
// ponytail: Phase 2 integrity test asserting DB queries, schema alignment, and path resolution.
require_once __DIR__ . '/../includes/config.php';

// 1. Test isValidFile & getFileUrl
assert(isValidFile('') === false, 'Empty path must be invalid');
assert(getFileUrl('') === '', 'Empty path URL must be empty string');
assert(str_contains(getFileUrl('\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\test.pdf'), 'serve_file.php?path='), 'UNC path must be converted to serve_file URL');

// 2. Test DB Schema Alignment
$stmt = $pdo->query("SHOW COLUMNS FROM disposisi_surat LIKE 'instruksi'");
assert($stmt->fetch() !== false, "disposisi_surat must contain 'instruksi' column");

$stmt = $pdo->query("SHOW COLUMNS FROM disposisi_surat LIKE 'kategori_id'");
$col_kategori = $stmt->fetch(PDO::FETCH_ASSOC);
assert($col_kategori['Null'] === 'YES' || $col_kategori['Default'] !== null, "disposisi_surat.kategori_id must be nullable or have a default value");

$stmt = $pdo->query("SHOW COLUMNS FROM disposisi_keluar LIKE 'kategori_id'");
$col_keluar_kat = $stmt->fetch(PDO::FETCH_ASSOC);
assert($col_keluar_kat['Null'] === 'YES' || $col_keluar_kat['Default'] !== null, "disposisi_keluar.kategori_id must be nullable or have a default value");

// 3. Test Transactional Inserts for both Surat Masuk & Keluar
$pdo->beginTransaction();

// Test disposisi_surat insert
$stmt1 = $pdo->prepare("INSERT INTO disposisi_surat (no, kode, kategori_id, tanggal_surat, tanggal_masuk, nomer_surat, dari, perihal, instruksi, diteruskan, file_path) 
                        VALUES (9999, '001', 1, '2026-01-01', '2026-01-01', 'TEST/001', 'Unit Testing', 'Perihal Test', 'Instruksi Test', 'Kabag Test', NULL)");
$res1 = $stmt1->execute();
assert($res1 === true, "disposisi_surat insert must succeed without DB constraint violation");

// Test search query that formerly crashed due to missing 'instruksi' column
$stmtSearch = $pdo->prepare("SELECT COUNT(*) FROM disposisi_surat WHERE (nomer_surat LIKE :q OR perihal LIKE :q OR dari LIKE :q OR instruksi LIKE :q OR diteruskan LIKE :q)");
$stmtSearch->execute([':q' => '%Test%']);
$count = $stmtSearch->fetchColumn();
assert($count >= 1, "Search query with 'instruksi' column must execute successfully");

// Test disposisi_keluar insert
$stmt2 = $pdo->prepare("INSERT INTO disposisi_keluar (no, kode, kategori_id, tanggal, nomor_surat, perihal, ke, file_path) 
                        VALUES (9999, '001', 1, '2026-01-01', 'TEST/002', 'Perihal Keluar Test', 'Tujuan Test', NULL)");
$res2 = $stmt2->execute();
assert($res2 === true, "disposisi_keluar insert must succeed without DB constraint violation");

$pdo->rollBack();

// 4. Test Filename Safety Sanitization
$raw_title = "SK Penetapan // ## Libur 2026!";
$clean_title = preg_replace("/[^a-zA-Z0-9\s]/", "", $raw_title);
$clean_title = trim($clean_title);
assert($clean_title === "SK Penetapan   Libur 2026", "Special characters must be stripped cleanly");

echo "[OK] All Phase 2 database & logic integrity assertions passed successfully.\n";
