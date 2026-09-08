<?php
// delete_sk.php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/config.php';

// Check if user is logged in and has appropriate role
check_login();
if (!in_array($role, ['admin_dok', 'ti_admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get and validate input
$data = json_decode(file_get_contents('php://input'), true);
$sk_id = isset($data['id']) ? filter_var($data['id'], FILTER_VALIDATE_INT) : null;

if (!$sk_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid SK ID']);
    exit;
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Check if SK exists and get file path
    $stmt = $pdo->prepare("SELECT file_path FROM sk_table WHERE id = ?");
    $stmt->execute([$sk_id]);
    $sk = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sk) {
        throw new Exception('SK tidak ditemukan');
    }

    // Delete the file if it exists
    if ($sk['file_path'] && file_exists($sk['file_path'])) {
        unlink($sk['file_path']);
    }

    // Delete related records in other tables first (if any)
    $stmt = $pdo->prepare("DELETE FROM sk_table WHERE id = ?");
    $stmt->execute([$sk_id]);

    // Delete the SK record
    $stmt = $pdo->prepare("DELETE FROM sk_table WHERE id = ?");
    $stmt->execute([$sk_id]);

    // Commit transaction
    $pdo->commit();

    // Return success response
    echo json_encode(['success' => true, 'message' => 'SK berhasil dihapus']);
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus SK: ' . $e->getMessage()]);
}
