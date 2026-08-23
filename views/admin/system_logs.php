<?php
session_start();

// Temporary bypass — no backend auth yet
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'Admin';
    $_SESSION['role'] = 'super_admin';
}

$currentUser = [
    'username' => $_SESSION['username'] ?? 'Admin',
    'role' => $_SESSION['role'] ?? 'admin'
];

$mockLogs = [
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'LOGIN', 'description' => 'User windy.sagaad logged in successfully', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-23 19:02:49'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'LOGIN', 'description' => 'User windy.sagaad logged in successfully', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-23 14:52:42'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'LOGIN', 'description' => 'User windy.sagaad logged in successfully', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-23 14:50:22'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'LOGIN', 'description' => 'User windy.sagaad logged in successfully', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-23 13:37:22'],
    ['user_name' => 'marsrodel', 'idNo' => '2023-0122', 'user_role' => 'customer', 'action' => 'FAILED_LOGIN', 'description' => "Failed login attempt for user 'marsrodel' (Invalid password)", 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-23 12:11:45'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'LOGIN', 'description' => 'User windy.sagaad logged in successfully', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-23 12:12:28'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'FAILED_LOGIN', 'description' => "Failed login attempt for user 'windy.sagaad' (Invalid password)", 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-21 22:48:27'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'FAILED_LOGIN', 'description' => "Failed login attempt for user 'windy.sagaad' (Invalid password)", 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-21 22:48:20'],
    ['user_name' => 'admin_user', 'idNo' => '2023-0100', 'user_role' => 'super_admin', 'action' => 'LOGIN', 'description' => 'User admin_user logged in successfully', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-21 18:30:15'],
    ['user_name' => 'admin_user', 'idNo' => '2023-0100', 'user_role' => 'super_admin', 'action' => 'CREATE_USER', 'description' => 'Created new user account for ana.ramos', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-21 18:35:42'],
    ['user_name' => 'admin_user', 'idNo' => '2023-0100', 'user_role' => 'super_admin', 'action' => 'CREATE_USER', 'description' => 'Created new user account for carlos.m', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-21 18:40:10'],
    ['user_name' => 'admin_user', 'idNo' => '2023-0100', 'user_role' => 'super_admin', 'action' => 'APPROVE_USER', 'description' => 'Approved pending user liza.santos', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-21 19:15:33'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'BLOCK_USER', 'description' => 'Blocked user juan.dc', 'device' => 'Windows', 'browser' => 'Edge', 'ip_address' => '192.168.1.15', 'created_at' => '2026-08-20 16:22:18'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'UNBLOCK_USER', 'description' => 'Unblocked user juan.dc', 'device' => 'Windows', 'browser' => 'Edge', 'ip_address' => '192.168.1.15', 'created_at' => '2026-08-20 16:30:05'],
    ['user_name' => 'ana.ramos', 'idNo' => '2025-0012', 'user_role' => 'customer', 'action' => 'LOGIN', 'description' => 'User ana.ramos logged in successfully', 'device' => 'macOS', 'browser' => 'Safari', 'ip_address' => '192.168.1.42', 'created_at' => '2026-08-20 10:15:30'],
    ['user_name' => 'ana.ramos', 'idNo' => '2025-0012', 'user_role' => 'customer', 'action' => 'LOGOUT', 'description' => 'User ana.ramos logged out', 'device' => 'macOS', 'browser' => 'Safari', 'ip_address' => '192.168.1.42', 'created_at' => '2026-08-20 11:05:12'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'UPDATE_USER', 'description' => 'Updated user carlo.mendoza (password updated)', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-19 14:20:45'],
    ['user_name' => 'carlos.m', 'idNo' => '2025-0015', 'user_role' => 'customer', 'action' => 'LOGIN', 'description' => 'User carlos.m logged in successfully', 'device' => 'Android', 'browser' => 'Chrome', 'ip_address' => '10.0.0.55', 'created_at' => '2026-08-19 09:30:00'],
    ['user_name' => 'carlos.m', 'idNo' => '2025-0015', 'user_role' => 'customer', 'action' => 'LOGOUT', 'description' => 'User carlos.m logged out', 'device' => 'Android', 'browser' => 'Chrome', 'ip_address' => '10.0.0.55', 'created_at' => '2026-08-19 10:15:22'],
    ['user_name' => 'admin_user', 'idNo' => '2023-0100', 'user_role' => 'super_admin', 'action' => 'DELETE_USER', 'description' => 'Deleted user account for test.user', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-19 11:45:08'],
    ['user_name' => 'admin_user', 'idNo' => '2023-0100', 'user_role' => 'super_admin', 'action' => 'REJECT_USER', 'description' => 'Rejected pending user test.reject', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-19 11:50:30'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'LOGIN', 'description' => 'User windy.sagaad logged in successfully', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-18 08:20:15'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'CREATE_USER', 'description' => 'Created new user account for liza.santos', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-18 08:25:40'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'UPDATE_USER', 'description' => 'Updated user ana.ramos (email changed)', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-18 09:10:55'],
    ['user_name' => 'liza.santos', 'idNo' => '2025-0018', 'user_role' => 'customer', 'action' => 'LOGIN', 'description' => 'User liza.santos logged in successfully', 'device' => 'Windows', 'browser' => 'Edge', 'ip_address' => '192.168.1.88', 'created_at' => '2026-08-18 15:30:22'],
    ['user_name' => 'liza.santos', 'idNo' => '2025-0018', 'user_role' => 'customer', 'action' => 'LOGOUT', 'description' => 'User liza.santos logged out', 'device' => 'Windows', 'browser' => 'Edge', 'ip_address' => '192.168.1.88', 'created_at' => '2026-08-18 16:20:10'],
    ['user_name' => 'admin_user', 'idNo' => '2023-0100', 'user_role' => 'super_admin', 'action' => 'LOGIN', 'description' => 'User admin_user logged in successfully', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-17 07:45:30'],
    ['user_name' => 'admin_user', 'idNo' => '2023-0100', 'user_role' => 'super_admin', 'action' => 'BLOCK_USER', 'description' => 'Blocked user grace.lim', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-17 08:15:20'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'FAILED_LOGIN', 'description' => "Failed login attempt for user 'windy.sagaad' (Invalid password)", 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-17 10:05:12'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'LOGIN', 'description' => 'User windy.sagaad logged in successfully', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-17 10:06:45'],
    ['user_name' => 'marsrodel', 'idNo' => '2023-0122', 'user_role' => 'customer', 'action' => 'LOGIN', 'description' => 'User marsrodel logged in successfully', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '192.168.1.200', 'created_at' => '2026-08-16 14:30:00'],
    ['user_name' => 'marsrodel', 'idNo' => '2023-0122', 'user_role' => 'customer', 'action' => 'LOGOUT', 'description' => 'User marsrodel logged out', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '192.168.1.200', 'created_at' => '2026-08-16 15:10:33'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'APPROVE_USER', 'description' => 'Approved pending user paolo.b', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-16 11:20:18'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'UPDATE_USER', 'description' => 'Updated user ana.ramos (profile completed)', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-16 11:25:42'],
    ['user_name' => 'admin_user', 'idNo' => '2023-0100', 'user_role' => 'super_admin', 'action' => 'LOGOUT', 'description' => 'User admin_user logged out', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-15 17:50:10'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'LOGOUT', 'description' => 'User windy.sagaad logged out', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-15 17:45:25'],
    ['user_name' => 'carlos.m', 'idNo' => '2025-0015', 'user_role' => 'customer', 'action' => 'FAILED_LOGIN', 'description' => "Failed login attempt for user 'carlos.m' (Account locked)", 'device' => 'Android', 'browser' => 'Chrome', 'ip_address' => '10.0.0.55', 'created_at' => '2026-08-15 09:12:08'],
    ['user_name' => 'carlos.m', 'idNo' => '2025-0015', 'user_role' => 'customer', 'action' => 'FAILED_LOGIN', 'description' => "Failed login attempt for user 'carlos.m' (Invalid password)", 'device' => 'Android', 'browser' => 'Chrome', 'ip_address' => '10.0.0.55', 'created_at' => '2026-08-15 09:11:55'],
    ['user_name' => 'carlos.m', 'idNo' => '2025-0015', 'user_role' => 'customer', 'action' => 'FAILED_LOGIN', 'description' => "Failed login attempt for user 'carlos.m' (Invalid password)", 'device' => 'Android', 'browser' => 'Chrome', 'ip_address' => '10.0.0.55', 'created_at' => '2026-08-15 09:11:40'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'UNBLOCK_USER', 'description' => 'Unblocked user grace.lim', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-15 10:30:22'],
    ['user_name' => 'admin_user', 'idNo' => '2023-0100', 'user_role' => 'super_admin', 'action' => 'CREATE_USER', 'description' => 'Created new user account for paolo.b', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-15 08:00:15'],
    ['user_name' => 'admin_user', 'idNo' => '2023-0100', 'user_role' => 'super_admin', 'action' => 'DELETE_USER', 'description' => 'Deleted user account for inactive.user', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-15 08:10:45'],
    ['user_name' => 'ana.ramos', 'idNo' => '2025-0012', 'user_role' => 'customer', 'action' => 'LOGIN', 'description' => 'User ana.ramos logged in successfully', 'device' => 'macOS', 'browser' => 'Safari', 'ip_address' => '192.168.1.42', 'created_at' => '2026-08-22 08:15:30'],
    ['user_name' => 'ana.ramos', 'idNo' => '2025-0012', 'user_role' => 'customer', 'action' => 'LOGOUT', 'description' => 'User ana.ramos logged out', 'device' => 'macOS', 'browser' => 'Safari', 'ip_address' => '192.168.1.42', 'created_at' => '2026-08-22 09:05:12'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'CREATE_USER', 'description' => 'Created new user account for miguel.t', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-22 10:20:45'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'APPROVE_USER', 'description' => 'Approved pending user miguel.t', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-22 10:25:18'],
    ['user_name' => 'liza.santos', 'idNo' => '2025-0018', 'user_role' => 'customer', 'action' => 'FAILED_LOGIN', 'description' => "Failed login attempt for user 'liza.santos' (Invalid password)", 'device' => 'Windows', 'browser' => 'Edge', 'ip_address' => '192.168.1.88', 'created_at' => '2026-08-22 14:30:55'],
    ['user_name' => 'liza.santos', 'idNo' => '2025-0018', 'user_role' => 'customer', 'action' => 'LOGIN', 'description' => 'User liza.santos logged in successfully', 'device' => 'Windows', 'browser' => 'Edge', 'ip_address' => '192.168.1.88', 'created_at' => '2026-08-22 14:32:10'],
    ['user_name' => 'admin_user', 'idNo' => '2023-0100', 'user_role' => 'super_admin', 'action' => 'LOGIN', 'description' => 'User admin_user logged in successfully', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-22 16:00:30'],
    ['user_name' => 'admin_user', 'idNo' => '2023-0100', 'user_role' => 'super_admin', 'action' => 'UPDATE_USER', 'description' => 'Updated user windy.sagaad (role changed to admin)', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-22 16:10:15'],
    ['user_name' => 'marsrodel', 'idNo' => '2023-0122', 'user_role' => 'customer', 'action' => 'FAILED_LOGIN', 'description' => "Failed login attempt for user 'marsrodel' (Invalid password)", 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '192.168.1.200', 'created_at' => '2026-08-22 18:45:22'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'BLOCK_USER', 'description' => 'Blocked user carlos.m', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-23 08:10:30'],
    ['user_name' => 'windy.sagaad', 'idNo' => '2023-0123', 'user_role' => 'admin', 'action' => 'UNBLOCK_USER', 'description' => 'Unblocked user carlos.m', 'device' => 'Windows', 'browser' => 'Chrome', 'ip_address' => '127.0.0.1', 'created_at' => '2026-08-23 08:15:45'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - Ube Delights Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin_security.css?v=1.2">
    <style>
        .info-label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; }
        .filters-card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 14px 18px; margin-bottom: 8px; }
        .filters-title { font-size: 1rem; font-weight: 700; color: var(--text-primary); margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .filters-title i { color: var(--accent); }
        .filters-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 12px; align-items: end; }
        .filter-field label { display: block; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; }
        .filter-field input,
        .filter-field select {
            width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm);
            font-family: var(--font-body); font-size: 0.85rem; color: var(--text-primary); background: var(--bg-main);
            transition: var(--transition); outline: none;
        }
        .filter-field input:focus,
        .filter-field select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(118, 75, 162, 0.1); }
        .filter-buttons { display: flex; gap: 8px; align-items: end; }
        .section-header { display: flex; align-items: center; gap: 10px; margin-bottom: 0; }
        .section-header h2 { margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-primary); }
        .section-header .count { font-size: 0.85rem; color: var(--text-muted); font-weight: 500; }
        .log-user-cell { font-weight: 600; color: var(--text-primary); }
        .log-id-cell { font-size: 11px; color: var(--text-muted); }
        .role-pill { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
        .role-admin { background: #dcfce7; color: #15803d; }
        .role-super_admin { background: #dbeafe; color: #1d4ed8; }
        .role-customer { background: #fef3c7; color: #b45309; }
        .activity-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; letter-spacing: 0.03em; white-space: nowrap; }
        .activity-login { background: #dcfce7; color: #166534; }
        .activity-logout { background: #fee2e2; color: #991b1b; }
        .activity-failed_login { background: #fef3c7; color: #92400e; }
        .activity-create { background: #dbeafe; color: #1e40af; }
        .activity-update { background: #ede9fe; color: #5b21b6; }
        .activity-block { background: #fee2e2; color: #dc2626; }
        .activity-unblock { background: #dbeafe; color: #1d4ed8; }
        .activity-delete { background: #fee2e2; color: #991b1b; }
        .activity-approve { background: #dcfce7; color: #065f46; }
        .activity-reject { background: #fee2e2; color: #991b1b; }
        .activity-desc { font-size: 11px; color: var(--text-muted); margin-top: 3px; max-width: 200px; white-space: normal; line-height: 1.4; }
        .ip-badge { background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 12px; color: var(--text-secondary); font-family: monospace; }
        .device-cell { font-size: 13px; color: var(--text-secondary); }
        .pagination-bar { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; padding: 16px 20px; border-top: 1px solid var(--border); }
        .pagination-info { font-size: 13px; color: var(--text-muted); font-weight: 500; }
        .pagination-info strong { color: var(--text-primary); }
        .per-page-group { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-secondary); font-weight: 600; }
        .per-page-group select { border: 1px solid var(--border); border-radius: 8px; padding: 5px 10px; font-size: 13px; color: var(--text-primary); background: #fff; cursor: pointer; font-weight: 600; }
        .pagination { display: flex; gap: 6px; flex-wrap: wrap; }
        .pagination-link { display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px; padding: 0 8px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; color: var(--text-secondary); border: 1px solid var(--border); background: #fff; cursor: pointer; transition: var(--transition); }
        .pagination-link:hover { border-color: var(--accent); color: var(--accent); }
        .pagination-link.current { background: var(--primary-gradient); color: #fff; border-color: transparent; }
        .pagination-link.disabled { opacity: 0.4; pointer-events: none; }
        @media (max-width: 900px) { .filters-grid { grid-template-columns: 1fr 1fr; } }
        @media (max-width: 600px) { .filters-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="admin-body">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <img src="../../images/logo.png" alt="Ube Delights" class="sidebar-logo">
            <div>
                <h2>Ube Delights</h2>
                <span class="sidebar-tag">Admin Panel</span>
            </div>
        </div>

        <div class="sidebar-profile">
            <div class="admin-chip">
                <div class="admin-avatar">AU</div>
                <div class="admin-chip-info">
                    <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong>
                    <small><?php echo ucfirst($_SESSION['role'] ?? 'admin'); ?></small>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a onclick="getAdminDashboard()" class="sidebar-link"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
            <a onclick="getAdminProducts()" class="sidebar-link"><i class="fa-solid fa-box"></i><span>Products</span></a>
            <a onclick="getAdminOrders()" class="sidebar-link"><i class="fa-solid fa-bag-shopping"></i><span>Orders</span></a>
            <a onclick="getAdminUserManagement()" class="sidebar-link"><i class="fa-solid fa-users-cog"></i><span>User Management</span></a>
            <a onclick="getAdminPendingApprovals()" class="sidebar-link"><i class="fa-solid fa-user-clock"></i><span>Pending Approvals</span></a>
            <a onclick="getAdminSystemLogs()" class="sidebar-link active"><i class="fa-solid fa-list-alt"></i><span>System Logs</span></a>
        </nav>

        <div class="sidebar-footer">
            <a onclick="getAdminLogout()" class="sidebar-logout"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <h1>System Logs</h1>
            <div class="topbar-right">
                <span class="topbar-date"><i class="fa-solid fa-calendar-days"></i><?php echo date('F j, Y'); ?></span>
            </div>
        </header>

        <main class="admin-content">
            <div class="filters-card">
                <div class="filters-title"><i class="fa-solid fa-filter"></i> Filters</div>
                <div class="filters-grid">
                    <div class="filter-field">
                        <label>User Name</label>
                        <input type="text" id="logsSearch" placeholder="Username...">
                    </div>
                    <div class="filter-field">
                        <label>Role</label>
                        <select id="logsRoleFilter">
                            <option value="">All Roles</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="admin">Admin</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label>From Date</label>
                        <input type="date" id="logsFromDate">
                    </div>
                    <div class="filter-field">
                        <label>To Date</label>
                        <input type="date" id="logsToDate">
                    </div>
                    <div class="filter-buttons">
                        <button class="btn-primary" id="btnApplyLogsFilter" style="padding:10px 18px; font-size:0.85rem;"><i class="fa-solid fa-filter"></i> Apply</button>
                        <button class="btn-outline" id="btnClearLogsFilter" style="padding:10px 18px; font-size:0.85rem;"><i class="fa-solid fa-xmark"></i> Clear</button>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="section-header">
                        <h2><i class="fa-solid fa-clipboard-list" style="color:var(--accent);"></i> Activity Records</h2>
                        <span class="count" id="logsTotalCount">(<?php echo count($mockLogs); ?> total)</span>
                    </div>
                </div>
                <div class="table-container">
                    <table class="data-table" id="logsTable">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Device / Browser</th>
                                <th>IP Address</th>
                                <th>Activity</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody">
                        </tbody>
                    </table>
                </div>
                <div class="pagination-bar" id="logsPaginationContainer">
                </div>
            </div>

            <div class="empty-state" id="emptyLogs" style="display:none;">
                <div class="empty-icon">📋</div>
                <h3>No logs found</h3>
                <p>No logs match your current filters.</p>
            </div>
        </main>
    </div>

    <div class="toast" id="toast"></div>

    <script src="../../javascript/admin-routing.js"></script>
    <script src="../../javascript/admin_security.js"></script>
    <script src="../../javascript/admin_logs.js"></script>
    <script>
        var allLogs = <?php echo json_encode($mockLogs); ?>;
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof initSystemLogs === 'function') {
                initSystemLogs(allLogs);
            }
        });
    </script>
</body>
</html>
