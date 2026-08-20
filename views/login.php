<?php
// Simple login: prepared statements + password_verify + basic lockout
include '../server/db.php';
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

    // 1) Find user by username (case-sensitive) or email
    // Use BINARY for username to enforce case sensitivity. Email remains as-is.
    $sqlUser = "SELECT user_id, username, email, password_hash, is_active FROM users WHERE (BINARY username = ? OR email = ?) LIMIT 1";
    $stmtUser = mysqli_prepare($connect, $sqlUser);
    mysqli_stmt_bind_param($stmtUser, 'ss', $login, $login);
    mysqli_stmt_execute($stmtUser);
    $resUser = mysqli_stmt_get_result($stmtUser);
    $user = $resUser ? mysqli_fetch_assoc($resUser) : null;
    mysqli_stmt_close($stmtUser);

    if (!$user) {
        header('Location: ./login.php?error=user');
        exit();
    }
    if (!$user['is_active']) {
        header('Location: ./login.php?error=inactive');
        exit();
    }

    $userId = (string)$user['user_id'];

    // 2) Verify password (lockout and attempt counting are handled in JavaScript)
    if (!password_verify($password, $user['password_hash'])) {
        $u = urlencode($login);
        header("Location: ./login.php?error=pass&u=$u");
        exit();
    }

    // 3) Success: start session
    $_SESSION['auth_user_id'] = $userId;
    $_SESSION['auth_username'] = $user['username'];

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

    if ($hasSQ) {
        header('Location: ./index.php');
        exit();
    } else {
        header('Location: ./set_security_questions.php');
        exit();
    }
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
        <div class="content-wrapper">
            <div class="form-section">
                <header>
                    <h1>Login</h1>
                </header>
                <div class="login-container">
                    <form action="" method="POST" novalidate>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" placeholder="Username" required value="<?php echo isset($_GET['u']) ? htmlspecialchars($_GET['u']) : '';?>">
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="password-wrapper">
                                <input type="password" id="password" name="password" placeholder="Password" required>
                                <i class="fa-solid fa-eye-slash" id="eyeicon"></i>
                            </div>
                        </div>
                        <div class="auth-alt">
                            <p id="forgot-section" class="forgot" >Forgot Password? <a onclick="getRecover()">Reset Here</a></p>
                        </div>
                        <div id="lockout-timer" class="lockout-timer"></div>
                        <button class="btn" type="submit">Login</button>
                    </form>
                    <p class="below">Don't have an account? <a onclick="getRegister()">Register</a></p>
                </div>
            </div>
            <div class="image-section">
                <img src="../images/cake.png" alt="Delicious Ube Cake" class="hero-image">
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Ube Delights. All rights reserved.</p>
    </footer>
    <script src="../javascript/disable_back.js"></script>
    <script src="../javascript/login.js"></script>
    <script src="../javascript/routing.js"></script>
    <script src="../javascript/inspect.js"></script>
</body>
</html>