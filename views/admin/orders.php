<?php require_once __DIR__ . '/../../server/admin_auth.php';

$orders = [];

$orderQuery = mysqli_query($connect, "
    SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) AS customer_name
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.user_id
    ORDER BY o.order_date DESC
");

if ($orderQuery) {
    while ($row = mysqli_fetch_assoc($orderQuery)) {
        $items = [];
        $itemQuery = mysqli_query($connect, "SELECT * FROM order_items WHERE order_id = " . intval($row['order_id']));
        if ($itemQuery) {
            while ($item = mysqli_fetch_assoc($itemQuery)) {
                $items[] = [
                    'name'  => $item['product_name'],
                    'qty'   => intval($item['quantity']),
                    'price' => floatval($item['unit_price']),
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
            'customer'   => $row['customer_name'] ?? 'Unknown',
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
    <title>Ube Delights - Admin Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin.css?v=1.6">
</head>
<body class="admin-body">
    <aside class="admin-sidebar">
        <div class="sidebar-logo">
            <img src="../../images/logo.png" alt="Ube Delights Logo">
            <div>
                <h2>Ube Delights</h2>
                <span class="sidebar-tag">Admin Panel</span>
            </div>
        </div>

        <div class="sidebar-profile">
            <div class="admin-chip">
                <div class="admin-avatar">AU</div>
                <div class="admin-chip-info">
                    <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong>
                    <small>ADMIN</small>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a onclick="getAdminDashboard()" class="sidebar-link"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
            <a onclick="getAdminProducts()" class="sidebar-link"><i class="fa-solid fa-box"></i><span>Products</span></a>
            <a onclick="getAdminOrders()" class="sidebar-link active"><i class="fa-solid fa-bag-shopping"></i><span>Orders</span></a>
            <a onclick="getAdminUserManagement()" class="sidebar-link"><i class="fa-solid fa-users-cog"></i><span>User Management</span></a>
            <a onclick="getAdminPendingApprovals()" class="sidebar-link"><i class="fa-solid fa-user-clock"></i><span>Pending Approvals</span></a>
            <a onclick="getAdminSystemLogs()" class="sidebar-link"><i class="fa-solid fa-list-alt"></i><span>System Logs</span></a>
        </nav>

        <div class="sidebar-footer">
            <a onclick="getAdminLogout()" class="sidebar-logout"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <h1>Orders</h1>
            <div class="topbar-right">
                <span class="topbar-date"><i class="fa-solid fa-calendar-days"></i><?php echo date('F j, Y'); ?></span>
            </div>
        </header>

        <main class="admin-content">
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
                            <th>Customer</th>
                            <th>Order Date</th>
                            <th>Updated At</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr class="order-row" data-status="<?php echo $order['status']; ?>"
                            data-order-id="<?php echo $order['order_id']; ?>"
                            data-updated="<?php echo htmlspecialchars($order['updated_at'] ?? '', ENT_QUOTES); ?>"
                            data-items="<?php echo htmlspecialchars(json_encode($order['items']), ENT_QUOTES); ?>"
                            data-street="<?php echo htmlspecialchars($order['street'], ENT_QUOTES); ?>"
                            data-barangay="<?php echo htmlspecialchars($order['barangay'], ENT_QUOTES); ?>"
                            data-city="<?php echo htmlspecialchars($order['city'], ENT_QUOTES); ?>"
                            data-province="<?php echo htmlspecialchars($order['province'], ENT_QUOTES); ?>"
                            data-zip="<?php echo htmlspecialchars($order['zip_code'], ENT_QUOTES); ?>"
                            data-notes="<?php echo htmlspecialchars($order['notes'], ENT_QUOTES); ?>"
                            data-subtotal="<?php echo $order['subtotal']; ?>"
                            data-shipping="<?php echo $order['shipping']; ?>">
                            <td class="order-id"><?php echo $order['id']; ?></td>
                            <td><i class="fa-solid fa-user" style="color:var(--accent-light);margin-right:5px;font-size:0.8rem;"></i><?php echo htmlspecialchars($order['customer']); ?></td>
                            <td class="order-date"><?php echo $order['date']; ?></td>
                            <td class="order-date"><?php echo $order['updated_at'] ? $order['updated_at'] : '—'; ?></td>
                            <td class="order-total"><?php echo '₱' . number_format($order['subtotal'] + $order['shipping']); ?></td>
                            <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                            <td class="order-actions-cell">
                                <button class="btn-icon btn-view" title="View details"><i class="fa-solid fa-eye"></i></button>
                                <?php if ($order['status'] === 'pending'): ?>
                                <button class="btn-action btn-confirm" data-action="confirm" title="Confirm"><i class="fa-solid fa-check"></i></button>
                                <button class="btn-action btn-cancel" data-action="cancel" title="Cancel"><i class="fa-solid fa-xmark"></i></button>
                                <?php elseif ($order['status'] === 'confirmed'): ?>
                                <button class="btn-action btn-deliver" data-action="deliver" title="Deliver"><i class="fa-solid fa-truck-fast"></i></button>
                                <button class="btn-action btn-cancel" data-action="cancel" title="Cancel"><i class="fa-solid fa-xmark"></i></button>
                                <?php elseif ($order['status'] === 'delivered'): ?>
                                <span class="cell-muted"><i class="fa-solid fa-circle-check" style="color:#15803d;"></i> Done</span>
                                <?php else: ?>
                                <span class="cell-muted"><i class="fa-solid fa-ban" style="color:#dc2626;"></i> Cancelled</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="empty-state" id="ordersEmpty" style="display:none;">
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
    </div>

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

    <div class="toast" id="toast"></div>

    <script src="../../javascript/admin-routing.js"></script>
    <script src="../../javascript/admin_orders.js"></script>
    <script src="../../javascript/inspect.js"></script>
</body>
</html>
