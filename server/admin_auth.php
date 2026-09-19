<?php
session_start();
include __DIR__ . '/db.php';

// Must be logged in
if (!isset($_SESSION['auth_user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Must be admin or super_admin
if (!isset($_SESSION['auth_role']) || !in_array($_SESSION['auth_role'], ['admin', 'super_admin'])) {
    header('Location: ../index.php');
    exit();
}

// Must be active (not blocked/pending/incomplete)
if (isset($_SESSION['auth_status']) && $_SESSION['auth_status'] !== 'active') {
    session_unset();
    session_destroy();
    header('Location: ../login.php?error=inactive');
    exit();
}

$currentUser = [
    'id'       => $_SESSION['auth_user_id'],
    'username' => $_SESSION['auth_username'],
    'role'     => $_SESSION['auth_role'],
];

// For super admins: refresh last_activity on each page load and verify still logged in
if ($_SESSION['auth_role'] === 'super_admin') {
    $uid = $_SESSION['auth_user_id'];
    $chk = mysqli_prepare($connect, "SELECT is_logged_in FROM users WHERE user_id = ?");
    mysqli_stmt_bind_param($chk, 's', $uid);
    mysqli_stmt_execute($chk);
    $chkRes = mysqli_stmt_get_result($chk);
    $chkRow = $chkRes ? mysqli_fetch_assoc($chkRes) : null;
    mysqli_stmt_close($chk);

    if ($chkRow && !$chkRow['is_logged_in']) {
        // Another super admin blocked/logged this one out
        session_unset();
        session_destroy();
        header('Location: ../views/login.php?error=inactive');
        exit();
    }

    // Update last_activity
    $upd = mysqli_prepare($connect, "UPDATE users SET last_activity = NOW() WHERE user_id = ?");
    mysqli_stmt_bind_param($upd, 's', $uid);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);
}
