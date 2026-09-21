<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['auth_user_id']) || !isset($_SESSION['auth_role'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$role = $_SESSION['auth_role'];

$roleFilter = '';
if ($role === 'admin') {
    $roleFilter = " AND (role IS NULL OR role != 'super_admin')";
}

$unreadQuery = "SELECT COUNT(*) AS cnt FROM notifications WHERE is_read = 0" . $roleFilter;
$unreadResult = $connect->query($unreadQuery);
$unreadCount = ($unreadResult) ? (int) $unreadResult->fetch_assoc()['cnt'] : 0;

$limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 50) : 20;
$query = "SELECT id, title, message, action_type, role, is_read, created_at FROM notifications WHERE 1=1" . $roleFilter . " ORDER BY created_at DESC LIMIT ?";
$stmt = $connect->prepare($query);
$stmt->bind_param('i', $limit);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $row['is_read'] = (bool) $row['is_read'];
    $notifications[] = $row;
}
$stmt->close();

echo json_encode([
    'success' => true,
    'unread_count' => $unreadCount,
    'notifications' => $notifications
]);
