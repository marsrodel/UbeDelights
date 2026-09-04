<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// If user is already logged in, redirect to dashboard
if (isset($_SESSION['auth_user_id'])) {
    header('Location: ./index.php');
    exit();
}
$vs = isset($_GET['viewsource']) ? '?viewsource=1' : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ube Delights - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/home.css?v=2.0">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <a onclick="getHome()" class="logo-link">
                    <img src="../images/logo.png" alt="Ube Roll Logo" class="logo-image">
                    <h2>Ube Delights</h2>
                </a>
            </div>
            <div class="nav-menu">
                <a onclick="getLogin()" class="nav-link">Login</a>
                <a onclick="getRegister()" class="nav-link">Register</a>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <section class="hero-section">
            <div class="hero-content">
                <h1>Welcome to <span>Ube Delights</span></h1>
                <p>Indulge in the rich, vibrant flavors of our signature ube cakes made with authentic purple yam from the Philippines.</p>
                <div class="hero-actions">
                    <a onclick="getRegister()" class="hero-btn hero-btn-primary"><i class="fa-solid fa-user-plus"></i> Get Started</a>
                    <a onclick="getLogin()" class="hero-btn hero-btn-secondary"><i class="fa-solid fa-right-to-bracket"></i> Sign In</a>
                </div>
            </div>
        </section>

        <section class="features-section">
            <div class="section-header">
                <h2>Why Choose Us</h2>
            </div>
            <div class="features-cards">
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-star"></i></div>
                    <h3>Fresh Daily</h3>
                    <p>Made fresh every day with authentic ube</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-truck-fast"></i></div>
                    <h3>Free Delivery</h3>
                    <p>Free delivery on orders above ₱500</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-gift"></i></div>
                    <h3>Custom Orders</h3>
                    <p>Custom cake orders 3 days in advance</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon"><i class="fa-solid fa-heart"></i></div>
                    <h3>100% Ube</h3>
                    <p>Real purple yam, no artificial flavors</p>
                </div>
            </div>
        </section>

    </main>

    <footer class="footer">
        <p>&copy; 2026 Ube Delights. All rights reserved.</p>
    </footer>
    <script src="../javascript/routing.js"></script>
    <script src="../javascript/inspect.js"></script>
</body>
</html>
