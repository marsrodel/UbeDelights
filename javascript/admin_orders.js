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

    document.querySelectorAll('.filter-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            currentFilter = this.dataset.status;
            currentPage = 1;
            renderTable();
        });
    });

    document.getElementById('paginationLinks').addEventListener('click', function(e) {
        var btn = e.target.closest('.pagination-link');
        if (!btn || btn.classList.contains('disabled')) return;
        currentPage = parseInt(btn.dataset.page, 10);
        renderTable();
    });

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

        var orderId = tr.dataset.orderId;
        var newStatus = action === 'confirm' ? 'confirmed' : action === 'deliver' ? 'delivered' : 'cancelled';

        fetch('../../server/update_order_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: parseInt(orderId), status: newStatus })
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
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
            } else {
                showToast('Failed to update order', 'error');
            }
        })
        .catch(function() {
            showToast('Network error', 'error');
        });
    });

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
