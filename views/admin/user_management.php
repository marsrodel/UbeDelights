<?php require_once __DIR__ . '/../../server/admin_auth.php';

$users = [];
if ($connect) {
    $sql = "SELECT user_id, username, first_name, middle_name, last_name, extension_name,
                   email, role, status, date_of_birth, age, sex,
                   street, barangay, city_municipality, province, country, zip_code
            FROM users WHERE status != 'pending'
            ORDER BY FIELD(role, 'super_admin', 'admin', 'customer'), user_id";
    $result = mysqli_query($connect, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $fullName = trim(implode(' ', array_filter([
                $row['first_name'],
                $row['middle_name'],
                $row['last_name'],
                $row['extension_name']
            ])));
            $users[] = [
                'id'       => $row['user_id'],
                'username' => $row['username'],
                'fullName' => $fullName,
                'firstName' => $row['first_name'],
                'middleName' => $row['middle_name'] ?? '',
                'lastName' => $row['last_name'],
                'extensionName' => $row['extension_name'] ?? '',
                'email'    => $row['email'],
                'role'     => $row['role'],
                'status'   => $row['status'] ?? 'pending',
                'dob'      => $row['date_of_birth'],
                'age'      => $row['age'],
                'sex'      => $row['sex'],
                'street'   => $row['street'],
                'barangay' => $row['barangay'],
                'city'     => $row['city_municipality'],
                'province' => $row['province'],
                'country'  => $row['country'],
                'zipCode'  => $row['zip_code'],
            ];
        }
    }
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
    <title>User Management - Ube Delights Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin_security.css?v=2.0">
    <link rel="stylesheet" href="../../css/user_management.css?v=2.0">
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
                <div class="admin-avatar">AU</div>
                <div class="admin-chip-info">
                    <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong>
                    <small>ADMIN</small>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a onclick="getAdminDashboard()" class="sidebar-link"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
            <a onclick="getAdminProducts()" class="sidebar-link"><i class="fa-solid fa-box"></i><span>Products</span></a>
            <a onclick="getAdminOrders()" class="sidebar-link"><i class="fa-solid fa-bag-shopping"></i><span>Orders</span><?php if ($orderCount > 0): ?><span class="sidebar-badge"><?php echo $orderCount; ?></span><?php endif; ?></a>
            <a onclick="getAdminUserManagement()" class="sidebar-link active"><i class="fa-solid fa-users-cog"></i><span>User Management</span></a>
            <a onclick="getAdminPendingApprovals()" class="sidebar-link"><i class="fa-solid fa-user-clock"></i><span>Pending Approvals</span><?php if ($pendingCount > 0): ?><span class="sidebar-badge"><?php echo $pendingCount; ?></span><?php endif; ?></a>
            <a onclick="getAdminSystemLogs()" class="sidebar-link"><i class="fa-solid fa-list-alt"></i><span>System Logs</span></a>
            <a onclick="getAdminProfile()" class="sidebar-link"><i class="fa-solid fa-user"></i><span>My Account</span></a>
        </nav>

        <div class="sidebar-footer">
            <a onclick="getAdminLogout()" class="sidebar-logout"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <h1>User Management</h1>
                <p class="topbar-subtitle">Manage system users and their permissions.</p>
            </div>
            <div class="topbar-right">
            </div>
        </header>

        <main class="admin-content">
            <div class="card">
                <div class="card-header-row">
                    <div class="card-header-left">
                        <h2><i class="fa-solid fa-users" style="color:var(--accent);"></i> Manage Users</h2>
                    </div>
                    <div class="card-header-right">
                        <div class="search-input-wrap">
                            <i class="fa-solid fa-search"></i>
                            <input type="text" id="userSearch" placeholder="Search users...">
                        </div>
                        <button class="btn-primary" id="btnSearchUsers" style="padding:10px 18px; font-size:0.85rem;">Search</button>
                        <select class="filter-select" id="roleFilter">
                            <option value="">All Roles</option>
                            <option value="super_admin">Super Admin</option>
                            <option value="admin">Admin</option>
                            <option value="customer">Customer</option>
                        </select>
                        <select class="filter-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="blocked">Blocked</option>
                            <option value="incomplete">Incomplete</option>
                        </select>
                    </div>
                </div>
                <div class="table-container">
                    <table class="data-table" id="usersTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody"></tbody>
                    </table>
                </div>
                <div class="pagination-bar" id="paginationContainer">
                    <div class="pagination-info" id="paginationInfo"></div>
                    <div class="pagination" id="paginationLinks"></div>
                </div>
            </div>

            <div class="empty-state" id="emptyUsers" style="display:none;">
                <div class="empty-icon">👥</div>
                <h3>No users found</h3>
                <p>No users match your current filters.</p>
            </div>
        </main>
    </div>

    <!-- View User Modal -->
    <div class="modal-overlay" id="viewUserModal" role="dialog" aria-modal="true">
        <div class="modal">
            <div class="modal-header">
                <h2>User Details</h2>
                <button class="modal-close" onclick="closeModal('viewUserModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" id="viewUserBody"></div>
            <div class="modal-footer">
                <button class="btn-outline" onclick="closeModal('viewUserModal')">Close</button>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal-overlay" id="editUserModal" role="dialog" aria-modal="true">
        <div class="modal">
            <div class="modal-header">
                <h2>Edit User</h2>
                <button class="modal-close" onclick="closeModal('editUserModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" id="editUserBody">
                <div class="um-view-form">
                    <input type="hidden" id="editUserId">
                    <div class="section-block">
                        <div class="section-title">Personal Information</div>
                        <div class="fields-row cols-4">
                            <div class="form-group">
                                <label>ID Number <span class="required">*</span></label>
                                <input type="text" id="editIdNumber" readonly>
                            </div>
                            <div class="form-group">
                                <label>First Name <span class="required">*</span></label>
                                <input type="text" id="editFirstName">
                            </div>
                            <div class="form-group">
                                <label>Middle Name <span class="optional" style="color:var(--danger);">(Optional)</span></label>
                                <input type="text" id="editMiddleName">
                            </div>
                            <div class="form-group">
                                <label>Last Name <span class="required">*</span></label>
                                <input type="text" id="editLastName">
                            </div>
                        </div>
                        <div class="fields-row cols-4">
                            <div class="form-group">
                                <label>Extension Name <span class="optional" style="color:var(--danger);">(Optional)</span></label>
                                <input type="text" id="editExtensionName" placeholder="Jr, Sr, III">
                            </div>
                            <div class="form-group">
                                <label>Date of Birth <span class="required">*</span></label>
                                <input type="date" id="editDob">
                            </div>
                            <div class="form-group">
                                <label>Age <span class="required">*</span></label>
                                <input type="text" id="editAge" readonly>
                            </div>
                            <div class="form-group">
                                <label>Sex <span class="required">*</span></label>
                                <select id="editSex">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="section-block">
                        <div class="section-title">Address Information</div>
                        <div class="fields-row cols-3">
                            <div class="form-group">
                                <label>Purok/Street <span class="required">*</span></label>
                                <input type="text" id="editStreet">
                            </div>
                            <div class="form-group">
                                <label>Barangay <span class="required">*</span></label>
                                <input type="text" id="editBarangay">
                            </div>
                            <div class="form-group">
                                <label>City/Municipality <span class="required">*</span></label>
                                <input type="text" id="editCity">
                            </div>
                        </div>
                        <div class="fields-row cols-3">
                            <div class="form-group">
                                <label>Province <span class="required">*</span></label>
                                <input type="text" id="editProvince">
                            </div>
                            <div class="form-group">
                                <label>Country <span class="required">*</span></label>
                                <input type="text" id="editCountry">
                            </div>
                            <div class="form-group">
                                <label>Zip Code <span class="required">*</span></label>
                                <input type="text" id="editZipcode">
                            </div>
                        </div>
                    </div>
                    <div class="section-block">
                        <div class="section-title">Account Information</div>
                        <div class="fields-row cols-3">
                            <div class="form-group">
                                <label>Email <span class="required">*</span></label>
                                <input type="email" id="editEmail">
                            </div>
                            <div class="form-group">
                                <label>Username <span class="required">*</span></label>
                                <input type="text" id="editUsername">
                            </div>
                            <div class="form-group"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-outline" onclick="closeModal('editUserModal')">Cancel</button>
                <button class="btn-primary" id="editUserSave"><i class="fa-solid fa-check"></i> Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Block/Unblock Confirmation Modal -->
    <div class="modal-overlay" id="blockModal" role="dialog" aria-modal="true">
        <div class="modal" style="max-width:450px;">
            <div class="modal-header" style="border-bottom:none;">
                <h2 id="blockModalTitle">Confirm Status Change</h2>
                <button class="modal-close" onclick="closeModal('blockModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" style="padding:0 24px 24px;">
                <p id="blockModalMessage" style="color:var(--text-secondary); font-size:0.95rem;">Block this user? They will not be able to log in.</p>
                <input type="hidden" id="blockUserId">
                <input type="hidden" id="blockAction">
            </div>
            <div class="modal-footer" style="border-top:none;">
                <button class="btn-outline" onclick="closeModal('blockModal')">Cancel</button>
                <button class="btn-primary" id="blockConfirmBtn">Yes</button>
            </div>
        </div>
    </div>

    <!-- Block Password Confirmation Modal -->
    <div class="modal-overlay" id="blockPasswordModal" role="dialog" aria-modal="true">
        <div class="modal" style="max-width:450px;">
            <div class="modal-header">
                <h2 id="blockPasswordTitle">Enter your password to block this user.</h2>
                <button class="modal-close" onclick="closeModal('blockPasswordModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form class="modal-form">
                <p style="color:var(--text-secondary); font-size:0.82rem;">Enter your account password to confirm this action.</p>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="blockPasswordInput" placeholder="Enter password">
                </div>
                <input type="hidden" id="blockPasswordUserId">
                <input type="hidden" id="blockPasswordAction">
            </form>
            <div class="modal-footer">
                <button class="btn-outline" onclick="closeModal('blockPasswordModal')">Cancel</button>
                <button class="btn-primary" id="blockPasswordConfirmBtn"><i class="fa-solid fa-check"></i> Confirm</button>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal-overlay" id="resetPasswordModal" role="dialog" aria-modal="true">
        <div class="modal" style="max-width:450px;">
            <div class="modal-header">
                <h2>Reset Customer Password</h2>
                <button class="modal-close" onclick="closeModal('resetPasswordModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form class="modal-form">
                <p style="color:var(--text-secondary); font-size:0.82rem;">Set a new password for this user. They will use it the next time they log in.</p>
                <div class="form-group">
                    <label>New password</label>
                    <input type="password" id="resetNewPassword" placeholder="Enter new password">
                </div>
                <div class="form-group">
                    <label>Confirm password</label>
                    <input type="password" id="resetConfirmPassword" placeholder="Confirm new password">
                </div>
                <input type="hidden" id="resetPasswordUserId">
            </form>
            <div class="modal-footer">
                <button class="btn-outline" onclick="closeModal('resetPasswordModal')">Cancel</button>
                <button class="btn-primary" id="resetPasswordConfirmBtn"><i class="fa-solid fa-key"></i> Reset Password</button>
            </div>
        </div>
    </div>

    <!-- Request Deletion Modal -->
    <div class="modal-overlay" id="requestDeletionModal" role="dialog" aria-modal="true">
        <div class="modal" style="max-width:450px;">
            <div class="modal-header">
                <h2>Request Account Deletion</h2>
                <button class="modal-close" onclick="closeModal('requestDeletionModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form class="modal-form">
                <p style="color:var(--text-secondary); font-size:0.82rem;">A deletion request will be sent to the super admin for approval. The account will not be removed until it is approved.</p>
                <div class="form-group">
                    <label>Reason <span class="required">*</span></label>
                    <textarea id="deletionReason" placeholder="Explain why this account should be deleted" rows="4"></textarea>
                </div>
                <input type="hidden" id="deletionUserId">
            </form>
            <div class="modal-footer">
                <button class="btn-outline" onclick="closeModal('requestDeletionModal')">Cancel</button>
                <button class="btn-primary" id="deletionConfirmBtn"><i class="fa-solid fa-paper-plane"></i> Submit Request</button>
            </div>
        </div>
    </div>

    <!-- Add New User Modal -->
    <div class="modal-overlay add-user-modal" id="addUserModal" role="dialog" aria-modal="true">
        <div class="modal">
            <div class="modal-body">
                <div class="register-container">
                    <div class="form-header">
                        <h2 id="addUserModalTitle"><i class="fa-solid fa-user-plus" style="color:var(--accent);"></i> Add New User</h2>
                        <button class="modal-close" onclick="closeModal('addUserModal')"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <form id="addUserForm">
                    <div class="form-sections">
                        <div class="section">
                            <h1>Personal Information</h1>
                            <div class="form-group">
                                <div class="form-box">
                                    <label for="id">ID Number <span class="required">*</span></label>
                                    <input type="text" id="id" placeholder="xxxx-xxxx" readonly>
                                </div>
                                <div class="form-box">
                                    <label for="fname">First Name <span class="required">*</span></label>
                                    <input type="text" id="fname">
                                </div>
                                <div class="form-box">
                                    <label for="mname">Middle Name <span class="optional">(optional)</span></label>
                                    <input type="text" id="mname">
                                </div>
                                <div class="form-box">
                                    <label for="lname">Last Name <span class="required">*</span></label>
                                    <input type="text" id="lname">
                                </div>
                                <div class="form-box">
                                    <label for="ename">Extension Name <span class="optional">(optional)</span></label>
                                    <input type="text" id="ename">
                                </div>
                                <div class="form-box">
                                    <label for="bday">Date of Birth <span class="required">*</span></label>
                                    <input type="date" id="bday" onchange="calculateAge()">
                                </div>
                                <div class="form-box">
                                    <label for="age">Age <span class="required">*</span></label>
                                    <input type="text" id="age" readonly>
                                </div>
                                <div class="form-box">
                                    <label for="sex">Sex <span class="required">*</span></label>
                                    <select id="sex">
                                        <option value="">-Select Sex-</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                                <div class="form-box">
                                    <label for="email">Email Address <span class="required">*</span></label>
                                    <input type="text" id="email">
                                </div>
                            </div>
                        </div>

                        <div class="section">
                            <h1>Address Information</h1>
                            <div class="form-group">
                                <div class="form-box">
                                    <label for="street">Purok/Street <span class="required">*</span></label>
                                    <input type="text" id="street">
                                </div>
                                <div class="form-box">
                                    <label for="brgy">Barangay <span class="required">*</span></label>
                                    <input type="text" id="brgy">
                                </div>
                                <div class="form-box">
                                    <label for="city">City/Municipality <span class="required">*</span></label>
                                    <input type="text" id="city">
                                </div>
                                <div class="form-box">
                                    <label for="province">Province <span class="required">*</span></label>
                                    <input type="text" id="province">
                                </div>
                                <div class="form-box">
                                    <label for="country">Country <span class="required">*</span></label>
                                    <input type="text" id="country">
                                </div>
                                <div class="form-box">
                                    <label for="zipcode">Zip Code <span class="required">*</span></label>
                                    <input type="number" id="zipcode">
                                </div>
                            </div>
                        </div>

                        <div class="section">
                            <h1>Account Information</h1>
                            <div class="form-group">
                                <div class="form-box">
                                    <label for="user">Username <span class="required">*</span></label>
                                    <input type="text" id="user">
                                </div>
                                <div class="form-box">
                                    <label for="role">Role <span class="required">*</span></label>
                                    <select id="role">
                                        <option value="">-Select Role-</option>
                                        <option value="customer">Customer</option>
                                        <option value="admin">Admin</option>
                                        <option value="super_admin">Super Admin</option>
                                    </select>
                                </div>
                                <div class="form-box">
                                    <label for="pass">Password <span class="required">*</span> <span id="pass-strength" class="field-hint"></span></label>
                                    <div class="password-wrapper">
                                        <input type="password" id="pass">
                                        <i class="fa-solid fa-eye-slash" id="eyeicon-register"></i>
                                    </div>
                                </div>
                                <div class="form-box">
                                    <label for="repass">Re-Enter Password <span class="required">*</span> <span id="repass-match" class="field-hint"></span></label>
                                    <input type="password" id="repass">
                                </div>
                            </div>
                        </div>
                    </div>
                    </form>

                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="closeModal('addUserModal')">Cancel</button>
                        <button type="button" class="add-btn" id="addUserSubmitBtn">Add User</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script src="../../javascript/admin-routing.js?v=2.0"></script>
    <script src="../../javascript/admin_security.js?v=3.0"></script>
    <script>var allUsers = <?php echo json_encode($users); ?>; var currentUserRole = <?php echo json_encode($_SESSION['auth_role'] ?? 'admin'); ?>; var currentUserId = <?php echo json_encode($_SESSION['auth_user_id'] ?? ''); ?>;</script>
    <script src="../../javascript/user_management.js?v=3.0"></script>
    <script src="../../javascript/inspect.js?v=2.0"></script>
</body>
</html>
