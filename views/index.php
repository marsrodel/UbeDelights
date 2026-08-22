<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['auth_user_id'])) {
    header('Location: ./login.php');
    exit();
}
$userId = (string)$_SESSION['auth_user_id'];
$firstName = '';
require_once __DIR__ . '/../server/db.php';
if ($connect) {
    $sql = "SELECT first_name FROM users WHERE user_id = ? LIMIT 1";
    if ($stmt = mysqli_prepare($connect, $sql)) {
        mysqli_stmt_bind_param($stmt, 's', $userId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res) {
            $row = mysqli_fetch_assoc($res);
            if ($row && isset($row['first_name'])) { $firstName = $row['first_name']; }
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($connect);
}

$stats = [
    ['label' => 'Total Orders', 'value' => '12',     'icon' => 'fa-solid fa-bag-shopping',  'color' => '#7c3aed'],
    ['label' => 'Pending',      'value' => '3',       'icon' => 'fa-solid fa-clock',          'color' => '#a855f7'],
    ['label' => 'Delivered',    'value' => '8',       'icon' => 'fa-solid fa-circle-check',   'color' => '#6d28d9'],
    ['label' => 'Total Spent',  'value' => '₱4,250', 'icon' => 'fa-solid fa-wallet',         'color' => '#8b5cf6'],
];

$featuredProducts = [
    ['name' => 'Ube Cheesecake', 'price' => '₱850', 'badge' => 'Best Seller', 'image' => '../images/items/cheesecake.jpg'],
    ['name' => 'Ube Roll', 'price' => '₱450', 'badge' => 'Popular', 'image' => '../images/items/uberoll.jpg'],
    ['name' => 'Classic Ube Cake', 'price' => '₱950', 'badge' => '', 'image' => '../images/items/classic.jpg'],
    ['name' => 'Ube Crinkles', 'price' => '₱280', 'badge' => 'New', 'image' => '../images/items/crinkles.jpg'],
];

$recentOrders = [
    ['id' => 'ORD-001', 'date' => 'Aug 18, 2025', 'items' => 'Ube Cheesecake, Ube Latte', 'total' => '₱1,000', 'status' => 'delivered'],
    ['id' => 'ORD-002', 'date' => 'Aug 15, 2025', 'items' => 'Ube Roll, Ube Crinkles', 'total' => '₱730', 'status' => 'pending'],
    ['id' => 'ORD-003', 'date' => 'Aug 12, 2025', 'items' => 'Classic Ube Cake', 'total' => '₱950', 'status' => 'confirmed'],
];

$features = [
    ['icon' => 'fa-solid fa-star',        'title' => 'Fresh Daily',     'description' => 'All products made fresh every day with authentic ube'],
    ['icon' => 'fa-solid fa-truck-fast',  'title' => 'Free Delivery',   'description' => 'Free delivery on orders above ₱500'],
    ['icon' => 'fa-solid fa-gift',        'title' => 'Custom Orders',   'description' => 'Custom cake orders accepted 3 days in advance'],
    ['icon' => 'fa-solid fa-heart',       'title' => '100% Ube',        'description' => 'Real purple yam, no artificial flavors'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ube Delights - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css?v=5.4">
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
                <a onclick="getIndex()" class="nav-link active">Dashboard</a>
                <a onclick="getShop()" class="nav-link">Shop</a>
                <a onclick="getCart()" class="nav-link cart-link">Cart <span class="cart-badge" id="cartBadge" style="display:none;">0</span></a>
                <a onclick="getOrders()" class="nav-link">My Orders</a>
                <a onclick="getProfile()" class="nav-link">Profile</a>
                <a onclick="getLogout()" class="nav-link">Log Out</a>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="hero-content">
            <h1>Welcome back, <span><?php echo htmlspecialchars($firstName !== '' ? $firstName : 'there');?>!</span></h1>
            <p>Discover our authentic ube treats made with love and the finest purple yam from the Philippines.</p>
        </div>
    </section>

    <main class="main-content">
        <div class="stats-grid">
            <?php foreach ($stats as $stat): ?>
            <div class="stat-card">
                <div class="stat-icon" style="background: <?php echo $stat['color']; ?>18; color: <?php echo $stat['color']; ?>;"><i class="<?php echo $stat['icon']; ?>"></i></div>
                <div class="stat-info">
                    <span class="stat-value" id="stat-<?php echo strtolower(str_replace(' ', '-', $stat['label'])); ?>"><?php echo $stat['value']; ?></span>
                    <span class="stat-label"><?php echo $stat['label']; ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="quick-actions">
            <a onclick="getShop()" class="action-card">
                <span class="action-icon"><i class="fa-solid fa-store"></i></span>
                <span class="action-title">Browse Shop</span>
                <span class="action-desc">Explore our ube treats</span>
            </a>
            <a onclick="getOrders()" class="action-card">
                <span class="action-icon"><i class="fa-solid fa-receipt"></i></span>
                <span class="action-title">My Orders</span>
                <span class="action-desc">Track your orders</span>
            </a>
            <a onclick="getProfile()" class="action-card">
                <span class="action-icon"><i class="fa-solid fa-user"></i></span>
                <span class="action-title">My Profile</span>
                <span class="action-desc">Manage your account</span>
            </a>
        </div>

        <div class="section-header">
            <div>
                <h2>Featured Products</h2>
                <p>Our most popular ube treats</p>
            </div>
            <a onclick="getShop()" class="view-all">View All →</a>
        </div>

        <div class="products-grid featured-products">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="product-card">
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php if ($product['badge']): ?>
                    <span class="product-badge"><?php echo $product['badge']; ?></span>
                    <?php endif; ?>
                </div>
                <div class="product-info">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <div class="product-price">
                        <span class="current"><?php echo $product['price']; ?></span>
                    </div>
                    <button class="btn-add-cart" data-name="<?php echo htmlspecialchars($product['name']); ?>" data-price="<?php echo $product['price']; ?>" onclick="addToCart(this)">Add to Cart</button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="section-header">
            <div>
                <h2>Recent Orders</h2>
                <p>Your latest purchases</p>
            </div>
            <a onclick="getOrders()" class="view-all">View All →</a>
        </div>

        <div class="orders-table-container">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="recentOrdersBody">
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td class="order-id"><?php echo $order['id']; ?></td>
                        <td><?php echo $order['date']; ?></td>
                        <td><?php echo $order['items']; ?></td>
                        <td class="order-total"><?php echo $order['total']; ?></td>
                        <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="features-section">
            <div class="features-grid">
                <?php foreach ($features as $item): ?>
                <div class="feature-card">
                    <div class="feature-icon"><i class="<?php echo $item['icon']; ?>"></i></div>
                    <div>
                        <h3><?php echo $item['title']; ?></h3>
                        <p><?php echo $item['description']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Ube Delights. All rights reserved.</p>
    </footer>

    <div class="toast" id="toast"></div>

    <script src="../javascript/disable_back.js"></script>
    <script src="../javascript/index.js"></script>
    <script src="../javascript/dashboard.js"></script>
    <script src="../javascript/routing.js"></script>
    <script src="../javascript/inspect.js"></script>
</body>
</html>
