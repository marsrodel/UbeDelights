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
    $roleFilter = " WHERE role IS NULL OR role != 'super_admin'";
} else {
    $roleFilter = " WHERE 1=1";
}

$connect->query("UPDATE notifications SET is_read = 1" . $roleFilter);

echo json_encode(['success' => true]);
