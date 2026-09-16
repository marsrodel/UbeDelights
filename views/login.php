<?php
mysqli_report(MYSQLI_REPORT_OFF);
// Simple login: prepared statements + password_verify + basic lockout
include '../server/db.php';
require_once __DIR__ . '/../server/user_logger.php';
require_once __DIR__ . '/../server/mail_helper.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// If already authenticated, send to dashboard
if (isset($_SESSION['auth_user_id'])) {
    header('Location: ./index.php');
    exit();
}

// Legacy server-side lockout logic has been removed; JavaScript now handles
// all attempt counting and lockout timing on the client. If any old lock
// markers remain in the session, clear them.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_SESSION['lock_user_id'])) {
    unset($_SESSION['lock_user_id'], $_SESSION['lock_username']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = isset($_POST['login']) ? trim($_POST['login']) : (isset($_POST['username']) ? trim($_POST['username']) : '');
    $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
    if ($login === '' || $password === '') {
        header('Location: ./login.php?error=empty');
        exit();
    }

    // Check staging accounts (admin-created accounts awaiting first login activation)
    $stagingSql = "SELECT * FROM staging_accounts WHERE (BINARY username = ? OR email = ?) LIMIT 1";
    $stagingStmt = mysqli_prepare($connect, $stagingSql);
    mysqli_stmt_bind_param($stagingStmt, 'ss', $login, $login);
    mysqli_stmt_execute($stagingStmt);
    $stagingRes = mysqli_stmt_get_result($stagingStmt);
    $stagingAccount = $stagingRes ? mysqli_fetch_assoc($stagingRes) : null;
    mysqli_stmt_close($stagingStmt);

    if ($stagingAccount) {
        // Check if expired
        if (strtotime($stagingAccount['expires_at']) <= time()) {
            // Send expiration email
            $expiryMailSubject = 'Ube Delights - Account Activation Expired';
            $expiryMailBody = '<div style="font-family:Arial,sans-serif;max-width:500px;margin:0 auto;">'
                . '<h2 style="color:#dc2626;">Account Activation Expired</h2>'
                . '<p>Hello, your Ube Delights account (<strong>' . htmlspecialchars($stagingAccount['username']) . '</strong>) was created on <strong>' . date('F j, Y', strtotime($stagingAccount['created_at'])) . '</strong> but was not activated within the 48-hour window.</p>'
                . '<p>The account has been removed from the system.</p>'
                . '<p>Please contact an administrator to have your account re-created.</p>'
                . '<p style="color:#888;font-size:12px;">This is an automated message from Ube Delights.</p>'
                . '</div>';
            mail_send_message($stagingAccount['email'], $expiryMailSubject, $expiryMailBody);

            // Delete expired staging account
            $delStmt = mysqli_prepare($connect, "DELETE FROM staging_accounts WHERE id = ?");
            mysqli_stmt_bind_param($delStmt, 'i', $stagingAccount['id']);
            mysqli_stmt_execute($delStmt);
            mysqli_stmt_close($delStmt);

            log_activity('STAGING_EXPIRED', "Staging account for {$stagingAccount['username']} (ID: {$stagingAccount['user_id']}) expired and deleted", 'Authentication', null, $stagingAccount['username']);
            header('Location: ./login.php?error=staging_expired');
            exit();
        }

        // Not expired — verify password, then activate account
        if (!password_verify($password, $stagingAccount['password_hash'])) {
            log_activity('failed_login', 'Wrong password attempt (staging)', 'Authentication', $stagingAccount['user_id'], $stagingAccount['username']);
            $u = urlencode($login);
            header("Location: ./login.php?error=pass&u=$u");
            exit();
        }

        // Activate: move from staging to users
        $initialStatus = ($stagingAccount['role'] === 'super_admin') ? 'blocked' : 'incomplete';
        $isIncomplete = 1;

        $activateStmt = $connect->prepare("INSERT INTO users (user_id, username, first_name, middle_name, last_name, extension_name, date_of_birth, age, sex, email, password_hash, role, status, is_active, is_incomplete, street, barangay, city_municipality, province, country, zip_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?)");
        $activateStmt->bind_param("sssssssissssssssssss",
            $stagingAccount['user_id'], $stagingAccount['username'], $stagingAccount['first_name'],
            $stagingAccount['middle_name'], $stagingAccount['last_name'], $stagingAccount['extension_name'],
            $stagingAccount['date_of_birth'], $stagingAccount['age'], $stagingAccount['sex'],
            $stagingAccount['email'], $stagingAccount['password_hash'], $stagingAccount['role'],
            $initialStatus, $isIncomplete,
            $stagingAccount['street'], $stagingAccount['barangay'], $stagingAccount['city_municipality'],
            $stagingAccount['province'], $stagingAccount['country'], $stagingAccount['zip_code']
        );
        $activateStmt->execute();
        $activateStmt->close();

        // If role is admin, create privileges
        if ($stagingAccount['role'] === 'admin') {
            mysqli_query($connect, "INSERT INTO admin_privileges (idNumber, can_manage_registrations, can_update_accounts, can_request_deletion, can_block, can_reset_password) VALUES ('" . mysqli_real_escape_string($connect, $stagingAccount['user_id']) . "', 1, 1, 1, 1, 1)");
        }

        // Delete from staging
        $delStmt2 = mysqli_prepare($connect, "DELETE FROM staging_accounts WHERE id = ?");
        mysqli_stmt_bind_param($delStmt2, 'i', $stagingAccount['id']);
        mysqli_stmt_execute($delStmt2);
        mysqli_stmt_close($delStmt2);

        log_activity('STAGING_ACTIVATED', "Staging account for {$stagingAccount['username']} (ID: {$stagingAccount['user_id']}) activated via first login", 'Authentication', $stagingAccount['user_id'], $stagingAccount['username']);

        // Redirect to login to go through the normal incomplete/OTP flow
        header('Location: ./login.php');
        exit();
    }

    // 1) Find user by username (case-sensitive) or email
    // Use BINARY for username to enforce case sensitivity. Email remains as-is.
    $sqlUser = "SELECT user_id, username, first_name, last_name, email, password_hash, is_active, role, status FROM users WHERE (BINARY username = ? OR email = ?) LIMIT 1";
    $stmtUser = mysqli_prepare($connect, $sqlUser);
    mysqli_stmt_bind_param($stmtUser, 'ss', $login, $login);
    mysqli_stmt_execute($stmtUser);
    $resUser = mysqli_stmt_get_result($stmtUser);
    $user = $resUser ? mysqli_fetch_assoc($resUser) : null;
    mysqli_stmt_close($stmtUser);

    if (!$user) {
        log_activity('failed_login', 'Unknown user: ' . $login, 'Authentication', null, $login);
        header('Location: ./login.php?error=user');
        exit();
    }

    // Incomplete accounts: send OTP and redirect to complete account flow
    if ($user['status'] === 'incomplete' || $user['is_incomplete'] == 1) {
        if (!password_verify($password, $user['password_hash'])) {
            log_activity('failed_login', 'Wrong password attempt', 'Authentication', (string)$user['user_id'], $user['username']);
            $u = urlencode($login);
            header("Location: ./login.php?error=pass&u=$u");
            exit();
        }
        $userId = (string)$user['user_id'];
        $_SESSION['auth_user_id'] = $userId;
        $_SESSION['auth_username'] = $user['username'];
        $_SESSION['auth_role'] = $user['role'];
        $_SESSION['auth_status'] = 'incomplete';

        require_once __DIR__ . '/../server/mail_helper.php';

        // Generate OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpHash = password_hash($otp, PASSWORD_BCRYPT);
        $expiry = date('Y-m-d H:i:s', time() + 300);
        $_SESSION['otp_expiry'] = time() + 300;

        // Invalidate old OTPs
        mysqli_query($connect, "DELETE FROM password_reset_otp WHERE idNumber = '" . mysqli_real_escape_string($connect, $userId) . "' AND used = 0");

        // Store new OTP
        mysqli_query($connect, "INSERT INTO password_reset_otp (idNumber, otp_hash, expires_at, used) VALUES ('" . mysqli_real_escape_string($connect, $userId) . "', '" . mysqli_real_escape_string($connect, $otpHash) . "', '$expiry', 0)");

        // Send OTP email
        $subject = 'Ube Delights - Account Verification';
        $emailBody = '<div style="font-family:Arial,sans-serif;max-width:480px;">'
            . '<h2>Account Verification</h2>'
            . '<p>Hello ' . htmlspecialchars($user['username']) . ',</p>'
            . '<p>Your one-time password (OTP) for completing your Ube Delights account setup is:</p>'
            . '<p style="font-size:28px;font-weight:bold;letter-spacing:4px;">' . $otp . '</p>'
            . '<p>This code expires in <strong>5 minutes</strong>. Do not share it with anyone.</p>'
            . '</div>';
        mail_send_message($user['email'], $subject, $emailBody, $otp);

        header('Location: ./complete_account.php?step=1');
        exit();
    }

    $userId = (string)$user['user_id'];

    // 2) Verify password FIRST (lockout and attempt counting are handled in JavaScript)
    if (!password_verify($password, $user['password_hash'])) {
        log_activity('failed_login', 'Wrong password attempt', 'Authentication', $userId, $user['username']);
        $u = urlencode($login);
        header("Location: ./login.php?error=pass&u=$u");
        exit();
    }

    // 3) Password correct — check account status
    if ($user['status'] === 'blocked') {
        log_activity('login_blocked', 'Blocked user attempted login', 'Authentication', $userId, $user['username']);
        header('Location: ./login.php?error=blocked');
        exit();
    }

    if ($user['status'] === 'pending') {
        log_activity('login_blocked', 'Pending user attempted login', 'Authentication', $userId, $user['username']);
        header('Location: ./login.php?error=pending');
        exit();
    }

    if (!$user['is_active']) {
        log_activity('login_blocked', 'Inactive user attempted login', 'Authentication', $userId, $user['username']);
        header('Location: ./login.php?error=inactive');
        exit();
    }

    // 3) Success: start session
    $_SESSION['auth_user_id'] = $userId;
    $_SESSION['auth_username'] = $user['username'];
    $_SESSION['auth_role'] = $user['role'];
    $_SESSION['auth_status'] = $user['status'];
    $_SESSION['auth_first_name'] = $user['first_name'] ?? '';
    $_SESSION['auth_last_name'] = $user['last_name'] ?? '';

    // Check if user already has security questions set
    // In new schema, questions are stored on users
    $sqlChk = "SELECT q1 FROM users WHERE user_id = ? LIMIT 1";
    $stmtChk = mysqli_prepare($connect, $sqlChk);
    mysqli_stmt_bind_param($stmtChk, 's', $userId);
    mysqli_stmt_execute($stmtChk);
    $resChk = mysqli_stmt_get_result($stmtChk);
    $rowChk = $resChk ? mysqli_fetch_assoc($resChk) : null;
    $hasSQ = ($rowChk && !empty($rowChk['q1']));
    mysqli_stmt_close($stmtChk);

    if (!$hasSQ) {
        header('Location: ./set_security_questions.php');
        exit();
    }

    log_activity('login', $user['username'] . ' logged in successfully', 'Authentication', $userId, $user['username']);

    // Role-based redirect
    if (in_array($_SESSION['auth_role'], ['admin', 'super_admin'])) {
        header('Location: ./admin/dashboard.php');
    } else {
        header('Location: ./index.php');
    }
    exit();
}
?>

<?php $vs = isset($_GET['viewsource']) ? '?viewsource=1' : ''; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/login.css?v=1.0">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a onclick="getHome()" class="logo-link">
                    <img src="../images/logo.png" alt="Ube Roll Logo" class="logo-image">
                    <h2>Ube Delights</h2>
                </a>
            </div>
            <div class="nav-menu">
                <a onclick="getHome()" class="nav-link">Home</a>
                <a onclick="getRegister()" class="nav-link">Register</a>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="login-form">
            <div class="login-container">
                <form action="" method="POST" novalidate>
                    <h1>Login</h1>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Enter Username" required value="<?php echo isset($_GET['u']) ? htmlspecialchars($_GET['u']) : '';?>">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" placeholder="Enter Password" required>
                            <i class="fa-solid fa-eye-slash" id="eyeicon"></i>
                        </div>
                    </div>
                    <div class="auth-alt">
                        <p id="forgot-section" class="forgot">Forgot Password? <a onclick="getForgotPassword()">Reset Here</a></p>
                    </div>
                    <div id="lockout-timer" class="lockout-timer"></div>
                    <button class="btn" type="submit">Login</button>
                    <p class="below">Don't have an account? <a onclick="getRegister()">Register</a></p>
                </form>
            </div>
        </div>
        <div class="intro-section">
            <h2>Welcome to<br><span>Ube Delights</span></h2>
            <p>Indulge in the rich, vibrant flavors of our signature ube cakes made with authentic purple yam from the Philippines.</p>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2026 Ube Delights. All rights reserved.</p>
    </footer>
    <!-- Pending Account Modal -->
    <div id="pending-modal-overlay" class="pending-modal-overlay">
        <div class="pending-modal">
            <div class="pending-modal-icon">
                <i class="fa-solid fa-clock"></i>
            </div>
            <h2>Account Pending</h2>
            <p>Your account is awaiting admin confirmation. Please wait for approval before logging in.</p>
            <button id="pending-modal-ok" class="pending-modal-btn">OK</button>
        </div>
    </div>

    <script src="../javascript/disable_back.js"></script>
    <script src="../javascript/login.js"></script>
    <script src="../javascript/routing.js"></script>
    <script src="../javascript/inspect.js"></script>
</body>
</html>