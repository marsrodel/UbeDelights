<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

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
