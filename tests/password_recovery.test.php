<?php
require_once __DIR__ . '/../server/password_recovery.php';

function expect_recovery($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}

$connection = mysqli_connect('localhost', 'root', '', 'ube_delights_db');
expect_recovery($connection !== false, 'Test database is unavailable.');

// Temporary tables shadow the application tables only for this connection.
expect_recovery(mysqli_query($connection, 'CREATE TEMPORARY TABLE users (
    user_id VARCHAR(9) PRIMARY KEY, email VARCHAR(100), username VARCHAR(50),
    q1 VARCHAR(50), a1 VARCHAR(50), q2 VARCHAR(50), a2 VARCHAR(50), q3 VARCHAR(50), a3 VARCHAR(50),
    status VARCHAR(20), is_active TINYINT, is_incomplete TINYINT
)') !== false, 'Could not create temporary users table.');
expect_recovery(mysqli_query($connection, 'CREATE TEMPORARY TABLE staging_accounts (
    user_id VARCHAR(9) PRIMARY KEY, expires_at DATETIME NOT NULL
)') !== false, 'Could not create temporary staging table.');

$states = [
    ['9999-0001', 'pending', 0, 0, 'pending_id'],
    ['9999-0002', 'blocked', 0, 0, 'blocked_id'],
    ['9999-0003', 'incomplete', 0, 1, 'incomplete_id'],
    ['9999-0004', 'active', 1, 1, 'incomplete_id'],
    ['9999-0005', 'active', 0, 0, 'inactive_id'],
    ['9999-0006', 'rejected', 0, 0, 'inactive_id'],
    ['9999-0007', 'active', 1, 0, null],
    ['9999-0011', 'active', 1, null, 'incomplete_id'],
];
$insert = mysqli_prepare($connection, 'INSERT INTO users (user_id, email, username, q1, a1, q2, a2, q3, a3, status, is_active, is_incomplete) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
foreach ($states as [$id, $status, $isActive, $isIncomplete]) {
    $email = 'test@example.invalid';
    $username = 'test-user';
    $question = 'Question';
    $answer = 'Hashed answer';
    mysqli_stmt_bind_param($insert, 'ssssssssssii', $id, $email, $username, $question, $answer, $question, $answer, $question, $answer, $status, $isActive, $isIncomplete);
    expect_recovery(mysqli_stmt_execute($insert), 'Could not insert temporary user.');
}
mysqli_stmt_close($insert);

expect_recovery(mysqli_query($connection, "INSERT INTO staging_accounts VALUES ('9999-0008', DATE_ADD(NOW(), INTERVAL 1 HOUR)), ('9999-0009', DATE_SUB(NOW(), INTERVAL 1 HOUR))") !== false, 'Could not insert temporary staging accounts.');

foreach ($states as [$id, , , , $expectedError]) {
    $result = password_recovery_find_account($connection, $id);
    expect_recovery($result['error'] === $expectedError, "Unexpected eligibility for $id.");
    expect_recovery($result['user'] !== null, "Expected a users-table row for $id.");
}
expect_recovery(password_recovery_find_account($connection, '9999-0008')['error'] === 'incomplete_id', 'Unfinished staged account was allowed.');
expect_recovery(password_recovery_find_account($connection, '9999-0009')['error'] === 'expired_id', 'Expired staging invitation was not identified.');
expect_recovery(password_recovery_find_account($connection, '9999-0010')['error'] === 'unknown_id', 'Unknown ID was not rejected.');

expect_recovery(password_recovery_required_step([], 4) === 2, 'Password reset skipped OTP.');
expect_recovery(password_recovery_required_step(['fp_otp_verified' => true], 4) === 3, 'Password reset skipped security questions.');
expect_recovery(password_recovery_required_step(['fp_otp_verified' => true, 'fp_security_verified' => true], 4) === 4, 'Verified recovery could not reach password reset.');

$_SESSION = [
    'fp_user_id' => '9999-0007', 'fp_email' => 'test@example.invalid',
    'fp_otp_verified' => true, 'fp_security_verified' => true,
    'unrelated' => 'keep',
];
password_recovery_clear_session();
foreach (['fp_user_id', 'fp_otp_verified', 'fp_security_verified'] as $key) {
    expect_recovery(!isset($_SESSION[$key]), "Recovery session key $key was not cleared.");
}
expect_recovery($_SESSION['unrelated'] === 'keep', 'Unrelated session data was removed.');

mysqli_close($connection);
echo "PASS: recovery eligibility, staging expiry, step order, and session cleanup.\n";
