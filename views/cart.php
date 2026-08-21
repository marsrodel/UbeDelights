<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['auth_user_id'])) {
    header('Location: ./login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ube Delights - Cart</title>
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
                <a onclick="getCart()" class="nav-link active cart-link">Cart <span class="cart-badge" id="cartBadge" style="display:none;">0</span></a>
                <a onclick="getOrders()" class="nav-link">My Orders</a>
                <a onclick="getProfile()" class="nav-link">Profile</a>
                <a onclick="getLogout()" class="nav-link">Log Out</a>
            </div>
        </div>
    </nav>

    <section class="hero-section hero-small">
        <div class="hero-content">
            <h1>Your <span>Cart</span></h1>
            <p>Review your items before checkout.</p>
        </div>
    </section>

    <main class="main-content">
        <div class="cart-layout">
            <div class="cart-items" id="cartItems">
                <div class="empty-cart" id="emptyCart">
                    <div class="empty-icon">🛒</div>
                    <h3>Your cart is empty</h3>
                    <p>Add some delicious ube treats from our shop!</p>
                    <a onclick="getShop()" class="btn-primary">Browse Shop</a>
                </div>
                <div class="cart-list" id="cartList" style="display: none;">
                    <div class="cart-header-row">
                        <span class="col-product">Product</span>
                        <span class="col-price">Price</span>
                        <span class="col-quantity">Quantity</span>
                        <span class="col-subtotal">Subtotal</span>
                        <span class="col-action"></span>
                    </div>
                    <div id="cartItemsList"></div>
                </div>
            </div>

            <div class="cart-summary" id="cartSummary" style="display: none;">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="summarySubtotal">₱0</span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span id="summaryShipping">₱0</span>
                </div>
                <div class="summary-row total">
                    <span>Total</span>
                    <span id="summaryTotal">₱0</span>
                </div>
                <button class="btn-checkout" id="btnCheckout">Proceed to Checkout</button>
                <a onclick="getShop()" class="btn-continue">← Continue Shopping</a>
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
    <script src="../javascript/cart.js"></script>
    <script src="../javascript/routing.js"></script>
    <script src="../javascript/inspect.js"></script>
</body>
</html>
