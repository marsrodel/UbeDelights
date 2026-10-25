<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit();
}

require_once __DIR__ . '/../server/db.php';
$baseUrl = $argv[1] ?? 'http://localhost/UbeDelights/server/';
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) return false;
    throw new ErrorException($message, 0, $severity, $file, $line);
});
$checks = 0;
$sessions = [];
$exitCode = 0;

function verify_logs_endpoint($condition, $message) {
    global $checks;
    if (!$condition) throw new RuntimeException($message);
    $checks++;
}

function logs_test_session($role, $userId, $status = 'active') {
    global $sessions;
    $id = 'audit-test-' . bin2hex(random_bytes(12));
    session_id($id);
    if (!session_start()) throw new RuntimeException('Could not create the test session.');
    $_SESSION = ['auth_user_id' => $userId, 'auth_username' => 'audit_viewer', 'auth_role' => $role, 'auth_status' => $status, 'last_activity' => time(), 'staging_cleanup_done' => true];
    session_write_close();
    $sessions[] = $id;
    return $id;
}

function logs_test_request($endpoint, $query = [], $session = null, $decodeJson = true) {
    global $baseUrl;
    $context = stream_context_create(['http' => [
        'ignore_errors' => true,
        'timeout' => 10,
        'header' => $session ? 'Cookie: PHPSESSID=' . $session . "\r\n" : '',
    ]]);
    $raw = file_get_contents($baseUrl . $endpoint . '?' . http_build_query($query), false, $context);
    preg_match('/\s(\d{3})\s/', $http_response_header[0] ?? '', $match);
    return [(int)($match[1] ?? 0), $decodeJson ? json_decode($raw, true, 512, JSON_THROW_ON_ERROR) : $raw];
}

try {
    $customerRow = mysqli_fetch_assoc(mysqli_query($connect, "SELECT user_id FROM users WHERE role = 'customer' LIMIT 1"));
    if (!$customerRow) throw new RuntimeException('A customer account is needed for endpoint verification.');
    $customerId = $customerRow['user_id'];
    $superAdmin = logs_test_session('super_admin', 'audit-viewer');
    $admin = logs_test_session('admin', 'audit-viewer');
    $customer = logs_test_session('customer', $customerId);
    $inactive = logs_test_session('admin', 'audit-viewer', 'blocked');
    $inactiveCustomer = logs_test_session('customer', $customerId, 'blocked');

    foreach ([$superAdmin, $admin] as $index => $session) {
        [$status, $html] = logs_test_request('../views/admin/system_logs.php', [], $session, false);
        verify_logs_endpoint($status === 200 && strpos($html, '<h1>Logs</h1>') !== false && strpos($html, '<title>Logs -') !== false, 'The admin page was not renamed to Logs.');
        verify_logs_endpoint(strpos($html, '<option value="authentication" selected>Login &amp; Security Logs</option>') !== false, 'The rendered page does not default to Login & Security Logs.');
        verify_logs_endpoint(strpos($html, '<option value="activity">Activity Logs</option>') !== false && strpos($html, '<option value="system">System Logs</option>') !== false, 'The log-type selector is incomplete.');
        preg_match('/<select id="logsRoleFilter">(.*?)<\/select>/s', $html, $roleFilter);
        verify_logs_endpoint((strpos($roleFilter[1], 'value="super_admin"') !== false) === ($index === 0), 'The role filter exposes restricted options.');
    }
    [$status, $html] = logs_test_request('../views/activity_logs.php', [], $customer, false);
    verify_logs_endpoint($status === 200 && strpos($html, 'Ube Delights - My Logs') !== false && strpos($html, '<th>Log Type</th>') !== false, 'The customer My Logs page did not render.');
    preg_match('/<select id="logsTypeFilter">(.*?)<\/select>/s', $html, $customerTypeFilter);
    verify_logs_endpoint(!empty($customerTypeFilter[1]) && strpos($customerTypeFilter[1], '<option value="authentication" selected>Login &amp; Security Logs</option>') !== false && strpos($customerTypeFilter[1], '<option value="activity">Activity Logs</option>') !== false && strpos($customerTypeFilter[1], 'value="system"') === false, 'Customer Log Type options are incorrect.');

    foreach ([$superAdmin, $admin] as $session) {
        [$status, $response] = logs_test_request('admin_get_logs.php', [], $session);
        verify_logs_endpoint($status === 200 && $response['success'] && $response['type'] === 'authentication', 'Login & Security Logs is not the default.');
    }
    foreach (['system', 'authentication', 'activity'] as $type) {
        [$status, $response] = logs_test_request('admin_get_logs.php', ['type' => $type, 'limit' => 100], $admin);
        verify_logs_endpoint($status === 200 && $response['type'] === $type, 'Category selection failed.');
        foreach ($response['logs'] as $row) verify_logs_endpoint(in_array($row['role'], ['admin', 'customer'], true) && $row['log_type'] === $type, 'Administrator received a restricted event.');
        foreach (['super_admin', 'system'] as $role) {
            [$status, $response] = logs_test_request('admin_get_logs.php', ['type' => $type, 'role' => $role], $admin);
            verify_logs_endpoint($status === 200 && $response['pagination']['total'] === 0, 'Role filtering bypassed permissions.');
        }
        [$status, $response] = logs_test_request('admin_get_logs.php', ['type' => $type, 'limit' => 1], $superAdmin);
        $table = ['system' => 'system_logs', 'authentication' => 'authentication_logs', 'activity' => 'activity_logs'][$type];
        $expected = (int)mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS n FROM $table"))['n'];
        verify_logs_endpoint($status === 200 && $response['pagination']['total'] === $expected, 'Super-admin totals do not match stored history.');
    }
    [$status, $own] = logs_test_request('customer_get_logs.php', ['limit' => 100], $customer);
    verify_logs_endpoint($status === 200 && $own['success'], 'Customer log retrieval failed.');
    foreach ($own['logs'] as $row) verify_logs_endpoint($row['idNumber'] === $customerId && $row['role'] === 'customer' && $row['log_type'] === 'authentication', 'Customer default included another account or log type.');
    [$status, $activity] = logs_test_request('customer_get_logs.php', ['type' => 'activity', 'limit' => 100], $customer);
    verify_logs_endpoint($status === 200 && $activity['success'], 'Customer Activity Logs retrieval failed.');
    foreach ($activity['logs'] as $row) verify_logs_endpoint($row['idNumber'] === $customerId && $row['role'] === 'customer' && $row['log_type'] === 'activity', 'Customer Activity Logs included another account or log type.');
    [$status, $tampered] = logs_test_request('customer_get_logs.php', ['idNumber' => 'other-account', 'user_id' => 'other-account', 'role' => 'super_admin', 'type' => 'authentication', 'limit' => 100], $customer);
    verify_logs_endpoint($status === 200 && $own['logs'] === $tampered['logs'] && $own['pagination'] === $tampered['pagination'], 'Customer request parameters changed account scope.');
    foreach ([['type' => 'system'], ['type' => ['activity']], ['type' => 'unknown']] as $invalid) {
        [$status, $response] = logs_test_request('customer_get_logs.php', $invalid, $customer);
        verify_logs_endpoint($status === 400 && !$response['success'], 'Customer accessed an invalid or restricted log type.');
    }

    foreach ([['admin_get_logs.php', null], ['customer_get_logs.php', null], ['admin_get_logs.php', $customer], ['customer_get_logs.php', $admin], ['admin_get_logs.php', $inactive], ['customer_get_logs.php', $inactiveCustomer]] as $case) {
        [$status, $response] = logs_test_request($case[0], [], $case[1]);
        verify_logs_endpoint($status === 403 && !$response['success'], 'Unauthorized access was accepted.');
    }
    foreach ([['type' => 'activity_logs;DROP TABLE users'], ['type' => ['system']], ['role' => 'invalid'], ['date_from' => '2026-02-30']] as $invalid) {
        [$status, $response] = logs_test_request('admin_get_logs.php', $invalid, $superAdmin);
        verify_logs_endpoint($status === 400 && !$response['success'], 'Invalid request parameters were accepted.');
    }
    [$status, $page] = logs_test_request('admin_get_logs.php', ['type' => 'authentication', 'page' => 2, 'limit' => 1], $superAdmin);
    verify_logs_endpoint($status === 200 && count($page['logs']) === 1 && $page['pagination']['page'] === 2, 'Endpoint pagination failed.');
    $resultMessage = 'PASS: ' . $checks . " endpoint checks. No account or log records changed.\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'FAIL: ' . $e->getMessage() . "\n");
    $exitCode = 1;
} finally {
    foreach ($sessions as $id) {
        session_id($id);
        session_start();
        $_SESSION = [];
        session_destroy();
    }
}
if ($exitCode === 0) echo $resultMessage;
exit($exitCode);
