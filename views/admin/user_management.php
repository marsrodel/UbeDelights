<?php
session_start();

// Temporary bypass — no backend auth yet
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'Admin';
    $_SESSION['role'] = 'super_admin';
}

$currentUser = [
    'username' => $_SESSION['username'] ?? 'Admin',
    'role' => $_SESSION['role'] ?? 'admin'
];

$mockUsers = [
    ['id' => '2011-1000', 'username' => 'winwin', 'fullName' => 'Win Sagaad', 'email' => 'sagaadwindelyn.csucc@gmail.com', 'role' => 'super_admin', 'status' => 'active'],
    ['id' => '2020-1153', 'username' => 'windy.sagaad', 'fullName' => 'Windelyn Sagaad', 'email' => 'windelyn.sagaad@csucc.edu.ph', 'role' => 'super_admin', 'status' => 'blocked'],
    ['id' => '2023-0123', 'username' => 'usertest', 'fullName' => 'Rodel Dam Bitch', 'email' => 'user@gmail.com', 'role' => 'admin', 'status' => 'active'],
    ['id' => '2020-1111', 'username' => 'lynlyn', 'fullName' => 'Lynlyn Sgaad', 'email' => 'windelynsagaad26@gmail.com', 'role' => 'admin', 'status' => 'blocked'],
    ['id' => '2020-6000', 'username' => 'aljun', 'fullName' => 'Aljun Sagaad', 'email' => 'aljunsagaad@gmail.com', 'role' => 'customer', 'status' => 'active'],
    ['id' => '2025-0001', 'username' => 'maria.santos', 'fullName' => 'Maria Santos', 'email' => 'maria.santos@gmail.com', 'role' => 'customer', 'status' => 'active'],
    ['id' => '2025-0002', 'username' => 'juan.dc', 'fullName' => 'Juan Dela Cruz', 'email' => 'juandc@yahoo.com', 'role' => 'customer', 'status' => 'active'],
    ['id' => '2025-0003', 'username' => 'ana.reyes', 'fullName' => 'Ana Reyes', 'email' => 'ana.reyes@outlook.com', 'role' => 'customer', 'status' => 'active'],
    ['id' => '2025-0004', 'username' => 'carlo.mendoza', 'fullName' => 'Carlo Mendoza', 'email' => 'carlo.mendoza@gmail.com', 'role' => 'customer', 'status' => 'blocked'],
    ['id' => '2025-0005', 'username' => 'lisa.garcia', 'fullName' => 'Lisa Garcia', 'email' => 'lisagarcia@gmail.com', 'role' => 'customer', 'status' => 'active'],
    ['id' => '2025-0006', 'username' => 'paolo.b', 'fullName' => 'Paolo Bautista', 'email' => 'paolo.b@gmail.com', 'role' => 'customer', 'status' => 'active'],
    ['id' => '2025-0007', 'username' => 'grace.lim', 'fullName' => 'Grace Lim', 'email' => 'grace.lim@yahoo.com', 'role' => 'customer', 'status' => 'blocked'],
    ['id' => '2025-0008', 'username' => 'miguel.t', 'fullName' => 'Miguel Torres', 'email' => 'miguel.torres@gmail.com', 'role' => 'customer', 'status' => 'active'],
    ['id' => '2025-0009', 'username' => 'ana.ramos', 'fullName' => 'Ana Ramos', 'email' => 'ana.ramos@gmail.com', 'role' => 'customer', 'status' => 'pending'],
    ['id' => '2025-0010', 'username' => 'carlos.m', 'fullName' => 'Carlos Mendoza', 'email' => 'carlos.m@yahoo.com', 'role' => 'customer', 'status' => 'pending'],
    ['id' => '2025-0011', 'username' => 'liza.santos', 'fullName' => 'Liza Santos', 'email' => 'liza.s@outlook.com', 'role' => 'customer', 'status' => 'active'],
    ['id' => '2025-0012', 'username' => 'mark.dela', 'fullName' => 'Mark Dela Peña', 'email' => 'mark.delapena@gmail.com', 'role' => 'customer', 'status' => 'active'],
    ['id' => '2025-0013', 'username' => 'jenny.cruz', 'fullName' => 'Jenny Cruz', 'email' => 'jenny.cruz@yahoo.com', 'role' => 'customer', 'status' => 'active'],
    ['id' => '2025-0014', 'username' => 'paul.v', 'fullName' => 'Paul Villanueva', 'email' => 'paul.v@gmail.com', 'role' => 'customer', 'status' => 'active'],
    ['id' => '2025-0015', 'username' => 'sara.g', 'fullName' => 'Sara Garcia', 'email' => 'sara.g@outlook.com', 'role' => 'customer', 'status' => 'active'],
    ['id' => '2025-0016', 'username' => 'danny.r', 'fullName' => 'Danny Reyes', 'email' => 'danny.r@gmail.com', 'role' => 'customer', 'status' => 'active'],
    ['id' => '2025-0017', 'username' => 'kate.m', 'fullName' => 'Kate Morales', 'email' => 'kate.m@yahoo.com', 'role' => 'customer', 'status' => 'active'],
    ['id' => '2025-0018', 'username' => 'brian.s', 'fullName' => 'Brian Santos', 'email' => 'brian.s@gmail.com', 'role' => 'customer', 'status' => 'active'],
    ['id' => '2025-0019', 'username' => 'rosie.l', 'fullName' => 'Rosie Lim', 'email' => 'rosie.l@outlook.com', 'role' => 'customer', 'status' => 'active'],
    ['id' => '2025-0020', 'username' => 'tom.n', 'fullName' => 'Tom Navarro', 'email' => 'tom.n@gmail.com', 'role' => 'customer', 'status' => 'active'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Ube Delights Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin_security.css?v=1.5">
    <style>
        .card-header-row { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid var(--border); gap: 16px; flex-wrap: wrap; }
        .card-header-left { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .card-header-left h2 { margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--text-primary); }
        .card-header-right { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .search-input-wrap { position: relative; min-width: 200px; }
        .search-input-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 14px; }
        .search-input-wrap input {
            width: 100%; padding: 10px 14px 10px 38px; border: 1px solid var(--border); border-radius: var(--radius-sm);
            font-family: var(--font-body); font-size: 0.85rem; color: var(--text-primary); background: var(--bg-main);
            outline: none; transition: var(--transition);
        }
        .search-input-wrap input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(118, 75, 162, 0.1); }
        .filter-select {
            padding: 10px 14px; border: 1px solid var(--border); border-radius: var(--radius-sm);
            font-family: var(--font-body); font-size: 0.85rem; color: var(--text-primary); background: var(--bg-main);
            outline: none; min-width: 130px; transition: var(--transition);
        }
        .filter-select:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(118, 75, 162, 0.1); }
        .role-pill { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
        .role-super_admin { background: #ede9fe; color: #5b21b6; }
        .role-admin { background: #dbeafe; color: #1d4ed8; }
        .role-customer { background: #f3f4f6; color: #374151; }
        .status-pill { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; }
        .status-active { background: #dcfce7; color: #15803d; }
        .status-blocked { background: #fee2e2; color: #dc2626; }
        .status-pending { background: #fef3c7; color: #b45309; }
        .status-incomplete { background: #fef3c7; color: #b45309; }
        .um-action-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 30px; height: 30px; border-radius: 8px; border: none;
            cursor: pointer; font-size: 12px; transition: var(--transition);
        }
        .um-action-btn:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .um-action-btn.btn-view { background: var(--text-primary); color: #fff; }
        .um-action-btn.btn-edit { background: #dbeafe; color: #1d4ed8; }
        .um-action-btn.btn-block { background: #dcfce7; color: #15803d; }
        .um-action-btn.btn-unblock { background: #fee2e2; color: #dc2626; }
        .um-action-btn.btn-delete { background: #fee2e2; color: #dc2626; }
        .pagination-bar { display: flex; align-items: center; justify-content: flex-end; padding: 16px 20px; border-top: 1px solid var(--border); }
        .pagination { display: flex; gap: 6px; flex-wrap: wrap; }
        .pagination-link { display: inline-flex; align-items: center; justify-content: center; min-width: 34px; height: 34px; padding: 0 8px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; color: var(--text-secondary); border: 1px solid var(--border); background: #fff; cursor: pointer; transition: var(--transition); }
        .pagination-link:hover { border-color: var(--accent); color: var(--accent); }
        .pagination-link.current { background: var(--primary-gradient); color: #fff; border-color: transparent; }
        .pagination-link.disabled { opacity: 0.4; pointer-events: none; }
        .info-item { background: var(--bg-main); padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border); }
        .info-label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px; }
        .info-value { font-size: 14px; color: var(--text-primary); font-weight: 600; }

        /* Add User Modal — compact header, matching form corners, bigger buttons */
        .add-user-modal .modal { max-width: 960px; max-height: 90vh; display: flex; flex-direction: column; border-radius: 12px; }
        .add-user-modal .modal-body { padding: 0; overflow-y: auto; flex: 1; }
        .add-user-modal .form-header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 0; margin-bottom: 4px;
        }
        .add-user-modal .form-header h2 { font-size: 1.05rem; margin: 0; border-bottom: none; color: #764ba2; }
        .add-user-modal .modal-close { width: 30px; height: 30px; border-radius: 50%; background: transparent; border: none; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 1rem; transition: var(--transition); }
        .add-user-modal .modal-close:hover { background: var(--bg-main); color: var(--text-primary); }
        .add-user-modal .register-container {
            background: #ffffff; border-radius: 10px; border: 3px solid rgba(102, 126, 234, 0.6);
            padding: 4px 15px 2px; margin: 0; width: 100%; box-sizing: border-box;
        }
        .add-user-modal h1 {
            font-size: 0.95rem; margin: 0; padding: 0; border-bottom: none; color: #340146c2; font-weight: 600;
        }
        .add-user-modal .form-sections { display: block; width: 100%; }
        .add-user-modal .section { margin-bottom: 8px; }
        .add-user-modal .form-group { display: grid; grid-template-columns: repeat(3, 1fr); gap: 3px 3px; margin: 0; }
        .add-user-modal .form-box { position: relative; margin: 0; }
        .add-user-modal .form-box label {
            position: absolute; top: 2px; left: 12px; font-weight: 600; color: #340146c2; font-size: 0.9rem;
            line-height: 1.3; pointer-events: none; transition: all 0.2s ease; z-index: 1; margin: 0;
        }
        .add-user-modal .form-box input,
        .add-user-modal .form-box select {
            width: 100%; padding: 18px 12px 2px; border: 1px solid #cda7e6; border-radius: 6px; font-size: 0.875rem;
            background-color: #f9fafb; transition: border-color 0.2s ease; height: 40px; box-sizing: border-box;
            font-family: inherit; color: #1a1a2e;
        }
        .add-user-modal .form-box input:focus,
        .add-user-modal .form-box select:focus {
            outline: none; border-color: #667eea; box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }
        .add-user-modal .form-box input:hover,
        .add-user-modal .form-box select:hover { border-color: #b98fe0; background-color: #ffffff; }
        .add-user-modal .form-box input::placeholder { color: #4b4c4e; font-style: italic; }
        .add-user-modal .form-box select { cursor: pointer; appearance: none; -webkit-appearance: none; -moz-appearance: none; background-image: none; padding-right: 10px; }
        .add-user-modal .form-box input[type="number"]::-webkit-inner-spin-button,
        .add-user-modal .form-box input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        .add-user-modal .form-box input[type="number"] { -moz-appearance: textfield; }
        .add-user-modal .form-box input[readonly] { background: #f9fafb; color: #1a1a2e; }
        .add-user-modal .required { color: #dc2626; font-weight: bold; margin-left: 2px; }
        .add-user-modal .optional { color: #dc2626; font-weight: 500; font-size: 0.7rem; margin-left: 2px; }
        .add-user-modal .password-wrapper { position: relative; display: flex; align-items: center; }
        .add-user-modal .password-wrapper input { width: 100%; padding-right: 45px; }
        .add-user-modal .password-wrapper i {
            position: absolute; right: 12px; font-size: 18px; cursor: pointer; color: #6B7280; transition: color 0.3s ease;
        }
        .add-user-modal .field-error { color: #dc2626; font-size: 0.8rem; margin-top: 0; padding-left: 12px; }
        .add-user-modal .form-actions { display: flex; justify-content: center; align-items: center; gap: 12px; margin-top: 10px; margin-bottom: 8px; }
        .add-user-modal .btn-cancel {
            background: #fff; border: 1.5px solid #d1d5db; color: #374151;
            padding: 10px 30px; border-radius: 8px; font-size: 0.9rem; font-weight: 600;
            font-family: 'Poppins', sans-serif; cursor: pointer; transition: all 0.2s;
        }
        .add-user-modal .btn-cancel:hover { background: #f3f4f6; border-color: #9ca3af; }
        .add-user-modal .add-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;
            padding: 10px 30px; border-radius: 8px; font-size: 0.9rem; font-weight: 600;
            font-family: 'Poppins', sans-serif; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .add-user-modal .add-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4), 0 4px 10px rgba(0, 0, 0, 0.1); }
        .add-user-modal .add-btn:active { transform: translateY(-1px); }
    </style>
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
                    <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong>
                    <small><?php echo ucfirst($_SESSION['role'] ?? 'admin'); ?></small>
                </div>
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
            <a onclick="getAdminLogout()" class="sidebar-logout"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <h1>User Management</h1>
                <p style="margin:2px 0 0; font-size:0.85rem; color:var(--text-muted);">Manage system users and their permissions.</p>
            </div>
            <div class="topbar-right">
                <button class="btn-primary" id="btnAddUser" style="padding:10px 12px; font-size:0.95rem; border-radius:10px;" title="Add User"><i class="fa-solid fa-user-plus"></i></button>
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
                            <option value="pending">Pending</option>
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
                        <tbody id="usersTableBody">
                        </tbody>
                    </table>
                </div>
                <div class="pagination-bar" id="paginationContainer">
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
                        <h2><i class="fa-solid fa-user-plus" style="color:var(--accent);"></i> Add New User</h2>
                        <button class="modal-close" onclick="closeModal('addUserModal')"><i class="fa-solid fa-xmark"></i></button>
                    </div>
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

                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="closeModal('addUserModal')">Cancel</button>
                        <button type="button" class="add-btn" id="addUserSubmit">Add User</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script src="../../javascript/admin-routing.js"></script>
    <script src="../../javascript/admin_security.js"></script>
    <script src="../../javascript/register.js"></script>
    <script>
        var allUsers = <?php echo json_encode($mockUsers); ?>;
        var currentUserRole = <?php echo json_encode($_SESSION['role'] ?? 'admin'); ?>;
        var state = { page: 1, perPage: 10, filters: { search: '', role: '', status: '' } };

        function escapeHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

        function getRoleBadge(role) {
            var map = { 'super_admin': 'role-super_admin', 'admin': 'role-admin', 'customer': 'role-customer' };
            var label = { 'super_admin': 'Super Admin', 'admin': 'Admin', 'customer': 'Customer' };
            return '<span class="role-pill ' + (map[role]||'') + '">' + (label[role]||role) + '</span>';
        }

        function getStatusBadge(status) {
            var map = { 'active': 'status-active', 'blocked': 'status-blocked', 'pending': 'status-pending', 'incomplete': 'status-incomplete' };
            return '<span class="status-pill ' + (map[status]||'') + '">' + (status||'').charAt(0).toUpperCase() + (status||'').slice(1) + '</span>';
        }

        function getActions(user) {
            var html = '<div style="display:flex; gap:6px;">';
            html += '<button class="um-action-btn btn-view" onclick="viewUser(\'' + user.id + '\')" title="View"><i class="fa-solid fa-eye"></i></button>';
            html += '<button class="um-action-btn btn-edit" onclick="editUser(\'' + user.id + '\')" title="Edit"><i class="fa-solid fa-pen"></i></button>';
            if (user.status === 'active') {
                html += '<button class="um-action-btn btn-block" onclick="blockUser(\'' + user.id + '\')" title="Block"><i class="fa-solid fa-lock"></i></button>';
            } else if (user.status === 'blocked') {
                html += '<button class="um-action-btn btn-unblock" onclick="unblockUser(\'' + user.id + '\')" title="Unblock"><i class="fa-solid fa-lock-open"></i></button>';
            }
            if (currentUserRole === 'super_admin') {
                html += '<button class="um-action-btn btn-delete" onclick="deleteUser(\'' + user.id + '\')" title="Delete"><i class="fa-solid fa-trash"></i></button>';
            }
            html += '</div>';
            return html;
        }

        function filterUsers(users) {
            var f = state.filters;
            return users.filter(function(u) {
                if (f.search) {
                    var s = f.search.toLowerCase();
                    if (u.username.toLowerCase().indexOf(s) === -1 && u.fullName.toLowerCase().indexOf(s) === -1 && u.email.toLowerCase().indexOf(s) === -1 && u.id.toLowerCase().indexOf(s) === -1) return false;
                }
                if (f.role && u.role !== f.role) return false;
                if (f.status && u.status !== f.status) return false;
                return true;
            });
        }

        function renderTable(users) {
            var tbody = document.getElementById('usersTableBody');
            var emptyEl = document.getElementById('emptyUsers');
            if (!tbody) return;
            if (users.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted);">No users found</td></tr>';
                if (emptyEl) emptyEl.style.display = '';
                return;
            }
            if (emptyEl) emptyEl.style.display = 'none';
            var html = '';
            for (var i = 0; i < users.length; i++) {
                var u = users[i];
                html += '<tr>' +
                    '<td class="cell-id">' + escapeHtml(u.id) + '</td>' +
                    '<td>' + escapeHtml(u.username) + '</td>' +
                    '<td class="cell-strong">' + escapeHtml(u.fullName) + '</td>' +
                    '<td class="cell-muted">' + escapeHtml(u.email) + '</td>' +
                    '<td>' + getRoleBadge(u.role) + '</td>' +
                    '<td>' + getStatusBadge(u.status) + '</td>' +
                    '<td>' + getActions(u) + '</td>' +
                '</tr>';
            }
            tbody.innerHTML = html;
        }

        function renderPagination(total) {
            var container = document.getElementById('paginationContainer');
            if (!container) return;
            var page = state.page, perPage = state.perPage;
            var totalPages = Math.ceil(total / perPage);

            var html = '<div class="pagination">';
            var s = Math.max(1, page - 2), e = Math.min(totalPages, page + 2);
            for (var i = s; i <= e; i++) {
                if (i === page) html += '<span class="pagination-link current">' + i + '</span>';
                else html += '<a class="pagination-link" data-page="' + i + '">' + i + '</a>';
            }
            if (page < totalPages) html += '<a class="pagination-link" data-page="' + (page+1) + '">Next &raquo;</a>';
            else html += '<span class="pagination-link disabled">Next &raquo;</span>';
            html += '</div>';
            container.innerHTML = html;

            container.querySelectorAll('.pagination-link[data-page]').forEach(function(link) {
                link.addEventListener('click', function() { state.page = parseInt(this.getAttribute('data-page')); render(); });
            });
        }

        function render() {
            var filtered = filterUsers(allUsers);
            var start = (state.page - 1) * state.perPage;
            renderTable(filtered.slice(start, start + state.perPage));
            renderPagination(filtered.length);
        }

        function viewUser(id) {
            var u = allUsers.find(function(x) { return x.id === id; });
            if (!u) return;
            document.getElementById('viewUserBody').innerHTML =
                '<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">' +
                    '<div class="info-item"><div class="info-label">ID Number</div><div class="info-value">' + u.id + '</div></div>' +
                    '<div class="info-item"><div class="info-label">Username</div><div class="info-value">' + u.username + '</div></div>' +
                    '<div class="info-item"><div class="info-label">Full Name</div><div class="info-value">' + u.fullName + '</div></div>' +
                    '<div class="info-item"><div class="info-label">Email</div><div class="info-value">' + u.email + '</div></div>' +
                    '<div class="info-item"><div class="info-label">Role</div><div class="info-value">' + getRoleBadge(u.role) + '</div></div>' +
                    '<div class="info-item"><div class="info-label">Status</div><div class="info-value">' + getStatusBadge(u.status) + '</div></div>' +
                '</div>';
            document.getElementById('viewUserModal').classList.add('active');
        }

        function editUser(id) {
            var u = allUsers.find(function(x) { return x.id === id; });
            if (!u) return;
            document.getElementById('editUserId').value = u.id;
            document.getElementById('editFullName').value = u.fullName;
            document.getElementById('editEmail').value = u.email;
            document.getElementById('editRole').value = u.role;
            document.getElementById('editStatus').value = u.status;
            document.getElementById('editUserModal').classList.add('active');
        }

        function blockUser(id) {
            var u = allUsers.find(function(x) { return x.id === id; });
            if (!u) return;
            document.getElementById('blockModalTitle').textContent = 'Block User';
            document.getElementById('blockModalMessage').textContent = 'Are you sure you want to block this user?';
            document.getElementById('blockModalUserName').textContent = u.fullName;
            document.getElementById('blockUserId').value = id;
            document.getElementById('blockAction').value = 'block';
            document.getElementById('blockConfirmBtn').style.background = 'var(--danger)';
            document.getElementById('blockConfirmText').textContent = 'Block';
            document.getElementById('blockModal').classList.add('active');
        }

        function unblockUser(id) {
            var u = allUsers.find(function(x) { return x.id === id; });
            if (!u) return;
            document.getElementById('blockModalTitle').textContent = 'Unblock User';
            document.getElementById('blockModalMessage').textContent = 'Are you sure you want to unblock this user?';
            document.getElementById('blockModalUserName').textContent = u.fullName;
            document.getElementById('blockUserId').value = id;
            document.getElementById('blockAction').value = 'unblock';
            document.getElementById('blockConfirmBtn').style.background = 'var(--success)';
            document.getElementById('blockConfirmText').textContent = 'Unblock';
            document.getElementById('blockModal').classList.add('active');
        }

        function deleteUser(id) {
            var u = allUsers.find(function(x) { return x.id === id; });
            if (!u) return;
            if (!confirm('Are you sure you want to delete ' + u.fullName + '? This action cannot be undone.')) return;
            allUsers = allUsers.filter(function(x) { return x.id !== id; });
            render();
            showToast('User deleted successfully.', 'success');
        }

        function closeModal(id) { document.getElementById(id).classList.remove('active'); }
        function showToast(msg) {
            var t = document.getElementById('toast');
            t.textContent = msg; t.className = 'toast show';
            setTimeout(function() { t.classList.remove('show'); }, 3000);
        }

        function generateNextId() {
            var year = new Date().getFullYear();
            var maxSeq = 0;
            for (var i = 0; i < allUsers.length; i++) {
                var parts = allUsers[i].id.split('-');
                if (parts[0] === String(year)) {
                    var seq = parseInt(parts[1], 10);
                    if (seq > maxSeq) maxSeq = seq;
                }
            }
            var next = maxSeq + 1;
            var suffix = String(next).padStart(4, '0');
            return year + '-' + suffix;
        }

        document.getElementById('btnAddUser').addEventListener('click', function() {
            var fields = ['fname', 'mname', 'lname', 'ename', 'bday', 'age', 'sex', 'email',
                'street', 'brgy', 'city', 'province', 'country', 'zipcode',
                'user', 'pass', 'repass'];
            for (var i = 0; i < fields.length; i++) {
                var el = document.getElementById(fields[i]);
                if (el) el.value = '';
                var err = document.getElementById(fields[i] + '-error');
                if (err && err.parentNode) err.parentNode.removeChild(err);
            }
            var hint1 = document.getElementById('pass-strength');
            if (hint1) hint1.textContent = '';
            var hint2 = document.getElementById('repass-match');
            if (hint2) hint2.textContent = '';
            var eyeIcon = document.getElementById('eyeicon-register');
            if (eyeIcon) { eyeIcon.className = 'fa-solid fa-eye-slash'; }
            var passField = document.getElementById('pass');
            if (passField) passField.type = 'password';
            document.getElementById('id').value = 'Loading...';
            document.getElementById('addUserModal').classList.add('active');

            fetch('../../server/generate_id.php')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    document.getElementById('id').value = data.id || '0000-0000';
                })
                .catch(function() {
                    document.getElementById('id').value = generateNextId();
                });
        });

        function clearAllRequiredErrors() {
            var errs = document.querySelectorAll('#addUserModal [id$="-error"]');
            for (var i = 0; i < errs.length; i++) {
                if (/this field is required/i.test(errs[i].textContent || '')) {
                    errs[i].parentNode && errs[i].parentNode.removeChild(errs[i]);
                }
            }
        }
        document.getElementById('addUserModal').addEventListener('focusin', function() {
            clearAllRequiredErrors();
        });
        document.getElementById('addUserModal').addEventListener('input', function() {
            clearAllRequiredErrors();
        });
        document.getElementById('addUserModal').addEventListener('change', function() {
            clearAllRequiredErrors();
        });

        document.getElementById('addUserSubmit').addEventListener('click', function() {
            var prevErrors = document.querySelectorAll('#addUserModal [id$="-error"]');
            for (var c = 0; c < prevErrors.length; c++) {
                prevErrors[c].parentNode && prevErrors[c].parentNode.removeChild(prevErrors[c]);
            }
            var requiredFields = ['fname','lname','bday','age','sex','email','street','brgy','city','province','country','zipcode','user','role','pass','repass'];
            var anyEmpty = false;
            for (var i = 0; i < requiredFields.length; i++) {
                var f = document.getElementById(requiredFields[i]);
                if (!f) continue;
                var v = (f.value || '').trim();
                if (f.tagName === 'SELECT') v = f.value;
                if (v === '') {
                    showErrorMessage(requiredFields[i], 'This field is required');
                    anyEmpty = true;
                }
            }
            if (anyEmpty) return;

            var fn = document.getElementById('fname').value.trim();
            var mn = document.getElementById('mname').value.trim();
            var ln = document.getElementById('lname').value.trim();
            var en = document.getElementById('ename').value.trim();
            var fullName = fn + (mn ? ' ' + mn : '') + ' ' + ln + (en ? ' ' + en : '');
            var newUser = {
                id: document.getElementById('id').value.trim(),
                username: document.getElementById('user').value.trim(),
                fullName: fullName.replace(/\s+/g, ' ').trim(),
                email: document.getElementById('email').value.trim(),
                role: document.getElementById('role').value,
                status: 'active'
            };
            allUsers.unshift(newUser);
            closeModal('addUserModal');
            state.page = 1;
            render();
            showToast('User added successfully.', 'success');
        });

        document.getElementById('btnSearchUsers').addEventListener('click', function() {
            state.filters.search = document.getElementById('userSearch').value.trim();
            state.filters.role = document.getElementById('roleFilter').value;
            state.filters.status = document.getElementById('statusFilter').value;
            state.page = 1; render();
        });
        document.getElementById('userSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') { state.filters.search = this.value.trim(); state.page = 1; render(); }
        });
        document.getElementById('roleFilter').addEventListener('change', function() { state.filters.role = this.value; state.page = 1; render(); });
        document.getElementById('statusFilter').addEventListener('change', function() { state.filters.status = this.value; state.page = 1; render(); });

        document.getElementById('editUserSave').addEventListener('click', function() {
            var id = document.getElementById('editUserId').value;
            var u = allUsers.find(function(x) { return x.id === id; });
            if (u) {
                u.fullName = document.getElementById('editFullName').value;
                u.email = document.getElementById('editEmail').value;
                u.role = document.getElementById('editRole').value;
                u.status = document.getElementById('editStatus').value;
            }
            closeModal('editUserModal');
            render();
            showToast('User updated successfully.', 'success');
        });

        document.getElementById('blockConfirmBtn').addEventListener('click', function() {
            var id = document.getElementById('blockUserId').value;
            var action = document.getElementById('blockAction').value;
            var u = allUsers.find(function(x) { return x.id === id; });
            if (u) u.status = action === 'block' ? 'blocked' : 'active';
            closeModal('blockModal');
            render();
            showToast(action === 'block' ? 'User blocked.' : 'User unblocked.', 'success');
        });

        document.querySelectorAll('.modal-overlay').forEach(function(o) {
            o.addEventListener('click', function(e) { if (e.target === o) o.classList.remove('active'); });
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.active').forEach(function(m) { m.classList.remove('active'); });
        });

        render();
    </script>
</body>
</html>
