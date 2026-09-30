<?php
require_once __DIR__ . '/../includes/config.php';

echo "Aligning SK & SOP tables and adding performance indexes...\n";

function addIndexIfNotExists($pdo, $table, $indexName, $indexCols)
{
    $stmt = $pdo->prepare("SHOW INDEX FROM `$table` WHERE Key_name = ?");
    $stmt->execute([$indexName]);
    if (!$stmt->fetch()) {
        echo "Creating index $indexName on $table...\n";
        $pdo->exec("ALTER TABLE `$table` ADD INDEX `$indexName` ($indexCols)");
    } else {
        echo "Index $indexName already exists on $table.\n";
    }
}

try {
    // 1. Align sk_table
    $stmt = $pdo->query("SHOW COLUMNS FROM sk_table LIKE 'tahun_disahkan'");
    if (!$stmt->fetch()) {
        echo "Adding 'tahun_disahkan' to sk_table...\n";
        $pdo->exec("ALTER TABLE sk_table ADD COLUMN tahun_disahkan VARCHAR(10) NULL AFTER judul_sk");
    }
    $pdo->exec("UPDATE sk_table SET tahun_disahkan = YEAR(tanggal_ditetapkan) WHERE (tahun_disahkan IS NULL OR tahun_disahkan = '') AND tanggal_ditetapkan IS NOT NULL");
    $pdo->exec("ALTER TABLE sk_table MODIFY COLUMN kategori VARCHAR(255) NULL DEFAULT 'Lain-lain', MODIFY COLUMN tanggal_ditetapkan DATE NULL");

    // 2. Align sop_table
    $stmt = $pdo->query("SHOW COLUMNS FROM sop_table LIKE 'tahun_disahkan'");
    if (!$stmt->fetch()) {
        echo "Adding 'tahun_disahkan' to sop_table...\n";
        $pdo->exec("ALTER TABLE sop_table ADD COLUMN tahun_disahkan VARCHAR(10) NULL AFTER judul_sop");
    }
    $pdo->exec("UPDATE sop_table SET tahun_disahkan = YEAR(tanggal_ditetapkan) WHERE (tahun_disahkan IS NULL OR tahun_disahkan = '') AND tanggal_ditetapkan IS NOT NULL");
    $pdo->exec("ALTER TABLE sop_table MODIFY COLUMN kategori VARCHAR(255) NULL DEFAULT 'Lain-lain', MODIFY COLUMN tanggal_ditetapkan DATE NULL");

    // 3. Add Indexes
    addIndexIfNotExists($pdo, 'disposisi_surat', 'idx_disp_tgl_kode', '`tanggal_masuk`, `kode`');
    addIndexIfNotExists($pdo, 'disposisi_surat', 'idx_disp_no', '`no`');

    addIndexIfNotExists($pdo, 'disposisi_keluar', 'idx_disp_keluar_tgl_kode', '`tanggal`, `kode`');
    addIndexIfNotExists($pdo, 'disposisi_keluar', 'idx_disp_keluar_no', '`no`');

    addIndexIfNotExists($pdo, 'sk_table', 'idx_sk_tahun_nomor', '`tahun_disahkan`, `nomor_sk`');
    addIndexIfNotExists($pdo, 'sk_table', 'idx_sk_judul', '`judul_sk`(100)');

    addIndexIfNotExists($pdo, 'sop_table', 'idx_sop_tahun_nomor', '`tahun_disahkan`, `nomor_sop`');
    addIndexIfNotExists($pdo, 'sop_table', 'idx_sop_judul', '`judul_sop`(100)');

    echo "[OK] All schema alignments and performance indexes created successfully.\n";
} catch (Exception $e) {
    echo "[ERROR] Failed: " . $e->getMessage() . "\n";
    exit(1);
}
