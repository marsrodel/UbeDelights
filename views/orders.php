<?php require_once __DIR__ . '/../server/customer_auth.php';

$mockOrders = [
    [
        'id' => 'UBD-MT2IYXVL', 'date' => 'August 25, 2026', 'updated_at' => null, 'status' => 'pending',
        'street' => '123 Sampaguita St.', 'barangay' => 'Brgy. San Isidro', 'city' => 'Quezon City', 'province' => 'Metro Manila', 'zip_code' => '1116',
        'payment' => 'Cash on Delivery', 'notes' => 'Please ring the bell twice.',
        'subtotal' => 1000, 'shipping' => 100,
        'items' => [
            ['name' => 'Ube Cheesecake', 'qty' => 1, 'price' => 850],
            ['name' => 'Ube Latte', 'qty' => 2, 'price' => 75],
        ],
    ],
    [
        'id' => 'UBD-MT33O3BT', 'date' => 'August 22, 2026', 'updated_at' => null, 'status' => 'pending',
        'street' => '456 Rosa St.', 'barangay' => 'Brgy. Maligaya', 'city' => 'Manila', 'province' => 'Metro Manila', 'zip_code' => '1008',
        'payment' => 'GCash', 'notes' => '',
        'subtotal' => 500, 'shipping' => 100,
        'items' => [
            ['name' => 'Ube Roll', 'qty' => 2, 'price' => 250],
        ],
    ],
    [
        'id' => 'UBD-MT18Y7AP', 'date' => 'August 18, 2026', 'updated_at' => 'August 20, 2026', 'status' => 'confirmed',
        'street' => '789 Orchid Ave.', 'barangay' => 'Brgy. Liping', 'city' => 'Pasig City', 'province' => 'Metro Manila', 'zip_code' => '1607',
        'payment' => 'Cash on Delivery', 'notes' => 'Leave at the front desk if no one is home.',
        'subtotal' => 365, 'shipping' => 100,
        'items' => [
            ['name' => 'Classic Ube Cake', 'qty' => 1, 'price' => 350],
            ['name' => 'Ube Pandesal', 'qty' => 3, 'price' => 5],
        ],
    ],
    [
        'id' => 'UBD-MT09K2ZQ', 'date' => 'August 10, 2026', 'updated_at' => 'August 12, 2026', 'status' => 'delivered',
        'street' => '321 Mayumi Lane', 'barangay' => 'Brgy. Santol', 'city' => 'Mandaluyong', 'province' => 'Metro Manila', 'zip_code' => '1550',
        'payment' => 'GCash', 'notes' => '',
        'subtotal' => 200, 'shipping' => 0,
        'items' => [
            ['name' => 'Ube Pandesal', 'qty' => 4, 'price' => 5],
            ['name' => 'Ube Halo-Halo', 'qty' => 1, 'price' => 180],
        ],
    ],
    [
        'id' => 'ORD-001', 'date' => 'August 18, 2025', 'updated_at' => 'August 20, 2025', 'status' => 'delivered',
        'street' => '55 Greenhills Blvd.', 'barangay' => 'Brgy. Wack-Wack', 'city' => 'Mandaluyong', 'province' => 'Metro Manila', 'zip_code' => '1554',
        'payment' => 'Cash on Delivery', 'notes' => '',
        'subtotal' => 1000, 'shipping' => 0,
        'items' => [
            ['name' => 'Ube Cheesecake', 'qty' => 1, 'price' => 850],
            ['name' => 'Ube Latte', 'qty' => 1, 'price' => 75],
        ],
    ],
    [
        'id' => 'ORD-002', 'date' => 'August 15, 2025', 'updated_at' => 'August 15, 2025', 'status' => 'cancelled',
        'street' => '88 Taurus St.', 'barangay' => 'Brgy. Horseshoe', 'city' => 'Quezon City', 'province' => 'Metro Manila', 'zip_code' => '1112',
        'payment' => 'GCash', 'notes' => '',
        'subtotal' => 730, 'shipping' => 100,
        'items' => [
            ['name' => 'Ube Roll', 'qty' => 1, 'price' => 250],
            ['name' => 'Ube Crinkles', 'qty' => 1, 'price' => 140],
            ['name' => 'Ube Cheesecake', 'qty' => 1, 'price' => 850],
        ],
    ],
    [
        'id' => 'ORD-003', 'date' => 'August 12, 2025', 'updated_at' => 'August 18, 2025', 'status' => 'delivered',
        'street' => '12 Pearl Dr.', 'barangay' => 'Brgy. Addition Hills', 'city' => 'San Juan', 'province' => 'Metro Manila', 'zip_code' => '1500',
        'payment' => 'Cash on Delivery', 'notes' => 'Gate code is 4567.',
        'subtotal' => 950, 'shipping' => 100,
        'items' => [
            ['name' => 'Classic Ube Cake', 'qty' => 1, 'price' => 350],
            ['name' => 'Ube Pandesal', 'qty' => 6, 'price' => 5],
            ['name' => 'Ube Halo-Halo', 'qty' => 1, 'price' => 180],
            ['name' => 'Ube Latte', 'qty' => 1, 'price' => 75],
        ],
    ],
    [
        'id' => 'ORD-004', 'date' => 'August 5, 2025', 'updated_at' => 'August 5, 2025', 'status' => 'cancelled',
        'street' => '9 Sunflower St.', 'barangay' => 'Brgy. Kabayanan', 'city' => 'Manila', 'province' => 'Metro Manila', 'zip_code' => '1007',
        'payment' => 'GCash', 'notes' => '',
        'subtotal' => 420, 'shipping' => 100,
        'items' => [
            ['name' => 'Ube Pandesal', 'qty' => 2, 'price' => 5],
            ['name' => 'Ube Halo-Halo', 'qty' => 1, 'price' => 180],
        ],
    ],
    [
        'id' => 'ORD-005', 'date' => 'July 28, 2025', 'updated_at' => 'July 30, 2025', 'status' => 'delivered',
        'street' => '21 Magnolia Ave.', 'barangay' => 'Brgy. Old Zaniga', 'city' => 'Mandaluyong', 'province' => 'Metro Manila', 'zip_code' => '1550',
        'payment' => 'Cash on Delivery', 'notes' => '',
        'subtotal' => 1700, 'shipping' => 0,
        'items' => [
            ['name' => 'Ube Cheesecake', 'qty' => 2, 'price' => 850],
        ],
    ],
    [
        'id' => 'ORD-006', 'date' => 'July 20, 2025', 'updated_at' => 'July 22, 2025', 'status' => 'delivered',
        'street' => '7 Bamboo Ct.', 'barangay' => 'Brgy. Plainview', 'city' => 'Mandaluyong', 'province' => 'Metro Manila', 'zip_code' => '1550',
        'payment' => 'GCash', 'notes' => 'Text me upon arrival.',
        'subtotal' => 475, 'shipping' => 100,
        'items' => [
            ['name' => 'Ube Latte', 'qty' => 3, 'price' => 75],
            ['name' => 'Ube Roll', 'qty' => 1, 'price' => 250],
        ],
    ],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ube Delights - My Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../css/dashboard.css?v=5.7">
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

        <div class="orders-table-container">
            <table class="orders-table" id="ordersTable">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Total</th>
                        <th>Order Date</th>
                        <th>Updated At</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mockOrders as $order): ?>
                    <tr class="order-row" data-status="<?php echo $order['status']; ?>"
                        data-updated="<?php echo htmlspecialchars($order['updated_at'] ?? '', ENT_QUOTES); ?>"
                        data-street="<?php echo htmlspecialchars($order['street'], ENT_QUOTES); ?>"
                        data-barangay="<?php echo htmlspecialchars($order['barangay'], ENT_QUOTES); ?>"
                        data-city="<?php echo htmlspecialchars($order['city'], ENT_QUOTES); ?>"
                        data-province="<?php echo htmlspecialchars($order['province'], ENT_QUOTES); ?>"
                        data-zip="<?php echo htmlspecialchars($order['zip_code'], ENT_QUOTES); ?>"
                        data-payment="<?php echo htmlspecialchars($order['payment'], ENT_QUOTES); ?>"
                        data-notes="<?php echo htmlspecialchars($order['notes'], ENT_QUOTES); ?>"
                        data-subtotal="<?php echo $order['subtotal']; ?>"
                        data-shipping="<?php echo $order['shipping']; ?>"
                        data-items="<?php echo htmlspecialchars(json_encode($order['items']), ENT_QUOTES); ?>">
                        <td class="order-id"><?php echo $order['id']; ?></td>
                        <td class="order-total"><?php echo '₱' . number_format($order['subtotal'] + $order['shipping']); ?></td>
                        <td class="order-date"><?php echo $order['date']; ?></td>
                        <td class="order-date"><?php echo $order['updated_at'] ? $order['updated_at'] : '—'; ?></td>
                        <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                        <td class="order-actions-cell">
                            <button class="btn-icon btn-view" title="View details"><i class="fa-solid fa-eye"></i></button>
                            <?php if ($order['status'] === 'pending'): ?>
                            <button class="btn-cancel-order" data-order="<?php echo $order['id']; ?>"><i class="fa-solid fa-xmark"></i> Cancel</button>
                            <?php else: ?>
                            <button class="btn-reorder" data-order="<?php echo $order['id']; ?>">Reorder</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="empty-orders" id="emptyOrders" style="display:none;">
                <div class="empty-icon">📦</div>
                <h3>No orders found</h3>
                <p>No orders match this filter.</p>
            </div>

            <div class="pagination-bar" id="paginationBar">
                <div class="pagination-info" id="paginationInfo"></div>
                <div class="pagination" id="paginationLinks"></div>
            </div>
        </div>
    </main>

    <!-- Order Detail Modal -->
    <div class="modal-overlay" id="orderModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Order Detail</h2>
                <button class="modal-close" id="modalClose"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" id="modalBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn-primary" id="modalCloseBtn">Close</button>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Ube Delights. All rights reserved.</p>
    </footer>

    <div class="toast" id="toast"></div>

    <script src="../javascript/disable_back.js"></script>
    <script src="../javascript/index.js"></script>
    <script src="../javascript/dashboard.js"></script>
    <script src="../javascript/orders.js"></script>
    <script src="../javascript/routing.js"></script>
    <script src="../javascript/inspect.js"></script>
</body>
</html>
