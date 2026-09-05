// ============================================================
//  PENDING APPROVALS — TABLE RENDERING, PAGINATION, SEARCH
// ============================================================
(function() {
    'use strict';

    var state = { page: 1, perPage: 10, search: '' };

    function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    function filterUsers(users) {
        if (!state.search) return users;
        var s = state.search.toLowerCase();
        return users.filter(function(u) {
            return (u.username||'').toLowerCase().indexOf(s) !== -1 ||
                   (u.fullName||'').toLowerCase().indexOf(s) !== -1 ||
                   (u.email||'').toLowerCase().indexOf(s) !== -1 ||
                   (u.id||'').toLowerCase().indexOf(s) !== -1;
        });
    }

    function renderTable(rows) {
        var tbody = document.getElementById('pendingTableBody');
        var emptyState = document.getElementById('emptyPending');
        var table = document.getElementById('pendingTable');
        if (!tbody) return;
        if (rows.length === 0) {
            tbody.innerHTML = '';
            if (table) table.style.display = 'none';
            if (emptyState) emptyState.style.display = '';
            return;
        }
        if (table) table.style.display = '';
        if (emptyState) emptyState.style.display = 'none';
        var html = '';
        rows.forEach(function(u) {
            html += '<tr data-user-id="' + esc(u.id) + '">';
            html += '<td class="cell-id">' + esc(u.id) + '</td>';
            html += '<td>' + esc(u.username) + '</td>';
            html += '<td class="cell-strong">' + esc(u.fullName) + '</td>';
            html += '<td class="cell-muted">' + esc(u.email) + '</td>';
            html += '<td class="actions-cell">';
            html += '<button class="pending-action-btn btn-view" onclick="viewPendingUser(\'' + esc(u.id) + '\')" title="View Details"><i class="fa-solid fa-eye"></i></button>';
            html += '<button class="pending-action-btn btn-approve" onclick="approvePendingUser(\'' + esc(u.id) + '\')" title="Approve"><i class="fa-solid fa-check"></i></button>';
            html += '<button class="pending-action-btn btn-reject" onclick="rejectPendingUser(\'' + esc(u.id) + '\')" title="Reject"><i class="fa-solid fa-xmark"></i></button>';
            html += '</td></tr>';
        });
        tbody.innerHTML = html;
    }

    function renderPagination(total) {
        var container = document.getElementById('pendingPagination');
        if (!container) return;
        var totalPages = Math.max(1, Math.ceil(total / state.perPage));
        if (state.page > totalPages) state.page = totalPages;
        var start = (state.page - 1) * state.perPage + 1;
        var end = Math.min(state.page * state.perPage, total);

        var html = '<div class="pagination-info">Showing <strong>' + (total > 0 ? start : 0) + '</strong> to <strong>' + end + '</strong> of <strong>' + total + '</strong> entries</div>';

        html += '<div class="per-page-group"><span>Show</span><select id="pendingPerPageSelect">';
        [10, 25, 50].forEach(function(opt) {
            html += '<option value="' + opt + '"' + (state.perPage === opt ? ' selected' : '') + '>' + opt + '</option>';
        });
        html += '</select><span>per page</span></div>';

        html += '<div class="pagination">';
        if (state.page > 1) {
            html += '<a class="pagination-link" data-page="' + (state.page - 1) + '">&laquo; Prev</a>';
        } else {
            html += '<span class="pagination-link disabled">&laquo; Prev</span>';
        }

        var startPage = Math.max(1, state.page - 2);
        var endPage = Math.min(totalPages, state.page + 2);

        if (startPage > 1) {
            html += '<a class="pagination-link" data-page="1">1</a>';
            if (startPage > 2) html += '<span class="pagination-link disabled">...</span>';
        }

        for (var i = startPage; i <= endPage; i++) {
            if (i === state.page) {
                html += '<span class="pagination-link current">' + i + '</span>';
            } else {
                html += '<a class="pagination-link" data-page="' + i + '">' + i + '</a>';
            }
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += '<span class="pagination-link disabled">...</span>';
            html += '<a class="pagination-link" data-page="' + totalPages + '">' + totalPages + '</a>';
        }

        if (state.page < totalPages) {
            html += '<a class="pagination-link" data-page="' + (state.page + 1) + '">Next &raquo;</a>';
        } else {
            html += '<span class="pagination-link disabled">Next &raquo;</span>';
        }
        html += '</div>';

        container.innerHTML = html;

        container.querySelectorAll('.pagination-link[data-page]').forEach(function(link) {
            link.addEventListener('click', function() {
                state.page = parseInt(this.getAttribute('data-page')) || 1;
                render();
            });
        });

        var perPageEl = document.getElementById('pendingPerPageSelect');
        if (perPageEl) {
            perPageEl.addEventListener('change', function() {
                state.perPage = parseInt(this.value) || 10;
                state.page = 1;
                render();
            });
        }
    }

    function render() {
        var filtered = filterUsers(typeof pendingUsers !== 'undefined' ? pendingUsers : []);
        var paged = filtered.slice((state.page - 1) * state.perPage, state.page * state.perPage);
        renderTable(paged);
        renderPagination(filtered.length);
    }

    function setupSearch() {
        var searchInput = document.querySelector('.search-box input');
        var searchBtn = document.getElementById('btnSearchUsers');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                state.search = this.value.trim();
                state.page = 1;
                render();
            });
        }
        if (searchBtn) {
            searchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                state.search = searchInput ? searchInput.value.trim() : '';
                state.page = 1;
                render();
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        setupSearch();
        render();
    });
})();

// ============================================================
//  MODALS & ACTION HANDLERS
// ============================================================
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function viewPendingUser(userId) {
    var user = pendingUsers.find(function(u) { return u.id === userId; });
    if (!user) return;
    var body = document.getElementById('viewPendingBody');
    if (!body) return;
    var dob = user.dob ? new Date(user.dob).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : '';
    var sex = user.sex || '';
    var age = user.age != null ? String(user.age) : '';
    body.innerHTML =
        '<div class="um-view-form">' +
            '<div class="section-block">' +
                '<div class="section-title">Personal Information</div>' +
                '<div class="fields-row cols-4">' +
                    '<div class="form-group"><label>ID Number</label><input type="text" value="' + esc(user.id) + '" readonly></div>' +
                    '<div class="form-group"><label>First Name</label><input type="text" value="' + esc(user.firstName || '') + '" readonly></div>' +
                    '<div class="form-group"><label>Middle Name</label><input type="text" value="' + esc(user.middleName || '') + '" readonly></div>' +
                    '<div class="form-group"><label>Last Name</label><input type="text" value="' + esc(user.lastName || '') + '" readonly></div>' +
                '</div>' +
                '<div class="fields-row cols-4">' +
                    '<div class="form-group"><label>Extension Name</label><input type="text" value="' + esc(user.extensionName || '') + '" readonly></div>' +
                    '<div class="form-group"><label>Date of Birth</label><input type="text" value="' + esc(dob) + '" readonly></div>' +
                    '<div class="form-group"><label>Age</label><input type="text" value="' + esc(age) + '" readonly></div>' +
                    '<div class="form-group"><label>Sex</label><input type="text" value="' + esc(sex) + '" readonly></div>' +
                '</div>' +
            '</div>' +
            '<div class="section-block">' +
                '<div class="section-title">Account Information</div>' +
                '<div class="fields-row cols-4">' +
                    '<div class="form-group"><label>Email</label><input type="text" value="' + esc(user.email || '') + '" readonly></div>' +
                    '<div class="form-group"><label>Username</label><input type="text" value="' + esc(user.username || '') + '" readonly></div>' +
                    '<div class="form-group"><label>Role</label><input type="text" value="Customer" readonly></div>' +
                    '<div class="form-group"><label>Status</label><input type="text" value="Pending" readonly></div>' +
                '</div>' +
            '</div>' +
            '<div class="section-block">' +
                '<div class="section-title">Address Information</div>' +
                '<div class="fields-row cols-3">' +
                    '<div class="form-group"><label>Purok/Street</label><input type="text" value="' + esc(user.street || '') + '" readonly></div>' +
                    '<div class="form-group"><label>Barangay</label><input type="text" value="' + esc(user.barangay || '') + '" readonly></div>' +
                    '<div class="form-group"><label>City/Municipality</label><input type="text" value="' + esc(user.city || '') + '" readonly></div>' +
                '</div>' +
                '<div class="fields-row cols-3">' +
                    '<div class="form-group"><label>Province</label><input type="text" value="' + esc(user.province || '') + '" readonly></div>' +
                    '<div class="form-group"><label>Country</label><input type="text" value="' + esc(user.country || '') + '" readonly></div>' +
                    '<div class="form-group"><label>Zip Code</label><input type="text" value="' + esc(user.zipCode || '') + '" readonly></div>' +
                '</div>' +
            '</div>' +
        '</div>';
    document.getElementById('viewPendingModal').classList.add('active');
}

function closeViewPendingModal() {
    document.getElementById('viewPendingModal').classList.remove('active');
}

function approvePendingUser(userId) {
    var user = pendingUsers.find(function(u) { return u.id === userId; });
    if (!user) return;
    document.getElementById('approvalModalTitle').textContent = 'Approve Registration';
    document.getElementById('approvalMessage').textContent = 'Are you sure you want to approve this registration?';
    document.getElementById('approvalUserName').textContent = user.fullName;
    document.getElementById('approvalUserId').value = userId;
    document.getElementById('approvalAction').value = 'approve';
    document.getElementById('approvalConfirmBtn').style.background = 'var(--success)';
    document.getElementById('approvalConfirmBtn').innerHTML = '<i class="fa-solid fa-check"></i> <span>Approve</span>';
    document.getElementById('approvalModal').classList.add('active');
}

function rejectPendingUser(userId) {
    var user = pendingUsers.find(function(u) { return u.id === userId; });
    if (!user) return;
    document.getElementById('approvalModalTitle').textContent = 'Reject Registration';
    document.getElementById('approvalMessage').textContent = 'Are you sure you want to reject and delete this registration request?';
    document.getElementById('approvalUserName').textContent = user.fullName;
    document.getElementById('approvalUserId').value = userId;
    document.getElementById('approvalAction').value = 'reject';
    document.getElementById('approvalConfirmBtn').style.background = 'var(--danger)';
    document.getElementById('approvalConfirmBtn').innerHTML = '<i class="fa-solid fa-xmark"></i> <span>Reject</span>';
    document.getElementById('approvalModal').classList.add('active');
}

document.getElementById('approvalConfirmBtn').addEventListener('click', function() {
    var userId = document.getElementById('approvalUserId').value;
    var action = document.getElementById('approvalAction').value;
    var row = document.querySelector('tr[data-user-id="' + userId + '"]');
    if (row) row.remove();
    closeApprovalModal();
    var t = document.getElementById('toast');
    t.textContent = action === 'approve' ? 'Registration approved!' : 'Registration rejected.';
    t.className = 'toast show';
    setTimeout(function() { t.classList.remove('show'); }, 3000);
});

function closeApprovalModal() {
    document.getElementById('approvalModal').classList.remove('active');
}

document.getElementById('approvalModalCancel').addEventListener('click', closeApprovalModal);
document.getElementById('viewPendingModalClose').addEventListener('click', closeViewPendingModal);
document.getElementById('viewPendingModalCloseBtn').addEventListener('click', closeViewPendingModal);

document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.classList.remove('active');
        }
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(m) {
            m.classList.remove('active');
        });
    }
});
