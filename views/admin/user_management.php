<?php require_once __DIR__ . '/../../server/admin_auth.php';

$users = [];
if ($connect) {
    $sql = "SELECT user_id, username, CONCAT(first_name, ' ', IFNULL(CONCAT(middle_name, ' '), ''), last_name, IF(IFNULL(extension_name, '') != '', CONCAT(' ', extension_name), '')) AS fullName, email, role, status FROM users WHERE status != 'pending' ORDER BY FIELD(role, 'super_admin', 'admin', 'customer'), user_id";
    $result = mysqli_query($connect, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $users[] = [
                'id'       => $row['user_id'],
                'username' => $row['username'],
                'fullName' => trim($row['fullName']),
                'email'    => $row['email'],
                'role'     => $row['role'],
                'status'   => $row['status'] ?? 'pending',
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
                <button class="btn-primary" id="btnAddUser" style="padding:10px 12px; font-size:0.95rem; border-radius:10px;" title="Add User" onclick="document.getElementById('addUserModal').classList.add('active'); document.body.style.overflow='hidden';"><i class="fa-solid fa-user-plus"></i></button>
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
        <div class="modal" style="max-width:650px;">
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
            <form class="modal-form" id="editUserForm">
                <input type="hidden" id="editUserId">
                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="editFullName" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="editEmail" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Role</label>
                        <select id="editRole">
                            <option value="customer">Customer</option>
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="editStatus">
                            <option value="active">Active</option>
                            <option value="blocked">Blocked</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>
            </form>
            <div class="modal-footer">
                <button class="btn-outline" onclick="closeModal('editUserModal')">Cancel</button>
                <button class="btn-primary" id="editUserSave"><i class="fa-solid fa-check"></i> Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Block/Unblock Confirmation Modal -->
    <div class="modal-overlay" id="blockModal" role="dialog" aria-modal="true">
        <div class="modal" style="max-width:450px;">
            <div class="modal-header">
                <h2 id="blockModalTitle">Block User</h2>
                <button class="modal-close" onclick="closeModal('blockModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <p id="blockModalMessage">Are you sure you want to block this user?</p>
                <p style="color:var(--text-secondary); font-size:0.9rem; margin-top:8px;">User: <strong id="blockModalUserName"></strong></p>
                <input type="hidden" id="blockUserId">
                <input type="hidden" id="blockAction">
            </div>
            <div class="modal-footer">
                <button class="btn-outline" onclick="closeModal('blockModal')">Cancel</button>
                <button class="btn-primary" id="blockConfirmBtn"><i class="fa-solid fa-check"></i> <span id="blockConfirmText">Confirm</span></button>
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
    <script>var allUsers = <?php echo json_encode($users); ?>; var currentUserRole = <?php echo json_encode($_SESSION['auth_role'] ?? 'admin'); ?>;</script>
    <script src="../../javascript/user_management.js?v=3.0"></script>
    <script src="../../javascript/inspect.js?v=2.0"></script>
</body>
</html>
