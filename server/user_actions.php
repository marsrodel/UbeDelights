<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

require_once 'db.php';
require_once 'user_logger.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $action = $_POST['action'] ?? '';
    $userId = $_POST['user_id'] ?? '';
    $currentUserId = $_SESSION['user_id'] ?? '';
    $currentRole = $_SESSION['role'] ?? '';

    if (empty($action) || empty($userId)) {
        throw new Exception('Invalid request');
    }

    // Prevent users from performing destructive actions on themselves
    $destructive_actions = ['block', 'unblock', 'delete', 'approve', 'reject'];
    if ($userId == $_SESSION['user_id'] && in_array($action, $destructive_actions)) {
        throw new Exception('You cannot perform this action on your own account');
    }

    // Convert userId to string if it's numeric to match database VARCHAR type
    $userId = (string) $userId;

    // Get target user details
    $stmt = $conn->prepare("SELECT username, role FROM users WHERE idNo = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $target_user = $user_result->fetch_assoc();

    if (!$target_user) {
        throw new Exception('User not found');
    }

    $target_username = $target_user['username'];
    $target_role = $target_user['role'];

    // Admin cannot modify super_admin
    if ($_SESSION['role'] === 'admin' && $target_role === 'super_admin') {
        throw new Exception('Admins cannot modify Super Admin accounts');
    }

    switch ($action) {
        case 'block':
            if ($currentRole === 'admin' && $target_role === 'super_admin') {
                throw new Exception('Admins cannot block Super Admin accounts');
            }

            $stmt = $conn->prepare("UPDATE users SET status = 'blocked' WHERE idNo = ?");
            $stmt->bind_param("s", $userId);
            if ($stmt->execute()) {
                logAction('BLOCK_USER', "User {$_SESSION['username']} blocked user $target_username");
                $response = ['success' => true, 'message' => 'User blocked successfully'];
            } else {
                throw new Exception('Failed to block user');
            }
            break;

        case 'unblock':
            if ($currentRole === 'admin' && $target_role === 'super_admin') {
                throw new Exception('Admins cannot unblock Super Admin accounts');
            }

            $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE idNo = ?");
            $stmt->bind_param("s", $userId);
            if ($stmt->execute()) {
                logAction('UNBLOCK_USER', "User {$_SESSION['username']} unblocked user $target_username");
                $response = ['success' => true, 'message' => 'User unblocked successfully'];
            } else {
                throw new Exception('Failed to unblock user');
            }
            break;

        case 'approve':
            $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE idNo = ?");
            $stmt->bind_param("s", $userId);
            if ($stmt->execute()) {
                logAction('APPROVE_USER', "User {$_SESSION['username']} approved user ID $userId");
                $response = ['success' => true, 'message' => 'User approved successfully'];
            } else {
                throw new Exception('Failed to approve user');
            }
            break;

        case 'reject':
            $stmt = $conn->prepare("DELETE FROM users WHERE idNo = ?");
            $stmt->bind_param("s", $userId);
            if ($stmt->execute()) {
                logAction('REJECT_USER', "User {$_SESSION['username']} rejected user ID $userId");
                $response = ['success' => true, 'message' => 'User rejected successfully'];
            } else {
                throw new Exception('Failed to reject user');
            }
            break;

        case 'delete':
            // Only super_admin can delete
            if ($_SESSION['role'] !== 'super_admin') {
                throw new Exception('Only super admins can delete user accounts');
            }

            // Super admin cannot delete their own account
            if ($userId === $_SESSION['user_id']) {
                throw new Exception('You cannot delete your own account');
            }

            // Get user details for logging
            $stmt = $conn->prepare("SELECT username FROM users WHERE idNo = ?");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $user_result = $stmt->get_result();
            $target_user = $user_result->fetch_assoc();

            if (!$target_user) {
                throw new Exception('User not found');
            }

            // Super admin cannot delete their own account
            if ($userId === $_SESSION['user_id']) {
                throw new Exception('You cannot delete your own account');
            }

            // Delete the user
            $del_stmt = $conn->prepare("DELETE FROM users WHERE idNo = ?");
            $del_stmt->bind_param("s", $userId);
            if ($del_stmt->execute()) {
                logAction('DELETE_USER', "User {$_SESSION['username']} deleted user $target_username (ID: $userId)");
                $response = ['success' => true, 'message' => "User $target_username has been deleted successfully"];
            } else {
                throw new Exception('Failed to delete user');
            }
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}