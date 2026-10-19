<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session_guard.php';
header('Content-Type: application/json; charset=utf-8');
session_guard_api_timeout();

if (!isset($_SESSION['auth_user_id']) || !isset($_SESSION['auth_role'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$role = $_SESSION['auth_role'];
if (!in_array($role, ['admin', 'super_admin'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit();
}

$userId = $_SESSION['auth_user_id'];
$roleFilter = $role === 'admin' ? " AND (n.role IS NULL OR n.role <> 'super_admin')" : '';

$unreadQuery = "SELECT COUNT(*) AS cnt
                FROM notifications n
                LEFT JOIN notification_reads nr
                    ON nr.notification_id = n.id AND nr.user_id = ?
                WHERE nr.notification_id IS NULL" . $roleFilter;
$unreadStmt = $connect->prepare($unreadQuery);
if (!$unreadStmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to load notifications']);
    exit();
}
$unreadStmt->bind_param('s', $userId);
if (!$unreadStmt->execute()) {
    $unreadStmt->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to load notifications']);
    exit();
}
$unreadResult = $unreadStmt->get_result();
$unreadCount = $unreadResult ? (int) $unreadResult->fetch_assoc()['cnt'] : 0;
$unreadStmt->close();

$limit = isset($_GET['limit']) ? max(1, min((int) $_GET['limit'], 50)) : 20;
$query = "SELECT n.id, n.title, n.message, n.action_type, n.role,
                 (nr.notification_id IS NOT NULL) AS is_read, n.created_at
          FROM notifications n
          LEFT JOIN notification_reads nr
              ON nr.notification_id = n.id AND nr.user_id = ?
          WHERE 1=1" . $roleFilter . "
          ORDER BY n.created_at DESC
          LIMIT ?";
$stmt = $connect->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to load notifications']);
    exit();
}
$stmt->bind_param('si', $userId, $limit);
if (!$stmt->execute()) {
    $stmt->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to load notifications']);
    exit();
}
$result = $stmt->get_result();
$notifications = [];
while ($result && ($row = $result->fetch_assoc())) {
    $row['is_read'] = (bool) $row['is_read'];
    $notifications[] = $row;
}
$stmt->close();

echo json_encode([
    'success' => true,
    'unread_count' => $unreadCount,
    'notifications' => $notifications
]);
