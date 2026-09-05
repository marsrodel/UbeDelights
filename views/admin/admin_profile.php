<?php require_once __DIR__ . '/../../server/admin_auth.php';

$profile = null;
if ($connect) {
    $userId = $_SESSION['auth_user_id'];
    $sql = "SELECT user_id, username, first_name, middle_name, last_name, extension_name,
                   email, role, status, date_of_birth, age, sex,
                   street, barangay, city_municipality, province, country, zip_code,
                   created_at, updated_at
            FROM users WHERE user_id = '" . mysqli_real_escape_string($connect, $userId) . "'";
    $result = mysqli_query($connect, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $profile = [
            'id'            => $row['user_id'],
            'username'      => $row['username'],
            'firstName'     => $row['first_name'],
            'middleName'    => $row['middle_name'] ?? '',
            'lastName'      => $row['last_name'],
            'extensionName' => $row['extension_name'] ?? '',
            'email'         => $row['email'],
            'role'          => $row['role'],
            'status'        => $row['status'],
            'dob'           => $row['date_of_birth'],
            'age'           => $row['age'],
            'sex'           => $row['sex'],
            'street'        => $row['street'],
            'barangay'      => $row['barangay'],
            'city'          => $row['city_municipality'],
            'province'      => $row['province'],
            'country'       => $row['country'],
            'zipCode'       => $row['zip_code'],
            'createdAt'     => $row['created_at'] ?? '-',
            'updatedAt'     => $row['updated_at'] ?? '-',
        ];
    }
}

$roleLabel = 'Admin';
if ($profile && $profile['role'] === 'super_admin') $roleLabel = 'Super Admin';

$statusLabel = '';
if ($profile) {
    $statusLabel = ucfirst($profile['status']);
}

$orderCount = 0;
$pendingCount = 0;
if ($connect) {
    $r = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending'");
    if ($r) $orderCount = mysqli_fetch_assoc($r)['cnt'];
    $r2 = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM users WHERE status = 'pending'");
    if ($r2) $pendingCount = mysqli_fetch_assoc($r2)['cnt'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Ube Delights Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin_security.css?v=2.0">
    <link rel="stylesheet" href="../../css/admin_profile.css?v=2.0">
</head>
<body class="admin-body">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <img src="../../images/logo.png" alt="Ube Delights" class="sidebar-logo">
            <div>
                <h2>Ube Delights</h2>
                <span class="sidebar-tag">Admin Panel</span>
            </div>
        </div>

        <div class="sidebar-profile">
            <div class="admin-chip">
                <div class="admin-avatar"><?php echo strtoupper(substr($profile['firstName'] ?? 'A', 0, 1) . substr($profile['lastName'] ?? 'U', 0, 1)); ?></div>
                <div class="admin-chip-info">
                    <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong>
                    <small><?php echo strtoupper($roleLabel); ?></small>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a onclick="getAdminDashboard()" class="sidebar-link"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
            <a onclick="getAdminProducts()" class="sidebar-link"><i class="fa-solid fa-box"></i><span>Products</span></a>
            <a onclick="getAdminOrders()" class="sidebar-link"><i class="fa-solid fa-bag-shopping"></i><span>Orders</span><?php if ($orderCount > 0): ?><span class="sidebar-badge"><?php echo $orderCount; ?></span><?php endif; ?></a>
            <a onclick="getAdminUserManagement()" class="sidebar-link"><i class="fa-solid fa-users-cog"></i><span>User Management</span></a>
            <a onclick="getAdminPendingApprovals()" class="sidebar-link"><i class="fa-solid fa-user-clock"></i><span>Pending Approvals</span><?php if ($pendingCount > 0): ?><span class="sidebar-badge"><?php echo $pendingCount; ?></span><?php endif; ?></a>
            <a onclick="getAdminSystemLogs()" class="sidebar-link"><i class="fa-solid fa-list-alt"></i><span>System Logs</span></a>
            <a onclick="getAdminProfile()" class="sidebar-link active"><i class="fa-solid fa-user"></i><span>My Account</span></a>
        </nav>

        <div class="sidebar-footer">
            <a onclick="getAdminLogout()" class="sidebar-logout"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <h1>My Account</h1>
                <p class="topbar-subtitle">Profile, password, and security info.</p>
            </div>
            <div class="topbar-right">
                <span class="topbar-date"><i class="fa-solid fa-calendar-days"></i><?php echo date('F j, Y'); ?></span>
            </div>
        </header>

        <main class="admin-content">
            <div class="profile-card">
                <form id="adminProfileForm" class="profile-form">

                    <h3 class="profile-section-title">Personal Information</h3>
                    <div class="form-grid cols-4">
                        <div class="form-field">
                            <label for="profileId">ID Number</label>
                            <input type="text" id="profileId" value="<?php echo htmlspecialchars($profile['id'] ?? ''); ?>" readonly>
                        </div>
                        <div class="form-field">
                            <label for="profileFirstName">First Name</label>
                            <input type="text" id="profileFirstName" name="first_name">
                        </div>
                        <div class="form-field">
                            <label for="profileMiddleName">Middle Name</label>
                            <input type="text" id="profileMiddleName" name="middle_name">
                        </div>
                        <div class="form-field">
                            <label for="profileLastName">Last Name</label>
                            <input type="text" id="profileLastName" name="last_name">
                        </div>
                    </div>
                    <div class="form-grid cols-4">
                        <div class="form-field">
                            <label for="profileExtensionName">Extension Name</label>
                            <input type="text" id="profileExtensionName" name="extension_name" placeholder="Jr, Sr, III">
                        </div>
                        <div class="form-field">
                            <label for="profileBirthdate">Date of Birth</label>
                            <input type="date" id="profileBirthdate" name="date_of_birth">
                        </div>
                        <div class="form-field">
                            <label for="profileAge">Age</label>
                            <input type="number" id="profileAge" readonly>
                        </div>
                        <div class="form-field">
                            <label for="profileSex">Sex</label>
                            <select id="profileSex" name="sex">
                                <option value="">Select Sex</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>

                    <h3 class="profile-section-title">Account Information</h3>
                    <div class="form-grid cols-4">
                        <div class="form-field">
                            <label for="profileEmail">Email</label>
                            <input type="email" id="profileEmail" name="email">
                        </div>
                        <div class="form-field">
                            <label for="profileUsername">Username</label>
                            <input type="text" id="profileUsername" name="username">
                        </div>
                        <div class="form-field">
                            <label for="profileRole">Role</label>
                            <input type="text" id="profileRole" value="<?php echo htmlspecialchars($roleLabel); ?>" readonly>
                        </div>
                        <div class="form-field">
                            <label for="profileStatus">Status</label>
                            <input type="text" id="profileStatus" value="<?php echo htmlspecialchars($statusLabel); ?>" readonly>
                        </div>
                    </div>

                    <h3 class="profile-section-title">Address Information</h3>
                    <div class="form-grid cols-3">
                        <div class="form-field">
                            <label for="profileStreet">Purok/Street</label>
                            <input type="text" id="profileStreet" name="street">
                        </div>
                        <div class="form-field">
                            <label for="profileBarangay">Barangay</label>
                            <input type="text" id="profileBarangay" name="barangay">
                        </div>
                        <div class="form-field">
                            <label for="profileCity">City/Municipality</label>
                            <input type="text" id="profileCity" name="city_municipality">
                        </div>
                    </div>
                    <div class="form-grid cols-3">
                        <div class="form-field">
                            <label for="profileProvince">Province</label>
                            <input type="text" id="profileProvince" name="province">
                        </div>
                        <div class="form-field">
                            <label for="profileCountry">Country</label>
                            <input type="text" id="profileCountry" name="country">
                        </div>
                        <div class="form-field">
                            <label for="profileZipcode">Zip Code</label>
                            <input type="text" id="profileZipcode" name="zip_code">
                        </div>
                    </div>

                    <div class="password-section">
                        <h3>Change Password</h3>
                        <p class="form-hint">Leave blank to keep your current password.</p>
                        <div class="form-grid cols-3">
                            <div class="form-field">
                                <label for="profileCurrentPassword">Current Password</label>
                                <input type="password" id="profileCurrentPassword" name="current_password" autocomplete="current-password">
                            </div>
                            <div class="form-field">
                                <label for="profileNewPassword">New Password</label>
                                <input type="password" id="profileNewPassword" name="new_password" autocomplete="new-password">
                            </div>
                            <div class="form-field">
                                <label for="profileConfirmPassword">Confirm Password</label>
                                <input type="password" id="profileConfirmPassword" name="confirm_password" autocomplete="new-password">
                            </div>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <button type="button" id="profileCancelBtn" class="btn-outline">Reset</button>
                        <button type="submit" class="btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <div class="profile-toast" id="profileToast"></div>

    <script>var adminProfile = <?php echo json_encode($profile); ?>;</script>
    <script src="../../javascript/admin-routing.js?v=2.0"></script>
    <script src="../../javascript/admin_profile.js?v=2.0"></script>
</body>
</html>
