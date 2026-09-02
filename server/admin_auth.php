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
