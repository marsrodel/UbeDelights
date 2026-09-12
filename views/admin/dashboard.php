<?php require_once __DIR__ . '/../../server/admin_auth.php';

$isSuperAdmin = (($_SESSION['auth_role'] ?? '') === 'super_admin');

if ($isSuperAdmin) {
    $totalUsers = 0;
    $activeUsers = 0;
    $pendingUsers = 0;
    $blockedUsers = 0;
    $pendingCount = 0;

    if ($connect) {
        $r = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM users");
        if ($r) $totalUsers = mysqli_fetch_assoc($r)['cnt'];

        $r = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM users WHERE status = 'active'");
        if ($r) $activeUsers = mysqli_fetch_assoc($r)['cnt'];

        $r = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM users WHERE status = 'pending'");
        if ($r) {
            $pendingUsers = mysqli_fetch_assoc($r)['cnt'];
            $pendingCount = $pendingUsers;
        }

        $r = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM users WHERE status = 'blocked'");
        if ($r) $blockedUsers = mysqli_fetch_assoc($r)['cnt'];
    }

    $stats = [
        ['label' => 'Total Users',       'value' => number_format($totalUsers),   'icon' => 'fa-solid fa-users',       'color' => '#7c3aed'],
        ['label' => 'Active Users',      'value' => number_format($activeUsers),  'icon' => 'fa-solid fa-user-check',  'color' => '#22c55e'],
        ['label' => 'Pending Approvals', 'value' => number_format($pendingUsers), 'icon' => 'fa-solid fa-user-clock',  'color' => '#f59e0b'],
        ['label' => 'Blocked Users',     'value' => number_format($blockedUsers), 'icon' => 'fa-solid fa-user-xmark',  'color' => '#ef4444'],
    ];

    $registrationsData = ['labels' => [], 'counts' => []];
    if ($connect) {
        $r = mysqli_query($connect, "SELECT DATE(created_at) AS reg_date, COUNT(*) AS cnt FROM users WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK) GROUP BY DATE(created_at) ORDER BY reg_date");
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $registrationsData['labels'][] = date('M d', strtotime($row['reg_date']));
                $registrationsData['counts'][] = (int)$row['cnt'];
            }
        }
    }

    $statusData = ['labels' => [], 'counts' => []];
    if ($connect) {
        $r = mysqli_query($connect, "SELECT status, COUNT(*) AS cnt FROM users GROUP BY status ORDER BY FIELD(status, 'active','pending','blocked','incomplete','rejected')");
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $statusData['labels'][] = ucfirst($row['status']);
                $statusData['counts'][] = (int)$row['cnt'];
            }
        }
    }

    $activityData = ['labels' => [], 'counts' => []];
    if ($connect) {
        $r = mysqli_query($connect, "SELECT module, COUNT(*) AS cnt FROM activity_logs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY module ORDER BY cnt DESC");
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $activityData['labels'][] = $row['module'];
                $activityData['counts'][] = (int)$row['cnt'];
            }
        }
    }

    $roleData = ['labels' => [], 'counts' => []];
    if ($connect) {
        $r = mysqli_query($connect, "SELECT role, COUNT(*) AS cnt FROM users GROUP BY role ORDER BY FIELD(role, 'super_admin','admin','customer')");
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $label = $row['role'] === 'super_admin' ? 'Super Admin' : ucfirst($row['role']);
                $roleData['labels'][] = $label;
                $roleData['counts'][] = (int)$row['cnt'];
            }
        }
    }
} else {
    $totalOrders = 0;
    $pendingOrders = 0;
    $deliveredOrders = 0;
    $cancelledOrders = 0;
    $pendingCount = 0;

    if ($connect) {
        $r = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM orders");
        if ($r) $totalOrders = mysqli_fetch_assoc($r)['cnt'];

        $r = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending'");
        if ($r) $pendingOrders = mysqli_fetch_assoc($r)['cnt'];

        $r = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM orders WHERE status = 'delivered'");
        if ($r) $deliveredOrders = mysqli_fetch_assoc($r)['cnt'];

        $r = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM orders WHERE status = 'cancelled'");
        if ($r) $cancelledOrders = mysqli_fetch_assoc($r)['cnt'];

        $r = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM users WHERE status = 'pending'");
        if ($r) $pendingCount = mysqli_fetch_assoc($r)['cnt'];
    }

    $stats = [
        ['label' => 'Total Orders', 'value' => number_format($totalOrders),   'icon' => 'fa-solid fa-bag-shopping',    'color' => '#7c3aed'],
        ['label' => 'Pending',      'value' => number_format($pendingOrders),  'icon' => 'fa-solid fa-clock',           'color' => '#f59e0b'],
        ['label' => 'Delivered',    'value' => number_format($deliveredOrders),'icon' => 'fa-solid fa-circle-check',    'color' => '#22c55e'],
        ['label' => 'Cancelled',    'value' => number_format($cancelledOrders),'icon' => 'fa-solid fa-circle-xmark',    'color' => '#ef4444'],
    ];

    $recentOrders = [];
    if ($connect) {
        $r = mysqli_query($connect, "SELECT order_id, customer_name, total_amount, status, order_date FROM orders ORDER BY order_date DESC LIMIT 5");
        if ($r) {
            while ($row = mysqli_fetch_assoc($r)) {
                $recentOrders[] = $row;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isSuperAdmin ? 'Super Admin' : 'Admin'; ?> Dashboard - Ube Delights</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin_security.css?v=1.2">
</head>
<body class="admin-body">
<?php $activePage = 'dashboard'; include '_sidebar.php'; ?>

    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <h1>Dashboard</h1>
                <p class="topbar-subtitle"><?php echo $isSuperAdmin ? 'System overview and activity insights.' : 'Overview of your store\'s performance and recent activity.'; ?></p>
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

            <?php if ($isSuperAdmin): ?>
            <div class="chart-grid">
                <div class="chart-card">
                    <h3>User Registrations (Last 8 Weeks)</h3>
                    <canvas id="registrationsChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Accounts by Status</h3>
                    <canvas id="statusChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>System Activity (Last 7 Days)</h3>
                    <canvas id="activityChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Accounts by Role</h3>
                    <canvas id="roleChart"></canvas>
                </div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="card-header">
                    <h2>Recent Orders</h2>
                    <a onclick="getAdminOrders()" class="view-all">View All &rarr;</a>
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
                                <td><strong>&#8369;<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                <td><span class="status-badge status-<?php echo strtolower($order['status']); ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="5" class="cell-muted" style="text-align:center; padding:30px;">No recent orders</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <div class="toast" id="toast"></div>

    <script src="../../javascript/admin-routing.js"></script>
    <script src="../../javascript/admin.js"></script>
    <script src="../../javascript/inspect.js"></script>
    <?php if ($isSuperAdmin): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>var chartRegistrationsData = <?php echo json_encode($registrationsData); ?>; var chartStatusData = <?php echo json_encode($statusData); ?>; var chartActivityData = <?php echo json_encode($activityData); ?>; var chartRoleData = <?php echo json_encode($roleData); ?>;</script>
    <script src="../../javascript/super_admin_dashboard.js"></script>
    <?php endif; ?>
</body>
</html>
