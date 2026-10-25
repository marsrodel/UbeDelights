<?php

/** Keep order history while removing account-specific credentials and privileges. */
function account_delete_with_history(mysqli $db, string $userId): void {
    $statements = [
        'UPDATE orders SET user_id = NULL WHERE user_id = ?',
        'DELETE FROM otp_requests WHERE idNumber = ?',
        'DELETE FROM admin_privileges WHERE idNumber = ?',
    ];

    // Older installations still have this table; current installations may not.
    $legacyTable = $db->query("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'login_otp' LIMIT 1");
    if (!$legacyTable) {
        throw new RuntimeException('Could not check legacy account data.');
    }
    if ($legacyTable->num_rows > 0) {
        $statements[] = 'DELETE FROM login_otp WHERE idNumber = ?';
    }

    foreach ($statements as $sql) {
        $stmt = $db->prepare($sql);
        if (!$stmt) throw new RuntimeException('Could not prepare account cleanup.');
        try {
            $stmt->bind_param('s', $userId);
            if (!$stmt->execute()) throw new RuntimeException('Could not clean up account data.');
        } finally {
            $stmt->close();
        }
    }

    $stmt = $db->prepare('DELETE FROM users WHERE user_id = ?');
    if (!$stmt) throw new RuntimeException('Could not prepare account deletion.');
    try {
        $stmt->bind_param('s', $userId);
        if (!$stmt->execute() || $stmt->affected_rows !== 1) {
            throw new RuntimeException('Could not delete the target account.');
        }
    } finally {
        $stmt->close();
    }
}
