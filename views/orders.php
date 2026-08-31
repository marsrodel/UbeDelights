<?php require_once __DIR__ . '/../server/customer_auth.php';

$userId = $currentUser['id'];
$orders = [];

$orderQuery = mysqli_query($connect, "SELECT * FROM orders WHERE user_id = '" . mysqli_real_escape_string($connect, $userId) . "' ORDER BY order_date DESC");

if ($orderQuery) {
    while ($row = mysqli_fetch_assoc($orderQuery)) {
        $items = [];
        $itemQuery = mysqli_query($connect, "SELECT oi.*, p.image FROM order_items oi LEFT JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = " . intval($row['order_id']));
        if ($itemQuery) {
            while ($item = mysqli_fetch_assoc($itemQuery)) {
                $items[] = [
                    'id'    => intval($item['product_id']),
                    'name'  => $item['product_name'],
                    'qty'   => intval($item['quantity']),
                    'price' => '₱' . number_format(floatval($item['unit_price']), 0),
                    'image' => $item['image'] ? '../' . ltrim($item['image'], '/') : '',
                ];
            }
        }

        $updatedAt = null;
        if ($row['updated_at'] !== $row['order_date']) {
            $updatedAt = date('F j, Y', strtotime($row['updated_at']));
        }

        $orders[] = [
            'order_id'   => intval($row['order_id']),
            'id'         => 'ORD-' . str_pad($row['order_id'], 3, '0', STR_PAD_LEFT),
            'date'       => date('F j, Y', strtotime($row['order_date'])),
            'updated_at' => $updatedAt,
            'status'     => $row['status'],
            'street'     => $row['street'],
            'barangay'   => $row['barangay'],
            'city'       => $row['city'],
            'province'   => $row['province'],
            'zip_code'   => $row['zip_code'],
            'notes'      => $row['notes'] ?? '',
            'subtotal'   => floatval($row['subtotal']),
            'shipping'   => floatval($row['shipping_fee']),
            'items'      => $items,
        ];
    }
}
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
                    <?php foreach ($orders as $order): ?>
                    <tr class="order-row" data-status="<?php echo $order['status']; ?>"
                        data-order-id="<?php echo $order['order_id']; ?>"
                        data-updated="<?php echo htmlspecialchars($order['updated_at'] ?? '', ENT_QUOTES); ?>"
                        data-street="<?php echo htmlspecialchars($order['street'], ENT_QUOTES); ?>"
                        data-barangay="<?php echo htmlspecialchars($order['barangay'], ENT_QUOTES); ?>"
                        data-city="<?php echo htmlspecialchars($order['city'], ENT_QUOTES); ?>"
                        data-province="<?php echo htmlspecialchars($order['province'], ENT_QUOTES); ?>"
                        data-zip="<?php echo htmlspecialchars($order['zip_code'], ENT_QUOTES); ?>"
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
