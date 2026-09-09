<?php
include '../server/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Must be an incomplete account user
if (!isset($_SESSION['auth_user_id']) || ($_SESSION['auth_status'] ?? '') !== 'incomplete') {
    header('Location: ./login.php');
    exit();
}

$userId = $_SESSION['auth_user_id'];

// Fetch user data for masked display
$stmt = mysqli_prepare($connect, "SELECT user_id, email, username FROM users WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, 's', $userId);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = $res ? mysqli_fetch_assoc($res) : null;
mysqli_stmt_close($stmt);

if (!$user) {
    header('Location: ./login.php');
    exit();
}

// Mask helpers
function mask_id($id) {
    $clean = str_replace('-', '', $id);
    if (strlen($clean) !== 8) return $id;
    return substr($clean, 0, 2) . '**-**' . substr($clean, 6, 2);
}
function mask_email($email) {
    $parts = explode('@', $email);
    if (count($parts) !== 2) return $email;
    $local = $parts[0];
    $masked = strlen($local) > 2 ? $local[0] . str_repeat('*', strlen($local) - 2) . $local[strlen($local) - 1] : str_repeat('*', strlen($local));
    return $masked . '@' . $parts[1];
}

$maskedId = mask_id($user['user_id']);
$maskedEmail = mask_email($user['email']);

// OTP expiry from session or default
$otpExpiry = isset($_SESSION['otp_expiry']) ? $_SESSION['otp_expiry'] : (time() + 300);
$otpExpiryTs = $otpExpiry;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Account - Ube Delights</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/complete_account.css?v=1.0">
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
                <a onclick="getLogout()" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <!-- STEP 1: OTP Verification (hero layout) -->
    <main class="main-content" id="step1-section">
        <div class="hero-section">
            <h1><span>Complete Your Account</span></h1>
            <p>We sent a verification code to your registered email. Enter it below to continue setting up your account.</p>
        </div>
        <div class="form-section">
            <div class="form-card">
                <h2>Email Verification</h2>
                <p class="step-desc">A one-time password (OTP) has been sent to your registered email. Please enter the 6-digit code below to continue.</p>
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
                    <span class="otp-timer">Expires in: <strong id="otpTimerDisplay" data-expiry="<?php echo $otpExpiryTs; ?>">05:00</strong></span>
                </div>
                <div id="otpError" class="error-text"></div>
                <div class="form-actions">
                    <a class="btn-prev" onclick="getLogout()">Back to Login</a>
                    <button type="button" class="btn-resend" id="resendBtn">Resend OTP</button>
                    <button type="button" class="btn-submit" id="verifyOtpBtn">Verify OTP</button>
                </div>
            </div>
        </div>
    </main>

    <!-- STEPS 2 & 3: Full form layout (no hero) -->
    <main class="main-content form-layout" id="steps23-section" style="display:none;">
        <div class="register-container">

            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step-circle active" id="indicator-step2">
                    <span>1</span>
                </div>
                <div class="step-label">Your Details</div>
                <div class="step-line"></div>
                <div class="step-circle" id="indicator-step3">
                    <span>2</span>
                </div>
                <div class="step-label">Account Security</div>
            </div>

            <!-- STEP 2: Personal Information & Address -->
            <div class="ca-step active" id="step-2">
                <div class="section-block">
                    <div class="section-title">Your Name & Details</div>
                    <div class="fields-row cols-3">
                        <div class="form-group">
                            <label for="fname">First Name <span class="required">*</span></label>
                            <input type="text" id="fname" placeholder="First name">
                        </div>
                        <div class="form-group">
                            <label for="mname">Middle Name <span class="optional">(Optional)</span></label>
                            <input type="text" id="mname" placeholder="Middle name">
                        </div>
                        <div class="form-group">
                            <label for="lname">Last Name <span class="required">*</span></label>
                            <input type="text" id="lname" placeholder="Last name">
                        </div>
                    </div>
                    <div class="fields-row cols-4">
                        <div class="form-group">
                            <label for="ename">Extension Name <span class="optional">(Optional)</span></label>
                            <input type="text" id="ename" placeholder="Jr, Sr, III">
                        </div>
                        <div class="form-group">
                            <label for="bday">Date of Birth <span class="required">*</span></label>
                            <input type="date" id="bday" onchange="calculateAge()">
                        </div>
                        <div class="form-group">
                            <label for="age">Age <span class="required">*</span></label>
                            <input type="text" id="age" readonly>
                        </div>
                        <div class="form-group">
                            <label for="sex">Sex <span class="required">*</span></label>
                            <select id="sex">
                                <option value="">Select Sex</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="section-block">
                    <div class="section-title">Complete Your Address</div>
                    <div class="fields-row cols-3">
                        <div class="form-group">
                            <label for="street">Purok/Street <span class="required">*</span></label>
                            <input type="text" id="street" placeholder="Purok 5">
                        </div>
                        <div class="form-group">
                            <label for="brgy">Barangay <span class="required">*</span></label>
                            <input type="text" id="brgy" placeholder="Barangay">
                        </div>
                        <div class="form-group">
                            <label for="city">City/Municipality <span class="required">*</span></label>
                            <input type="text" id="city" placeholder="City">
                        </div>
                    </div>
                    <div class="fields-row cols-3">
                        <div class="form-group">
                            <label for="province">Province <span class="required">*</span></label>
                            <input type="text" id="province" placeholder="Province">
                        </div>
                        <div class="form-group">
                            <label for="country">Country <span class="required">*</span></label>
                            <input type="text" id="country" placeholder="Country">
                        </div>
                        <div class="form-group">
                            <label for="zipcode">Zip Code <span class="required">*</span></label>
                            <input type="number" id="zipcode" placeholder="8600" maxlength="4">
                        </div>
                    </div>
                </div>

                <div id="step2Error" class="block-error"></div>
                <div class="form-actions-right">
                    <button type="button" class="btn-submit" id="step2NextBtn">Next <i class="fa-solid fa-arrow-right"></i></button>
                </div>
            </div>

            <!-- STEP 3: Password & Security Questions -->
            <div class="ca-step" id="step-3" style="display:none;">
                <div class="section-block">
                    <div class="section-title">Set Your Password</div>
                    <p class="section-desc">Use 8-50 characters with upper, lower, number, and symbol.</p>
                    <div class="fields-row cols-2">
                        <div class="form-group">
                            <label for="pass">New Password <span class="required">*</span> <span id="pass-strength" class="field-hint"></span></label>
                            <div class="password-wrapper">
                                <input type="password" id="pass" placeholder="New password">
                                <i class="fa-solid fa-eye-slash" id="eyeicon-pass"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="repass">Confirm Password <span class="required">*</span> <span id="repass-match" class="field-hint"></span></label>
                            <div class="password-wrapper">
                                <input type="password" id="repass" placeholder="Confirm password">
                                <i class="fa-solid fa-eye-slash" id="eyeicon-repass"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-block">
                    <div class="section-title">Security Questions</div>
                    <p class="section-desc">Choose 3 unique questions and provide answers. These will be used for account recovery.</p>
                    <div class="fields-row cols-3">
                        <div class="form-group">
                            <label for="q1">Question 1 <span class="required">*</span></label>
                            <select id="q1">
                                <option value="" disabled selected>-Select a question-</option>
                            </select>
                            <label for="a1" style="margin-top:8px;">Answer <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="a1" placeholder="Your answer">
                                <i class="fa-solid fa-eye-slash" id="eyeicon-a1"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="q2">Question 2 <span class="required">*</span></label>
                            <select id="q2">
                                <option value="" disabled selected>-Select a question-</option>
                            </select>
                            <label for="a2" style="margin-top:8px;">Answer <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="a2" placeholder="Your answer">
                                <i class="fa-solid fa-eye-slash" id="eyeicon-a2"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="q3">Question 3 <span class="required">*</span></label>
                            <select id="q3">
                                <option value="" disabled selected>-Select a question-</option>
                            </select>
                            <label for="a3" style="margin-top:8px;">Answer <span class="required">*</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="a3" placeholder="Your answer">
                                <i class="fa-solid fa-eye-slash" id="eyeicon-a3"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="step3Error" class="block-error"></div>
                <div class="form-actions-row">
                    <button type="button" class="btn-back" id="step3BackBtn"><i class="fa-solid fa-arrow-left"></i> Back</button>
                    <button type="button" class="btn-submit" id="completeSetupBtn">Complete Setup</button>
                </div>
            </div>

        </div>
    </main>

    <!-- Success Modal -->
    <div class="modal-overlay" id="successModal">
        <div class="modal" style="max-width:450px;">
            <div class="modal-header" style="border-bottom:none;">
                <h2 id="successModalTitle">Success</h2>
            </div>
            <div class="modal-body" style="padding:0 24px;">
                <p id="successModalMessage" style="color:var(--text-secondary, #666); font-size:0.9rem;"></p>
            </div>
            <div class="modal-footer" style="border-top:none; justify-content:flex-end;">
                <button class="btn-submit" id="successModalOkBtn">OK</button>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Ube Delights. All rights reserved.</p>
    </footer>

    <script src="../javascript/disable_back.js"></script>
    <script src="../javascript/register.js"></script>
    <script src="../javascript/routing.js"></script>
    <script src="../javascript/complete_account.js"></script>
    <script src="../javascript/inspect.js"></script>
</body>
</html>
