<?php
session_start();
include __DIR__ . '/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['auth_user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit();
}

$userId = $_SESSION['auth_user_id'];

$firstName   = trim($_POST['first_name'] ?? '');
$middleName  = trim($_POST['middle_name'] ?? '');
$lastName    = trim($_POST['last_name'] ?? '');
$extension   = trim($_POST['extension_name'] ?? '');
$email       = trim($_POST['email'] ?? '');
$username    = trim($_POST['username'] ?? '');
$dob         = $_POST['date_of_birth'] ?? '';
$sex         = $_POST['sex'] ?? '';
$street      = trim($_POST['street'] ?? '');
$barangay    = trim($_POST['barangay'] ?? '');
$city        = trim($_POST['city_municipality'] ?? '');
$province    = trim($_POST['province'] ?? '');
$country     = trim($_POST['country'] ?? '');
$zipCode     = trim($_POST['zip_code'] ?? '');

$curPass     = $_POST['current_password'] ?? '';
$newPass     = $_POST['new_password'] ?? '';
$confirmPass = $_POST['confirm_password'] ?? '';

if ($firstName === '' || $lastName === '' || $email === '' || $username === '') {
    echo json_encode(['success' => false, 'message' => 'Required fields are empty.']);
    exit();
}

if (!$connect) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit();
}

$checkEmail = mysqli_query($connect, "SELECT user_id FROM users WHERE email = '" . mysqli_real_escape_string($connect, $email) . "' AND user_id != '" . mysqli_real_escape_string($connect, $userId) . "'");
if ($checkEmail && mysqli_num_rows($checkEmail) > 0) {
    echo json_encode(['success' => false, 'message' => 'Email is already in use.']);
    exit();
}

$checkUsername = mysqli_query($connect, "SELECT user_id FROM users WHERE username = '" . mysqli_real_escape_string($connect, $username) . "' AND user_id != '" . mysqli_real_escape_string($connect, $userId) . "'");
if ($checkUsername && mysqli_num_rows($checkUsername) > 0) {
    echo json_encode(['success' => false, 'message' => 'Username is already taken.']);
    exit();
}

$passwordSet = '';
if ($newPass !== '') {
    if ($curPass === '') {
        echo json_encode(['success' => false, 'message' => 'Current password is required to set a new password.']);
        exit();
    }
    $row = mysqli_fetch_assoc(mysqli_query($connect, "SELECT password_hash FROM users WHERE user_id = '" . mysqli_real_escape_string($connect, $userId) . "'"));
    if (!password_verify($curPass, $row['password_hash'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
        exit();
    }
    if ($newPass !== $confirmPass) {
        echo json_encode(['success' => false, 'message' => 'New password and confirmation do not match.']);
        exit();
    }
    $hashed = password_hash($newPass, PASSWORD_DEFAULT);
    $passwordSet = ", password_hash = '" . mysqli_real_escape_string($connect, $hashed) . "'";
}

$sql = "UPDATE users SET
    first_name = '" . mysqli_real_escape_string($connect, $firstName) . "',
    middle_name = '" . mysqli_real_escape_string($connect, $middleName) . "',
    last_name = '" . mysqli_real_escape_string($connect, $lastName) . "',
    extension_name = '" . mysqli_real_escape_string($connect, $extension) . "',
    email = '" . mysqli_real_escape_string($connect, $email) . "',
    username = '" . mysqli_real_escape_string($connect, $username) . "',
    date_of_birth = '" . mysqli_real_escape_string($connect, $dob) . "',
    sex = '" . mysqli_real_escape_string($connect, $sex) . "',
    street = '" . mysqli_real_escape_string($connect, $street) . "',
    barangay = '" . mysqli_real_escape_string($connect, $barangay) . "',
    city_municipality = '" . mysqli_real_escape_string($connect, $city) . "',
    province = '" . mysqli_real_escape_string($connect, $province) . "',
    country = '" . mysqli_real_escape_string($connect, $country) . "',
    zip_code = '" . mysqli_real_escape_string($connect, $zipCode) . "',
    updated_at = NOW()
    " . $passwordSet . "
    WHERE user_id = '" . mysqli_real_escape_string($connect, $userId) . "'";

if (mysqli_query($connect, $sql)) {
    $_SESSION['auth_username'] = $username;
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile.']);
}
