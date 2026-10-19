<?php
require_once __DIR__ . '/../server/session_guard.php';

$checks = [
    session_guard_timeout_seconds() === 300,
    session_guard_is_expired(1000, 1299) === false,
    session_guard_is_expired(1000, 1300) === true,
    session_guard_seconds_since_activity(1000, 1299) === 299,
];

$_SERVER['SCRIPT_NAME'] = '/UbeDelights/views/admin/dashboard.php';
$checks[] = session_guard_login_url('timeout') === '/UbeDelights/views/login.php?error=timeout';
$_SERVER['SCRIPT_NAME'] = '/UbeDelights/server/session_activity.php';
$checks[] = session_guard_login_url() === '/UbeDelights/views/login.php';

if (in_array(false, $checks, true)) {
    fwrite(STDERR, "Session timeout checks failed.\n");
    exit(1);
}

echo "PASS: 299 seconds remains active, 300 seconds expires, and login redirects are path-safe.", PHP_EOL;
