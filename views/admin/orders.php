<?php require_once __DIR__ . '/../../server/admin_auth.php';

$mockOrders = [
    ['id' => 'ORD-012', 'customer' => 'Maria Santos',    'date' => 'August 25, 2026', 'updated_at' => null, 'status' => 'pending',
     'street' => '123 Sampaguita St.', 'barangay' => 'Brgy. San Isidro', 'city' => 'Quezon City', 'province' => 'Metro Manila', 'zip_code' => '1116',
     'notes' => 'Please ring the bell twice.', 'subtotal' => 1000, 'shipping' => 100,
     'items' => [['name' => 'Ube Cheesecake', 'qty' => 1, 'price' => 850], ['name' => 'Ube Latte', 'qty' => 2, 'price' => 75]]],
    ['id' => 'ORD-011', 'customer' => 'Juan Dela Cruz',   'date' => 'August 24, 2026', 'updated_at' => null, 'status' => 'pending',
     'street' => '456 Rosa St.', 'barangay' => 'Brgy. Maligaya', 'city' => 'Manila', 'province' => 'Metro Manila', 'zip_code' => '1008',
     'notes' => '', 'subtotal' => 500, 'shipping' => 100,
     'items' => [['name' => 'Ube Roll', 'qty' => 2, 'price' => 250]]],
    ['id' => 'ORD-010', 'customer' => 'Ana Reyes',        'date' => 'August 23, 2026', 'updated_at' => 'August 25, 2026', 'status' => 'confirmed',
     'street' => '789 Orchid Ave.', 'barangay' => 'Brgy. Liping', 'city' => 'Pasig City', 'province' => 'Metro Manila', 'zip_code' => '1607',
     'notes' => 'Leave at the front desk.', 'subtotal' => 365, 'shipping' => 100,
     'items' => [['name' => 'Classic Ube Cake', 'qty' => 1, 'price' => 350], ['name' => 'Ube Pandesal', 'qty' => 3, 'price' => 5]]],
    ['id' => 'ORD-009', 'customer' => 'Carlo Mendoza',    'date' => 'August 22, 2026', 'updated_at' => 'August 23, 2026', 'status' => 'confirmed',
     'street' => '321 Mayumi Lane', 'barangay' => 'Brgy. Santol', 'city' => 'Mandaluyong', 'province' => 'Metro Manila', 'zip_code' => '1550',
     'notes' => '', 'subtotal' => 200, 'shipping' => 100,
     'items' => [['name' => 'Ube Pandesal', 'qty' => 4, 'price' => 5], ['name' => 'Ube Halo-Halo', 'qty' => 1, 'price' => 180]]],
    ['id' => 'ORD-008', 'customer' => 'Lisa Garcia',      'date' => 'August 21, 2026', 'updated_at' => 'August 22, 2026', 'status' => 'delivered',
     'street' => '55 Greenhills Blvd.', 'barangay' => 'Brgy. Wack-Wack', 'city' => 'Mandaluyong', 'province' => 'Metro Manila', 'zip_code' => '1554',
     'notes' => '', 'subtotal' => 280, 'shipping' => 100,
     'items' => [['name' => 'Ube Crinkles', 'qty' => 2, 'price' => 140]]],
    ['id' => 'ORD-007', 'customer' => 'Mark Torres',      'date' => 'August 20, 2026', 'updated_at' => 'August 22, 2026', 'status' => 'delivered',
     'street' => '88 Taurus St.', 'barangay' => 'Brgy. Horseshoe', 'city' => 'Quezon City', 'province' => 'Metro Manila', 'zip_code' => '1112',
     'notes' => '', 'subtotal' => 850, 'shipping' => 0,
     'items' => [['name' => 'Ube Cheesecake', 'qty' => 1, 'price' => 850]]],
    ['id' => 'ORD-006', 'customer' => 'Sofia Ramos',      'date' => 'August 19, 2026', 'updated_at' => 'August 21, 2026', 'status' => 'delivered',
     'street' => '12 Pearl Dr.', 'barangay' => 'Brgy. Addition Hills', 'city' => 'San Juan', 'province' => 'Metro Manila', 'zip_code' => '1500',
     'notes' => 'Gate code is 4567.', 'subtotal' => 475, 'shipping' => 100,
     'items' => [['name' => 'Ube Latte', 'qty' => 3, 'price' => 75], ['name' => 'Ube Roll', 'qty' => 1, 'price' => 250]]],
    ['id' => 'ORD-005', 'customer' => 'Jose Villanueva', 'date' => 'August 18, 2026', 'updated_at' => 'August 18, 2026', 'status' => 'cancelled',
     'street' => '9 Sunflower St.', 'barangay' => 'Brgy. Kabayanan', 'city' => 'Manila', 'province' => 'Metro Manila', 'zip_code' => '1007',
     'notes' => '', 'subtotal' => 700, 'shipping' => 100,
     'items' => [['name' => 'Classic Ube Cake', 'qty' => 2, 'price' => 350]]],
    ['id' => 'ORD-004', 'customer' => 'Maria Santos',    'date' => 'August 17, 2026', 'updated_at' => 'August 19, 2026', 'status' => 'delivered',
     'street' => '21 Magnolia Ave.', 'barangay' => 'Brgy. Old Zaniga', 'city' => 'Mandaluyong', 'province' => 'Metro Manila', 'zip_code' => '1550',
     'notes' => '', 'subtotal' => 385, 'shipping' => 100,
     'items' => [['name' => 'Ube Halo-Halo', 'qty' => 2, 'price' => 180], ['name' => 'Ube Pandesal', 'qty' => 5, 'price' => 5]]],
    ['id' => 'ORD-003', 'customer' => 'Juan Dela Cruz',   'date' => 'August 16, 2026', 'updated_at' => 'August 20, 2026', 'status' => 'delivered',
     'street' => '7 Bamboo Ct.', 'barangay' => 'Brgy. Plainview', 'city' => 'Mandaluyong', 'province' => 'Metro Manila', 'zip_code' => '1550',
     'notes' => 'Text me upon arrival.', 'subtotal' => 925, 'shipping' => 100,
     'items' => [['name' => 'Ube Cheesecake', 'qty' => 1, 'price' => 850], ['name' => 'Ube Latte', 'qty' => 1, 'price' => 75]]],
    ['id' => 'ORD-002', 'customer' => 'Ana Reyes',        'date' => 'August 15, 2026', 'updated_at' => 'August 15, 2026', 'status' => 'cancelled',
     'street' => '12 Pearl Dr.', 'barangay' => 'Brgy. Addition Hills', 'city' => 'San Juan', 'province' => 'Metro Manila', 'zip_code' => '1500',
     'notes' => '', 'subtotal' => 750, 'shipping' => 100,
     'items' => [['name' => 'Ube Roll', 'qty' => 3, 'price' => 250]]],
    ['id' => 'ORD-001', 'customer' => 'Carlo Mendoza',    'date' => 'August 14, 2026', 'updated_at' => 'August 18, 2026', 'status' => 'delivered',
     'street' => '55 Greenhills Blvd.', 'barangay' => 'Brgy. Wack-Wack', 'city' => 'Mandaluyong', 'province' => 'Metro Manila', 'zip_code' => '1554',
     'notes' => '', 'subtotal' => 490, 'shipping' => 100,
     'items' => [['name' => 'Ube Crinkles', 'qty' => 1, 'price' => 140], ['name' => 'Classic Ube Cake', 'qty' => 1, 'price' => 350]]],
];
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
                        <?php foreach ($mockOrders as $order): ?>
                        <tr class="order-row" data-status="<?php echo $order['status']; ?>"
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
    <script>
    (function() {
        'use strict';

        var ROWS_PER_PAGE = 8;
        var currentPage = 1;
        var currentFilter = 'all';
        var toast = document.getElementById('toast');
        var modal = document.getElementById('orderModal');
        var modalBody = document.getElementById('modalBody');
        var modalClose = document.getElementById('modalClose');
        var modalCloseBtn = document.getElementById('modalCloseBtn');

        function showToast(message, type) {
            if (!toast) return;
            toast.textContent = message;
            toast.className = 'toast' + (type ? ' ' + type : '');
            requestAnimationFrame(function() { toast.classList.add('show'); });
            setTimeout(function() { toast.classList.remove('show'); }, 2500);
        }

        function getFilteredRows() {
            var rows = document.querySelectorAll('.order-row');
            var filtered = [];
            rows.forEach(function(row) {
                if (currentFilter === 'all' || row.dataset.status === currentFilter) {
                    filtered.push(row);
                }
            });
            return filtered;
        }

        function renderTable() {
            var rows = getFilteredRows();
            var allRows = document.querySelectorAll('.order-row');
            var totalPages = Math.max(1, Math.ceil(rows.length / ROWS_PER_PAGE));
            if (currentPage > totalPages) currentPage = totalPages;

            var start = (currentPage - 1) * ROWS_PER_PAGE;
            var end = start + ROWS_PER_PAGE;

            allRows.forEach(function(r) { r.style.display = 'none'; });
            rows.forEach(function(r, i) {
                r.style.display = (i >= start && i < end) ? '' : 'none';
            });

            var emptyState = document.getElementById('ordersEmpty');
            var table = document.getElementById('ordersTable');
            var pagBar = document.getElementById('paginationBar');
            if (rows.length === 0) {
                emptyState.style.display = '';
                table.style.display = 'none';
                pagBar.style.display = 'none';
            } else {
                emptyState.style.display = 'none';
                table.style.display = '';
                pagBar.style.display = rows.length > ROWS_PER_PAGE ? '' : 'none';
            }

            var info = document.getElementById('paginationInfo');
            info.innerHTML = 'Showing <strong>' + (start + 1) + '&ndash;' + Math.min(end, rows.length) + '</strong> of <strong>' + rows.length + '</strong> orders';

            var links = document.getElementById('paginationLinks');
            var html = '<button class="pagination-link' + (currentPage === 1 ? ' disabled' : '') + '" data-page="' + (currentPage - 1) + '">&laquo;</button>';
            for (var p = 1; p <= totalPages; p++) {
                html += '<button class="pagination-link' + (p === currentPage ? ' current' : '') + '" data-page="' + p + '">' + p + '</button>';
            }
            html += '<button class="pagination-link' + (currentPage === totalPages ? ' disabled' : '') + '" data-page="' + (currentPage + 1) + '">&raquo;</button>';
            links.innerHTML = html;
        }

        function getActionsHtml(status) {
            var eye = '<button class="btn-icon btn-view" title="View details"><i class="fa-solid fa-eye"></i></button>';
            if (status === 'pending') {
                return eye + '<button class="btn-action btn-confirm" data-action="confirm" title="Confirm"><i class="fa-solid fa-check"></i></button><button class="btn-action btn-cancel" data-action="cancel" title="Cancel"><i class="fa-solid fa-xmark"></i></button>';
            } else if (status === 'confirmed') {
                return eye + '<button class="btn-action btn-deliver" data-action="deliver" title="Deliver"><i class="fa-solid fa-truck-fast"></i></button><button class="btn-action btn-cancel" data-action="cancel" title="Cancel"><i class="fa-solid fa-xmark"></i></button>';
            } else if (status === 'delivered') {
                return eye + '<span class="cell-muted"><i class="fa-solid fa-circle-check" style="color:#15803d;"></i> Done</span>';
            } else {
                return eye + '<span class="cell-muted"><i class="fa-solid fa-ban" style="color:#dc2626;"></i> Cancelled</span>';
            }
        }

        function openOrderModal(tr) {
            var orderId = tr.querySelector('.order-id').textContent;
            var total = tr.querySelector('.order-total').textContent;
            var date = tr.querySelectorAll('td')[2].textContent;
            var updatedAt = tr.dataset.updated || '—';
            var status = tr.querySelector('.status-badge').textContent;
            var statusClass = tr.querySelector('.status-badge').className.replace('status-badge ', '');
            var customer = tr.querySelectorAll('td')[1].textContent.trim();
            var items = JSON.parse(tr.dataset.items || '[]');
            var street = tr.dataset.street;
            var barangay = tr.dataset.barangay;
            var city = tr.dataset.city;
            var province = tr.dataset.province;
            var zip = tr.dataset.zip;
            var notes = tr.dataset.notes;
            var subtotal = parseInt(tr.dataset.subtotal, 10);
            var shipping = parseInt(tr.dataset.shipping, 10);

            var address = street + '<br>' + barangay + ', ' + city + '<br>' + province + ', ' + zip;

            var itemsRows = '';
            items.forEach(function(item) {
                var sub = item.qty * item.price;
                itemsRows +=
                    '<tr>' +
                        '<td>' + item.name + '</td>' +
                        '<td class="col-qty">' + item.qty + '</td>' +
                        '<td class="col-price">\u20B1' + item.price.toLocaleString() + '</td>' +
                        '<td class="col-sub">\u20B1' + sub.toLocaleString() + '</td>' +
                    '</tr>';
            });

            var notesHtml = notes ? '<div class="order-detail-meta"><span><strong>Notes:</strong> ' + notes + '</span></div>' : '';

            modalBody.innerHTML =
                '<div class="order-detail-status">' +
                    '<div><span class="order-detail-id">' + orderId + '</span><br><span class="order-detail-date">Placed: ' + date + ' &middot; Updated: ' + updatedAt + '</span></div>' +
                    '<span class="status-badge ' + statusClass + '">' + status + '</span>' +
                '</div>' +
                '<div class="order-detail-section">' +
                    '<h4><i class="fa-solid fa-user"></i> Customer</h4>' +
                    '<div class="order-detail-address">' + customer + '</div>' +
                '</div>' +
                '<div class="order-detail-section">' +
                    '<h4><i class="fa-solid fa-location-dot"></i> Delivery Address</h4>' +
                    '<div class="order-detail-address">' + address + '</div>' +
                '</div>' +
                '<div class="order-detail-section">' +
                    '<h4><i class="fa-solid fa-bag-shopping"></i> Items</h4>' +
                    '<table class="order-detail-items">' +
                        '<thead><tr><th>Product</th><th class="col-qty">Qty</th><th class="col-price">Price</th><th class="col-sub">Subtotal</th></tr></thead>' +
                        '<tbody>' + itemsRows + '</tbody>' +
                    '</table>' +
                '</div>' +
                '<div class="order-detail-totals">' +
                    '<div class="total-row"><span>Subtotal</span><span>\u20B1' + subtotal.toLocaleString() + '</span></div>' +
                    '<div class="total-row"><span>Shipping</span><span>' + (shipping > 0 ? '\u20B1' + shipping.toLocaleString() : 'Free') + '</span></div>' +
                    '<div class="total-row grand"><span>Total</span><span>' + total + '</span></div>' +
                '</div>' +
                notesHtml;

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Filter tabs
        document.querySelectorAll('.filter-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');
                currentFilter = this.dataset.status;
                currentPage = 1;
                renderTable();
            });
        });

        // Pagination
        document.getElementById('paginationLinks').addEventListener('click', function(e) {
            var btn = e.target.closest('.pagination-link');
            if (!btn || btn.classList.contains('disabled')) return;
            currentPage = parseInt(btn.dataset.page, 10);
            renderTable();
        });

        // Table actions (eye icon + confirm/deliver/cancel)
        document.getElementById('ordersTable').addEventListener('click', function(e) {
            var viewBtn = e.target.closest('.btn-view');
            if (viewBtn) {
                var tr = viewBtn.closest('tr');
                if (tr) openOrderModal(tr);
                return;
            }

            var btn = e.target.closest('.btn-action');
            if (!btn) return;
            var action = btn.dataset.action;
            var tr = btn.closest('tr');
            var badge = tr.querySelector('.status-badge');
            var actionsCell = tr.querySelector('.order-actions-cell');

            if (action === 'confirm') {
                badge.className = 'status-badge status-confirmed';
                badge.textContent = 'Confirmed';
                tr.dataset.status = 'confirmed';
                actionsCell.innerHTML = getActionsHtml('confirmed');
                showToast('Order confirmed!', 'success');
            } else if (action === 'deliver') {
                badge.className = 'status-badge status-delivered';
                badge.textContent = 'Delivered';
                tr.dataset.status = 'delivered';
                actionsCell.innerHTML = getActionsHtml('delivered');
                showToast('Order marked as delivered!', 'success');
            } else if (action === 'cancel') {
                badge.className = 'status-badge status-cancelled';
                badge.textContent = 'Cancelled';
                tr.dataset.status = 'cancelled';
                actionsCell.innerHTML = getActionsHtml('cancelled');
                showToast('Order cancelled.', 'error');
            }
            renderTable();
        });

        // Modal close
        if (modalClose) modalClose.addEventListener('click', closeModal);
        if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeModal();
            });
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
        });

        renderTable();
    })();
    </script>
    <script src="../../javascript/inspect.js"></script>
</body>
</html>
