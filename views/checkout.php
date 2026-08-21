<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['auth_user_id'])) {
    header('Location: ./login.php');
    exit();
}

include '../server/db.php';
$userId = $_SESSION['auth_user_id'];

$firstName = $lastName = $email = $phone = $street = $barangay = $city = $province = $zip = '';
if ($connect) {
    $stmt = mysqli_prepare($connect, "SELECT first_name, last_name, email, street, barangay, city_municipality, province, zip_code FROM users WHERE user_id = ? LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $userId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = $res ? mysqli_fetch_assoc($res) : null;
        mysqli_stmt_close($stmt);
        if ($row) {
            $firstName = $row['first_name'] ?? '';
            $lastName = $row['last_name'] ?? '';
            $email = $row['email'] ?? '';
            $street = $row['street'] ?? '';
            $barangay = $row['barangay'] ?? '';
            $city = $row['city_municipality'] ?? '';
            $province = $row['province'] ?? '';
            $zip = $row['zip_code'] ?? '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ube Delights - Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                <a onclick="getOrders()" class="nav-link">My Orders</a>
                <a onclick="getProfile()" class="nav-link">Profile</a>
                <a onclick="getLogout()" class="nav-link">Log Out</a>
            </div>
        </div>
    </nav>

    <section class="hero-section hero-small">
        <div class="hero-content">
            <h1>Check<span>out</span></h1>
            <p>Complete your order details below.</p>
        </div>
    </section>

    <main class="main-content">
        <div class="checkout-layout">
            <div class="checkout-form-section">
                <form id="checkoutForm" novalidate>
                    <div class="checkout-card">
                        <h3><i class="fa-solid fa-location-dot"></i> Delivery Address</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="coFirstName">First Name</label>
                                <input type="text" id="coFirstName" value="<?php echo htmlspecialchars($firstName); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="coLastName">Last Name</label>
                                <input type="text" id="coLastName" value="<?php echo htmlspecialchars($lastName); ?>" required>
                            </div>
                            <div class="form-group full-width">
                                <label for="coStreet">Street / Purok</label>
                                <input type="text" id="coStreet" value="<?php echo htmlspecialchars($street); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="coBarangay">Barangay</label>
                                <input type="text" id="coBarangay" value="<?php echo htmlspecialchars($barangay); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="coCity">City / Municipality</label>
                                <input type="text" id="coCity" value="<?php echo htmlspecialchars($city); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="coProvince">Province</label>
                                <input type="text" id="coProvince" value="<?php echo htmlspecialchars($province); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="coZip">Zip Code</label>
                                <input type="text" id="coZip" value="<?php echo htmlspecialchars($zip); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-contact-payment">
                        <div class="checkout-card">
                            <h3><i class="fa-solid fa-phone"></i> Contact Information</h3>
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label for="coEmail">Email Address</label>
                                    <input type="email" id="coEmail" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                                <div class="form-group full-width">
                                    <label for="coPhone">Phone Number</label>
                                    <input type="tel" id="coPhone" placeholder="09XX XXX XXXX" required>
                                </div>
                            </div>
                        </div>

                        <div class="checkout-card">
                            <h3><i class="fa-solid fa-credit-card"></i> Payment Method</h3>
                            <div class="payment-options">
                                <label class="payment-option selected">
                                    <input type="radio" name="payment" value="cod" checked>
                                    <div class="payment-info">
                                        <i class="fa-solid fa-money-bill-wave"></i>
                                        <div>
                                            <strong>Cash on Delivery</strong>
                                            <span>Pay when your order arrives</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="payment-option">
                                    <input type="radio" name="payment" value="gcash">
                                    <div class="payment-info">
                                        <i class="fa-solid fa-mobile-screen-button"></i>
                                        <div>
                                            <strong>GCash</strong>
                                            <span>Pay via GCash mobile wallet</span>
                                        </div>
                                    </div>
                                </label>
                                <label class="payment-option">
                                    <input type="radio" name="payment" value="maya">
                                    <div class="payment-info">
                                        <i class="fa-solid fa-wallet"></i>
                                        <div>
                                            <strong>Maya (PayMaya)</strong>
                                            <span>Pay via Maya mobile wallet</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-card">
                        <h3><i class="fa-solid fa-comment"></i> Order Notes</h3>
                        <div class="form-group full-width">
                            <textarea id="coNotes" rows="3" placeholder="Special instructions (e.g., gift message, delivery time preference)"></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <div class="checkout-summary-section">
                <div class="cart-summary checkout-summary">
                    <h3>Order Summary</h3>
                    <div id="checkoutItems" class="checkout-items"></div>
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="coSubtotal">₱0</span>
                    </div>
                    <div class="summary-row">
                        <span>Shipping</span>
                        <span id="coShipping">₱0</span>
                    </div>
                    <div class="summary-row total">
                        <span>Total</span>
                        <span id="coTotal">₱0</span>
                    </div>
                    <button class="btn-checkout" id="btnPlaceOrder">
                        <i class="fa-solid fa-lock"></i> Place Order
                    </button>
                    <a onclick="getCart()" class="btn-continue">← Back to Cart</a>
                </div>
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
    <script src="../javascript/checkout.js"></script>
    <script src="../javascript/routing.js"></script>
    <script src="../javascript/inspect.js"></script>
</body>
</html>
