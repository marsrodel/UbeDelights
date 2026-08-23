<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin', 'customer'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

require_once 'db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    $action = $_POST['action'] ?? '';
    $currentRole = $_SESSION['role'] ?? '';
    $currentUserId = $_SESSION['user_id'] ?? '';
    $currentUsername = $_SESSION['username'] ?? '';

    if (empty($action)) {
        throw new Exception('Invalid action');
    }

    // Pagination and filtering
    $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
    $offset = ($page - 1) * $limit;

    $action_filter = $_POST['action_filter'] ?? '';
    $user_filter = $_POST['user_filter'] ?? '';
    $role_filter = $_POST['role_filter'] ?? '';
    $from_date = $_POST['from_date'] ?? '';
    $to_date = $_POST['to_date'] ?? '';
    $search = $_POST['search'] ?? '';

    require_once 'db.php';

    $where_conditions = [];
    $params = [];
    $types = '';

    // Role-based filtering
    if ($currentRole === 'super_admin') {
        // Super admin sees all logs
        $where_conditions[] = '1=1';
    } elseif ($currentRole === 'admin') {
        // Admin sees admin and customer logs only
        $where_conditions[] = "(ul.action NOT IN ('LOGIN', 'LOGOUT') OR u.role IN ('admin', 'customer'))";
        // More precise: admin sees admin and customer logs
        // We'll filter in the query by joining with users table
    } else {
        // Customer sees only their own logs
        $where_conditions[] = "ul.user_name = ?";
        $params[] = $_SESSION['username'];
        $types .= 's';
    }

    if (!empty($action_filter)) {
        $where_conditions[] = "ul.action = ?";
        $params[] = $action_filter;
        $types .= 's';
    }

    if (!empty($user_filter)) {
        $where_conditions[] = "ul.user_name LIKE ?";
        $params[] = '%' . $user_filter . '%';
        $types .= 's';
    }

    if (!empty($search)) {
        $where_conditions[] = "(ul.user_name LIKE ? OR ul.description LIKE ?)";
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
        $types .= 'ss';
    }

    if (!empty($from_date)) {
        $where_conditions[] = "DATE(ul.created_at) >= ?";
        $params[] = $from_date;
        $types .= 's';
    }

    if (!empty($to_date)) {
        $where_conditions[] = "DATE(ul.created_at) <= ?";
        $params[] = $to_date;
        $types .= 's';
    }

    if (!empty($role_filter)) {
        $where_conditions[] = "u.role = ?";
        $params[] = $role_filter;
        $types .= 's';
    }

    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

    // Role-based query building
    $join_clause = "LEFT JOIN users u ON ul.user_name = u.username";
    
    if ($currentRole === 'admin') {
        // Admin: exclude super_admin logs, show only admin and customer
        if ($where_clause) {
            $where_clause .= " AND u.role IN ('admin', 'customer')";
        } else {
            $where_clause = "WHERE u.role IN ('admin', 'customer')";
        }
    } elseif ($currentRole === 'customer') {
        // Customer: only their own logs (already handled above)
    }
    // super_admin sees all (no additional filter)

    // Get total count for pagination
    $count_query = "SELECT COUNT(*) as total FROM user_logs ul $join_clause $where_clause";
    $count_stmt = $connect->prepare($count_query);
    if (!empty($params)) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $total_logs = $count_stmt->get_result()->fetch_assoc()['total'];
    $total_pages = ceil($total_logs / $limit);

    // Fetch logs with pagination
    $logs_query = "
        SELECT 
            ul.id, ul.user_name, ul.action, ul.description, ul.ip_address, 
            ul.device, ul.browser, ul.created_at, 
            u.idNo, u.role as user_role
        FROM user_logs ul 
        $join_clause
        $where_clause
        ORDER BY ul.created_at DESC 
        LIMIT ? OFFSET ?
    ";
    $logs_stmt = $conn->prepare($logs_query);
    
    $pagination_params = array_merge($params, [$limit, $offset]);
    $pagination_types = $types . 'ii';
    
    if (!empty($pagination_params)) {
        $logs_stmt->bind_param($pagination_types, ...$pagination_params);
    }
    $logs_stmt->execute();
    $logs_result = $logs_stmt->get_result();

    $logs = [];
    while ($log = $logs_result->fetch_assoc()) {
        $logs[] = $log;
    }

    // Get unique actions for filter dropdown
    $actions_query = "SELECT DISTINCT action FROM user_logs ORDER BY action";
    $actions_result = $connect->query($actions_query);
    $actions = [];
    while ($row = $actions_result->fetch_assoc()) {
        $actions[] = $row['action'];
    }

    $response = [
        'success' => true,
        'logs' => $logs,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_logs' => $total_logs,
            'limit' => $limit
        ],
        'actions' => $actions
    ];

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
if (isset($connect) && $connect instanceof mysqli) {
    $connect->close();
}