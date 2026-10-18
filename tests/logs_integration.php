<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}

$testDatabase = $argv[1] ?? '';
if (!preg_match('/^ubedelights_log_(verification|import_verification)$/', $testDatabase)) {
    fwrite(STDERR, "Pass a dedicated log verification database.\n");
    exit(1);
}

require_once __DIR__ . '/../server/log_reader.php';
$liveConnection = $connect;
$connect = mysqli_connect($host, $user, $pass, $testDatabase);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$checks = 0;
$exitCode = 0;
$marker = 'audit_test_' . bin2hex(random_bytes(6));

function verify_log_test($condition, $message) {
    global $checks;
    if (!$condition) throw new RuntimeException($message);
    $checks++;
}

function log_test_user($role) {
    global $connect;
    $stmt = mysqli_prepare($connect, 'SELECT user_id, username FROM users WHERE role = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 's', $role);
    mysqli_stmt_execute($stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);
    if (!$row) throw new RuntimeException('Missing test role: ' . $role);
    return $row;
}

function log_test_fixture($accountId, $action, $timestamp) {
    global $connect, $marker;
    $stmt = mysqli_prepare($connect, "INSERT INTO authentication_logs (idNumber, username, role, action, details, created_at) VALUES (?, 'test_customer', 'customer', ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'ssss', $accountId, $action, $marker, $timestamp);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

mysqli_begin_transaction($connect);
try {
    $admin = log_test_user('admin');
    $superAdmin = log_test_user('super_admin');
    $customer = log_test_user('customer');
    $beforeNotifications = (int)mysqli_fetch_assoc(mysqli_query($connect, 'SELECT COUNT(*) AS n FROM notifications'))['n'];
    foreach ([$admin, $superAdmin, $customer] as $account) {
        verify_log_test(log_activity('login', $marker, 'Authentication', $account['user_id'], $account['username']), 'Login writer failed.');
        verify_log_test(log_activity('PROFILE_UPDATE', $marker, 'Account Management', $account['user_id'], $account['username']), 'Activity writer failed.');
        verify_log_test(log_system_event('test_system', $marker, 'Verification', 'INFO', $account['user_id'], $account['username']), 'System writer failed.');
    }
    verify_log_test(log_activity('otp_verified', $marker, 'Account Setup', $customer['user_id']), 'OTP writer failed.');
    verify_log_test(log_activity('failed_login', $marker, 'Authentication', null, 'unknown_audit_account'), 'Unknown login writer failed.');
    verify_log_test(log_system_event('test_system', $marker, 'Verification', 'INFO'), 'Automated system writer failed.');

    $filters = logs_request_filters(['search' => $marker, 'limit' => 100]);
    $allCategories = ['system', 'authentication', 'activity'];
    $all = logs_fetch_page($connect, $allCategories, $filters, 'super_admin');
    verify_log_test($all['pagination']['total'] === 12, 'An event was lost or duplicated.');
    $counts = array_count_values(array_column($all['logs'], 'log_type'));
    verify_log_test($counts['system'] === 4 && $counts['authentication'] === 5 && $counts['activity'] === 3, 'Incorrect log categories.');
    $adminLogs = logs_fetch_page($connect, $allCategories, $filters, 'admin');
    verify_log_test($adminLogs['pagination']['total'] === 7, 'Incorrect administrator visibility.');
    foreach ($adminLogs['logs'] as $row) verify_log_test(in_array($row['role'], ['admin', 'customer'], true), 'Administrator saw a restricted role.');
    foreach (['super_admin', 'system'] as $restrictedRole) {
        $restricted = $filters;
        $restricted['role'] = $restrictedRole;
        verify_log_test(logs_fetch_page($connect, $allCategories, $restricted, 'admin')['pagination']['total'] === 0, 'A role filter bypassed permissions.');
    }
    $own = logs_fetch_page($connect, ['activity', 'authentication'], $filters, 'customer', $customer['user_id']);
    verify_log_test($own['pagination']['total'] === 3, 'Customer history is incomplete or includes another account.');
    foreach ($own['logs'] as $row) verify_log_test($row['idNumber'] === $customer['user_id'] && $row['role'] === 'customer', 'Customer saw another account.');
    verify_log_test((int)mysqli_fetch_assoc(mysqli_query($connect, 'SELECT COUNT(*) AS n FROM notifications'))['n'] === $beforeNotifications + 4, 'Existing notification creation changed.');

    $paged = $filters;
    $paged['page'] = 2;
    $paged['limit'] = 2;
    $page = logs_fetch_page($connect, $allCategories, $paged, 'super_admin');
    verify_log_test(count($page['logs']) === 2 && $page['pagination']['totalPages'] === 6 && $page['pagination']['page'] === 2, 'Pagination failed.');
    $paged['date_from'] = '2097-01-01';
    verify_log_test(logs_fetch_page($connect, $allCategories, $paged, 'super_admin')['pagination']['total'] === 0, 'Date filtering failed.');

    $stmt = mysqli_prepare($connect, "UPDATE users SET role = 'super_admin', first_name = 'Renamed' WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, 's', $admin['user_id']);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $historical = logs_fetch_page($connect, $allCategories, $filters, 'admin');
    verify_log_test($historical['pagination']['total'] === 7, 'Changing a current role changed historical access.');
    foreach ($historical['logs'] as $row) {
        if ($row['idNumber'] === $admin['user_id']) verify_log_test($row['role'] === 'admin' && strpos($row['fullName'], 'Renamed') === false, 'Identity snapshots were rewritten.');
    }

    log_test_fixture('2099-9001', 'login', '2030-01-01 10:00:00');
    log_test_fixture('2099-9001', 'logout', '2030-01-01 10:00:00');
    log_test_fixture('2099-9001', 'login', '2030-01-01 10:01:00');
    log_test_fixture('2099-9001', 'login', '2030-01-01 10:02:00');
    $paired = logs_fetch_page($connect, ['authentication'], $filters, 'customer', '2099-9001')['logs'];
    verify_log_test($paired[0]['time_out'] === 'active', 'Current login should be active.');
    verify_log_test($paired[1]['time_out'] === null, 'A login was paired across another login.');
    verify_log_test($paired[2]['time_in'] === null && $paired[2]['time_out'] === '2030-01-01 10:00:00', 'Logout timestamp failed.');
    verify_log_test($paired[3]['time_out'] === '2030-01-01 10:00:00', 'Same-second login/logout pairing failed.');

    foreach ([['role' => 'invalid'], ['date_from' => '2026-02-30'], ['date_from' => '2026-10-20', 'date_to' => '2026-10-01'], ['search' => []]] as $invalid) {
        $rejected = false;
        try { logs_request_filters($invalid); } catch (InvalidArgumentException $e) { $rejected = true; }
        verify_log_test($rejected, 'Invalid filters were accepted.');
    }
    echo 'PASS: ' . $checks . " log integration checks. All fixture writes rolled back.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n");
    $exitCode = 1;
} finally {
    mysqli_rollback($connect);
    mysqli_close($connect);
    $connect = $liveConnection;
}
exit($exitCode);
