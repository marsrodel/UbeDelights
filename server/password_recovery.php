<?php

function password_recovery_clear_session() {
    unset(
        $_SESSION['fp_user_id'],
        $_SESSION['fp_email'],
        $_SESSION['fp_username'],
        $_SESSION['fp_qa'],
        $_SESSION['fp_otp_expiry'],
        $_SESSION['fp_otp_attempts'],
        $_SESSION['fp_otp_verified'],
        $_SESSION['fp_security_verified']
    );
}

function password_recovery_user_error($user) {
    if (!$user) return 'unknown_id';
    if ($user['status'] === 'blocked') return 'blocked_id';
    if ($user['status'] === 'pending') return 'pending_id';
    if ($user['status'] === 'incomplete' || $user['is_incomplete'] === null || (int)$user['is_incomplete'] !== 0) return 'incomplete_id';
    if ($user['status'] !== 'active' || (int)$user['is_active'] !== 1) return 'inactive_id';
    return null;
}

function password_recovery_required_step($session, $step) {
    if ($step >= 3 && empty($session['fp_otp_verified'])) return 2;
    if ($step >= 4 && empty($session['fp_security_verified'])) return 3;
    return $step;
}

function password_recovery_find_account($connect, $userId) {
    $stmt = mysqli_prepare($connect, 'SELECT user_id, email, username, q1, a1, q2, a2, q3, a3, status, is_active, is_incomplete FROM users WHERE user_id = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 's', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    if ($user) return ['user' => $user, 'error' => password_recovery_user_error($user)];

    $stmt = mysqli_prepare($connect, 'SELECT expires_at <= NOW() AS expired FROM staging_accounts WHERE user_id = ? LIMIT 1');
    mysqli_stmt_bind_param($stmt, 's', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $staging = $result ? mysqli_fetch_assoc($result) : null;
    mysqli_stmt_close($stmt);

    if ($staging) return ['user' => null, 'error' => (int)$staging['expired'] === 1 ? 'expired_id' : 'incomplete_id'];
    return ['user' => null, 'error' => 'unknown_id'];
}
