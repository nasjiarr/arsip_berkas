<?php
// delete_sop.php
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
$sop_id = isset($data['id']) ? filter_var($data['id'], FILTER_VALIDATE_INT) : null;

if (!$sop_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid SOP ID']);
    exit;
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Check if SOP exists and get file path
    $stmt = $pdo->prepare("SELECT file_path FROM sop_table WHERE id = ?");
    $stmt->execute([$sop_id]);
    $sop = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sop) {
        throw new Exception('SOP tidak ditemukan');
    }

    // Delete the file if it exists
    if ($sop['file_path'] && file_exists($sop['file_path'])) {
        unlink($sop['file_path']);
    }

    // Delete related records in other tables first (if any)
    $stmt = $pdo->prepare("DELETE FROM sop_table WHERE id = ?");
    $stmt->execute([$sop_id]);

    // Delete the SOP record
    $stmt = $pdo->prepare("DELETE FROM sop_table WHERE id = ?");
    $stmt->execute([$sop_id]);

    // Commit transaction
    $pdo->commit();

    // Return success response
    echo json_encode(['success' => true, 'message' => 'SOP berhasil dihapus']);
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus SOP: ' . $e->getMessage()]);
}
