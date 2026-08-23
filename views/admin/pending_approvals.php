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
    <title>Pending Approvals - Ube Delights Admin</title>
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
            <a onclick="getAdminUserManagement()" class="sidebar-link"><i class="fa-solid fa-users-cog"></i><span>User Management</span></a>
            <a onclick="getAdminPendingApprovals()" class="sidebar-link active"><i class="fa-solid fa-user-clock"></i><span>Pending Approvals</span></a>
            <a onclick="getAdminSystemLogs()" class="sidebar-link"><i class="fa-solid fa-list-alt"></i><span>System Logs</span></a>
        </nav>

        <div class="sidebar-footer">
            <div class="admin-chip">
                <div class="admin-avatar">AU</div>
                <div class="admin-chip-info">
                    <strong><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></strong>
                    <small><?php echo ucfirst($_SESSION['role'] ?? 'admin'); ?></small>
                </div>
            </div>
            <a onclick="getAdminLogout()" class="sidebar-logout"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="admin-main">
        <header class="admin-topbar">
            <h1>Pending Approvals</h1>
            <div class="topbar-right">
                <span class="topbar-date"><i class="fa-solid fa-calendar-days"></i><?php echo date('F j, Y'); ?></span>
            </div>
        </header>

        <main class="admin-content">
            <!-- Page Header -->
            <div class="content-header">
                <div>
                    <h2>Pending Registrations</h2>
                    <p>Review and approve or reject new user registrations</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f59e0b15; color: #f59e0b;"><i class="fa-solid fa-user-clock"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="statTotalPending">-</span>
                        <span class="stat-label">Total Pending</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #22c55e15; color: #22c55e;"><i class="fa-solid fa-check-circle"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="statApprovedToday">-</span>
                        <span class="stat-label">Approved Today</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ef444415; color: #ef4444;"><i class="fa-solid fa-times-circle"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="statRejectedToday">-</span>
                        <span class="stat-label">Rejected Today</span>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <div class="search-box">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="pendingSearch" placeholder="Search pending users...">
                </div>
            </div>

            <!-- Pending Users Table -->
            <div class="card">
                <div class="card-header">
                    <h2>Pending Registrations</h2>
                </div>
                <div class="table-container">
                    <table class="data-table" id="pendingTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pendingTableBody">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container" id="pendingPaginationContainer">
                    <!-- Pagination rendered via JS -->
                </div>
            </div>

            <!-- Empty State -->
            <div class="empty-state" id="emptyPending" style="display:none;">
                <div class="empty-icon">👥</div>
                <h3>No pending registrations</h3>
                <p>All caught up! No pending user registrations.</p>
            </div>
        </main>
    </div>

    <!-- View Pending User Modal -->
    <div class="modal-overlay" id="viewPendingModal" role="dialog" aria-modal="true" aria-labelledby="viewPendingModalTitle">
        <div class="modal" style="max-width: 700px;">
            <div class="modal-header">
                <h2 id="viewPendingModalTitle">Registration Details</h2>
                <button class="modal-close" id="viewPendingModalClose" aria-label="Close modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" id="viewPendingBody">
                <div style="text-align: center; padding: 40px;">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size: 24px; color: var(--accent);"></i>
                    <p style="margin-top: 10px; color: var(--text-secondary);">Loading user data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-outline" id="viewPendingModalCloseBtn">Close</button>
            </div>
        </div>
    </div>

    <!-- Approve/Reject Confirmation Modal -->
    <div class="modal-overlay" id="approvalModal" role="dialog" aria-modal="true" aria-labelledby="approvalModalTitle">
        <div class="modal" style="max-width: 500px;">
            <div class="modal-header">
                <h2 id="approvalModalTitle">Approve Registration</h2>
                <button class="modal-close" id="approvalModalClose" aria-label="Close modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <p id="approvalMessage">Are you sure you want to approve this registration?</p>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 8px;">User: <strong id="approvalUserName"></strong></p>
                <input type="hidden" id="approvalUserId">
                <input type="hidden" id="approvalAction">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" id="approvalModalCancel">Cancel</button>
                <button type="button" class="btn-primary" id="approvalConfirmBtn" style="background: var(--success);"><i class="fa-solid fa-check"></i> <span id="approvalConfirmText">Approve</span></button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toast"></div>

    <!-- Scripts -->
    <script src="../../javascript/admin-routing.js"></script>
    <script src="../../javascript/admin_security.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof initPendingApprovals === 'function') {
                initPendingApprovals();
            }
        });
    </script>
</body>
</html>