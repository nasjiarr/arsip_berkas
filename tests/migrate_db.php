<?php
require_once __DIR__ . '/../includes/config.php';

try {
    echo "Running Phase 2 Database Schema Migration...\n";

    // 1. Check if 'instruksi' column exists in disposisi_surat
    $stmt = $pdo->query("SHOW COLUMNS FROM disposisi_surat LIKE 'instruksi'");
    if (!$stmt->fetch()) {
        echo "Adding 'instruksi' column to disposisi_surat...\n";
        $pdo->exec("ALTER TABLE disposisi_surat ADD COLUMN instruksi TEXT NULL AFTER perihal");
    }

    // 2. Populate instruksi from existing instruksi_direksi_* if empty
    $pdo->exec("UPDATE disposisi_surat 
                SET instruksi = CONCAT_WS(' | ', 
                    NULLIF(TRIM(instruksi_direksi_umum), ''), 
                    NULLIF(TRIM(instruksi_direksi_bisnis), ''), 
                    NULLIF(TRIM(instruksi_direksi_kepatuhan), '')
                ) 
                WHERE (instruksi IS NULL OR instruksi = '') 
                AND (instruksi_direksi_umum != '' OR instruksi_direksi_bisnis != '' OR instruksi_direksi_kepatuhan != '')");

    // 3. Make kategori_id nullable or default 23 (LAIN-LAIN)
    echo "Adjusting kategori_id and direksi columns in disposisi_surat...\n";
    $pdo->exec("ALTER TABLE disposisi_surat 
                MODIFY COLUMN kategori_id INT NULL DEFAULT 23,
                MODIFY COLUMN instruksi_direksi_bisnis TEXT NULL,
                MODIFY COLUMN instruksi_direksi_kepatuhan TEXT NULL");

    // 4. Adjust kategori_id in disposisi_keluar
    echo "Adjusting kategori_id in disposisi_keluar...\n";
    $pdo->exec("ALTER TABLE disposisi_keluar MODIFY COLUMN kategori_id INT NULL DEFAULT 23");

    echo "[OK] Schema migration completed successfully.\n";
} catch (Exception $e) {
    echo "[ERROR] Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
