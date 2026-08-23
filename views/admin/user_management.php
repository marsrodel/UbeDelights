<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header('Location: ./login.php');
    exit();
}

$currentUser = [
    'username' => $_SESSION['username'] ?? 'Admin',
    'role' => $_SESSION['role'] ?? 'admin'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Ube Delights Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin_security.css?v=1.0">
</head>
<body class="admin-body">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <img src="../../images/logo.png" alt="Ube Delights" class="sidebar-logo">
            <div>
                <h2>Ube Delights</h2>
                <span class="sidebar-tag">Admin Panel</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a onclick="getAdminDashboard()" class="sidebar-link"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
            <a onclick="getAdminProducts()" class="sidebar-link"><i class="fa-solid fa-box"></i><span>Products</span></a>
            <a onclick="getAdminOrders()" class="sidebar-link"><i class="fa-solid fa-bag-shopping"></i><span>Orders</span></a>
            <a onclick="getAdminUserManagement()" class="sidebar-link active"><i class="fa-solid fa-users-cog"></i><span>User Management</span></a>
            <a onclick="getAdminPendingApprovals()" class="sidebar-link"><i class="fa-solid fa-user-clock"></i><span>Pending Approvals</span></a>
            <a onclick="getAdminSystemLogs()" class="sidebar-link"><i class="fa-solid fa-list-alt"></i><span>System Logs</span></a>
        </nav>

        <div class="sidebar-footer">
            <div class="admin-chip">
                <div class="admin-avatar">AU</div>
                <div class="admin-chip-info">
                    <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong>
                    <small><?php echo ucfirst($currentUser['role']); ?></small>
                </div>
            </div>
            <a onclick="getAdminLogout()" class="sidebar-logout"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="admin-main">
        <header class="admin-topbar">
            <h1>User Management</h1>
            <div class="topbar-right">
                <span class="topbar-date"><i class="fa-solid fa-calendar-days"></i><?php echo date('F j, Y'); ?></span>
            </div>
        </header>

        <main class="admin-content">
            <!-- Page Header -->
            <div class="content-header">
                <div>
                    <h2>Manage Users</h2>
                    <p>View, add, edit, and manage user accounts</p>
                </div>
                <button class="btn-primary" id="btnAddUser"><i class="fa-solid fa-plus"></i> Add User</button>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid" id="statsGrid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #7c3aed15; color: #7c3aed;"><i class="fa-solid fa-users"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="statTotalUsers">-</span>
                        <span class="stat-label">Total Users</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #22c55e15; color: #22c55e;"><i class="fa-solid fa-user-check"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="statActiveUsers">-</span>
                        <span class="stat-label">Active</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ef444415; color: #ef4444;"><i class="fa-solid fa-user-slash"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="statBlockedUsers">-</span>
                        <span class="stat-label">Blocked</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f59e0b15; color: #f59e0b;"><i class="fa-solid fa-user-clock"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="statPendingUsers">-</span>
                        <span class="stat-label">Pending</span>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <div class="search-box">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="userSearch" placeholder="Search users...">
                </div>
                <div class="filter-group">
                    <select id="roleFilter">
                        <option value="">All Roles</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="admin">Admin</option>
                        <option value="customer">Customer</option>
                    </select>
                    <select id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="blocked">Blocked</option>
                        <option value="pending">Pending</option>
                        <option value="incomplete">Incomplete</option>
                    </select>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <h2>All Users</h2>
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
                        <tbody id="usersTableBody">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container" id="paginationContainer">
                    <!-- Pagination rendered via JS -->
                </div>
            </div>

            <!-- Empty State -->
            <div class="empty-state" id="emptyUsers" style="display:none;">
                <div class="empty-icon">👥</div>
                <h3>No users found</h3>
                <p>No users match your current filters.</p>
            </div>
        </main>
    </div>

    <!-- Add User Modal -->
    <div class="modal-overlay" id="addUserModal" role="dialog" aria-modal="true" aria-labelledby="addUserModalTitle">
        <div class="modal">
            <div class="modal-header">
                <h2 id="addUserModalTitle">Add New User</h2>
                <button class="modal-close" id="addUserModalClose" aria-label="Close modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form class="modal-form" id="addUserForm">
                <input type="hidden" name="action" value="create_user">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="addIdNo">ID Number <span class="required">*</span></label>
                        <input type="text" id="addIdNo" name="idNo" required placeholder="e.g., 2026-0001">
                    </div>
                    <div class="form-group">
                        <label for="addUsername">Username <span class="required">*</span></label>
                        <input type="text" id="addUsername" name="username" required placeholder="e.g., johndoe">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="addEmail">Email <span class="required">*</span></label>
                        <input type="email" id="addEmail" name="email" required placeholder="e.g., john@example.com">
                    </div>
                    <div class="form-group">
                        <label for="addRole">Role <span class="required">*</span></label>
                        <select id="addRole" name="role" required>
                            <option value="">Select Role</option>
                            <option value="customer">Customer</option>
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="addPassword">Password <span class="required">*</span></label>
                        <input type="password" id="addPassword" name="password" required minlength="8" placeholder="Min 8 characters">
                    </div>
                    <div class="form-group">
                        <label for="addConfirmPassword">Confirm Password <span class="required">*</span></label>
                        <input type="password" id="addConfirmPassword" name="confirmPassword" required minlength="8">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Personal Information <span class="required">*</span></label>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="addFirstName">First Name <span class="required">*</span></label>
                        <input type="text" id="addFirstName" name="firstName" required>
                    </div>
                    <div class="form-group">
                        <label for="addMiddleName">Middle Name</label>
                        <input type="text" id="addMiddleName" name="middleName" maxlength="1" placeholder="M">
                    </div>
                    <div class="form-group">
                        <label for="addLastName">Last Name <span class="required">*</span></label>
                        <input type="text" id="addLastName" name="lastName" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="addExtension">Extension</label>
                        <input type="text" id="addExtension" name="extension" placeholder="Jr, Sr, III">
                    </div>
                    <div class="form-group">
                        <label for="addSex">Sex <span class="required">*</span></label>
                        <select id="addSex" name="sex" required>
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="addBirthday">Birthday <span class="required">*</span></label>
                        <input type="date" id="addBirthday" name="birthday" required max="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Address Information <span class="required">*</span></label>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="addPurok">Purok/Street <span class="required">*</span></label>
                        <input type="text" id="addPurok" name="purok" required>
                    </div>
                    <div class="form-group">
                        <label for="addBarangay">Barangay <span class="required">*</span></label>
                        <input type="text" id="addBarangay" name="barangay" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="addMunicipality">Municipality/City <span class="required">*</span></label>
                        <input type="text" id="addMunicipality" name="municipality" required>
                    </div>
                    <div class="form-group">
                        <label for="addProvince">Province <span class="required">*</span></label>
                        <input type="text" id="addProvince" name="province" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="addCountry">Country <span class="required">*</span></label>
                        <input type="text" id="addCountry" name="country" required>
                    </div>
                    <div class="form-group">
                        <label for="addZipCode">ZIP Code <span class="required">*</span></label>
                        <input type="text" id="addZipCode" name="zipCode" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-outline" id="addUserModalCancel">Cancel</button>
                    <button type="submit" class="btn-primary" id="addUserSubmitBtn"><i class="fa-solid fa-save"></i> Add User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal-overlay" id="editUserModal" role="dialog" aria-modal="true" aria-labelledby="editUserModalTitle">
        <div class="modal">
            <div class="modal-header">
                <h2 id="editUserModalTitle">Edit User</h2>
                <button class="modal-close" id="editUserModalClose" aria-label="Close modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form class="modal-form" id="editUserForm">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="user_id" id="editUserId">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="editIdNo">ID Number</label>
                        <input type="text" id="editIdNo" disabled style="background: var(--bg-main);">
                    </div>
                    <div class="form-group">
                        <label for="editUsername">Username <span class="required">*</span></label>
                        <input type="text" id="editUsername" name="username" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editEmail">Email <span class="required">*</span></label>
                        <input type="email" id="editEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="editRole">Role <span class="required">*</span></label>
                        <select id="editRole" name="role" required>
                            <option value="customer">Customer</option>
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editFirstName">First Name <span class="required">*</span></label>
                        <input type="text" id="editFirstName" name="firstName" required>
                    </div>
                    <div class="form-group">
                        <label for="editMiddleName">Middle Name</label>
                        <input type="text" id="editMiddleName" name="middleName" maxlength="1">
                    </div>
                    <div class="form-group">
                        <label for="editLastName">Last Name <span class="required">*</span></label>
                        <input type="text" id="editLastName" name="lastName" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editExtension">Extension</label>
                        <input type="text" id="editExtension" name="extension">
                    </div>
                    <div class="form-group">
                        <label for="editSex">Sex <span class="required">*</span></label>
                        <select id="editSex" name="sex" required>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editBirthday">Birthday <span class="required">*</span></label>
                        <input type="date" id="editBirthday" name="birthday" required max="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Address Information <span class="required">*</span></label>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editPurok">Purok/Street <span class="required">*</span></label>
                        <input type="text" id="editPurok" name="purok" required>
                    </div>
                    <div class="form-group">
                        <label for="editBarangay">Barangay <span class="required">*</span></label>
                        <input type="text" id="editBarangay" name="barangay" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editMunicipality">Municipality/City <span class="required">*</span></label>
                        <input type="text" id="editMunicipality" name="municipality" required>
                    </div>
                    <div class="form-group">
                        <label for="editProvince">Province <span class="required">*</span></label>
                        <input type="text" id="editProvince" name="province" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editCountry">Country <span class="required">*</span></label>
                        <input type="text" id="editCountry" name="country" required>
                    </div>
                    <div class="form-group">
                        <label for="editZipCode">ZIP Code <span class="required">*</span></label>
                        <input type="text" id="editZipCode" name="zipCode" required>
                    </div>
                </div>

                <!-- Password Update Section (Super Admin only) -->
                <div id="editPasswordSection" style="display:none;">
                    <hr style="margin: 16px 0; border-color: var(--border-light);">
                    <div style="font-size: 12px; font-weight: 600; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">
                        <i class="fa-solid fa-lock" style="margin-right: 6px;"></i> Update Password <span style="font-weight: 400; color: var(--text-muted); font-size: 10px;">(Super Admin Only — leave blank to keep current)</span>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="editNewPassword">New Password</label>
                            <input type="password" id="editNewPassword" name="new_password" placeholder="Leave blank to keep current" minlength="8">
                        </div>
                        <div class="form-group">
                            <label for="editConfirmPassword">Confirm New Password</label>
                            <input type="password" id="editConfirmPassword" name="confirmPassword" placeholder="Confirm new password">
                            <div id="editPasswordMatchMsg" style="font-size: 12px; margin-top: 4px;"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-outline" id="editUserModalCancel">Cancel</button>
                    <button type="submit" class="btn-primary" id="editUserSubmitBtn"><i class="fa-solid fa-save"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- View User Modal -->
    <div class="modal-overlay" id="viewUserModal" role="dialog" aria-modal="true" aria-labelledby="viewUserModalTitle">
        <div class="modal" style="max-width: 700px;">
            <div class="modal-header">
                <h2 id="viewUserModalTitle">User Details</h2>
                <button class="modal-close" id="viewUserModalClose" aria-label="Close modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" id="viewUserBody">
                <div style="text-align: center; padding: 40px;">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size: 24px; color: var(--accent);"></i>
                    <p style="margin-top: 10px; color: var(--text-secondary);">Loading user data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-outline" id="viewUserModalCloseBtn">Close</button>
                <button class="btn-primary" id="viewEditBtn" style="display:none;"><i class="fa-solid fa-pen-to-square"></i> Edit User</button>
            </div>
        </div>
    </div>

    <!-- Block/Unblock Confirmation Modal -->
    <div class="modal-overlay" id="blockModal" role="dialog" aria-modal="true" aria-labelledby="blockModalTitle">
        <div class="modal" style="max-width: 450px;">
            <div class="modal-header">
                <h2 id="blockModalTitle">Block User</h2>
                <button class="modal-close" id="blockModalClose" aria-label="Close modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to <strong id="blockActionText">block</strong> this user?</p>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 8px;">User: <strong id="blockUserName"></strong></p>
                <input type="hidden" id="blockUserId">
                <input type="hidden" id="blockActionType">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" id="blockModalCancel">Cancel</button>
                <button type="button" class="btn-primary" id="blockConfirmBtn" style="background: var(--danger);"><i class="fa-solid fa-ban"></i> <span id="blockConfirmText">Block</span></button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
        <div class="modal" style="max-width: 500px;">
            <div class="modal-header">
                <h2 id="deleteModalTitle"><i class="fa-solid fa-trash-can" style="color: var(--danger); margin-right: 8px;"></i> Delete User</h2>
                <button class="modal-close" id="deleteModalClose" aria-label="Close modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius-sm); padding: 16px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                        <i class="fa-solid fa-triangle-exclamation" style="color: var(--danger); font-size: 1.5rem;"></i>
                        <div>
                            <strong style="color: var(--danger);">Super Admin Only Action</strong>
                            <div style="font-size: 0.85rem; color: var(--text-secondary);">Only Super Admins can delete users. This action is permanent.</div>
                        </div>
                    </div>
                    <p style="margin: 0; color: var(--text-secondary);">You are about to permanently delete <strong id="deleteUserName"></strong>.</p>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); display: block; margin-bottom: 6px;">Reason for deletion (required)</label>
                    <textarea id="deleteReason" rows="3" placeholder="Enter reason for deletion..." style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-sm); font-family: inherit;"></textarea>
                </div>
                <input type="hidden" id="deleteUserId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" id="deleteModalCancel">Cancel</button>
                <button type="button" class="btn-primary" id="deleteConfirmBtn" style="background: var(--danger);"><i class="fa-solid fa-trash-can"></i> Delete Permanently</button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toast"></div>

    <!-- Scripts -->
    <script src="../../javascript/admin-routing.js"></script>
    <script src="../../javascript/admin_security.js"></script>
    <script>
        // Initialize user management
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof initUserManagement === 'function') {
                initUserManagement();
            }
        });
    </script>
</body>
</html>