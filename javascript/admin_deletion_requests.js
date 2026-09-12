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
            html += '<td><strong>' + esc(r.targetName) + '</strong></td>';
            html += '<td class="cell-muted" title="' + esc(r.reason) + '">' + truncate(r.reason, 40) + '</td>';
            html += '<td>' + esc(r.requestedBy) + '</td>';
            html += '<td class="cell-muted">' + formatDate(r.createdAt) + '</td>';
            html += '<td>' + statusBadge(r.status) + '</td>';
            html += '<td class="actions-cell">';
            html += '<button class="pending-action-btn btn-view" onclick="viewDeletionRequest(\'' + esc(r.id) + '\')" title="View Details"><i class="fa-solid fa-eye"></i></button> ';
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

var pendingDrAction = '';
var pendingDrRequestId = '';

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

function viewDeletionRequest(requestId) {
    var req = deletionRequests.find(function(r) { return String(r.id) === String(requestId); });
    if (!req) return;
    var body = document.getElementById('viewDrBody');
    if (!body) return;
    var dob = (req.targetDob && req.targetDob !== '0000-00-00') ? new Date(req.targetDob).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : '';
    var roleLabel = req.targetRole === 'super_admin' ? 'Super Admin' : req.targetRole === 'admin' ? 'Admin' : 'Customer';
    var statusLabel = req.status.charAt(0).toUpperCase() + req.status.slice(1);
    body.innerHTML =
        '<div class="um-view-form">' +
            '<div class="section-block">' +
                '<div class="section-title">Personal Information</div>' +
                '<div class="fields-row cols-4">' +
                    '<div class="form-group"><label>ID Number</label><input type="text" value="' + esc(req.targetId) + '" readonly></div>' +
                    '<div class="form-group"><label>First Name</label><input type="text" value="' + esc(req.targetFirstName || '') + '" readonly></div>' +
                    '<div class="form-group"><label>Middle Name</label><input type="text" value="' + esc(req.targetMiddleName || '') + '" readonly></div>' +
                    '<div class="form-group"><label>Last Name</label><input type="text" value="' + esc(req.targetLastName || '') + '" readonly></div>' +
                '</div>' +
                '<div class="fields-row cols-4">' +
                    '<div class="form-group"><label>Extension Name</label><input type="text" value="' + esc(req.targetExtension || '') + '" readonly></div>' +
                    '<div class="form-group"><label>Date of Birth</label><input type="text" value="' + esc(dob) + '" readonly></div>' +
                    '<div class="form-group"><label>Age</label><input type="text" value="' + esc(req.targetAge != null ? String(req.targetAge) : '') + '" readonly></div>' +
                    '<div class="form-group"><label>Sex</label><input type="text" value="' + esc(req.targetSex || '') + '" readonly></div>' +
                '</div>' +
            '</div>' +
            '<div class="section-block">' +
                '<div class="section-title">Account Information</div>' +
                '<div class="fields-row cols-4">' +
                    '<div class="form-group"><label>Email</label><input type="text" value="' + esc(req.targetEmail || '') + '" readonly></div>' +
                    '<div class="form-group"><label>Username</label><input type="text" value="' + esc(req.targetUsername) + '" readonly></div>' +
                    '<div class="form-group"><label>Role</label><input type="text" value="' + esc(roleLabel) + '" readonly></div>' +
                    '<div class="form-group"><label>Status</label><input type="text" value="' + esc(statusLabel) + '" readonly></div>' +
                '</div>' +
            '</div>' +
            '<div class="section-block">' +
                '<div class="section-title">Address Information</div>' +
                '<div class="fields-row cols-3">' +
                    '<div class="form-group"><label>Purok/Street</label><input type="text" value="' + esc(req.targetStreet || '') + '" readonly></div>' +
                    '<div class="form-group"><label>Barangay</label><input type="text" value="' + esc(req.targetBarangay || '') + '" readonly></div>' +
                    '<div class="form-group"><label>City/Municipality</label><input type="text" value="' + esc(req.targetCity || '') + '" readonly></div>' +
                '</div>' +
                '<div class="fields-row cols-3">' +
                    '<div class="form-group"><label>Province</label><input type="text" value="' + esc(req.targetProvince || '') + '" readonly></div>' +
                    '<div class="form-group"><label>Country</label><input type="text" value="' + esc(req.targetCountry || '') + '" readonly></div>' +
                    '<div class="form-group"><label>Zip Code</label><input type="text" value="' + esc(req.targetZipcode || '') + '" readonly></div>' +
                '</div>' +
            '</div>' +
        '</div>';
    document.getElementById('viewDrModal').classList.add('active');
}

function closeViewDrModal() {
    document.getElementById('viewDrModal').classList.remove('active');
    document.body.style.overflow = '';
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
                document.getElementById('drSuccessMessage').textContent = res.message || 'Request updated successfully.';
                document.getElementById('drSuccessModal').classList.add('active');
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

// Password lockout
var drPasswordAttempts = 0;
var drPasswordMaxAttempts = 3;
var drPasswordLocked = false;

function resetDrPasswordLockout() {
    drPasswordAttempts = 0;
    drPasswordLocked = false;
    var input = document.getElementById('drPasswordInput');
    var error = document.getElementById('drPasswordError');
    var btn = document.getElementById('drPasswordConfirmBtn');
    if (input) { input.disabled = false; input.value = ''; input.style.cursor = ''; }
    if (error) error.textContent = '';
    if (btn) { btn.disabled = false; btn.style.opacity = ''; btn.style.cursor = ''; }
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

    // Approve confirm → open password modal
    document.getElementById('approveDrConfirmBtn').addEventListener('click', function() {
        var id = document.getElementById('approveDrId').value;
        if (!id) return;
        closeModal('approveDrModal');
        pendingDrAction = 'approve';
        pendingDrRequestId = id;
        document.getElementById('drPasswordTitle').textContent = 'Enter your password to delete this account.';
        document.getElementById('drPasswordAction').value = 'approve';
        document.getElementById('drPasswordRequestId').value = id;
        resetDrPasswordLockout();
        document.getElementById('drPasswordModal').classList.add('active');
    });

    // Reject confirm → open password modal
    document.getElementById('rejectDrConfirmBtn').addEventListener('click', function() {
        var id = document.getElementById('rejectDrId').value;
        if (!id) return;
        closeModal('rejectDrModal');
        pendingDrAction = 'reject';
        pendingDrRequestId = id;
        document.getElementById('drPasswordTitle').textContent = 'Enter your password to reject this deletion request.';
        document.getElementById('drPasswordAction').value = 'reject';
        document.getElementById('drPasswordRequestId').value = id;
        resetDrPasswordLockout();
        document.getElementById('drPasswordModal').classList.add('active');
    });

    // Password confirm → verify password → process action
    var drPasswordConfirmBtn = document.getElementById('drPasswordConfirmBtn');
    var drPasswordInput = document.getElementById('drPasswordInput');
    var drPasswordError = document.getElementById('drPasswordError');

    if (drPasswordConfirmBtn) {
        drPasswordConfirmBtn.addEventListener('click', function() {
            if (drPasswordLocked) {
                drPasswordError.textContent = 'Too many failed attempts. Action is locked.';
                return;
            }

            var pwd = drPasswordInput.value.trim();
            if (!pwd) {
                drPasswordError.textContent = 'Password is required.';
                return;
            }

            drPasswordConfirmBtn.disabled = true;
            drPasswordConfirmBtn.style.opacity = '0.5';
            drPasswordConfirmBtn.style.cursor = 'not-allowed';

            var fd = new FormData();
            fd.append('password', pwd);

            var xhr = new XMLHttpRequest();
            xhr.open('POST', '../../server/verify_admin_password.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    var res;
                    try { res = JSON.parse(xhr.responseText); } catch(e) { res = {}; }

                    if (res.success) {
                        var action = document.getElementById('drPasswordAction').value;
                        var requestId = document.getElementById('drPasswordRequestId').value;
                        closeModal('drPasswordModal');
                        reviewDeletionRequest(requestId, action);
                    } else {
                        drPasswordAttempts++;
                        if (drPasswordAttempts >= drPasswordMaxAttempts) {
                            drPasswordLocked = true;
                            drPasswordError.textContent = 'Too many failed attempts. Action is locked.';
                            drPasswordInput.disabled = true;
                            drPasswordInput.style.cursor = 'not-allowed';
                        } else {
                            drPasswordError.textContent = 'Incorrect Password. Attempt ' + drPasswordAttempts + ' of ' + drPasswordMaxAttempts + '.';
                            drPasswordInput.value = '';
                            drPasswordInput.focus();
                        }
                        drPasswordConfirmBtn.disabled = false;
                        drPasswordConfirmBtn.style.opacity = '';
                        drPasswordConfirmBtn.style.cursor = '';
                    }
                } else {
                    drPasswordError.textContent = 'Server error. Please try again.';
                    drPasswordConfirmBtn.disabled = false;
                    drPasswordConfirmBtn.style.opacity = '';
                    drPasswordConfirmBtn.style.cursor = '';
                }
            };
            xhr.onerror = function() {
                drPasswordError.textContent = 'Network error. Please try again.';
                drPasswordConfirmBtn.disabled = false;
                drPasswordConfirmBtn.style.opacity = '';
                drPasswordConfirmBtn.style.cursor = '';
            };
            xhr.send(fd);
        });
    }

    // Success modal OK → reload
    var drSuccessOkBtn = document.getElementById('drSuccessOkBtn');
    if (drSuccessOkBtn) {
        drSuccessOkBtn.addEventListener('click', function() {
            closeModal('drSuccessModal');
            location.reload();
        });
    }

    // View modal close
    var viewDrClose = document.getElementById('viewDrModalClose');
    var viewDrCloseBtn = document.getElementById('viewDrModalCloseBtn');
    if (viewDrClose) viewDrClose.addEventListener('click', closeViewDrModal);
    if (viewDrCloseBtn) viewDrCloseBtn.addEventListener('click', closeViewDrModal);
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
