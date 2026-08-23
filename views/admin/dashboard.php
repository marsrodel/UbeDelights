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

// Mock stats - in real implementation, these would come from the database
$stats = [
    ['label' => 'Total Users', 'value' => '156', 'icon' => 'fa-solid fa-users', 'color' => '#7c3aed'],
    ['label' => 'Active Users', 'value' => '142', 'icon' => 'fa-solid fa-user-check', 'color' => '#22c55e'],
    ['label' => 'Pending Approvals', 'value' => '7', 'icon' => 'fa-solid fa-user-clock', 'color' => '#f59e0b'],
    ['label' => 'Blocked Users', 'value' => '3', 'icon' => 'fa-solid fa-user-slash', 'color' => '#ef4444'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ube Delights - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin_security.css?v=1.0">
</head>
<body class="admin-body">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <img src="../../images/logo.png" alt="Ube Delights Logo" class="sidebar-logo">
            <div>
                <h2>Ube Delights</h2>
                <span class="sidebar-tag">Admin Panel</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a onclick="getAdminDashboard()" class="sidebar-link active"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
            <a onclick="getAdminProducts()" class="sidebar-link"><i class="fa-solid fa-box"></i><span>Products</span></a>
            <a onclick="getAdminOrders()" class="sidebar-link"><i class="fa-solid fa-bag-shopping"></i><span>Orders</span></a>
            <a onclick="getAdminUserManagement()" class="sidebar-link"><i class="fa-solid fa-users-cog"></i><span>User Management</span></a>
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

    <div class="admin-main">
        <header class="admin-topbar">
            <h1>Dashboard</h1>
            <div class="topbar-right">
                <span class="topbar-date"><i class="fa-solid fa-calendar-days"></i><?php echo date('F j, Y'); ?></span>
            </div>
        </header>

        <main class="admin-content">
            <div class="stats-grid">
                <?php foreach ($stats as $stat): ?>
                <div class="stat-card">
                    <div class="stat-icon" style="background: <?php echo $stat['color']; ?>15; color: <?php echo $stat['color']; ?>;">
                        <i class="<?php echo $stat['icon']; ?>"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $stat['value']; ?></span>
                        <span class="stat-label"><?php echo $stat['label']; ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Recent Activity</h2>
                    <a onclick="getAdminSystemLogs()" class="view-all">View All →</a>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>Date / Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>windy.sagaad</strong></td>
                                <td><span class="action-badge action-create">Create User</span></td>
                                <td>Created new user maria.santos</td>
                                <td><?php echo date('M j, Y g:i A', strtotime('-2 hours')); ?></td>
                            </tr>
                            <tr>
                                <td><strong>admin_user</strong></td>
                                <td><span class="action-badge action-block">Block User</span></td>
                                <td>Blocked user juan.dc</td>
                                <td><?php echo date('M j, Y g:i A', strtotime('-5 hours')); ?></td>
                            </tr>
                            <tr>
                                <td><strong>super_admin</strong></td>
                                <td><span class="action-badge action-approve">Approve User</span></td>
                                <td>Approved pending user ana.reyes</td>
                                <td><?php echo date('M j, Y g:i A', strtotime('-1 day')); ?></td>
                            </tr>
                            <tr>
                                <td><strong>windy.sagaad</strong></td>
                                <td><span class="action-badge action-update">Update User</span></td>
                                <td>Updated user carlo.mendoza (password updated)</td>
                                <td><?php echo date('M j, Y g:i A', strtotime('-2 days')); ?></td>
                            </tr>
                            <tr>
                                <td><strong>lynlyn</strong></td>
                                <td><span class="action-badge action-login">Login</span></td>
                                <td>User logged in successfully</td>
                                <td><?php echo date('M j, Y g:i A', strtotime('-3 days')); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div class="toast" id="toast"></div>

    <script src="../../javascript/admin-routing.js"></script>
    <script src="../../javascript/admin.js"></script>
</body>
</html>