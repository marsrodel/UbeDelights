<?php
include '../server/db.php';
include '../server/mail_helper.php';
session_start();

if (isset($_SESSION['auth_user_id'])) {
    header('Location: ./index.php');
    exit();
}

/* ── Helpers ── */
function fetch_user_full($connect, $user_id) {
    $sql = "SELECT user_id, email, username, q1, a1, q2, a2, q3, a3 FROM users WHERE user_id = ? LIMIT 1";
    $stmt = mysqli_prepare($connect, $sql);
    mysqli_stmt_bind_param($stmt, 's', $user_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = $res ? mysqli_fetch_assoc($res) : null;
    mysqli_stmt_close($stmt);
    return $row;
}

function mask_id($id) {
    $clean = str_replace('-', '', $id);
    if (strlen($clean) !== 8) return $id;
    return substr($clean, 0, 2) . '**-**' . substr($clean, 6, 2);
}

function mask_email($email) {
    $parts = explode('@', $email);
    if (count($parts) !== 2) return $email;
    $name = $parts[0];
    $domain = $parts[1];
    $masked = strlen($name) > 1 ? $name[0] . str_repeat('*', max(1, strlen($name) - 1)) : '*';
    return $masked . '@' . $domain;
}

function mask_username($username) {
    if (strlen($username) <= 2) return str_repeat('*', strlen($username));
    return substr($username, 0, 2) . str_repeat('*', strlen($username) - 2);
}

function fetch_question_groups() {
    return [
        'group1' => [
            'What was the name of your first pet?',
            'What was the make of your first vehicle?',
            'What is your favorite travel destination?'
        ],
        'group2' => [
            'What is your favorite flower?',
            'What is your favorite subject in school?',
            'What is your favorite color?'
        ],
        'group3' => [
            "What is your oldest sibling's first name?",
            "What is your best friend's name?",
            'What is your favorite childhood nickname?'
        ]
    ];
}

/* ── POST Handlers ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isJson = !empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
    $jsonData = $isJson ? json_decode(file_get_contents('php://input'), true) : null;
    $action = '';
    if ($isJson && isset($jsonData['fp_action'])) {
        $action = $jsonData['fp_action'];
    } elseif (isset($_POST['fp_action'])) {
        $action = $_POST['fp_action'];
    }

    /* Step 1: Verify ID */
    if ($action === 'verify_id') {
        $idnum = isset($_POST['id_number']) ? trim($_POST['id_number']) : '';
        if ($idnum === '') {
            header('Location: ./forgot_password.php?step=1&error=empty_id');
            exit();
        }
        if (!preg_match('/^\d{4}-\d{4}$/', $idnum)) {
            header('Location: ./forgot_password.php?step=1&error=invalid_id');
            exit();
        }
        $user = fetch_user_full($connect, $idnum);
        if (!$user) {
            header('Location: ./forgot_password.php?step=1&error=unknown_id');
            exit();
        }

        // Generate real 6-digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpHash = password_hash($otp, PASSWORD_BCRYPT);
        $otpExpirySeconds = 300; // 5 minutes
        $expiresAt = date('Y-m-d H:i:s', time() + $otpExpirySeconds);

        // Invalidate any old unused OTPs for this user
        $invalidate = mysqli_prepare($connect, 'UPDATE password_reset_otp SET used = 1 WHERE idNumber = ? AND used = 0');
        mysqli_stmt_bind_param($invalidate, 's', $idnum);
        mysqli_stmt_execute($invalidate);
        mysqli_stmt_close($invalidate);

        // Store new OTP in database
        $insert = mysqli_prepare($connect, 'INSERT INTO password_reset_otp (idNumber, otp_hash, expires_at) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($insert, 'sss', $idnum, $otpHash, $expiresAt);
        mysqli_stmt_execute($insert);
        mysqli_stmt_close($insert);

        // Send OTP email
        $subject = 'UbeDelights Password Reset OTP';
        $body = '<div style="font-family:Arial,sans-serif;max-width:480px;">'
            . '<h2>Password Reset Verification</h2>'
            . '<p>Hello ' . htmlspecialchars($user['username']) . ',</p>'
            . '<p>Your one-time password (OTP) for resetting your UbeDelights password is:</p>'
            . '<p style="font-size:28px;font-weight:bold;letter-spacing:4px;">' . $otp . '</p>'
            . '<p>This code expires in <strong>5 minutes</strong>. Do not share it with anyone.</p>'
            . '</div>';

        $mailResult = mail_send_message($user['email'], $subject, $body, $otp);

        $_SESSION['fp_user_id'] = (string)$user['user_id'];
        $_SESSION['fp_email'] = $user['email'];
        $_SESSION['fp_username'] = $user['username'];
        $_SESSION['fp_qa'] = [
            'q1' => $user['q1'], 'a1' => $user['a1'],
            'q2' => $user['q2'], 'a2' => $user['a2'],
            'q3' => $user['q3'], 'a3' => $user['a3']
        ];
        $_SESSION['fp_otp_expiry'] = strtotime($expiresAt);
        $_SESSION['fp_otp_attempts'] = 0;

        header('Location: ./forgot_password.php?step=2');
        exit();
    }

    /* Step 2: Verify OTP */
    if ($action === 'verify_otp') {
        if (!isset($_SESSION['fp_user_id'])) {
            header('Location: ./forgot_password.php?step=1');
            exit();
        }
        $otp = isset($_POST['otp_code']) ? trim($_POST['otp_code']) : '';
        if ($otp === '') {
            header('Location: ./forgot_password.php?step=2&error=otp_empty');
            exit();
        }

        $idnum = $_SESSION['fp_user_id'];

        // Fetch latest unused OTP from database
        $stmt = mysqli_prepare($connect, 'SELECT id, otp_hash, expires_at, attempts FROM password_reset_otp WHERE idNumber = ? AND used = 0 ORDER BY id DESC LIMIT 1');
        mysqli_stmt_bind_param($stmt, 's', $idnum);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);

        if ($result->num_rows === 0) {
            header('Location: ./forgot_password.php?step=2&error=otp_none');
            exit();
        }

        $row = $result->fetch_assoc();
        $otpId = (int)$row['id'];
        $attempts = (int)$row['attempts'] + 1;

        // Max 5 attempts
        if ($attempts > 5) {
            $fail = mysqli_prepare($connect, 'UPDATE password_reset_otp SET used = 1, attempts = ? WHERE id = ?');
            mysqli_stmt_bind_param($fail, 'ii', $attempts, $otpId);
            mysqli_stmt_execute($fail);
            mysqli_stmt_close($fail);
            header('Location: ./forgot_password.php?step=2&error=otp_locked');
            exit();
        }

        // Update attempt count
        $attemptUpdate = mysqli_prepare($connect, 'UPDATE password_reset_otp SET attempts = ? WHERE id = ?');
        mysqli_stmt_bind_param($attemptUpdate, 'ii', $attempts, $otpId);
        mysqli_stmt_execute($attemptUpdate);
        mysqli_stmt_close($attemptUpdate);

        // Check expiry
        if (strtotime($row['expires_at']) < time()) {
            $expire = mysqli_prepare($connect, 'UPDATE password_reset_otp SET used = 1 WHERE id = ?');
            mysqli_stmt_bind_param($expire, 'i', $otpId);
            mysqli_stmt_execute($expire);
            mysqli_stmt_close($expire);
            header('Location: ./forgot_password.php?step=2&error=otp_expired');
            exit();
        }

        // Verify OTP against hash
        if (!password_verify($otp, $row['otp_hash'])) {
            header('Location: ./forgot_password.php?step=2&error=otp_wrong');
            exit();
        }

        // OTP verified — mark as used
        $mark = mysqli_prepare($connect, 'UPDATE password_reset_otp SET used = 1 WHERE id = ?');
        mysqli_stmt_bind_param($mark, 'i', $otpId);
        mysqli_stmt_execute($mark);
        mysqli_stmt_close($mark);

        unset($_SESSION['fp_otp_attempts']);
        header('Location: ./forgot_password.php?step=3');
        exit();
    }

    /* Step 2: Resend OTP */
    if ($action === 'resend_otp') {
        if (!isset($_SESSION['fp_user_id'])) {
            header('Location: ./forgot_password.php?step=1');
            exit();
        }

        $idnum = $_SESSION['fp_user_id'];
        $email = $_SESSION['fp_email'] ?? '';
        $username = $_SESSION['fp_username'] ?? '';

        // Generate new OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $otpHash = password_hash($otp, PASSWORD_BCRYPT);
        $otpExpirySeconds = 300;
        $expiresAt = date('Y-m-d H:i:s', time() + $otpExpirySeconds);

        // Invalidate old OTPs
        $invalidate = mysqli_prepare($connect, 'UPDATE password_reset_otp SET used = 1 WHERE idNumber = ? AND used = 0');
        mysqli_stmt_bind_param($invalidate, 's', $idnum);
        mysqli_stmt_execute($invalidate);
        mysqli_stmt_close($invalidate);

        // Store new OTP
        $insert = mysqli_prepare($connect, 'INSERT INTO password_reset_otp (idNumber, otp_hash, expires_at) VALUES (?, ?, ?)');
        mysqli_stmt_bind_param($insert, 'sss', $idnum, $otpHash, $expiresAt);
        mysqli_stmt_execute($insert);
        mysqli_stmt_close($insert);

        // Send email
        $subject = 'UbeDelights Password Reset OTP';
        $body = '<div style="font-family:Arial,sans-serif;max-width:480px;">'
            . '<h2>Password Reset Verification</h2>'
            . '<p>Hello ' . htmlspecialchars($username) . ',</p>'
            . '<p>Your new one-time password (OTP) for resetting your UbeDelights password is:</p>'
            . '<p style="font-size:28px;font-weight:bold;letter-spacing:4px;">' . $otp . '</p>'
            . '<p>This code expires in <strong>5 minutes</strong>. Do not share it with anyone.</p>'
            . '</div>';

        mail_send_message($email, $subject, $body, $otp);

        $_SESSION['fp_otp_expiry'] = strtotime($expiresAt);
        $_SESSION['fp_otp_attempts'] = 0;

        header('Location: ./forgot_password.php?step=2');
        exit();
    }

    /* Step 3: Verify Security Questions */
    if ($action === 'verify_security') {
        if (!isset($_SESSION['fp_user_id']) || !isset($_SESSION['fp_qa'])) {
            if ($isJson) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Session expired. Please start over.']);
            } else {
                header('Location: ./forgot_password.php?step=1');
            }
            exit();
        }

        if ($isJson) {
            $sq1 = isset($jsonData['questions'][0]) ? trim($jsonData['questions'][0]) : '';
            $sq2 = isset($jsonData['questions'][1]) ? trim($jsonData['questions'][1]) : '';
            $sq3 = isset($jsonData['questions'][2]) ? trim($jsonData['questions'][2]) : '';
            $a1 = isset($jsonData['answers'][0]) ? trim($jsonData['answers'][0]) : '';
            $a2 = isset($jsonData['answers'][1]) ? trim($jsonData['answers'][1]) : '';
            $a3 = isset($jsonData['answers'][2]) ? trim($jsonData['answers'][2]) : '';
        } else {
            $sq1 = isset($_POST['sq_q1']) ? trim($_POST['sq_q1']) : '';
            $sq2 = isset($_POST['sq_q2']) ? trim($_POST['sq_q2']) : '';
            $sq3 = isset($_POST['sq_q3']) ? trim($_POST['sq_q3']) : '';
            $a1 = isset($_POST['sq_a1']) ? trim($_POST['sq_a1']) : '';
            $a2 = isset($_POST['sq_a2']) ? trim($_POST['sq_a2']) : '';
            $a3 = isset($_POST['sq_a3']) ? trim($_POST['sq_a3']) : '';
        }
        $qa = $_SESSION['fp_qa'];

        if ($sq1 === '' || $sq2 === '' || $sq3 === '' || $a1 === '' || $a2 === '' || $a3 === '') {
            if ($isJson) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'All fields are required.']);
            } else {
                header('Location: ./forgot_password.php?step=3&error=empty_answers');
            }
            exit();
        }
        if (count(array_unique([$sq1, $sq2, $sq3])) < 3) {
            if ($isJson) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Each security question must be different.']);
            } else {
                header('Location: ./forgot_password.php?step=3&error=duplicate_questions');
            }
            exit();
        }
        $stored = [
            $qa['q1'] => $qa['a1'],
            $qa['q2'] => $qa['a2'],
            $qa['q3'] => $qa['a3']
        ];
        $selectedQuestions = [$sq1, $sq2, $sq3];
        $selectedAnswers = [$a1, $a2, $a3];
        $answerResults = [];
        $correct = 0;
        for ($i = 0; $i < 3; $i++) {
            $q = $selectedQuestions[$i];
            $a = $selectedAnswers[$i];
            if ($q !== '' && isset($stored[$q]) && password_verify($a, $stored[$q])) {
                $answerResults[] = true;
                $correct++;
            } else {
                $answerResults[] = false;
            }
        }
        if ($isJson) {
            header('Content-Type: application/json');
            if ($correct >= 2) {
                unset($_SESSION['fp_qa']);
                echo json_encode(['success' => true, 'answerResults' => $answerResults, 'correctCount' => $correct]);
            } else {
                $msg = $correct === 1
                    ? 'At least 1 correct answer now to proceed.'
                    : 'You need at least 2 correct answers to proceed.';
                echo json_encode(['success' => false, 'answerResults' => $answerResults, 'correctCount' => $correct, 'message' => $msg]);
            }
        } else {
            if ($correct < 2) {
                header('Location: ./forgot_password.php?step=3&error=not_enough');
                exit();
            }
            unset($_SESSION['fp_qa']);
            header('Location: ./forgot_password.php?step=4');
        }
        exit();
    }

    /* Step 4: Reset Password */
    if ($action === 'reset_password') {
        if (!isset($_SESSION['fp_user_id'])) {
            header('Location: ./forgot_password.php?step=1');
            exit();
        }
        $newPw = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confPw = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        if ($newPw === '' || $confPw === '') {
            header('Location: ./forgot_password.php?step=4&error=empty');
            exit();
        }
        if ($newPw !== $confPw) {
            header('Location: ./forgot_password.php?step=4&error=mismatch');
            exit();
        }
        if (strlen($newPw) < 8) {
            header('Location: ./forgot_password.php?step=4&error=weak');
            exit();
        }

        $hash = password_hash($newPw, PASSWORD_DEFAULT);
        $user_id = $_SESSION['fp_user_id'];
        $stmt = mysqli_prepare($connect, "UPDATE users SET password_hash = ? WHERE user_id = ?");
        mysqli_stmt_bind_param($stmt, 'ss', $hash, $user_id);
        $ok = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        if (!$ok) {
            header('Location: ./forgot_password.php?step=4&error=save');
            exit();
        }

        unset($_SESSION['fp_user_id'], $_SESSION['fp_email'], $_SESSION['fp_username'], $_SESSION['fp_qa']);
        header('Location: ./forgot_password.php?reset=success');
        exit();
    }
}

/* ── Determine Current Step ── */
$currentStep = isset($_GET['step']) ? (int)$_GET['step'] : 1;
if ($currentStep < 1 || $currentStep > 4) $currentStep = 1;

/* Redirect to step 1 if no session data for higher steps */
if ($currentStep >= 2 && !isset($_SESSION['fp_user_id'])) {
    $currentStep = 1;
}

$maskedId = isset($_SESSION['fp_user_id']) ? mask_id($_SESSION['fp_user_id']) : '';
$maskedEmail = isset($_SESSION['fp_email']) ? mask_email($_SESSION['fp_email']) : '';
$maskedUsername = isset($_SESSION['fp_username']) ? mask_username($_SESSION['fp_username']) : '';
$otpExpiry = isset($_SESSION['fp_otp_expiry']) ? (int)$_SESSION['fp_otp_expiry'] : 0;
$questionGroups = fetch_question_groups();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot Password</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../css/forgot_password.css?v=1.0" />
</head>
<body>
  <nav class="navbar">
    <div class="nav-container">
      <div class="nav-logo">
        <a onclick="getHome()" class="logo-link">
          <img src="../images/logo.png" alt="Ube Delights Logo" class="logo-image" />
          <h2>Ube Delights</h2>
        </a>
      </div>
      <div class="nav-menu">
        <a onclick="getLogin()" class="nav-link">Login</a>
        <a onclick="getRegister()" class="nav-link">Register</a>
      </div>
    </div>
  </nav>

  <main class="main-content">
    <!-- LEFT: Hero -->
    <div class="hero-section">
      <h1><span>Forgot Password?</span></h1>
      <p>No worries! Enter your ID number and we'll help you reset your password.</p>
    </div>

    <!-- RIGHT: Form -->
    <div class="form-section">
      <div class="form-card" data-step="<?php echo $currentStep; ?>">
        <form id="fp-form" method="POST" action="" autocomplete="off">

          <!-- STEP 1: User Verification -->
          <div class="fp-step <?php echo $currentStep === 1 ? 'active' : ''; ?>" id="step-1">
            <h2>User Verification</h2>
            <p class="step-desc">Enter your <strong>ID number</strong> below. We'll send a verification code to the email registered on that account before you answer your security questions.</p>
            <div class="form-group">
              <label for="id_number">ID Number <span class="required">*</span></label>
              <input type="text" id="id_number" name="id_number" placeholder="e.g. 2026-0015" maxlength="9" required />
            </div>
            <div class="form-actions">
              <a class="btn-prev" onclick="getLogin()">Previous</a>
              <button type="submit" name="fp_action" value="verify_id" class="btn-submit">Verify</button>
            </div>
          </div>

          <!-- STEP 2: Email Verification -->
          <div class="fp-step <?php echo $currentStep === 2 ? 'active' : ''; ?>" id="step-2">
            <h2>Email Verification</h2>
            <p class="step-desc">A one-time password (OTP) has been sent to your registered email. Please enter the 6-digit code below to continue resetting your password.</p>
            <div class="user-info-row user-info-row-stacked">
              <span>ID Number: <strong><?php echo htmlspecialchars($maskedId); ?></strong></span>
              <span>Email: <strong><?php echo htmlspecialchars($maskedEmail); ?></strong></span>
            </div>
            <label class="otp-label">Enter Verification Code</label>
            <div class="otp-inputs">
              <input type="password" maxlength="1" class="otp-box" data-index="0" />
              <input type="password" maxlength="1" class="otp-box" data-index="1" />
              <input type="password" maxlength="1" class="otp-box" data-index="2" />
              <input type="password" maxlength="1" class="otp-box" data-index="3" />
              <input type="password" maxlength="1" class="otp-box" data-index="4" />
              <input type="password" maxlength="1" class="otp-box" data-index="5" />
            </div>
            <div class="otp-meta">
              <a class="show-code-link" id="showCodeBtn">Show code</a>
              <span class="otp-timer">Expires in: <strong id="otpTimerDisplay" data-expiry="<?php echo $otpExpiry; ?>">05:00</strong></span>
            </div>
            <div id="otpError" class="error-text"></div>
            <div class="form-actions">
              <button type="button" class="btn-prev" data-goto="1">Previous</button>
              <button type="button" class="btn-resend" id="resendBtn">Resend OTP</button>
              <button type="button" class="btn-submit" id="verifyOtpBtn">Verify OTP</button>
            </div>
          </div>

          <!-- STEP 3: Security Questions -->
          <div class="fp-step <?php echo $currentStep === 3 ? 'active' : ''; ?>" id="step-3">
            <h2>Answer Your Security Questions</h2>
            <p class="step-desc">Pick the questions you set up and answer at least 2 correctly.</p>
            <div class="user-info-row">
              <span>ID Number: <strong><?php echo htmlspecialchars($maskedId); ?></strong></span>
              <span>Username: <strong><?php echo htmlspecialchars($maskedUsername); ?></strong></span>
            </div>

            <div class="question-group">
              <label>Security Question 1</label>
              <select name="sq_q1" id="sq_q1" required>
                <option value="" disabled selected>-- Select a question --</option>
                <?php foreach ($questionGroups['group1'] as $q): ?>
                  <option value="<?php echo htmlspecialchars($q); ?>"><?php echo htmlspecialchars($q); ?></option>
                <?php endforeach; ?>
              </select>
              <div class="password-wrapper">
                <input type="password" id="sq_a1" name="sq_a1" placeholder="Enter your answer" required />
                <i class="fa-solid fa-eye-slash"></i>
              </div>
            </div>

            <div class="question-group">
              <label>Security Question 2</label>
              <select name="sq_q2" id="sq_q2" required>
                <option value="" disabled selected>-- Select a question --</option>
                <?php foreach ($questionGroups['group2'] as $q): ?>
                  <option value="<?php echo htmlspecialchars($q); ?>"><?php echo htmlspecialchars($q); ?></option>
                <?php endforeach; ?>
              </select>
              <div class="password-wrapper">
                <input type="password" id="sq_a2" name="sq_a2" placeholder="Enter your answer" required />
                <i class="fa-solid fa-eye-slash"></i>
              </div>
            </div>

            <div class="question-group">
              <label>Security Question 3</label>
              <select name="sq_q3" id="sq_q3" required>
                <option value="" disabled selected>-- Select a question --</option>
                <?php foreach ($questionGroups['group3'] as $q): ?>
                  <option value="<?php echo htmlspecialchars($q); ?>"><?php echo htmlspecialchars($q); ?></option>
                <?php endforeach; ?>
              </select>
              <div class="password-wrapper">
                <input type="password" id="sq_a3" name="sq_a3" placeholder="Enter your answer" required />
                <i class="fa-solid fa-eye-slash"></i>
              </div>
            </div>

            <div class="form-actions">
              <button type="button" class="btn-prev" data-goto="2">Previous</button>
              <button type="submit" name="fp_action" value="verify_security" class="btn-submit">Verify</button>
            </div>
          </div>

          <!-- STEP 4: Reset Password -->
          <div class="fp-step <?php echo $currentStep === 4 ? 'active' : ''; ?>" id="step-4">
            <h2>Reset your Password</h2>
            <div class="user-info-row">
              <span>ID Number: <strong><?php echo htmlspecialchars($maskedId); ?></strong></span>
              <span>Username: <strong><?php echo htmlspecialchars($maskedUsername); ?></strong></span>
            </div>
            <div class="form-group">
              <label for="newPassword">New Password <span class="required">*</span> <span id="strengthIndicator" class="strength-text"></span></label>
              <div class="password-wrapper">
                <input type="password" id="newPassword" name="new_password" placeholder="Enter new password" required />
                <i class="fa-solid fa-eye-slash"></i>
              </div>
            </div>
            <div class="form-group">
              <label for="confirmPassword">Confirm Password <span class="required">*</span> <span id="confirmMatch"></span></label>
              <div class="password-wrapper">
                <input type="password" id="confirmPassword" name="confirm_password" placeholder="Re-enter new password" required />
                <i class="fa-solid fa-eye-slash"></i>
              </div>
            </div>
            <div class="form-actions">
              <button type="button" class="btn-prev" data-goto="3">Previous</button>
              <button type="submit" name="fp_action" value="reset_password" class="btn-submit">Reset Password</button>
            </div>
          </div>

        </form>
      </div>
    </div>
  </main>

  <!-- SUCCESS MODAL -->
  <div class="fp-success-overlay" id="successModal">
    <div class="fp-success-card">
      <i class="fa-solid fa-circle-check success-icon"></i>
      <h3>Password Reset</h3>
      <p>Your password has been reset successfully.</p>
      <button onclick="window.location.href='./login.php'">OK</button>
    </div>
  </div>

  <footer class="footer">
    <p>&copy; 2026 Ube Delights. All rights reserved.</p>
  </footer>
  <script src="../javascript/disable_back.js"></script>
  <script src="../javascript/forgot_password.js?v=1.0"></script>
  <script src="../javascript/routing.js"></script>
  <script src="../javascript/inspect.js"></script>
</body>
</html>
