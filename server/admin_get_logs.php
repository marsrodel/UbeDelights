<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/user_logger.php';

header('Content-Type: application/json');

if (!isset($_SESSION['auth_user_id']) || !in_array($_SESSION['auth_role'] ?? '', ['admin', 'super_admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if (!isset($_SESSION['auth_status']) || $_SESSION['auth_status'] !== 'active') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Account not active']);
    exit();
}

$currentUserRole = $_SESSION['auth_role'];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role = isset($_GET['role']) ? trim($_GET['role']) : '';
$dateFrom = isset($_GET['date_from']) ? trim($_GET['date_from']) : '';
$dateTo = isset($_GET['date_to']) ? trim($_GET['date_to']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 10;
$offset = ($page - 1) * $limit;

$where = [];

if ($currentUserRole === 'admin') {
    $where[] = "role != 'super_admin'";
}

if ($search !== '') {
    $where[] = "(idNumber LIKE ? OR username LIKE ? OR fullName LIKE ? OR action LIKE ? OR details LIKE ?)";
}

if ($role !== '') {
    $where[] = "role = ?";
}

if ($dateFrom !== '') {
    $where[] = "DATE(created_at) >= ?";
}

if ($dateTo !== '') {
    $where[] = "DATE(created_at) <= ?";
}

$whereClause = '';
$params = [];
$types = '';

if (!empty($where)) {
    $whereClause = 'WHERE ' . implode(' AND ', $where);
}

if ($search !== '') {
    $s = "%$search%";
    $params = array_merge($params, [$s, $s, $s, $s, $s]);
    $types .= 'sssss';
}
if ($role !== '') {
    $params[] = $role;
    $types .= 's';
}
if ($dateFrom !== '') {
    $params[] = $dateFrom;
    $types .= 's';
}
if ($dateTo !== '') {
    $params[] = $dateTo;
    $types .= 's';
}

$countSql = "SELECT COUNT(*) AS total FROM activity_logs $whereClause";
$countStmt = mysqli_prepare($connect, $countSql);
if (!empty($params)) {
    mysqli_stmt_bind_param($countStmt, $types, ...$params);
}
mysqli_stmt_execute($countStmt);
$countResult = mysqli_stmt_get_result($countStmt);
$total = $countResult ? (int)mysqli_fetch_assoc($countResult)['total'] : 0;
mysqli_stmt_close($countStmt);

$sql = "SELECT id, idNumber, username, fullName, role, action, module, details, ip_address, browser, device, os, DATE(created_at) AS log_date, TIME(created_at) AS log_time, created_at
        FROM activity_logs $whereClause
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?";

$fetchParams = array_merge($params, [$limit, $offset]);
$fetchTypes = $types . 'ii';

$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, $fetchTypes, ...$fetchParams);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$logs = [];
while ($row = $result->fetch_assoc()) {
    $logs[] = [
        'id' => $row['id'],
        'idNumber' => $row['idNumber'] ?? 'N/A',
        'username' => $row['username'] ?? 'System',
        'fullName' => $row['fullName'] ?? $row['username'] ?? 'System',
        'role' => $row['role'] ?? 'system',
        'action' => $row['action'],
        'module' => $row['module'] ?? '',
        'details' => $row['details'] ?? '',
        'ip_address' => $row['ip_address'] ?? 'N/A',
        'browser' => $row['browser'] ?? 'Unknown',
        'device' => $row['device'] ?? 'Unknown',
        'os' => $row['os'] ?? 'Unknown',
        'log_date' => $row['log_date'],
        'log_time' => $row['log_time'],
        'created_at' => $row['created_at'],
    ];
}
mysqli_stmt_close($stmt);

$logs = logs_attach_time_out($connect, $logs);

$totalPages = max(1, (int)ceil($total / $limit));

echo json_encode([
    'success' => true,
    'logs' => $logs,
    'pagination' => [
        'page' => $page,
        'limit' => $limit,
        'total' => $total,
        'totalPages' => $totalPages,
    ],
]);
