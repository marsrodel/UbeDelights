<?php
$stats = [
    ['label' => 'Total Revenue', 'value' => '₱18,450', 'icon' => 'fa-solid fa-wallet', 'color' => '#7c3aed'],
    ['label' => 'Total Orders', 'value' => '24', 'icon' => 'fa-solid fa-bag-shopping', 'color' => '#a855f7'],
    ['label' => 'Pending Orders', 'value' => '5', 'icon' => 'fa-solid fa-clock', 'color' => '#6d28d9'],
    ['label' => 'Total Customers', 'value' => '86', 'icon' => 'fa-solid fa-users', 'color' => '#8b5cf6'],
];

$recentOrders = [
    ['id' => 'ORD-008', 'customer' => 'Maria Santos', 'date' => 'Aug 22, 2025', 'items' => 'Ube Cheesecake x1, Ube Latte x2', 'total' => '₱1,150', 'status' => 'pending'],
    ['id' => 'ORD-007', 'customer' => 'Juan Dela Cruz', 'date' => 'Aug 21, 2025', 'items' => 'Ube Roll x2', 'total' => '₱900', 'status' => 'confirmed'],
    ['id' => 'ORD-006', 'customer' => 'Ana Reyes', 'date' => 'Aug 20, 2025', 'items' => 'Classic Ube Cake x1', 'total' => '₱950', 'status' => 'delivered'],
    ['id' => 'ORD-005', 'customer' => 'Carlo Mendoza', 'date' => 'Aug 19, 2025', 'items' => 'Ube Pandesal x4, Ube Halo-Halo x1', 'total' => '₱660', 'status' => 'delivered'],
    ['id' => 'ORD-004', 'customer' => 'Lisa Garcia', 'date' => 'Aug 18, 2025', 'items' => 'Ube Crinkles x2, Ube Macapuno x1', 'total' => '₱710', 'status' => 'cancelled'],
    ['id' => 'ORD-003', 'customer' => 'Paolo Bautista', 'date' => 'Aug 17, 2025', 'items' => 'Ube Cheesecake x1', 'total' => '₱850', 'status' => 'delivered'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ube Delights - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin.css?v=1.0">
</head>
<body class="admin-body">
    <aside class="admin-sidebar">
        <div class="sidebar-logo">
            <img src="../../images/logo.png" alt="Ube Delights Logo">
            <div>
                <h2>Ube Delights</h2>
                <span class="sidebar-tag">Admin Panel</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a onclick="getAdminDashboard()" class="sidebar-link active"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
            <a onclick="getAdminProducts()" class="sidebar-link"><i class="fa-solid fa-box"></i><span>Products</span></a>
            <a onclick="getAdminOrders()" class="sidebar-link"><i class="fa-solid fa-bag-shopping"></i><span>Orders</span></a>
            <a onclick="getAdminCustomers()" class="sidebar-link"><i class="fa-solid fa-users"></i><span>Customers</span></a>
        </nav>

        <div class="sidebar-footer">
            <div class="admin-chip">
                <div class="admin-avatar">AU</div>
                <div class="admin-chip-info">
                    <strong>Admin User</strong>
                    <small>Administrator</small>
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
                    <h2>Recent Orders</h2>
                    <a onclick="getAdminOrders()" class="view-all">View All →</a>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td class="cell-id"><?php echo $order['id']; ?></td>
                                <td class="cell-strong"><?php echo htmlspecialchars($order['customer']); ?></td>
                                <td class="cell-muted"><?php echo $order['date']; ?></td>
                                <td><?php echo htmlspecialchars($order['items']); ?></td>
                                <td class="cell-strong"><?php echo $order['total']; ?></td>
                                <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
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
