<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['auth_user_id'])) {
    header('Location: ./login.php');
    exit();
}

$mockOrders = [
    [
        'id' => 'ORD-001',
        'date' => 'August 18, 2025',
        'items' => [
            ['name' => 'Ube Cheesecake', 'qty' => 1, 'price' => '₱850'],
            ['name' => 'Ube Latte', 'qty' => 1, 'price' => '₱150'],
        ],
        'total' => '₱1,000',
        'status' => 'delivered',
    ],
    [
        'id' => 'ORD-002',
        'date' => 'August 15, 2025',
        'items' => [
            ['name' => 'Ube Roll', 'qty' => 1, 'price' => '₱450'],
            ['name' => 'Ube Crinkles', 'qty' => 1, 'price' => '₱280'],
        ],
        'total' => '₱730',
        'status' => 'pending',
    ],
    [
        'id' => 'ORD-003',
        'date' => 'August 12, 2025',
        'items' => [
            ['name' => 'Classic Ube Cake', 'qty' => 1, 'price' => '₱950'],
        ],
        'total' => '₱950',
        'status' => 'confirmed',
    ],
    [
        'id' => 'ORD-004',
        'date' => 'August 5, 2025',
        'items' => [
            ['name' => 'Ube Pandesal', 'qty' => 2, 'price' => '₱120'],
            ['name' => 'Ube Halo-Halo', 'qty' => 1, 'price' => '₱180'],
        ],
        'total' => '₱420',
        'status' => 'cancelled',
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ube Delights - My Orders</title>
    <link rel="stylesheet" href="../css/dashboard.css?v=4.0">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a onclick="getIndex()" class="logo-link">
                    <img src="../images/logo.png" alt="Ube Delights" class="logo-image">
                    <h2>Ube Delights</h2>
                </a>
            </div>
            <div class="nav-menu">
                <a onclick="getIndex()" class="nav-link">Dashboard</a>
                <a onclick="getShop()" class="nav-link">Shop</a>
                <a onclick="getCart()" class="nav-link cart-link">Cart <span class="cart-badge" id="cartBadge" style="display:none;">0</span></a>
                <a onclick="getOrders()" class="nav-link active">My Orders</a>
                <a onclick="getProfile()" class="nav-link">Profile</a>
                <a onclick="getLogout()" class="nav-link">Log Out</a>
            </div>
        </div>
    </nav>

    <section class="hero-section hero-small">
        <div class="hero-content">
            <h1>My <span>Orders</span></h1>
            <p>Track and manage your orders.</p>
        </div>
    </section>

    <main class="main-content">
        <div class="order-filters">
            <button class="filter-btn active" data-status="all">All Orders</button>
            <button class="filter-btn" data-status="pending">Pending</button>
            <button class="filter-btn" data-status="confirmed">Confirmed</button>
            <button class="filter-btn" data-status="delivered">Delivered</button>
            <button class="filter-btn" data-status="cancelled">Cancelled</button>
        </div>

        <div class="orders-list" id="ordersList">
            <?php foreach ($mockOrders as $order): ?>
            <div class="order-card" data-status="<?php echo $order['status']; ?>">
                <div class="order-header">
                    <div class="order-meta">
                        <span class="order-id"><?php echo $order['id']; ?></span>
                        <span class="order-date"><?php echo $order['date']; ?></span>
                    </div>
                    <span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span>
                </div>
                <div class="order-items">
                    <?php foreach ($order['items'] as $item): ?>
                    <div class="order-item">
                        <img src="../images/cake.png" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-thumb">
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
                    <button class="btn-reorder" data-order="<?php echo $order['id']; ?>">Reorder</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="empty-orders" id="emptyOrders" style="display:none;">
            <div class="empty-icon">📦</div>
            <h3>No orders yet</h3>
            <p>Start shopping to place your first order!</p>
            <a onclick="getShop()" class="btn-primary">Browse Shop</a>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Ube Delights. All rights reserved.</p>
    </footer>

    <div class="toast" id="toast"></div>

    <script>
    var MOCK_ORDERS = <?php echo json_encode($mockOrders); ?>;
    </script>
    <script src="../javascript/disable_back.js"></script>
    <script src="../javascript/index.js"></script>
    <script src="../javascript/dashboard.js"></script>
    <script src="../javascript/orders.js"></script>
    <script src="../javascript/routing.js"></script>
    <script src="../javascript/inspect.js"></script>
</body>
</html>
