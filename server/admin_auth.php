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

// Auto-cleanup expired staging accounts (once per session)
if (empty($_SESSION['staging_cleanup_done'])) {
    $expired = $connect->query("SELECT username, email FROM staging_accounts WHERE expires_at < NOW()");
    if ($expired && $expired->num_rows > 0) {
        require_once __DIR__ . '/mail_helper.php';
        while ($row = $expired->fetch_assoc()) {
            $mailSubject = 'Ube Delights - Account Expired';
            $mailBody = '<div style="font-family:Arial,sans-serif;max-width:500px;margin:0 auto;">'
                . '<h2 style="color:#6B21A8;">Account Invitation Expired</h2>'
                . '<p>Hello ' . htmlspecialchars($row['username']) . ',</p>'
                . '<p>Your admin-created account invitation has expired. The account was not completed within the 24-hour window.</p>'
                . '<p>Please contact your administrator if you would like to be re-invited.</p>'
                . '<p style="color:#888;font-size:12px;">This is an automated message from Ube Delights.</p>'
                . '</div>';
            mail_send_message($row['email'], $mailSubject, $mailBody);
        }
    }
    $connect->query("DELETE FROM staging_accounts WHERE expires_at < NOW()");
    $_SESSION['staging_cleanup_done'] = true;
}
