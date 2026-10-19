<?php
session_start();
include __DIR__ . '/db.php';
require_once __DIR__ . '/session_guard.php';

if (!isset($_SESSION['auth_user_id'])) {
    header('Location: ' . session_guard_login_url());
    exit();
}

if (isset($_SESSION['auth_status']) && $_SESSION['auth_status'] !== 'active') {
    session_guard_clear_session();
    header('Location: ' . session_guard_login_url('inactive'));
    exit();
}

session_guard_page_timeout();

if (isset($_SESSION['auth_role']) && in_array($_SESSION['auth_role'], ['admin', 'super_admin'])) {
    header('Location: ' . session_guard_app_url('/views/admin/dashboard.php'));
    exit();
}

$currentUser = [
    'id'       => $_SESSION['auth_user_id'],
    'username' => $_SESSION['auth_username'],
];
