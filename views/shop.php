<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['auth_user_id'])) {
    header('Location: ./login.php');
    exit();
}

$products = [
    ['name' => 'Ube Cheesecake', 'price' => '₱850', 'category' => 'cakes', 'description' => 'Creamy cheesecake with authentic ube swirl on a graham crust', 'badge' => 'Best Seller'],
    ['name' => 'Ube Roll', 'price' => '₱450', 'category' => 'rolls', 'description' => 'Soft sponge roll filled with smooth ube jam and cream', 'badge' => 'Popular'],
    ['name' => 'Ube Crinkles', 'price' => '₱280', 'category' => 'pastries', 'description' => 'Soft, chewy purple yam cookies coated in powdered sugar', 'badge' => ''],
    ['name' => 'Ube Halo-Halo', 'price' => '₱180', 'category' => 'beverages', 'description' => 'Refreshing shaved ice dessert with ube ice cream topping', 'badge' => 'New'],
    ['name' => 'Classic Ube Cake', 'price' => '₱950', 'category' => 'cakes', 'description' => 'Classic ube layer cake with smooth cream cheese frosting', 'badge' => ''],
    ['name' => 'Ube Pandesal', 'price' => '₱120', 'category' => 'pastries', 'description' => 'Soft purple yam bread rolls, perfect for breakfast', 'badge' => ''],
    ['name' => 'Ube Latte', 'price' => '₱150', 'category' => 'beverages', 'description' => 'Creamy ube-flavored milk tea with latte art', 'badge' => ''],
    ['name' => 'Ube Macapuno Cake', 'price' => '₱1,100', 'category' => 'cakes', 'description' => 'Rich ube cake with sweet coconut strings and latik', 'badge' => 'Premium'],
];

$categories = [
    ['name' => 'All', 'icon' => '🍽️'],
    ['name' => 'Cakes', 'icon' => '🎂'],
    ['name' => 'Pastries', 'icon' => '🥐'],
    ['name' => 'Rolls', 'icon' => '🍰'],
    ['name' => 'Beverages', 'icon' => '🧋'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ube Delights - Shop</title>
    <link rel="stylesheet" href="../css/dashboard.css?v=3.0">
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
                <span class="tab-icon"><?php echo $cat['icon']; ?></span>
                <?php echo $cat['name']; ?>
            </button>
            <?php endforeach; ?>
        </div>

        <div class="products-grid shop-grid" id="productsGrid">
            <?php foreach ($products as $product): ?>
            <div class="product-card" data-category="<?php echo $product['category']; ?>" data-name="<?php echo strtolower($product['name']); ?>">
                <div class="product-image">
                    <img src="../images/cake.png" alt="<?php echo htmlspecialchars($product['name']); ?>">
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
