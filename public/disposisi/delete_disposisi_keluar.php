<?php
// ponytail: CSRF-guarded POST deletion for disposisi keluar.
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once 'update_nomor.php';

check_login();

// Verify user role - only sekre and ti_admin can delete
$allowed_roles = ['sekre', 'ti_admin'];
if (!in_array($_SESSION['user']['role'] ?? '', $allowed_roles)) {
    $_SESSION['error'] = "Anda tidak memiliki akses untuk menghapus data.";
    header("Location: disposisi_keluar.php");
    exit();
}

// Require POST and valid CSRF token
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $_SESSION['error'] = "Aksi tidak valid atau token kedaluwarsa.";
    header("Location: disposisi_keluar.php");
    exit();
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    $_SESSION['error'] = "ID tidak valid.";
    header("Location: disposisi_keluar.php");
    exit();
}

try {
    $pdo->beginTransaction();

    // Get the file path before deleting the record
    $query = "SELECT file_path FROM disposisi_keluar WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Delete the record from database
    $delete_query = "DELETE FROM disposisi_keluar WHERE id = :id";
    $stmt = $pdo->prepare($delete_query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    // Re-sequence agenda numbers sequentially without gaps
    updateNomorUrut($pdo, 'disposisi_keluar');

    $pdo->commit();

    if ($result && !empty($result['file_path']) && file_exists($result['file_path'])) {
        @unlink($result['file_path']);
    }

    set_flash_message('success', 'Data disposisi keluar berhasil dihapus.');
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash_message('danger', 'Terjadi kesalahan saat menghapus data.');
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash_message('danger', 'Terjadi kesalahan saat menghapus file.');
}

header("Location: disposisi_keluar.php");
exit();
