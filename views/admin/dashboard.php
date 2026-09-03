<?php require_once __DIR__ . '/../../server/admin_auth.php';

$stats = [
    ['label' => 'Total Users', 'value' => '0', 'icon' => 'fa-solid fa-users', 'color' => '#7c3aed'],
    ['label' => 'Active Users', 'value' => '0', 'icon' => 'fa-solid fa-user-check', 'color' => '#22c55e'],
    ['label' => 'Pending Approvals', 'value' => '0', 'icon' => 'fa-solid fa-user-clock', 'color' => '#f59e0b'],
    ['label' => 'Blocked Users', 'value' => '0', 'icon' => 'fa-solid fa-user-slash', 'color' => '#ef4444'],
];

if ($connect) {
    $total = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM users"));
    $stats[0]['value'] = number_format($total['cnt']);

    $active = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM users WHERE status = 'active'"));
    $stats[1]['value'] = number_format($active['cnt']);

    $pending = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM users WHERE status = 'pending'"));
    $stats[2]['value'] = number_format($pending['cnt']);

    $blocked = mysqli_fetch_assoc(mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM users WHERE status = 'blocked'"));
    $stats[3]['value'] = number_format($blocked['cnt']);
}

$recentLogs = [];
if ($connect) {
    $logResult = mysqli_query($connect, "SELECT l.username, l.action, l.description, l.created_at FROM user_logs l ORDER BY l.created_at DESC LIMIT 5");
    if ($logResult) {
        while ($row = mysqli_fetch_assoc($logResult)) {
            $recentLogs[] = $row;
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
    <title>Ube Delights - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin_security.css?v=1.2">
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
            <a onclick="getAdminDashboard()" class="sidebar-link active"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
            <a onclick="getAdminProducts()" class="sidebar-link"><i class="fa-solid fa-box"></i><span>Products</span></a>
            <a onclick="getAdminOrders()" class="sidebar-link"><i class="fa-solid fa-bag-shopping"></i><span>Orders</span><?php if ($orderCount > 0): ?><span class="sidebar-badge"><?php echo $orderCount; ?></span><?php endif; ?></a>
            <a onclick="getAdminUserManagement()" class="sidebar-link"><i class="fa-solid fa-users-cog"></i><span>User Management</span></a>
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
                <h1>Dashboard</h1>
                <p class="topbar-subtitle">Overview of your store's performance and recent activity.</p>
            </div>
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
                            <?php if (count($recentLogs) > 0): ?>
                            <?php foreach ($recentLogs as $log): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($log['username'] ?? 'System'); ?></strong></td>
                                <td><span class="action-badge action-<?php echo strtolower($log['action']); ?>"><?php echo ucwords(str_replace('_', ' ', $log['action'])); ?></span></td>
                                <td><?php echo htmlspecialchars($log['description']); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center; padding:30px; color:var(--text-muted);">No recent activity</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div class="toast" id="toast"></div>

    <script src="../../javascript/admin-routing.js"></script>
    <script src="../../javascript/admin.js"></script>
    <script src="../../javascript/inspect.js"></script>
</body>
</html>