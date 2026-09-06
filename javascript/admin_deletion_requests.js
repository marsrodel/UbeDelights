// ============================================================
//  DELETION REQUESTS — TABLE RENDERING, PAGINATION
// ============================================================
(function() {
    'use strict';

    var state = { page: 1, perPage: 10 };

    function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    function statusBadge(status) {
        var cls = 'status-' + status;
        var label = status.charAt(0).toUpperCase() + status.slice(1);
        return '<span class="status-badge ' + cls + '">' + label + '</span>';
    }

    function roleLabel(role) {
        if (role === 'super_admin') return 'Super Admin';
        if (role === 'admin') return 'Admin';
        return 'Customer';
    }

    function truncate(str, max) {
        str = str || '';
        return str.length > max ? esc(str.substring(0, max)) + '…' : esc(str);
    }

    function formatDate(dateStr) {
        if (!dateStr) return '—';
        var d = new Date(dateStr);
        if (isNaN(d.getTime())) return esc(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) +
               ' ' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }

    function renderTable(rows) {
        var tbody = document.getElementById('drTableBody');
        var emptyState = document.getElementById('emptyDr');
        var table = document.getElementById('drTable');
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
        rows.forEach(function(r) {
            html += '<tr data-dr-id="' + esc(r.id) + '">';
            html += '<td><strong>' + esc(r.targetName) + '</strong><br><span class="cell-muted" style="font-size:0.82rem;">' + esc(r.targetId) + ' · ' + roleLabel(r.targetRole) + ' · ' + esc(r.targetUsername) + '</span></td>';
            html += '<td class="cell-muted" title="' + esc(r.reason) + '">' + truncate(r.reason, 40) + '</td>';
            html += '<td>' + esc(r.requestedBy) + '</td>';
            html += '<td class="cell-muted">' + formatDate(r.createdAt) + '</td>';
            html += '<td>' + statusBadge(r.status) + '</td>';
            html += '<td class="actions-cell">';
            if (r.status === 'pending') {
                html += '<button class="pending-action-btn btn-approve" data-action="approve-dr" data-id="' + esc(r.id) + '" title="Approve"><i class="fa-solid fa-check"></i></button> ';
                html += '<button class="pending-action-btn btn-reject" data-action="reject-dr" data-id="' + esc(r.id) + '" title="Reject"><i class="fa-solid fa-xmark"></i></button>';
            }
            html += '</td></tr>';
        });
        tbody.innerHTML = html;
    }

    function renderPagination(total) {
        var container = document.getElementById('drPagination');
        if (!container) return;
        var totalPages = Math.max(1, Math.ceil(total / state.perPage));
        if (state.page > totalPages) state.page = totalPages;
        var start = (state.page - 1) * state.perPage + 1;
        var end = Math.min(state.page * state.perPage, total);

        var html = '<div class="pagination-info">Showing <strong>' + (total > 0 ? start : 0) + '</strong> to <strong>' + end + '</strong> of <strong>' + total + '</strong> entries</div>';

        html += '<div class="per-page-group"><span>Show</span><select id="drPerPageSelect">';
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

        var perPageEl = document.getElementById('drPerPageSelect');
        if (perPageEl) {
            perPageEl.addEventListener('change', function() {
                state.perPage = parseInt(this.value) || 10;
                state.page = 1;
                render();
            });
        }
    }

    function render() {
        var all = typeof deletionRequests !== 'undefined' ? deletionRequests : [];
        var paged = all.slice((state.page - 1) * state.perPage, state.page * state.perPage);
        renderTable(paged);
        renderPagination(all.length);
    }

    document.addEventListener('DOMContentLoaded', function() {
        render();
    });
})();

// ============================================================
//  MODALS & ACTION HANDLERS
// ============================================================
function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function openApproveDr(id) {
    var req = deletionRequests.find(function(r) { return String(r.id) === String(id); });
    if (!req) return;
    document.getElementById('approveDrUserName').querySelector('strong').textContent = req.targetName + ' (' + req.targetUsername + ')';
    document.getElementById('approveDrId').value = id;
    document.getElementById('approveDrModal').classList.add('active');
}

function openRejectDr(id) {
    var req = deletionRequests.find(function(r) { return String(r.id) === String(id); });
    if (!req) return;
    document.getElementById('rejectDrUserName').querySelector('strong').textContent = req.targetName + ' (' + req.targetUsername + ')';
    document.getElementById('rejectDrId').value = id;
    document.getElementById('rejectDrModal').classList.add('active');
}

function closeModal(modalId) {
    var modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

function reviewDeletionRequest(requestId, action) {
    var fd = new FormData();
    fd.append('request_id', requestId);
    fd.append('action', action);

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../../server/review_deletion.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            var res;
            try { res = JSON.parse(xhr.responseText); } catch(e) { res = {}; }
            if (res.success) {
                closeModal(action === 'approve' ? 'approveDrModal' : 'rejectDrModal');
                var toast = document.getElementById('toast');
                if (toast) {
                    toast.textContent = res.message || 'Request updated.';
                    toast.className = 'toast show';
                    setTimeout(function() { toast.classList.remove('show'); }, 3000);
                }
                location.reload();
            } else {
                alert(res.message || 'Action failed.');
            }
        } else {
            alert('Server error. Please try again.');
        }
    };
    xhr.onerror = function() { alert('Network error. Please try again.'); };
    xhr.send(fd);
}

document.addEventListener('DOMContentLoaded', function() {
    var tbody = document.getElementById('drTableBody');
    if (!tbody) return;

    tbody.addEventListener('click', function(e) {
        var approveBtn = e.target.closest('[data-action="approve-dr"]');
        if (approveBtn) { openApproveDr(approveBtn.getAttribute('data-id')); return; }

        var rejectBtn = e.target.closest('[data-action="reject-dr"]');
        if (rejectBtn) { openRejectDr(rejectBtn.getAttribute('data-id')); return; }
    });

    document.getElementById('approveDrConfirmBtn').addEventListener('click', function() {
        var id = document.getElementById('approveDrId').value;
        if (id) reviewDeletionRequest(id, 'approve');
    });

    document.getElementById('rejectDrConfirmBtn').addEventListener('click', function() {
        var id = document.getElementById('rejectDrId').value;
        if (id) reviewDeletionRequest(id, 'reject');
    });
});

document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(m) {
            m.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});
