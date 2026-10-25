<?php
session_start();
include __DIR__ . '/db.php';
require_once __DIR__ . '/user_logger.php';
require_once __DIR__ . '/session_guard.php';
require_once __DIR__ . '/account_deletion.php';

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

        $requestStmt = mysqli_prepare($connect, "SELECT target_id_number, status, requested_by, reason, created_at FROM deletion_requests WHERE id = ? FOR UPDATE");
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
        $requesterId = (string)$requestState['requested_by'];
        $requesterUsername = $requesterId;
        $requesterNames = log_deletion_requester_names($connect, [$requestId => $requesterId]);
        $requesterStmt = mysqli_prepare($connect, "SELECT username FROM users WHERE user_id = ? LIMIT 1");
        if ($requesterStmt) {
            $requesterStmt->bind_param('s', $requesterId);
            if ($requesterStmt->execute()) {
                $requesterRow = $requesterStmt->get_result()->fetch_assoc();
                if ($requesterRow && !empty($requesterRow['username'])) $requesterUsername = $requesterRow['username'];
            }
            $requesterStmt->close();
        }
        $logMessage = log_deletion_event_details($logMessage, [
            'kind' => 'deletion_review',
            'request_id' => $requestId,
            'outcome' => 'rejected',
            'requested_by' => $requesterUsername,
            'requested_by_id' => $requesterId,
            'requested_by_full_name' => $requesterNames[$requestId] ?? null,
            'requested_on' => $requestState['created_at'],
            'reason' => $requestState['reason'],
        ]);
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

try {
    if (!mysqli_begin_transaction($connect)) {
        throw new RuntimeException('Could not start the approval transaction.');
    }

    $requestStmt = $connect->prepare('SELECT target_id_number, requested_by, reason, status, created_at FROM deletion_requests WHERE id = ? FOR UPDATE');
    if (!$requestStmt) throw new RuntimeException('Could not load the deletion request.');
    $requestStmt->bind_param('i', $requestId);
    $requestStmt->execute();
    $request = $requestStmt->get_result()->fetch_assoc();
    $requestStmt->close();

    if (!$request) {
        mysqli_rollback($connect);
        http_response_code(404);
        echo json_encode(['success' => false, 'code' => 'request_not_found', 'message' => 'Deletion request was not found.']);
        exit();
    }
    if ($request['status'] !== 'pending') {
        mysqli_rollback($connect);
        http_response_code(409);
        echo json_encode(['success' => false, 'code' => 'request_already_reviewed', 'message' => 'This deletion request has already been reviewed. Refresh the page and try again.']);
        exit();
    }

    $targetId = (string)$request['target_id_number'];
    $targetStmt = $connect->prepare('SELECT username FROM users WHERE user_id = ? FOR UPDATE');
    if (!$targetStmt) throw new RuntimeException('Could not load the target account.');
    $targetStmt->bind_param('s', $targetId);
    $targetStmt->execute();
    $target = $targetStmt->get_result()->fetch_assoc();
    $targetStmt->close();
    if (!$target) {
        mysqli_rollback($connect);
        http_response_code(404);
        echo json_encode(['success' => false, 'code' => 'target_not_found', 'message' => 'The target account could not be found; the request remains pending.']);
        exit();
    }

    $reviewedBy = (string)$_SESSION['auth_user_id'];
    $actorUsername = (string)($_SESSION['auth_username'] ?? 'Super Admin');
    $requesterId = (string)$request['requested_by'];
    $requesterNames = log_deletion_requester_names($connect, [$requestId => $requesterId]);
    $requesterUsername = $requesterId;
    $requesterStmt = $connect->prepare('SELECT username FROM users WHERE user_id = ? LIMIT 1');
    if ($requesterStmt) {
        $requesterStmt->bind_param('s', $requesterId);
        $requesterStmt->execute();
        $requesterRow = $requesterStmt->get_result()->fetch_assoc();
        if (!empty($requesterRow['username'])) $requesterUsername = $requesterRow['username'];
        $requesterStmt->close();
    }

    $updateStmt = $connect->prepare("UPDATE deletion_requests SET status = 'approved', reviewed_by = ?, reviewed_at = NOW() WHERE id = ? AND status = 'pending'");
    if (!$updateStmt) throw new RuntimeException('Could not prepare the approval update.');
    $updateStmt->bind_param('si', $reviewedBy, $requestId);
    $updated = $updateStmt->execute();
    $updatedRows = $updateStmt->affected_rows;
    $updateStmt->close();
    if (!$updated || $updatedRows !== 1) {
        throw new RuntimeException('Could not update the deletion request.');
    }

    account_delete_with_history($connect, $targetId);

    $logMessage = "$actorUsername approved deletion request for {$target['username']} (ID: $targetId)";
    $logMessage = log_deletion_event_details($logMessage, [
        'kind' => 'deletion_review',
        'request_id' => $requestId,
        'outcome' => 'approved',
        'requested_by' => $requesterUsername,
        'requested_by_id' => $requesterId,
        'requested_by_full_name' => $requesterNames[$requestId] ?? null,
        'requested_on' => $request['created_at'],
        'reason' => $request['reason'],
    ]);
    if (!log_activity('DELETION_REVIEW', $logMessage, 'Deletion Requests', $reviewedBy, $actorUsername)) {
        throw new RuntimeException('Could not record the approval activity log.');
    }
    if (!mysqli_commit($connect)) {
        throw new RuntimeException('Could not save the approval.');
    }

    echo json_encode(['success' => true, 'message' => 'Deletion request approved. The account has been deleted. Order history remains available.']);
} catch (Throwable $e) {
    mysqli_rollback($connect);
    error_log('Deletion request approval failed: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'code' => 'approval_failed', 'message' => 'The deletion request could not be approved. The account remains unchanged; please try again.']);
}

mysqli_close($connect);
