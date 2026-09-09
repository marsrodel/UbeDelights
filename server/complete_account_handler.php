<?php
include __DIR__ . '/db.php';
include __DIR__ . '/mail_helper.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

header('Content-Type: application/json');

$userId = $_SESSION['auth_user_id'] ?? null;
$status = $_SESSION['auth_status'] ?? null;

if (!$userId || $status !== 'incomplete') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {

    case 'verify_otp':
        $otpCode = trim($_POST['otp_code'] ?? '');
        if ($otpCode === '') {
            echo json_encode(['success' => false, 'message' => 'Please enter the 6-digit OTP.']);
            exit();
        }

        $sql = "SELECT id, otp_hash, expires_at, attempts, used FROM password_reset_otp WHERE idNumber = ? AND used = 0 ORDER BY id DESC LIMIT 1";
        $stmt = mysqli_prepare($connect, $sql);
        mysqli_stmt_bind_param($stmt, 's', $userId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);

        if (!$row) {
            echo json_encode(['success' => false, 'message' => 'No active OTP found. Please request a new one.']);
            exit();
        }

        // Check expiry
        if (strtotime($row['expires_at']) < time()) {
            mysqli_query($connect, "UPDATE password_reset_otp SET used = 1 WHERE id = {$row['id']}");
            echo json_encode(['success' => false, 'message' => 'OTP has expired. Please resend.']);
            exit();
        }

        // Check attempts
        if ($row['attempts'] >= 5) {
            mysqli_query($connect, "UPDATE password_reset_otp SET used = 1 WHERE id = {$row['id']}");
            echo json_encode(['success' => false, 'message' => 'Too many failed attempts. Please request a new OTP.']);
            exit();
        }

        // Increment attempts
        mysqli_query($connect, "UPDATE password_reset_otp SET attempts = attempts + 1 WHERE id = {$row['id']}");

        // Verify
        if (!password_verify($otpCode, $row['otp_hash'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid OTP. Please try again.']);
            exit();
        }

        // Mark as used
        mysqli_query($connect, "UPDATE password_reset_otp SET used = 1 WHERE id = {$row['id']}");

        // Set session flag
        $_SESSION['otp_verified'] = true;

        echo json_encode(['success' => true, 'message' => 'OTP verified successfully.']);
        break;

    case 'resend_otp':
        // Invalidate old OTPs
        mysqli_query($connect, "UPDATE password_reset_otp SET used = 1 WHERE idNumber = '" . mysqli_real_escape_string($connect, $userId) . "' AND used = 0");

        // Fetch user email
        $userRes = mysqli_query($connect, "SELECT email FROM users WHERE user_id = '" . mysqli_real_escape_string($connect, $userId) . "' LIMIT 1");
        $userData = $userRes ? mysqli_fetch_assoc($userRes) : null;

        if (!$userData) {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
            exit();
        }

        // Generate new OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpHash = password_hash($otp, PASSWORD_BCRYPT);
        $expiry = date('Y-m-d H:i:s', time() + 300);

        mysqli_query($connect, "INSERT INTO password_reset_otp (idNumber, otp_hash, expires_at, used) VALUES ('" . mysqli_real_escape_string($connect, $userId) . "', '" . mysqli_real_escape_string($connect, $otpHash) . "', '$expiry', 0)");

        // Send email
        $subject = 'Ube Delights - Account Verification';
        $emailBody = '<div style="font-family:Arial,sans-serif;max-width:480px;">'
            . '<h2>Account Verification</h2>'
            . '<p>Hello ' . htmlspecialchars($userData['username']) . ',</p>'
            . '<p>Your new one-time password (OTP) for completing your Ube Delights account setup is:</p>'
            . '<p style="font-size:28px;font-weight:bold;letter-spacing:4px;">' . $otp . '</p>'
            . '<p>This code expires in <strong>5 minutes</strong>. Do not share it with anyone.</p>'
            . '</div>';
        mail_send_message($userData['email'], $subject, $emailBody, $otp);

        // Update session expiry
        $_SESSION['otp_expiry'] = time() + 300;

        echo json_encode(['success' => true, 'message' => 'OTP resent successfully.']);
        break;

    case 'save_personal':
        if (empty($_SESSION['otp_verified'])) {
            echo json_encode(['success' => false, 'message' => 'Please verify OTP first.']);
            exit();
        }

        $fields = [
            'fname' => trim($_POST['fname'] ?? ''),
            'mname' => trim($_POST['mname'] ?? ''),
            'lname' => trim($_POST['lname'] ?? ''),
            'ename' => trim($_POST['ename'] ?? ''),
            'bday' => trim($_POST['bday'] ?? ''),
            'sex' => trim($_POST['sex'] ?? ''),
            'street' => trim($_POST['street'] ?? ''),
            'brgy' => trim($_POST['brgy'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'province' => trim($_POST['province'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'zipcode' => trim($_POST['zipcode'] ?? '')
        ];

        // Required fields check
        $required = ['fname', 'lname', 'bday', 'sex', 'street', 'brgy', 'city', 'province', 'country', 'zipcode'];
        foreach ($required as $f) {
            if ($fields[$f] === '') {
                echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $f)) . ' is required.']);
                exit();
            }
        }

        // Calculate age
        $dob = new DateTime($fields['bday']);
        $now = new DateTime();
        $age = $dob->diff($now)->y;
        if ($age < 0 || $dob > $now) {
            echo json_encode(['success' => false, 'message' => 'Invalid date of birth.']);
            exit();
        }

        $fields['age'] = $age;

        // Store in session
        $_SESSION['incomplete_personal'] = $fields;

        echo json_encode(['success' => true, 'message' => 'Personal information saved.']);
        break;

    case 'complete_setup':
        if (empty($_SESSION['otp_verified'])) {
            echo json_encode(['success' => false, 'message' => 'Please verify OTP first.']);
            exit();
        }
        if (empty($_SESSION['incomplete_personal'])) {
            echo json_encode(['success' => false, 'message' => 'Please complete personal information first.']);
            exit();
        }

        $password = $_POST['pass'] ?? '';
        $q1 = trim($_POST['q1'] ?? '');
        $a1 = trim($_POST['a1'] ?? '');
        $q2 = trim($_POST['q2'] ?? '');
        $a2 = trim($_POST['a2'] ?? '');
        $q3 = trim($_POST['q3'] ?? '');
        $a3 = trim($_POST['a3'] ?? '');

        // Validate password
        if (strlen($password) < 8) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
            exit();
        }
        if (strlen($password) > 50) {
            echo json_encode(['success' => false, 'message' => 'Password must not exceed 50 characters.']);
            exit();
        }
        if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[^A-Za-z0-9]/', $password)) {
            echo json_encode(['success' => false, 'message' => 'Password must include uppercase, lowercase, number, and special character.']);
            exit();
        }

        // Validate security questions
        if ($q1 === '' || $q2 === '' || $q3 === '' || $a1 === '' || $a2 === '' || $a3 === '') {
            echo json_encode(['success' => false, 'message' => 'All security questions and answers are required.']);
            exit();
        }
        if (count(array_unique([$q1, $q2, $q3])) < 3) {
            echo json_encode(['success' => false, 'message' => 'Security questions must be unique.']);
            exit();
        }

        $personal = $_SESSION['incomplete_personal'];
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $h1 = password_hash($a1, PASSWORD_DEFAULT);
        $h2 = password_hash($a2, PASSWORD_DEFAULT);
        $h3 = password_hash($a3, PASSWORD_DEFAULT);

        // Update user record
        $sql = "UPDATE users SET
            first_name = ?, middle_name = ?, last_name = ?, extension_name = ?,
            date_of_birth = ?, age = ?, sex = ?,
            street = ?, barangay = ?, city_municipality = ?, province = ?, country = ?, zip_code = ?,
            password_hash = ?,
            q1 = ?, a1 = ?, q2 = ?, a2 = ?, q3 = ?, a3 = ?,
            status = 'active', is_active = 1
            WHERE user_id = ?";

        $stmt = mysqli_prepare($connect, $sql);
        $fn = $personal['fname'];
        $mn = $personal['mname'];
        $ln = $personal['lname'];
        $en = $personal['ename'];
        $dob = $personal['bday'];
        $age = (int)$personal['age'];
        $sex = $personal['sex'];
        $st = $personal['street'];
        $brgy = $personal['brgy'];
        $city = $personal['city'];
        $prov = $personal['province'];
        $ctry = $personal['country'];
        $zip = $personal['zipcode'];

        mysqli_stmt_bind_param($stmt, 'sssssisssssssissssss',
            $fn, $mn, $ln, $en, $dob, $age, $sex,
            $st, $brgy, $city, $prov, $ctry, $zip,
            $passwordHash, $q1, $h1, $q2, $h2, $q3, $h3, $userId
        );

        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!$ok) {
            echo json_encode(['success' => false, 'message' => 'Failed to update account. Please try again.']);
            exit();
        }

        // Fetch updated user data for session
        $userRes = mysqli_query($connect, "SELECT user_id, username, first_name, last_name, role, status FROM users WHERE user_id = '" . mysqli_real_escape_string($connect, $userId) . "' LIMIT 1");
        $userData = $userRes ? mysqli_fetch_assoc($userRes) : null;

        // Clear incomplete session data
        unset($_SESSION['otp_verified'], $_SESSION['incomplete_personal'], $_SESSION['otp_expiry']);

        // Set full auth session
        $_SESSION['auth_user_id'] = $userId;
        $_SESSION['auth_username'] = $userData['username'];
        $_SESSION['auth_role'] = $userData['role'];
        $_SESSION['auth_status'] = $userData['status'];
        $_SESSION['auth_first_name'] = $userData['first_name'] ?? '';
        $_SESSION['auth_last_name'] = $userData['last_name'] ?? '';

        log_activity('login', $userData['username'] . ' completed account setup and logged in', 'Authentication', $userId, $userData['username']);

        // Determine redirect based on role
        $redirect = in_array($userData['role'], ['admin', 'super_admin']) ? './admin/dashboard.php' : './index.php';

        echo json_encode(['success' => true, 'message' => 'Account setup completed successfully!', 'redirect' => $redirect]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}
?>
