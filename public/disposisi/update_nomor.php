<?php
// ponytail: transactional sequential renumbering; upgrade to window functions if MySQL >= 8.0 CTE update preferred.
function updateNomorUrut($pdo, $table = 'disposisi_surat')
{
    $allowed_tables = [
        'disposisi_surat'  => 'tanggal_masuk',
        'disposisi_keluar' => 'tanggal'
    ];

    if (!isset($allowed_tables[$table])) {
        return false;
    }
    $col = $allowed_tables[$table];

    $has_tx = $pdo->inTransaction();
    try {
        if (!$has_tx) {
            $pdo->beginTransaction();
        }
        $stmt = $pdo->query("SELECT id FROM `$table` ORDER BY `$col` ASC, id ASC");
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $update_stmt = $pdo->prepare("UPDATE `$table` SET no = :no WHERE id = :id");
        $no = 1;
        foreach ($ids as $id) {
            $update_stmt->execute([':no' => $no++, ':id' => $id]);
        }

        if (!$has_tx) {
            $pdo->commit();
        }
        return true;
    } catch (Exception $e) {
        if (!$has_tx && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("[ERROR] updateNomorUrut failed for table $table: " . $e->getMessage());
        return false;
    }
}
