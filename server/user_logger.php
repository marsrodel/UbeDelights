<?php
require_once __DIR__ . '/db.php';

function log_activity($action, $details, $module = 'General', $idNumber = null, $username = null) {
    global $connect;
    if (!$connect) return false;

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $uaInfo = parse_user_agent($ua);

    $fullName = null;
    $role = null;
    if ($idNumber || $username) {
        $lookup = log_lookup_user($connect, $idNumber, $username);
        if ($lookup) {
            $fullName = $lookup['fullName'];
            $role = $lookup['role'];
        }
    }

    $severity = log_severity_for_action($action);

    $detailsText = $details;
    if (!empty($uaInfo['browser']) || !empty($uaInfo['os']) || !empty($uaInfo['device'])) {
        $parts = [];
        if (!empty($uaInfo['browser'])) $parts[] = 'Browser: ' . $uaInfo['browser'];
        if (!empty($uaInfo['os'])) $parts[] = 'OS: ' . $uaInfo['os'];
        if (!empty($uaInfo['device'])) $parts[] = 'Device: ' . $uaInfo['device'];
        $detailsText .= ' | ' . implode(' | ', $parts);
    }

    $sql = "INSERT INTO activity_logs (idNumber, username, fullName, role, module, action, details, ip_address, severity, browser, device, os) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($connect, $sql);
    if (!$stmt) return false;
    mysqli_stmt_bind_param($stmt, 'ssssssssssss',
        $idNumber, $username, $fullName, $role, $module, $action, $detailsText, $ip, $severity,
        $uaInfo['browser'], $uaInfo['device'], $uaInfo['os']
    );
    $ok = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    return $ok;
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
    ];
    return $map[$action] ?? 'INFO';
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
        $row['time_in'] = $row['created_at'] ?? null;
        $row['time_out'] = null;

        if (($row['action'] ?? '') !== 'login' || empty($row['idNumber']) || empty($row['created_at'])) {
            continue;
        }

        $nextStmt = mysqli_prepare($db, "SELECT created_at FROM activity_logs WHERE idNumber = ? AND action = 'login' AND created_at > ? ORDER BY created_at ASC LIMIT 1");
        mysqli_stmt_bind_param($nextStmt, 'ss', $row['idNumber'], $row['created_at']);
        mysqli_stmt_execute($nextStmt);
        $nextResult = mysqli_stmt_get_result($nextStmt);
        $nextLogin = $nextResult ? mysqli_fetch_assoc($nextResult) : null;
        mysqli_stmt_close($nextStmt);

        if ($nextLogin) {
            $sql = "SELECT created_at FROM activity_logs WHERE idNumber = ? AND action IN ('logout', 'session_timeout') AND created_at > ? AND created_at < ? ORDER BY created_at ASC LIMIT 1";
            $stmt = mysqli_prepare($db, $sql);
            mysqli_stmt_bind_param($stmt, 'sss', $row['idNumber'], $row['created_at'], $nextLogin['created_at']);
        } else {
            $sql = "SELECT created_at FROM activity_logs WHERE idNumber = ? AND action IN ('logout', 'session_timeout') AND created_at > ? ORDER BY created_at ASC LIMIT 1";
            $stmt = mysqli_prepare($db, $sql);
            mysqli_stmt_bind_param($stmt, 'ss', $row['idNumber'], $row['created_at']);
        }
        mysqli_stmt_execute($stmt);
        $logoutResult = mysqli_stmt_get_result($stmt);
        $logout = $logoutResult ? mysqli_fetch_assoc($logoutResult) : null;
        mysqli_stmt_close($stmt);

        if ($logout) {
            $row['time_out'] = $logout['created_at'];
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
