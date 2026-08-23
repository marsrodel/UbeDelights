<?php
$customers = [
    ['id' => '2025-0001', 'name' => 'Maria Santos', 'email' => 'maria.santos@gmail.com', 'joined' => 'Aug 02, 2025', 'orders' => 6, 'status' => 'active'],
    ['id' => '2025-0002', 'name' => 'Juan Dela Cruz', 'email' => 'juandc@yahoo.com', 'joined' => 'Jul 28, 2025', 'orders' => 4, 'status' => 'active'],
    ['id' => '2025-0003', 'name' => 'Ana Reyes', 'email' => 'ana.reyes@outlook.com', 'joined' => 'Jul 21, 2025', 'orders' => 3, 'status' => 'active'],
    ['id' => '2025-0004', 'name' => 'Carlo Mendoza', 'email' => 'carlo.mendoza@gmail.com', 'joined' => 'Jul 15, 2025', 'orders' => 2, 'status' => 'blocked'],
    ['id' => '2025-0005', 'name' => 'Lisa Garcia', 'email' => 'lisagarcia@gmail.com', 'joined' => 'Jul 09, 2025', 'orders' => 5, 'status' => 'active'],
    ['id' => '2025-0006', 'name' => 'Paolo Bautista', 'email' => 'paolo.b@gmail.com', 'joined' => 'Jun 30, 2025', 'orders' => 1, 'status' => 'active'],
    ['id' => '2025-0007', 'name' => 'Grace Lim', 'email' => 'grace.lim@yahoo.com', 'joined' => 'Jun 24, 2025', 'orders' => 7, 'status' => 'blocked'],
    ['id' => '2025-0008', 'name' => 'Miguel Torres', 'email' => 'miguel.torres@gmail.com', 'joined' => 'Jun 18, 2025', 'orders' => 2, 'status' => 'active'],
];

function initials($name) {
    $parts = preg_split('/\s+/', trim($name));
    $first = strtoupper(substr($parts[0], 0, 1));
    $last = strtoupper(substr(end($parts), 0, 1));
    return $first . $last;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ube Delights - Admin Customers</title>
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
            <a onclick="getAdminDashboard()" class="sidebar-link"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
            <a onclick="getAdminProducts()" class="sidebar-link"><i class="fa-solid fa-box"></i><span>Products</span></a>
            <a onclick="getAdminOrders()" class="sidebar-link"><i class="fa-solid fa-bag-shopping"></i><span>Orders</span></a>
            <a onclick="getAdminCustomers()" class="sidebar-link active"><i class="fa-solid fa-users"></i><span>Customers</span></a>
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
            <h1>Customers</h1>
            <div class="topbar-right">
                <span class="topbar-date"><i class="fa-solid fa-calendar-days"></i><?php echo date('F j, Y'); ?></span>
            </div>
        </header>

        <main class="admin-content">
            <div class="card">
                <div class="card-header">
                    <h2>All Customers (<?php echo count($customers); ?>)</h2>
                </div>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Customer ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Joined</th>
                                <th>Orders</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td class="cell-id"><?php echo $customer['id']; ?></td>
                                <td>
                                    <div class="customer-cell">
                                        <span class="customer-initials"><?php echo initials($customer['name']); ?></span>
                                        <span class="customer-name"><?php echo htmlspecialchars($customer['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                <td class="cell-muted"><?php echo $customer['joined']; ?></td>
                                <td class="cell-strong"><?php echo $customer['orders']; ?></td>
                                <td>
                                    <?php if ($customer['status'] === 'active'): ?>
                                    <span class="status-badge badge-active">Active</span>
                                    <?php else: ?>
                                    <span class="status-badge badge-blocked">Blocked</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn-outline btn-toggle-block"><?php echo $customer['status'] === 'active' ? 'Block' : 'Unblock'; ?></button>
                                </td>
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
