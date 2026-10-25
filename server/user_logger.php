<?php
require_once __DIR__ . '/db.php';

function log_activity($action, $details, $module = 'General', $idNumber = null, $username = null) {
    return log_write_event(log_category_for_action($action), $action, $details, $module, $idNumber, $username);
}

function log_deletion_event_details($summary, $metadata) {
    $metadata['version'] = 1;
    $json = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    if ($json === false) return $summary;
    return rtrim((string)$summary) . ' [[DELETION_META_V1:' . base64_encode($json) . ']]';
}

function log_category_for_action($action) {
    $authentication = [
        'login', 'logout', 'failed_login', 'login_blocked', 'login_pending', 'login_rejected',
        'session_timeout', 'unauthorized_access', 'account_lockout', 'staging_activated',
        'account_activated', 'otp_requested', 'otp_resent', 'otp_verified', 'otp_failed',
        'otp_expired', 'otp_locked', 'password_reset_completed',
    ];
    return in_array(strtolower($action), $authentication, true) ? 'authentication' : 'activity';
}

function log_system_event($action, $details, $module = 'System', $severity = 'ERROR', $idNumber = null, $username = null) {
    return log_write_event('system', $action, $details, $module, $idNumber, $username, $severity);
}

function log_write_event($category, $action, $details, $module, $idNumber, $username, $severity = null) {
    global $connect;
    if (!$connect) return false;

    $tables = ['activity' => 'activity_logs', 'authentication' => 'authentication_logs', 'system' => 'system_logs'];
    if (!isset($tables[$category])) return false;

    static $writing = false;
    if ($writing) return false;
    $writing = true;

    try {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $uaInfo = parse_user_agent($ua);

        $fullName = null;
        $role = null;
        if ($idNumber || $username) {
            $lookup = log_lookup_user($connect, $idNumber, $username);
            if ($lookup) {
                $idNumber = $lookup['idNumber'];
                $username = $lookup['username'];
                $fullName = $lookup['fullName'];
                $role = $lookup['role'];
            }
        }

        $severity = $severity ?? log_severity_for_action($action);
        if (!in_array($severity, ['INFO', 'WARNING', 'ERROR', 'CRITICAL'], true)) $severity = 'INFO';

        $detailsText = $details;
        if (!empty($uaInfo['browser']) || !empty($uaInfo['os']) || !empty($uaInfo['device'])) {
            $parts = [];
            if (!empty($uaInfo['browser'])) $parts[] = 'Browser: ' . $uaInfo['browser'];
            if (!empty($uaInfo['os'])) $parts[] = 'OS: ' . $uaInfo['os'];
            if (!empty($uaInfo['device'])) $parts[] = 'Device: ' . $uaInfo['device'];
            $detailsText .= ' | ' . implode(' | ', $parts);
        }

        $table = $tables[$category];
        $sql = "INSERT INTO $table (idNumber, username, fullName, role, module, action, details, ip_address, severity, browser, device, os) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($connect, $sql);
        if (!$stmt) throw new RuntimeException('Could not prepare audit event.');
        mysqli_stmt_bind_param($stmt, 'ssssssssssss',
            $idNumber, $username, $fullName, $role, $module, $action, $detailsText, $ip, $severity,
            $uaInfo['browser'], $uaInfo['device'], $uaInfo['os']
        );
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        if (!$ok) throw new RuntimeException('Could not write audit event.');

        if ($ok) {
            log_create_notification($action, $details, $role, $idNumber);
        }

        return $ok;
    } catch (Throwable $e) {
        error_log('Ube Delights could not write an audit event.');
        return false;
    } finally {
        $writing = false;
    }
}

function log_lookup_user($db, $idNumber = null, $username = null) {
    if ($idNumber) {
        $stmt = mysqli_prepare($db, "SELECT user_id, username, CONCAT(first_name, ' ', IFNULL(CONCAT(middle_name, ' '), ''), last_name, IFNULL(CONCAT(' ', extension_name), '')) AS fullName, role FROM users WHERE user_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $idNumber);
    } elseif ($username) {
        $stmt = mysqli_prepare($db, "SELECT user_id, username, CONCAT(first_name, ' ', IFNULL(CONCAT(middle_name, ' '), ''), last_name, IFNULL(CONCAT(' ', extension_name), '')) AS fullName, role FROM users WHERE username = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $username);
    } else {
        return null;
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);
    if (!$row) {
        $column = $idNumber ? 'user_id' : 'username';
        $value = $idNumber ?: $username;
        $stmt = mysqli_prepare($db, "SELECT user_id, username, username AS fullName, role FROM staging_accounts WHERE $column = ? LIMIT 1");
        if (!$stmt) return null;
        mysqli_stmt_bind_param($stmt, 's', $value);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = $result ? mysqli_fetch_assoc($result) : null;
        mysqli_stmt_close($stmt);
    }
    if (!$row) return null;
    return [
        'idNumber' => $row['user_id'],
        'username' => $row['username'],
        'fullName' => trim($row['fullName']),
        'role' => $row['role'],
    ];
}

function log_severity_for_action($action) {
    $map = [
        'failed_login' => 'WARNING',
        'login_blocked' => 'ERROR',
        'login_pending' => 'WARNING',
        'login_rejected' => 'WARNING',
        'unauthorized_access' => 'ERROR',
        'session_timeout' => 'ERROR',
        'account_lockout' => 'CRITICAL',
        'otp_failed' => 'WARNING',
        'otp_expired' => 'WARNING',
        'otp_locked' => 'ERROR',
    ];
    return $map[strtolower($action)] ?? 'INFO';
}

function parse_user_agent($ua) {
    $browser = 'Unknown';
    $device = 'Desktop';
    $os = 'Unknown';

    if (preg_match('/Chrome\/([0-9\.]+)/', $ua, $m) && !preg_match('/Edg\//', $ua)) {
        $browser = 'Chrome ' . $m[1];
    } elseif (preg_match('/Edg\/([0-9\.]+)/', $ua, $m)) {
        $browser = 'Edge ' . $m[1];
    } elseif (preg_match('/Firefox\/([0-9\.]+)/', $ua, $m)) {
        $browser = 'Firefox ' . $m[1];
    } elseif (preg_match('/Safari\/([0-9\.]+)/', $ua, $m) && !preg_match('/Chrome/', $ua)) {
        $browser = 'Safari ' . $m[1];
    } elseif (preg_match('/Opera\/|OPR\/([0-9\.]+)/', $ua, $m)) {
        $browser = 'Opera ' . ($m[1] ?? '');
    }

    if (preg_match('/Windows/i', $ua)) $os = 'Windows';
    elseif (preg_match('/Mac OS X/i', $ua)) $os = 'macOS';
    elseif (preg_match('/Android/i', $ua)) $os = 'Android';
    elseif (preg_match('/iPhone|iPad|iPod/i', $ua)) $os = 'iOS';
    elseif (preg_match('/Linux/i', $ua)) $os = 'Linux';

    if (preg_match('/Mobile|Android|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i', $ua)) {
        $device = 'Mobile';
    } elseif (preg_match('/iPad|Tablet/i', $ua)) {
        $device = 'Tablet';
    }

    return ['browser' => $browser, 'device' => $device, 'os' => $os];
}

function logs_attach_time_out($db, $rows) {
    foreach ($rows as &$row) {
        $row['time_in'] = null;
        $row['time_out'] = null;

        if (($row['log_type'] ?? log_category_for_action($row['action'] ?? '')) !== 'authentication') continue;
        $row['time_in'] = $row['created_at'] ?? null;

        $action = strtolower($row['action'] ?? '');
        if ($action === 'logout' || $action === 'session_timeout') {
            $row['time_in'] = null;
            $row['time_out'] = $row['created_at'] ?? null;
        }

        if ($action === 'login' && !empty($row['idNumber']) && !empty($row['created_at'])) {
            $stmt = mysqli_prepare($db, "SELECT action, created_at FROM authentication_logs WHERE idNumber = ? AND role <=> ? AND action IN ('login', 'logout', 'session_timeout') AND (created_at > ? OR (created_at = ? AND id > ?)) ORDER BY created_at ASC, id ASC LIMIT 1");
            $eventId = (int)$row['id'];
            $eventRole = $row['role'] ?? null;
            mysqli_stmt_bind_param($stmt, 'ssssi', $row['idNumber'], $eventRole, $row['created_at'], $row['created_at'], $eventId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $next = $result ? mysqli_fetch_assoc($result) : null;
            mysqli_stmt_close($stmt);

            if (!$next) {
                $row['time_out'] = 'active';
            } elseif (in_array(strtolower($next['action']), ['logout', 'session_timeout'], true)) {
                $row['time_out'] = $next['created_at'];
            }
        }
    }
    unset($row);
    return $rows;
}

function canPerformAction($target_role, $action) {
    $current_role = $_SESSION['auth_role'] ?? null;
    if ($current_role === 'admin' && $target_role === 'super_admin') return false;
    if ($current_role === 'admin' && $action === 'create_super_admin') return false;
    if ($action === 'delete' && $current_role !== 'super_admin') return false;
    return true;
}

function log_create_notification($action, $details, $role, $idNumber) {
    global $connect;
    if (!$connect) return;

    $notifMap = [
        'login'           => ['title' => 'User Login',          'type' => 'auth'],
        'logout'          => ['title' => 'User Logout',         'type' => 'auth'],
        'failed_login'    => ['title' => 'Failed Login',        'type' => 'security'],
        'login_blocked'   => ['title' => 'Login Blocked',       'type' => 'security'],
        'session_timeout' => ['title' => 'Session Timeout',     'type' => 'security'],
        'CREATE_USER'     => ['title' => 'Account Created',     'type' => 'account'],
        'BLOCK_USER'      => ['title' => 'Account Blocked',     'type' => 'account'],
        'UNBLOCK_USER'    => ['title' => 'Account Unblocked',   'type' => 'account'],
        'RESET_PASSWORD'  => ['title' => 'Password Reset',      'type' => 'account'],
        'DELETE_USER'     => ['title' => 'Account Deleted',     'type' => 'account'],
        'approve'         => ['title' => 'Registration Approved','type' => 'account'],
        'reject'          => ['title' => 'Registration Rejected','type' => 'account'],
    ];

    if (!isset($notifMap[$action])) return;

    $info = $notifMap[$action];
    $title = $info['title'];
    $type = $info['type'];
    $message = $details;

    $stmt = mysqli_prepare($connect, "INSERT INTO notifications (title, message, action_type, role) VALUES (?, ?, ?, ?)");
    if (!$stmt) return;
    mysqli_stmt_bind_param($stmt, 'ssss', $title, $message, $type, $role);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}
