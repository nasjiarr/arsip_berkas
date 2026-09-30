<?php
// ponytail: CSRF-guarded POST deletion for disposisi keluar.
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

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

    if ($stmt->execute()) {
        if ($result && !empty($result['file_path']) && file_exists($result['file_path'])) {
            @unlink($result['file_path']);
        }

        $_SESSION['success'] = "Data berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus data.";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Terjadi kesalahan saat menghapus data.";
}

header("Location: disposisi_keluar.php");
exit();
