<?php
// ponytail: Phase 4 assertions verifying centralized configuration, path normalization, safe error handling, and transactional renumbering.
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../public/disposisi/update_nomor.php';

// 1. Storage paths & normalization
assert(defined('STORAGE_BASE_PATH'), 'STORAGE_BASE_PATH must be defined');
assert(defined('PATH_DISPOSISI'), 'PATH_DISPOSISI must be defined');
assert(defined('PATH_BERKAS_KREDIT'), 'PATH_BERKAS_KREDIT must be defined');
assert(defined('PATH_TTD'), 'PATH_TTD must be defined');
assert(defined('PATH_SK'), 'PATH_SK must be defined');
assert(defined('PATH_SOP'), 'PATH_SOP must be defined');

$samplePath = get_storage_path('TEST_FOLDER');
assert(str_ends_with($samplePath, DIRECTORY_SEPARATOR), 'get_storage_path must end with directory separator');

// 2. Error handling sanitization
$testException = new Exception('Sensitive SQL/Path internal error details');
$safeMsg = handle_system_error($testException);
assert(!str_contains($safeMsg, 'Sensitive SQL'), 'System error message must not leak internal exception details');

// 3. PDO connection attributes
assert($pdo->getAttribute(PDO::ATTR_ERRMODE) === PDO::ERRMODE_EXCEPTION, 'PDO must use ERRMODE_EXCEPTION');
assert($pdo->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE) === PDO::FETCH_ASSOC, 'PDO default fetch mode must be FETCH_ASSOC');
assert($pdo->getAttribute(PDO::ATTR_EMULATE_PREPARES) === true, 'PDO emulate prepares must be true for parameter reuse');

// 4. Transactional updateNomorUrut test
assert(updateNomorUrut($pdo, 'invalid_table') === false, 'Invalid table name must fail gracefully');

$pdo->beginTransaction();
$stmt = $pdo->prepare("INSERT INTO disposisi_surat (no, kode, kategori_id, tanggal_surat, tanggal_masuk, nomer_surat, dari, perihal, instruksi, diteruskan, file_path) 
                       VALUES (999, '001', 1, '2026-01-01', '2026-01-01', 'TEST/MAINT', 'Test', 'Test', 'Test', 'Test', NULL)");
$stmt->execute();
$renumberResult = updateNomorUrut($pdo, 'disposisi_surat');
$pdo->rollBack();

assert($renumberResult === true, 'updateNomorUrut must succeed in transaction');

echo "[OK] All Phase 4 maintainability & code quality assertions passed successfully.\n";
