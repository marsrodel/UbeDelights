<?php
session_start();
include __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['auth_user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

$password = $_POST['password'] ?? '';

if ($password === '') {
    echo json_encode(['success' => false, 'message' => 'Password is required.']);
    exit();
}

$userId = $_SESSION['auth_user_id'];
$escapedId = mysqli_real_escape_string($connect, $userId);
$result = mysqli_query($connect, "SELECT password_hash FROM users WHERE user_id = '$escapedId'");

if (!$result || mysqli_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
    exit();
}

$row = mysqli_fetch_assoc($result);

if (password_verify($password, $row['password_hash'])) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

mysqli_close($connect);
?>