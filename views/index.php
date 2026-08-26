<?php require_once __DIR__ . '/../server/customer_auth.php';

$userId = $currentUser['id'];
$firstName = '';

if ($connect) {
    // Fetch first name
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

    // Stats: total orders
    $totalOrders = 0;
    $sql = "SELECT COUNT(*) AS cnt FROM orders WHERE user_id = ?";
    if ($stmt = mysqli_prepare($connect, $sql)) {
        mysqli_stmt_bind_param($stmt, 's', $userId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res && $row = mysqli_fetch_assoc($res)) { $totalOrders = (int)$row['cnt']; }
        mysqli_stmt_close($stmt);
    }

    // Stats: pending orders
    $pendingOrders = 0;
    $sql = "SELECT COUNT(*) AS cnt FROM orders WHERE user_id = ? AND status = 'pending'";
    if ($stmt = mysqli_prepare($connect, $sql)) {
        mysqli_stmt_bind_param($stmt, 's', $userId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res && $row = mysqli_fetch_assoc($res)) { $pendingOrders = (int)$row['cnt']; }
        mysqli_stmt_close($stmt);
    }

    // Stats: delivered orders
    $deliveredOrders = 0;
    $sql = "SELECT COUNT(*) AS cnt FROM orders WHERE user_id = ? AND status = 'delivered'";
    if ($stmt = mysqli_prepare($connect, $sql)) {
        mysqli_stmt_bind_param($stmt, 's', $userId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res && $row = mysqli_fetch_assoc($res)) { $deliveredOrders = (int)$row['cnt']; }
        mysqli_stmt_close($stmt);
    }

    // Stats: total spent
    $totalSpent = 0;
    $sql = "SELECT COALESCE(SUM(total_amount), 0) AS total FROM orders WHERE user_id = ?";
    if ($stmt = mysqli_prepare($connect, $sql)) {
        mysqli_stmt_bind_param($stmt, 's', $userId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res && $row = mysqli_fetch_assoc($res)) { $totalSpent = (float)$row['total']; }
        mysqli_stmt_close($stmt);
    }

    // Recent orders (max 3)
    $recentOrders = [];
    $sql = "SELECT o.order_id, o.order_date, o.total_amount, o.status,
                   GROUP_CONCAT(oi.product_name SEPARATOR ', ') AS items
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.order_id
            ORDER BY o.order_date DESC
            LIMIT 3";
    if ($stmt = mysqli_prepare($connect, $sql)) {
        mysqli_stmt_bind_param($stmt, 's', $userId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $recentOrders[] = [
                    'id'     => 'ORD-' . str_pad($row['order_id'], 3, '0', STR_PAD_LEFT),
                    'date'   => date('M j, Y', strtotime($row['order_date'])),
                    'items'  => $row['items'],
                    'total'  => '₱' . number_format((float)$row['total_amount'], 0),
                    'status' => $row['status'],
                ];
            }
        }
        mysqli_stmt_close($stmt);
    }
}

$stats = [
    ['label' => 'Total Orders', 'value' => number_format($totalOrders), 'icon' => 'fa-solid fa-bag-shopping',  'color' => '#7c3aed'],
    ['label' => 'Pending',      'value' => number_format($pendingOrders), 'icon' => 'fa-solid fa-clock',          'color' => '#a855f7'],
    ['label' => 'Delivered',    'value' => number_format($deliveredOrders), 'icon' => 'fa-solid fa-circle-check',   'color' => '#6d28d9'],
    ['label' => 'Total Spent',  'value' => '₱' . number_format($totalSpent), 'icon' => 'fa-solid fa-wallet',         'color' => '#8b5cf6'],
];

$featuredIds = [];
$featuredFile = __DIR__ . '/../server/featured_data.json';
if (file_exists($featuredFile)) {
    $featuredData = json_decode(file_get_contents($featuredFile), true);
    if (isset($featuredData['featured_ids'])) {
        $featuredIds = $featuredData['featured_ids'];
    }
}

// Featured products (from DB)
$featuredProducts = [];
if ($connect && !empty($featuredIds)) {
    $placeholders = implode(',', array_fill(0, count($featuredIds), '?'));
    $types = str_repeat('i', count($featuredIds));
    $sql = "SELECT product_id, name, price, status, image
            FROM products
            WHERE product_id IN ($placeholders)";
    if ($stmt = mysqli_prepare($connect, $sql)) {
        mysqli_stmt_bind_param($stmt, $types, ...$featuredIds);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $featuredProducts[] = [
                    'id'    => (int)$row['product_id'],
                    'name'  => $row['name'],
                    'price' => '₱' . number_format((float)$row['price'], 0),
                    'badge' => $row['status'] ?? '',
                    'image' => '../' . ltrim($row['image'], '/'),
                ];
            }
        }
        mysqli_stmt_close($stmt);
    }
}


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
    <link rel="stylesheet" href="../css/dashboard.css?v=5.5">
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
            <?php if (count($featuredProducts) > 0): ?>
            <a onclick="getShop()" class="view-all">View All →</a>
            <?php endif; ?>
        </div>

        <?php if (count($featuredProducts) > 0): ?>
        <div class="products-grid featured-products">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="product-card<?php echo ($product['badge'] === 'Not Available') ? ' unavailable' : ''; ?>" data-status="<?php echo htmlspecialchars($product['badge'], ENT_QUOTES); ?>">
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
        <?php else: ?>
        <div class="empty-featured">
            <p>There are no featured products.</p>
            <a onclick="getShop()" class="view-all-inline">View All Products →</a>
        </div>
        <?php endif; ?>

        <div class="section-header">
            <div>
                <h2>Recent Orders</h2>
                <p>Your latest purchases</p>
            </div>
            <a onclick="getOrders()" class="view-all">View All →</a>
        </div>

        <?php if (count($recentOrders) > 0): ?>
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
                        <td><?php echo htmlspecialchars($order['items']); ?></td>
                        <td class="order-total"><?php echo $order['total']; ?></td>
                        <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="empty-recent-orders">
            <p>No recent orders</p>
            <a onclick="getShop()" class="view-all-inline">Browse Shop →</a>
        </div>
        <?php endif; ?>

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
