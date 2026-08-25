<?php require_once __DIR__ . '/../server/customer_auth.php';

$userId = $currentUser['id'];
$firstName = '';
$lastName = '';
$email = '';
$username = '';
$created = '';
if ($connect) {
    $sql = "SELECT first_name, last_name, email, username, created_at FROM users WHERE user_id = ? LIMIT 1";
    if ($stmt = mysqli_prepare($connect, $sql)) {
        mysqli_stmt_bind_param($stmt, 's', $userId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res) {
            $row = mysqli_fetch_assoc($res);
            if ($row) {
                $firstName = $row['first_name'] ?? '';
                $lastName = $row['last_name'] ?? '';
                $email = $row['email'] ?? '';
                $username = $row['username'] ?? '';
                $created = $row['created_at'] ?? '';
            }
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($connect);
}
$initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ube Delights - Profile</title>
    <link rel="stylesheet" href="../css/dashboard.css?v=4.0">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a onclick="getIndex()" class="logo-link">
                    <img src="../images/logo.png" alt="Ube Delights" class="logo-image">
                    <h2>Ube Delights</h2>
                </a>
            </div>
            <div class="nav-menu">
                <a onclick="getIndex()" class="nav-link">Dashboard</a>
                <a onclick="getShop()" class="nav-link">Shop</a>
                <a onclick="getCart()" class="nav-link cart-link">Cart <span class="cart-badge" id="cartBadge" style="display:none;">0</span></a>
                <a onclick="getOrders()" class="nav-link">My Orders</a>
                <a onclick="getProfile()" class="nav-link active">Profile</a>
                <a onclick="getLogout()" class="nav-link">Log Out</a>
            </div>
        </div>
    </nav>

    <section class="hero-section hero-small">
        <div class="hero-content">
            <h1>My <span>Profile</span></h1>
            <p>Manage your account information.</p>
        </div>
    </section>

    <main class="main-content">
        <div class="profile-layout">
            <div class="profile-card">
                <div class="profile-avatar">
                    <?php echo $initials; ?>
                </div>
                <h2 class="profile-name"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h2>
                <span class="profile-username">@<?php echo htmlspecialchars($username); ?></span>
            </div>

            <div class="profile-details">
                <div class="detail-card">
                    <h3>Personal Information</h3>
                    <div class="detail-row">
                        <span class="detail-label">Full Name</span>
                        <span class="detail-value"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email</span>
                        <span class="detail-value"><?php echo htmlspecialchars($email); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Username</span>
                        <span class="detail-value"><?php echo htmlspecialchars($username); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">User ID</span>
                        <span class="detail-value"><?php echo htmlspecialchars($userId); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Member Since</span>
                        <span class="detail-value"><?php echo $created ? date('F Y', strtotime($created)) : 'N/A'; ?></span>
                    </div>
                </div>

                <div class="detail-card">
                    <h3>Account Actions</h3>
                    <div class="action-buttons">
                        <button class="btn-outline" id="btnEditProfile">Edit Profile</button>
                        <button class="btn-outline" id="btnChangePassword">Change Password</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Ube Delights. All rights reserved.</p>
    </footer>

    <div class="toast" id="toast"></div>

    <script src="../javascript/disable_back.js"></script>
    <script src="../javascript/index.js"></script>
    <script src="../javascript/dashboard.js"></script>
    <script src="../javascript/routing.js"></script>
    <script src="../javascript/inspect.js"></script>
    <script src="../javascript/profile.js"></script>
</body>
</html>
