<?php
session_start();
include __DIR__ . '/db.php';
require_once __DIR__ . '/user_logger.php';

header('Content-Type: application/json');

if (!isset($_SESSION['auth_user_id']) || $_SESSION['auth_role'] !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$requestId = intval($_POST['request_id'] ?? 0);
$action = trim($_POST['action'] ?? '');

if ($requestId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid request ID.']);
    exit();
}

if (!in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit();
}

$escapedId = mysqli_real_escape_string($connect, $requestId);
$result = mysqli_query($connect, "SELECT dr.*, u.username AS target_username, u.role AS target_role
    FROM deletion_requests dr
    JOIN users u ON u.user_id = dr.target_id_number
    WHERE dr.id = '$escapedId' AND dr.status = 'pending'");

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Deletion request not found or already reviewed.']);
    exit();
}

$request = mysqli_fetch_assoc($result);
$reviewedBy = $_SESSION['auth_user_id'];
$reviewedAt = date('Y-m-d H:i:s');
$newStatus = $action === 'approve' ? 'approved' : 'rejected';

$update = mysqli_query($connect, "UPDATE deletion_requests
    SET status = '$newStatus', reviewed_by = '$reviewedBy', reviewed_at = '$reviewedAt'
    WHERE id = '$escapedId'");

if (!$update) {
    echo json_encode(['success' => false, 'message' => 'Failed to update deletion request.']);
    exit();
}

if ($action === 'approve') {
    $targetId = mysqli_real_escape_string($connect, $request['target_id_number']);

    mysqli_query($connect, "DELETE FROM order_items WHERE order_id IN (SELECT order_id FROM orders WHERE user_id = '$targetId')");
    mysqli_query($connect, "DELETE FROM orders WHERE user_id = '$targetId'");
    mysqli_query($connect, "DELETE FROM password_reset_otp WHERE idNumber = '$targetId'");
    mysqli_query($connect, "DELETE FROM login_otp WHERE idNumber = '$targetId'");
    mysqli_query($connect, "DELETE FROM admin_privileges WHERE idNumber = '$targetId'");
    mysqli_query($connect, "UPDATE deletion_requests SET target_id_number = NULL WHERE target_id_number = '$targetId'");
    mysqli_query($connect, "DELETE FROM users WHERE user_id = '$targetId'");
}

$logMessage = "{$_SESSION['auth_username']} {$newStatus}d deletion request for {$request['target_username']} (ID: {$request['target_id_number']})";
log_activity('DELETION_REVIEW', $logMessage, 'Deletion Requests', $_SESSION['auth_user_id'], $_SESSION['auth_username']);

$label = $action === 'approve' ? 'approved' : 'rejected';
$msg = $action === 'approve' ? "Deletion request approved. The account has been deleted." : "Deletion request rejected. The account has been dismissed.";
echo json_encode(['success' => true, 'message' => $msg]);

mysqli_close($connect);
?>
