<?php
session_start();
include __DIR__ . '/db.php';
require_once __DIR__ . '/user_logger.php';
require_once __DIR__ . '/session_guard.php';

header('Content-Type: application/json');
session_guard_api_timeout();

if (!isset($_SESSION['auth_user_id']) || $_SESSION['auth_role'] !== 'super_admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied.']);
    exit();
}

session_guard_touch();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$requestId = intval($_POST['request_id'] ?? 0);
$action = trim($_POST['action'] ?? '');

if ($requestId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request ID.']);
    exit();
}

if (!in_array($action, ['approve', 'reject'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit();
}

if ($action === 'reject') {
    $failureMessage = 'The deletion request could not be rejected. It remains pending; please try again.';
    try {
        if (!mysqli_begin_transaction($connect)) {
            throw new RuntimeException('Could not start the review transaction.');
        }

        $requestStmt = mysqli_prepare($connect, "SELECT target_id_number, status FROM deletion_requests WHERE id = ? FOR UPDATE");
        if (!$requestStmt) throw new RuntimeException('Could not load the deletion request.');
        mysqli_stmt_bind_param($requestStmt, 'i', $requestId);
        if (!mysqli_stmt_execute($requestStmt)) {
            mysqli_stmt_close($requestStmt);
            throw new RuntimeException('Could not load the deletion request.');
        }
        $requestResult = mysqli_stmt_get_result($requestStmt);
        $requestState = $requestResult ? mysqli_fetch_assoc($requestResult) : null;
        mysqli_stmt_close($requestStmt);

        if (!$requestState) {
            mysqli_rollback($connect);
            http_response_code(404);
            echo json_encode(['success' => false, 'code' => 'request_not_found', 'message' => 'Deletion request was not found.']);
            exit();
        }
        if ($requestState['status'] !== 'pending') {
            mysqli_rollback($connect);
            http_response_code(409);
            echo json_encode(['success' => false, 'code' => 'request_already_reviewed', 'message' => 'This deletion request has already been reviewed. Refresh the page and try again.']);
            exit();
        }

        $targetId = (string)$requestState['target_id_number'];
        $targetStmt = mysqli_prepare($connect, "SELECT username FROM users WHERE user_id = ? LIMIT 1");
        if (!$targetStmt) throw new RuntimeException('Could not load the target account.');
        mysqli_stmt_bind_param($targetStmt, 's', $targetId);
        if (!mysqli_stmt_execute($targetStmt)) {
            mysqli_stmt_close($targetStmt);
            throw new RuntimeException('Could not load the target account.');
        }
        $targetResult = mysqli_stmt_get_result($targetStmt);
        $target = $targetResult ? mysqli_fetch_assoc($targetResult) : null;
        mysqli_stmt_close($targetStmt);
        if (!$target) {
            mysqli_rollback($connect);
            http_response_code(404);
            echo json_encode(['success' => false, 'code' => 'target_not_found', 'message' => 'The target account could not be found; the request remains pending.']);
            exit();
        }

        $reviewedBy = (string)$_SESSION['auth_user_id'];
        $updateStmt = mysqli_prepare($connect, "UPDATE deletion_requests SET status = 'rejected', reviewed_by = ?, reviewed_at = NOW() WHERE id = ? AND status = 'pending'");
        if (!$updateStmt) throw new RuntimeException('Could not prepare the rejection update.');
        mysqli_stmt_bind_param($updateStmt, 'si', $reviewedBy, $requestId);
        $updated = mysqli_stmt_execute($updateStmt);
        $updatedRows = mysqli_stmt_affected_rows($updateStmt);
        mysqli_stmt_close($updateStmt);
        if (!$updated) throw new RuntimeException('Could not update the deletion request.');
        if ($updatedRows !== 1) {
            mysqli_rollback($connect);
            http_response_code(409);
            echo json_encode(['success' => false, 'code' => 'request_already_reviewed', 'message' => 'This deletion request could not be rejected because it has already changed. Refresh the page and try again.']);
            exit();
        }

        $actorUsername = (string)($_SESSION['auth_username'] ?? 'Super Admin');
        $logMessage = "$actorUsername rejected deletion request for {$target['username']} (ID: $targetId)";
        if (!log_activity('DELETION_REVIEW', $logMessage, 'Deletion Requests', $reviewedBy, $actorUsername)) {
            $failureMessage = 'Could not record the activity log. The request remains pending; please try again.';
            throw new RuntimeException('Activity log write failed.');
        }

        if (!mysqli_commit($connect)) {
            throw new RuntimeException('Could not save the rejection. Please try again.');
        }

        echo json_encode(['success' => true, 'message' => 'Deletion request rejected. The target account remains active.']);
        mysqli_close($connect);
        exit();
    } catch (Throwable $e) {
        if ($connect instanceof mysqli) mysqli_rollback($connect);
        error_log('Deletion request rejection failed: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'code' => 'rejection_failed', 'message' => $failureMessage]);
        exit();
    }
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
    mysqli_query($connect, "DELETE FROM otp_requests WHERE idNumber = '$targetId'");
    mysqli_query($connect, "DELETE FROM login_otp WHERE idNumber = '$targetId'");
    mysqli_query($connect, "DELETE FROM admin_privileges WHERE idNumber = '$targetId'");
    mysqli_query($connect, "UPDATE deletion_requests SET target_id_number = NULL WHERE target_id_number = '$targetId'");
    mysqli_query($connect, "DELETE FROM users WHERE user_id = '$targetId'");
}

$logMessage = "{$_SESSION['auth_username']} {$newStatus} deletion request for {$request['target_username']} (ID: {$request['target_id_number']})";
log_activity('DELETION_REVIEW', $logMessage, 'Deletion Requests', $_SESSION['auth_user_id'], $_SESSION['auth_username']);

$label = $action === 'approve' ? 'approved' : 'rejected';
$msg = $action === 'approve' ? "Deletion request approved. The account has been deleted." : "Deletion request rejected. The account has been dismissed.";
echo json_encode(['success' => true, 'message' => $msg]);

mysqli_close($connect);
?>
