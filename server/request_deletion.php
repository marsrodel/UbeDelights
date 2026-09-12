<?php
session_start();
include __DIR__ . '/db.php';
require_once __DIR__ . '/user_logger.php';

header('Content-Type: application/json');

if (!isset($_SESSION['auth_user_id']) || !in_array($_SESSION['auth_role'], ['admin', 'super_admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$userId = $_SESSION['auth_user_id'];
$role = $_SESSION['auth_role'];
$targetId = trim($_POST['user_id'] ?? '');
$reason = trim($_POST['reason'] ?? '');

if ($targetId === '') {
    echo json_encode(['success' => false, 'message' => 'Invalid user.']);
    exit();
}

if ($role === 'admin') {
    $r = mysqli_query($connect, "SELECT can_request_deletion FROM admin_privileges WHERE idNumber = '" . mysqli_real_escape_string($connect, $userId) . "'");
    $priv = $r ? mysqli_fetch_assoc($r) : null;
    if (!$priv || !$priv['can_request_deletion']) {
        echo json_encode(['success' => false, 'message' => 'You are not authorized to request account deletion.']);
        exit();
    }
}

if ($reason === '') {
    echo json_encode(['success' => false, 'message' => 'A reason is required.']);
    exit();
}

if ($targetId === $userId) {
    echo json_encode(['success' => false, 'message' => 'You cannot request deletion of your own account.']);
    exit();
}

$escapedTarget = mysqli_real_escape_string($connect, $targetId);
$result = mysqli_query($connect, "SELECT user_id, username, role FROM users WHERE user_id = '$escapedTarget'");

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit();
}

$target = mysqli_fetch_assoc($result);

if ($role === 'admin' && $target['role'] === 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'Admins cannot request deletion of Super Admin accounts.']);
    exit();
}

$checkPending = mysqli_query($connect, "SELECT id FROM deletion_requests WHERE target_id_number = '$escapedTarget' AND status = 'pending'");
if ($checkPending && mysqli_num_rows($checkPending) > 0) {
    echo json_encode(['success' => false, 'message' => 'A pending deletion request already exists for this user.']);
    exit();
}

$escapedRequestedBy = mysqli_real_escape_string($connect, $userId);
$escapedReason = mysqli_real_escape_string($connect, $reason);

$insert = mysqli_query($connect, "INSERT INTO deletion_requests (target_id_number, requested_by, reason) VALUES ('$escapedTarget', '$escapedRequestedBy', '$escapedReason')");

if ($insert) {
    log_activity('DELETION_REQUEST', "{$_SESSION['auth_username']} requested deletion of {$target['username']} (ID: $targetId)", 'User Management', $_SESSION['auth_user_id'], $_SESSION['auth_username']);
    echo json_encode(['success' => true, 'message' => 'Deletion request submitted for super admin review.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit deletion request.']);
}

mysqli_close($connect);
?>