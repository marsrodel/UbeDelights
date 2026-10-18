<?php
session_start();
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$userId = $_SESSION['auth_user_id'];
$roleFilter = $role === 'admin' ? " AND (n.role IS NULL OR n.role <> 'super_admin')" : '';
$query = "INSERT IGNORE INTO notification_reads (notification_id, user_id)
          SELECT n.id, ?
          FROM notifications n
          WHERE 1=1" . $roleFilter;
$stmt = $connect->prepare($query);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to mark notifications as read']);
    exit();
}
$stmt->bind_param('s', $userId);
if (!$stmt->execute()) {
    $stmt->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to mark notifications as read']);
    exit();
}
$stmt->close();

echo json_encode(['success' => true]);
