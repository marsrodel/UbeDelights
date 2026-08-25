<?php
$mockOrders = [
    [
        'id' => 'ORD-008',
        'customer' => 'Maria Santos',
        'date' => 'August 22, 2025',
        'items' => [
            ['name' => 'Ube Cheesecake', 'qty' => 1, 'price' => '₱850', 'image' => '../../images/items/cheesecake.jpg'],
            ['name' => 'Ube Latte', 'qty' => 2, 'price' => '₱150', 'image' => '../../images/items/latte.jpg'],
        ],
        'total' => '₱1,150',
        'status' => 'pending',
    ],
    [
        'id' => 'ORD-007',
        'customer' => 'Juan Dela Cruz',
        'date' => 'August 21, 2025',
        'items' => [
            ['name' => 'Ube Roll', 'qty' => 2, 'price' => '₱450', 'image' => '../../images/items/uberoll.jpg'],
        ],
        'total' => '₱900',
        'status' => 'confirmed',
    ],
    [
        'id' => 'ORD-006',
        'customer' => 'Ana Reyes',
        'date' => 'August 20, 2025',
        'items' => [
            ['name' => 'Classic Ube Cake', 'qty' => 1, 'price' => '₱950', 'image' => '../../images/items/classic.jpg'],
        ],
        'total' => '₱950',
        'status' => 'delivered',
    ],
    [
        'id' => 'ORD-005',
        'customer' => 'Carlo Mendoza',
        'date' => 'August 19, 2025',
        'items' => [
            ['name' => 'Ube Pandesal', 'qty' => 4, 'price' => '₱120', 'image' => '../../images/items/pandesal.jpg'],
            ['name' => 'Ube Halo-Halo', 'qty' => 1, 'price' => '₱180', 'image' => '../../images/items/halohalo.jpg'],
        ],
        'total' => '₱660',
        'status' => 'delivered',
    ],
    [
        'id' => 'ORD-004',
        'customer' => 'Lisa Garcia',
        'date' => 'August 18, 2025',
        'items' => [
            ['name' => 'Ube Crinkles', 'qty' => 2, 'price' => '₱280', 'image' => '../../images/items/crinkles.jpg'],
            ['name' => 'Ube Macapuno', 'qty' => 1, 'price' => '₱150', 'image' => '../../images/items/macapuno.jpg'],
        ],
        'total' => '₱710',
        'status' => 'cancelled',
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ube Delights - Admin Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin.css?v=1.4">
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

        <div class="sidebar-profile">
            <div class="admin-chip">
                <div class="admin-avatar">AU</div>
                <div class="admin-chip-info">
                    <strong>Admin User</strong>
                    <small>Administrator</small>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a onclick="getAdminDashboard()" class="sidebar-link"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
            <a onclick="getAdminProducts()" class="sidebar-link"><i class="fa-solid fa-box"></i><span>Products</span></a>
            <a onclick="getAdminOrders()" class="sidebar-link active"><i class="fa-solid fa-bag-shopping"></i><span>Orders</span></a>
            <a onclick="getAdminUserManagement()" class="sidebar-link"><i class="fa-solid fa-users-cog"></i><span>User Management</span></a>
            <a onclick="getAdminPendingApprovals()" class="sidebar-link"><i class="fa-solid fa-user-clock"></i><span>Pending Approvals</span></a>
            <a onclick="getAdminSystemLogs()" class="sidebar-link"><i class="fa-solid fa-list-alt"></i><span>System Logs</span></a>
        </nav>

        <div class="sidebar-footer">
            <a onclick="getAdminLogout()" class="sidebar-logout"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <h1>Orders</h1>
            <div class="topbar-right">
                <span class="topbar-date"><i class="fa-solid fa-calendar-days"></i><?php echo date('F j, Y'); ?></span>
            </div>
        </header>

        <main class="admin-content">
            <div class="order-filters">
                <button class="filter-btn active" data-status="all">All Orders</button>
                <button class="filter-btn" data-status="pending">Pending</button>
                <button class="filter-btn" data-status="confirmed">Confirmed</button>
                <button class="filter-btn" data-status="delivered">Delivered</button>
                <button class="filter-btn" data-status="cancelled">Cancelled</button>
            </div>

            <div class="orders-list">
                <?php foreach ($mockOrders as $order): ?>
                <div class="order-card" data-status="<?php echo $order['status']; ?>">
                    <div class="order-header">
                        <div class="order-meta">
                            <span class="order-id"><?php echo $order['id']; ?></span>
                            <span class="order-customer"><i class="fa-solid fa-user"></i><?php echo htmlspecialchars($order['customer']); ?></span>
                            <span class="order-date"><?php echo $order['date']; ?></span>
                        </div>
                        <span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                    </div>

                    <div class="order-items">
                        <?php foreach ($order['items'] as $item): ?>
                        <div class="order-item">
                            <img src="<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-thumb">
                            <div class="item-details">
                                <span class="item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                <span class="item-qty">Qty: <?php echo $item['qty']; ?></span>
                            </div>
                            <span class="item-price"><?php echo $item['price']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-footer">
                        <span class="order-total">Total: <strong><?php echo $order['total']; ?></strong></span>
                        <div class="order-actions">
                            <?php if ($order['status'] === 'pending'): ?>
                            <button class="btn-action btn-confirm" data-action="confirm"><i class="fa-solid fa-check"></i>Confirm</button>
                            <button class="btn-action btn-cancel" data-action="cancel"><i class="fa-solid fa-xmark"></i>Cancel Order</button>
                            <?php elseif ($order['status'] === 'confirmed'): ?>
                            <button class="btn-action btn-deliver" data-action="deliver"><i class="fa-solid fa-truck-fast"></i>Mark Delivered</button>
                            <button class="btn-action btn-cancel" data-action="cancel"><i class="fa-solid fa-xmark"></i>Cancel Order</button>
                            <?php elseif ($order['status'] === 'delivered'): ?>
                            <span class="cell-muted"><i class="fa-solid fa-circle-check" style="color:#15803d;"></i> Completed</span>
                            <?php else: ?>
                            <span class="cell-muted"><i class="fa-solid fa-ban" style="color:#dc2626;"></i> No actions available</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="empty-state" id="ordersEmpty" style="display:none;">
                <div class="empty-icon">📦</div>
                <h3>No orders found</h3>
                <p>No orders match this filter.</p>
            </div>
        </main>
    </div>

    <div class="toast" id="toast"></div>

    <script src="../../javascript/admin-routing.js"></script>
    <script src="../../javascript/admin.js"></script>
    <script src="../../javascript/inspect.js"></script>
</body>
</html>
