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
    ['name' => 'Cakes', 'icon' => '🎂', 'count' => 3, 'description' => 'Signature ube cakes'],
    ['name' => 'Pastries', 'icon' => '🥐', 'count' => 2, 'description' => 'Freshly baked goods'],
    ['name' => 'Rolls', 'icon' => '🍰', 'count' => 1, 'description' => 'Soft sponge rolls'],
    ['name' => 'Beverages', 'icon' => '🧋', 'count' => 2, 'description' => 'Refreshing drinks'],
];

$features = [
    ['icon' => '✨', 'title' => 'Fresh Daily', 'description' => 'All products made fresh every day with authentic ube'],
    ['icon' => '🚚', 'title' => 'Free Delivery', 'description' => 'Free delivery on orders above ₱500'],
    ['icon' => '🎁', 'title' => 'Custom Orders', 'description' => 'Custom cake orders accepted 3 days in advance'],
    ['icon' => '💜', 'title' => '100% Ube', 'description' => 'Real purple yam, no artificial flavors'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ube Delights - Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css?v=2.0">
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
                <span class="nav-link greeting">Hi <?php echo htmlspecialchars($firstName !== '' ? $firstName : 'there');?>!</span>
                <a onclick="getLogout()" class="nav-link logout">Log Out</a>
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
        <div class="section-header">
            <div>
                <h2>Categories</h2>
                <p>Browse by your favorite treats</p>
            </div>
        </div>
        <div class="categories-grid">
            <?php foreach ($categories as $cat): ?>
            <div class="category-card" data-category="<?php echo strtolower($cat['name']); ?>">
                <div class="category-icon"><?php echo $cat['icon']; ?></div>
                <h3><?php echo $cat['name']; ?></h3>
                <p><?php echo $cat['count']; ?> items</p>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="section-header">
            <div>
                <h2>Our Menu</h2>
                <p>Fresh ube delicacies made with love</p>
            </div>
        </div>

        <div class="products-grid" id="productsGrid">
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
                    <div class="product-actions">
                        <button class="btn-add-cart" data-name="<?php echo htmlspecialchars($product['name']); ?>" data-price="<?php echo $product['price']; ?>">Add to Cart</button>
                        <button class="btn-wishlist" data-name="<?php echo htmlspecialchars($product['name']); ?>">♡</button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="no-results" id="noResults" style="display: none;">
                <div class="icon">🔍</div>
                <h3>No products found</h3>
                <p>Try searching with different keywords</p>
            </div>
        </div>

        <div class="features-section">
            <div class="features-grid">
                <?php foreach ($features as $item): ?>
                <div class="feature-card">
                    <div class="feature-icon"><?php echo $item['icon']; ?></div>
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
