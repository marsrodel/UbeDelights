<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/session_guard.php';

header('Content-Type: application/json; charset=utf-8');
session_guard_api_timeout(['auth_user_id']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit();
}

$role = $_SESSION['auth_role'] ?? '';
$status = $_SESSION['auth_status'] ?? '';
if (!in_array($role, ['admin', 'super_admin', 'customer'], true) || !in_array($status, ['active', 'incomplete'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Account is not active.']);
    exit();
}

$action = $_POST['action'] ?? '';
if (!in_array($action, ['heartbeat', 'continue', 'expire'], true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid session activity action.']);
    exit();
}

$now = time();
$lastActivity = $_SESSION['last_activity'] ?? null;
$expired = session_guard_is_expired($lastActivity, $now);

if ($action === 'expire') {
    if (!$expired) {
        $elapsed = session_guard_seconds_since_activity($lastActivity, $now);
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'code' => 'session_active',
            'retry_after_seconds' => max(1, session_guard_timeout_seconds() - $elapsed)
        ]);
        exit();
    }

    $loginUrl = session_guard_login_url('timeout');
    session_guard_expire();
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Your session expired due to inactivity. Please sign in again.', 'code' => 'session_timeout', 'login_url' => $loginUrl]);
    exit();
}

if ($expired) {
    session_guard_api_timeout(['auth_user_id']);
}

session_guard_touch($now);
if ($role === 'super_admin' && $status === 'active') {
    require_once __DIR__ . '/db.php';
    $userId = $_SESSION['auth_user_id'];
    $stmt = mysqli_prepare($connect, "UPDATE users SET last_activity = NOW() WHERE user_id = ? AND is_logged_in = 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $userId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
echo json_encode(['success' => true, 'activity_time' => $now]);
