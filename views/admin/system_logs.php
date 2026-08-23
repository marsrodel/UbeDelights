<?php
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    header('Location: ./login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Logs - Ube Delights Admin</title>
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
            <a onclick="getAdminPendingApprovals()" class="sidebar-link"><i class="fa-solid fa-user-clock"></i><span>Pending Approvals</span></a>
            <a onclick="getAdminSystemLogs()" class="sidebar-link active"><i class="fa-solid fa-list-alt"></i><span>System Logs</span></a>
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
            <h1>System Logs</h1>
            <div class="topbar-right">
                <span class="topbar-date"><i class="fa-solid fa-calendar-days"></i><?php echo date('F j, Y'); ?></span>
            </div>
        </header>

        <main class="admin-content">
            <!-- Page Header -->
            <div class="content-header">
                <div>
                    <h2>System Logs</h2>
                    <p>Monitor all system-wide administrative actions and changes</p>
                </div>
                <?php if ($_SESSION['role'] === 'super_admin'): ?>
                <form method="POST" action="../../server/user_logs.php" onsubmit="return confirm('Are you sure you want to clear all system activity logs? This action cannot be undone.');" style="display: inline;">
                    <input type="hidden" name="action" value="clear_logs">
                    <button type="submit" class="btn-primary" style="background: var(--danger);"><i class="fa-solid fa-trash-alt"></i> Clear Logs</button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #7c3aed15; color: #7c3aed;"><i class="fa-solid fa-list-alt"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="statTotalLogs">-</span>
                        <span class="stat-label">Total Logs</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #22c55e15; color: #22c55e;"><i class="fa-solid fa-user-plus"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="statCreateLogs">-</span>
                        <span class="stat-label">User Creations</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #f59e0b15; color: #f59e0b;"><i class="fa-solid fa-user-pen"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="statUpdateLogs">-</span>
                        <span class="stat-label">User Updates</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background: #ef444415; color: #ef4444;"><i class="fa-solid fa-user-slash"></i></div>
                    <div class="stat-info">
                        <span class="stat-value" id="statDeleteLogs">-</span>
                        <span class="stat-label">Deletions</span>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-bar">
                <div class="search-box" style="flex: 1; min-width: 200px;">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="logsSearch" placeholder="Search logs...">
                </div>
                <div class="filter-group">
                    <select id="logsActionFilter">
                        <option value="">All Actions</option>
                        <option value="LOGIN">LOGIN</option>
                        <option value="LOGOUT">LOGOUT</option>
                        <option value="CREATE_USER">CREATE_USER</option>
                        <option value="UPDATE_USER">UPDATE_USER</option>
                        <option value="BLOCK_USER">BLOCK_USER</option>
                        <option value="UNBLOCK_USER">UNBLOCK_USER</option>
                        <option value="DELETE_USER">DELETE_USER</option>
                        <option value="APPROVE_USER">APPROVE_USER</option>
                        <option value="REJECT_USER">REJECT_USER</option>
                        <option value="FAILED_LOGIN">FAILED_LOGIN</option>
                    </select>
                    <select id="logsRoleFilter">
                        <option value="">All Roles</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="admin">Admin</option>
                        <option value="customer">Customer</option>
                    </select>
                    <input type="date" id="logsFromDate" placeholder="From Date">
                    <input type="date" id="logsToDate" placeholder="To Date">
                </div>
                <div class="filter-actions">
                    <button class="btn-primary" id="btnApplyLogsFilter"><i class="fa-solid fa-filter"></i> Apply</button>
                    <button class="btn-outline" id="btnClearLogsFilter"><i class="fa-solid fa-xmark"></i> Clear</button>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="card">
                <div class="card-header">
                    <h2>System Audit Logs</h2>
                    <span class="stat-value" id="logsTotalCount" style="font-size: 0.9rem; font-weight: 500;">Loading...</span>
                </div>
                <div class="table-container">
                    <table class="data-table" id="logsTable">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>Device / Browser</th>
                                <th>IP Address</th>
                                <th>Date / Time</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody">
                            <!-- Populated via JS -->
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container" id="logsPaginationContainer">
                    <!-- Pagination rendered via JS -->
                </div>
            </div>

            <!-- Empty State -->
            <div class="empty-state" id="emptyLogs" style="display:none;">
                <div class="empty-icon">📋</div>
                <h3>No logs found</h3>
                <p>No logs match your current filters.</p>
            </div>
        </main>
    </div>

    <!-- Toast -->
    <div class="toast" id="toast"></div>

    <!-- Scripts -->
    <script src="../../javascript/admin-routing.js"></script>
    <script src="../../javascript/admin_security.js"></script>
    <script src="../../javascript/admin_logs.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof initSystemLogs === 'function') {
                initSystemLogs();
            }
        });
    </script>
</body>
</html>