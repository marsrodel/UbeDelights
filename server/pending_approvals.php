<?php
session_start();

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

    if (empty($action) || empty($userId)) {
        throw new Exception('Invalid request');
    }

    // Convert userId to string to match database VARCHAR type
    $userId = (string) $userId;

    // Get target user details
    $stmt = $conn->prepare("SELECT username, role FROM users WHERE idNo = ? AND status = 'pending'");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $target_user = $user_result->fetch_assoc();

    if (!$target_user) {
        throw new Exception('Pending user not found');
    }

    $target_username = $target_user['username'];
    $target_role = $target_user['role'];

    // Admin cannot approve/reject super_admin (shouldn't happen for pending but safety check)
    if ($_SESSION['role'] === 'admin' && $target_role === 'super_admin') {
        throw new Exception('Admins cannot process Super Admin accounts');
    }

    switch ($action) {
        case 'approve':
            $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE idNo = ? AND status = 'pending'");
            $stmt->bind_param("s", $userId);
            if ($stmt->execute()) {
                logAction('APPROVE_USER', "User {$_SESSION['username']} approved pending user $target_username");
                $response = ['success' => true, 'message' => 'User approved successfully'];
            } else {
                throw new Exception('Failed to approve user');
            }
            break;

        case 'reject':
            $stmt = $conn->prepare("DELETE FROM users WHERE idNo = ? AND status = 'pending'");
            $stmt->bind_param("s", $userId);
            if ($stmt->execute()) {
                logAction('REJECT_USER', "User {$_SESSION['username']} rejected pending user $target_username");
                $response = ['success' => true, 'message' => 'User rejected successfully'];
            } else {
                throw new Exception('Failed to reject user');
            }
            break;

        case 'get_pending':
            // Get pending users with filtering and pagination
            $search = trim($_POST['search'] ?? '');
            $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
            $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
            $offset = ($page - 1) * $limit;

            $where_clauses = ["status = 'pending'"];
            $params = [];
            $types = "";

            if (!empty($search)) {
                $where_clauses[] = "(idNo LIKE ? OR username LIKE ? OR firstName LIKE ? OR lastName LIKE ? OR emailAddress LIKE ?)";
                $search_param = "%$search%";
                $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param, $search_param]);
                $types .= "sssss";
            }

            $where_sql = "WHERE " . implode(" AND ", $where_clauses);

            // Count total for pagination
            $count_query = "SELECT COUNT(*) as total FROM users $where_sql";
            $count_stmt = $conn->prepare($count_query);
            if (!empty($params)) {
                $count_stmt->bind_param($types, ...$params);
            }
            $count_stmt->execute();
            $total_pending = $count_stmt->get_result()->fetch_assoc()['total'];
            $total_pages = ceil($total_pending / $limit);

            // Fetch results
            $pending_query = "SELECT idNo, username, firstName, middleName, lastName, emailAddress FROM users $where_sql ORDER BY id DESC LIMIT ? OFFSET ?";
            $pending_stmt = $conn->prepare($pending_query);
            $pagination_params = array_merge($params, [$limit, $offset]);
            $pagination_types = $types . "ii";
            $pending_stmt->bind_param($pagination_types, ...$pagination_params);
            $pending_stmt->execute();
            $pending_result = $pending_stmt->get_result();

            $users = [];
            while ($user = $pending_result->fetch_assoc()) {
                $users[] = $user;
            }

            $response = [
                'success' => true,
                'users' => $users,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $total_pages,
                    'total_pending' => $total_pending,
                    'limit' => $limit
                ]
            ];
            break;

        case 'get_pending_user':
            // Get full pending user details for view modal
            if (empty($userId)) {
                throw new Exception('User ID is required');
            }
            
            $stmt = $conn->prepare("SELECT idNo, username, firstName, middleName, lastName, extension, birthday, age, sex, emailAddress, purok, barangay, municipality, province, country, zipCode, role, status FROM users WHERE idNo = ? AND status = 'pending'");
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                $response = [
                    'success' => true,
                    'user' => $user
                ];
            } else {
                throw new Exception('Pending user not found');
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
    $connect->close();
}