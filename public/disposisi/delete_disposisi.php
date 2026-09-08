<?php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';

// Check if user is logged in
check_login();

// Define network path
define('NETWORK_PDF_PATH', '\\\\172.16.34.5\\ftp\\DISPOSISI SURAT\\');

// Verify user role - only sekre and ti_admin can delete
$allowed_roles = ['sekre', 'ti_admin'];
if (!in_array($_SESSION['user']['role'], $allowed_roles)) {
    $_SESSION['error'] = "Anda tidak memiliki akses untuk menghapus data.";
    header("Location: disposisi.php");
    exit();
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "ID tidak valid.";
    header("Location: disposisi.php");
    exit();
}

try {
    // Get the file path before deleting the record
    $query = "SELECT file_path FROM disposisi_surat WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Delete the record from database
    $delete_query = "DELETE FROM disposisi_surat WHERE id = :id";
    $stmt = $pdo->prepare($delete_query);
    $stmt->bindParam(':id', $_GET['id'], PDO::PARAM_INT);

    if ($stmt->execute()) {
        // If deletion is successful and there was a file, attempt to delete it
        if ($result && !empty($result['file_path'])) {
            // Check if file is in network path or local storage
            if (strpos($result['file_path'], 'DISPOSISI SURAT') !== false) {
                // File is in network path
                if (file_exists($result['file_path'])) {
                    if (!@unlink($result['file_path'])) {
                        error_log("Failed to delete network file: " . $result['file_path']);
                        // Don't show error to user, just log it
                    }
                }
            } else {
                // File is in local storage
                $local_path = UPLOAD_DIR . str_replace(UPLOAD_URL, '', $result['file_path']);
                if (file_exists($local_path)) {
                    if (!@unlink($local_path)) {
                        error_log("Failed to delete local file: " . $local_path);
                        // Don't show error to user, just log it
                    }
                }
            }
        }
        $_SESSION['success'] = "Data berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus data.";
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
} catch (Exception $e) {
    $_SESSION['error'] = "Terjadi kesalahan saat menghapus file: " . $e->getMessage();
}

// Redirect back to the disposisi page
header("Location: disposisi.php");
exit();
