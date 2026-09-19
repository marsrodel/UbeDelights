<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/user_logger.php';
require_once __DIR__ . '/db.php';

$logId = $_SESSION['auth_user_id'] ?? null;
$logUser = $_SESSION['auth_username'] ?? null;
$logRole = $_SESSION['auth_role'] ?? null;

if ($logUser) {
    log_activity('logout', 'User logged out', 'Authentication', $logId, $logUser);
}

// Clear is_logged_in for super admins so the other super admin can log in (shift)
if ($logRole === 'super_admin' && $logId && $connect) {
    $stmt = mysqli_prepare($connect, "UPDATE users SET is_logged_in = 0 WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 's', $logId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// Clear PHP session data
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

// Clear custom session token cookie if used
if (isset($_COOKIE['session_token'])) {
    setcookie('session_token', '', time() - 3600, '/');
}

header('Location: ../views/login.php');
exit();
?>
