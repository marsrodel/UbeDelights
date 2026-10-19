<?php

function session_guard_timeout_seconds() {
    return 300;
}

function session_guard_is_expired($lastActivity, $now = null) {
    if (!is_numeric($lastActivity)) return false;
    $now = $now ?? time();
    return ($now - (int)$lastActivity) >= session_guard_timeout_seconds();
}

function session_guard_seconds_since_activity($lastActivity, $now = null) {
    $now = $now ?? time();
    if (!is_numeric($lastActivity)) return 0;
    return max(0, $now - (int)$lastActivity);
}

function session_guard_user_id() {
    return $_SESSION['auth_user_id'] ?? $_SESSION['user_id'] ?? null;
}

function session_guard_user_role() {
    return $_SESSION['auth_role'] ?? $_SESSION['role'] ?? null;
}

function session_guard_username() {
    return $_SESSION['auth_username'] ?? $_SESSION['username'] ?? null;
}

function session_guard_app_url($path) {
    $scriptPath = parse_url($_SERVER['SCRIPT_NAME'] ?? '', PHP_URL_PATH) ?: '';
    $basePath = '';
    foreach (['/server/', '/views/'] as $segment) {
        $position = strpos($scriptPath, $segment);
        if ($position !== false) {
            $basePath = substr($scriptPath, 0, $position);
            break;
        }
    }
    return rtrim($basePath, '/') . '/' . ltrim($path, '/');
}

function session_guard_login_url($error = null) {
    $url = session_guard_app_url('/views/login.php');
    return $error ? $url . '?error=' . rawurlencode($error) : $url;
}

function session_guard_is_api_request() {
    $scriptPath = parse_url($_SERVER['SCRIPT_NAME'] ?? '', PHP_URL_PATH) ?: '';
    return strpos($scriptPath, '/server/') !== false;
}

function session_guard_clear_session() {
    if (session_status() !== PHP_SESSION_ACTIVE) return;
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    if (isset($_COOKIE['session_token'])) setcookie('session_token', '', time() - 3600, '/');
    session_destroy();
}

function session_guard_release_super_admin() {
    global $connect;
    if (session_guard_user_role() !== 'super_admin') return;
    $userId = session_guard_user_id();
    if (!$userId) return;

    require_once __DIR__ . '/db.php';
    if (!$connect) return;

    $stmt = mysqli_prepare($connect, "UPDATE users SET is_logged_in = 0 WHERE user_id = ?");
    if (!$stmt) return;
    mysqli_stmt_bind_param($stmt, 's', $userId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

function session_guard_expire() {
    global $connect;
    $userId = session_guard_user_id();
    $username = session_guard_username();
    try {
        require_once __DIR__ . '/user_logger.php';
        log_activity('session_timeout', 'Session expired after 5 minutes of inactivity', 'Authentication', $userId, $username);
    } catch (Throwable $e) {
        error_log('Ube Delights could not record a session timeout.');
    }
    session_guard_release_super_admin();
    session_guard_clear_session();
}

function session_guard_api_timeout($identityKeys = ['auth_user_id', 'user_id']) {
    if (session_status() === PHP_SESSION_NONE) session_start();

    $hasIdentity = false;
    foreach ($identityKeys as $key) {
        if (!empty($_SESSION[$key])) {
            $hasIdentity = true;
            break;
        }
    }
    if (!$hasIdentity) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized.', 'code' => 'unauthorized']);
        exit();
    }

    if (session_guard_is_expired($_SESSION['last_activity'] ?? null)) {
        $loginUrl = session_guard_login_url('timeout');
        session_guard_expire();
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Your session expired due to inactivity. Please sign in again.', 'code' => 'session_timeout', 'login_url' => $loginUrl]);
        exit();
    }
}

function session_guard_touch($now = null) {
    $_SESSION['last_activity'] = $now ?? time();
}

function session_guard_page_timeout() {
    if (!session_guard_user_id()) return;

    if (session_guard_is_expired($_SESSION['last_activity'] ?? null)) {
        $loginUrl = session_guard_login_url('timeout');
        session_guard_expire();
        if (session_guard_is_api_request()) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Your session expired due to inactivity. Please sign in again.', 'code' => 'session_timeout', 'login_url' => $loginUrl]);
            exit();
        }
        header('Location: ' . $loginUrl);
        exit();
    }

    session_guard_touch();
}
