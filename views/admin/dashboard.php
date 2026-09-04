<?php require_once __DIR__ . '/../../server/admin_auth.php';

$totalOrders = 0;
$pendingOrders = 0;
$deliveredOrders = 0;
$cancelledOrders = 0;

if ($connect) {
    $r = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM orders");
    if ($r) $totalOrders = mysqli_fetch_assoc($r)['cnt'];

    $r = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending'");
    if ($r) $pendingOrders = mysqli_fetch_assoc($r)['cnt'];

    $r = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM orders WHERE status = 'delivered'");
    if ($r) $deliveredOrders = mysqli_fetch_assoc($r)['cnt'];

    $r = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM orders WHERE status = 'cancelled'");
    if ($r) $cancelledOrders = mysqli_fetch_assoc($r)['cnt'];
}

$stats = [
    ['label' => 'Total Orders', 'value' => number_format($totalOrders), 'icon' => 'fa-solid fa-bag-shopping', 'color' => '#7c3aed'],
    ['label' => 'Pending',      'value' => number_format($pendingOrders), 'icon' => 'fa-solid fa-clock',        'color' => '#f59e0b'],
    ['label' => 'Delivered',    'value' => number_format($deliveredOrders), 'icon' => 'fa-solid fa-circle-check', 'color' => '#22c55e'],
    ['label' => 'Cancelled',    'value' => number_format($cancelledOrders), 'icon' => 'fa-solid fa-circle-xmark', 'color' => '#ef4444'],
];

$recentOrders = [];
if ($connect) {
    $sql = "SELECT order_id, customer_name, total_amount, status, order_date
            FROM orders
            ORDER BY order_date DESC
            LIMIT 5";
    $result = mysqli_query($connect, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $recentOrders[] = $row;
        }
    }
}

$pendingCount = 0;
if ($connect) {
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
            <a onclick="getAdminOrders()" class="sidebar-link"><i class="fa-solid fa-bag-shopping"></i><span>Orders</span><?php if ($pendingOrders > 0): ?><span class="sidebar-badge"><?php echo $pendingOrders; ?></span><?php endif; ?></a>
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
                    <h2>Recent Orders</h2>
                    <a onclick="getAdminOrders()" class="view-all">View All →</a>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($recentOrders) > 0): ?>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td><strong>ORD-<?php echo str_pad($order['order_id'], 3, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><strong>₱<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                <td><span class="status-badge status-<?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center; padding:30px; color:var(--text-muted);">No recent orders</td>
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