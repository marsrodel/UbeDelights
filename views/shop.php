<?php require_once __DIR__ . '/../server/customer_auth.php';

$products = [];
if ($connect) {
    $sql = "SELECT product_id, name, price, category, status, description, image FROM products ORDER BY name";
    if ($stmt = mysqli_prepare($connect, $sql)) {
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($res) {
            while ($row = mysqli_fetch_assoc($res)) {
                $products[] = [
                    'name'        => $row['name'],
                    'price'       => '₱' . number_format((float)$row['price'], 0),
                    'category'    => $row['category'],
                    'description' => $row['description'],
                    'badge'       => $row['status'] ?? '',
                    'image'       => '../' . ltrim($row['image'], '/'),
                ];
            }
        }
        mysqli_stmt_close($stmt);
    }
}

$categories = [
    ['name' => 'All',       'icon' => 'fa-solid fa-border-all'],
    ['name' => 'Cakes',     'icon' => 'fa-solid fa-cake-candles'],
    ['name' => 'Pastries',  'icon' => 'fa-solid fa-bread-slice'],
    ['name' => 'Rolls',     'icon' => 'fa-solid fa-cookie'],
    ['name' => 'Beverages', 'icon' => 'fa-solid fa-mug-hot'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ube Delights - Shop</title>
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
                <a onclick="getIndex()" class="nav-link">Dashboard</a>
                <a onclick="getShop()" class="nav-link active">Shop</a>
                <a onclick="getCart()" class="nav-link cart-link">Cart <span class="cart-badge" id="cartBadge" style="display:none;">0</span></a>
                <a onclick="getOrders()" class="nav-link">My Orders</a>
                <a onclick="getProfile()" class="nav-link">Profile</a>
                <a onclick="getLogout()" class="nav-link">Log Out</a>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="hero-content">
            <h1>Our <span>Shop</span></h1>
            <p>Browse our selection of authentic ube treats made fresh daily.</p>
        </div>
    </section>

    <main class="main-content">
        <div class="category-tabs">
            <?php foreach ($categories as $index => $cat): ?>
            <button class="tab-btn <?php echo $index === 0 ? 'active' : ''; ?>" data-category="<?php echo strtolower($cat['name']); ?>">
                <span class="tab-icon"><i class="<?php echo $cat['icon']; ?>"></i></span>
                <?php echo $cat['name']; ?>
            </button>
            <?php endforeach; ?>
        </div>

        <div class="products-grid shop-grid" id="productsGrid">
            <?php if (count($products) > 0): ?>
            <?php foreach ($products as $product): ?>
            <div class="product-card<?php echo ($product['badge'] === 'Not Available') ? ' unavailable' : ''; ?>" data-category="<?php echo $product['category']; ?>" data-name="<?php echo strtolower($product['name']); ?>" data-status="<?php echo htmlspecialchars($product['badge'], ENT_QUOTES); ?>">
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
                    <p class="description"><?php echo htmlspecialchars($product['description']); ?></p>
                    <button class="btn-add-cart" data-name="<?php echo htmlspecialchars($product['name']); ?>" data-price="<?php echo $product['price']; ?>" onclick="addToCart(this)">Add to Cart</button>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="no-results" id="noResults" style="display: none;">
                <div class="icon">🔍</div>
                <h3>No products found</h3>
                <p>Try selecting a different category</p>
            </div>
            <?php else: ?>
            <div class="empty-shop">
                <i class="fa-solid fa-store-slash"></i>
                <h3>No products available yet</h3>
                <p>Check back soon — we're stocking up on fresh ube treats!</p>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Ube Delights. All rights reserved.</p>
    </footer>

    <div class="toast" id="toast"></div>

    <script src="../javascript/disable_back.js"></script>
    <script src="../javascript/index.js"></script>
    <script src="../javascript/dashboard.js"></script>
    <script src="../javascript/shop.js"></script>
    <script src="../javascript/routing.js"></script>
    <script src="../javascript/inspect.js"></script>
</body>
</html>
