<?php
function updateNomorUrut($pdo)
{
    try {
        // Ambil semua ID secara berurutan
        $query = "SELECT id FROM disposisi_surat ORDER BY tanggal_masuk ASC, id ASC";
        $stmt = $pdo->query($query);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $no = 1;
        foreach ($rows as $row) {
            $update_query = "UPDATE disposisi_surat SET no = :no WHERE id = :id";
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->bindParam(':no', $no, PDO::PARAM_INT);
            $update_stmt->bindParam(':id', $row['id'], PDO::PARAM_INT);
            $update_stmt->execute();
            $no++;
        }

        return true;
    } catch (Exception $e) {
        return false;
    }
}
