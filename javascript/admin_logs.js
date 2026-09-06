(function() {
    'use strict';

    var state = {
        page: 1,
        perPage: 10,
        filters: { search: '', role: '', date_from: '', date_to: '' },
        loading: false
    };

    var searchTimer = null;

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatLogTimestamp(ts) {
        if (!ts) return '—';
        var d = new Date(ts.replace(' ', 'T'));
        if (isNaN(d)) return ts;
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var month = months[d.getMonth()];
        var day = d.getDate();
        var year = d.getFullYear();
        var hours = d.getHours();
        var ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12 || 12;
        var mins = d.getMinutes().toString().padStart(2, '0');
        return month + ' ' + day + ', ' + year + '<br><span style="font-size:0.75rem;color:var(--text-muted);">' + hours + ':' + mins + ' ' + ampm + '</span>';
    }

    function cleanLogDetails(details) {
        if (!details) return '';
        return details.replace(/\s*\|\s*Browser:.*$/i, '').trim();
    }

    function getRoleLabel(role) {
        var map = { 'super_admin': 'Super Admin', 'admin': 'Admin', 'customer': 'Customer' };
        return map[role] || role || '—';
    }

    function getRoleClass(role) {
        var map = { 'super_admin': 'role-super_admin', 'admin': 'role-admin', 'customer': 'role-customer' };
        return map[role] || 'role-customer';
    }

    function getActionLabel(action) {
        var map = {
            'login': 'Login',
            'logout': 'Logout',
            'failed_login': 'Failed Login',
            'login_blocked': 'Login Blocked',
            'CREATE_USER': 'Create User',
            'UPDATE_USER': 'Update User',
            'BLOCK_USER': 'Block User',
            'UNBLOCK_USER': 'Unblock User',
            'DELETE_USER': 'Delete User',
            'APPROVE_USER': 'Approve User',
            'REJECT_USER': 'Reject User',
            'PROFILE_UPDATE': 'Profile Update',
            'ORDER_STATUS': 'Order Status',
            'PRODUCT_ADD': 'Add Product',
            'PRODUCT_EDIT': 'Edit Product',
            'PRODUCT_DELETE': 'Delete Product',
            'DELETION_REQUEST': 'Deletion Request',
            'RESET_PASSWORD': 'Reset Password'
        };
        return map[action] || action;
    }

    function getActionClass(action) {
        var map = {
            'login': 'activity-login',
            'logout': 'activity-logout',
            'failed_login': 'activity-failed_login',
            'login_blocked': 'activity-block',
            'CREATE_USER': 'activity-create',
            'UPDATE_USER': 'activity-update',
            'PROFILE_UPDATE': 'activity-update',
            'BLOCK_USER': 'activity-block',
            'UNBLOCK_USER': 'activity-unblock',
            'DELETE_USER': 'activity-delete',
            'PRODUCT_DELETE': 'activity-delete',
            'DELETION_REQUEST': 'activity-delete',
            'APPROVE_USER': 'activity-approve',
            'REJECT_USER': 'activity-reject',
            'PRODUCT_ADD': 'activity-create',
            'PRODUCT_EDIT': 'activity-update',
            'ORDER_STATUS': 'activity-update',
            'RESET_PASSWORD': 'activity-update'
        };
        return map[action] || 'activity-login';
    }

    function buildParams() {
        var p = new URLSearchParams();
        p.set('page', state.page);
        p.set('limit', state.perPage);
        if (state.filters.search) p.set('search', state.filters.search);
        if (state.filters.role) p.set('role', state.filters.role);
        if (state.filters.date_from) p.set('date_from', state.filters.date_from);
        if (state.filters.date_to) p.set('date_to', state.filters.date_to);
        return p.toString();
    }

    function loadLogs() {
        if (state.loading) return;
        state.loading = true;

        var xhr = new XMLHttpRequest();
        xhr.open('GET', '../../server/admin_get_logs.php?' + buildParams(), true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState !== 4) return;
            state.loading = false;
            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (data.success) {
                        renderTable(data.logs);
                        renderPagination(data.pagination);
                    }
                } catch (e) {}
            }
        };
        xhr.send();
    }

    function renderTable(logs) {
        var tbody = document.getElementById('logsTableBody');
        var emptyEl = document.getElementById('emptyLogs');
        var tableEl = document.getElementById('logsTable');
        if (!tbody) return;

        if (!logs || logs.length === 0) {
            tbody.innerHTML = '';
            if (tableEl) tableEl.style.display = 'none';
            if (emptyEl) emptyEl.style.display = '';
            return;
        }

        if (tableEl) tableEl.style.display = '';
        if (emptyEl) emptyEl.style.display = 'none';

        var html = '';
        for (var i = 0; i < logs.length; i++) {
            var log = logs[i];
            var ip = log.ip_address === '::1' ? '127.0.0.1' : (log.ip_address || '—');
            var timeIn = log.time_in ? formatLogTimestamp(log.time_in) : '—';
            var timeOut = '—';
            if (log.time_out === 'active') {
                timeOut = '<span style="color:var(--success);font-weight:600;">Active</span>';
            } else if (log.time_out) {
                timeOut = formatLogTimestamp(log.time_out);
            }
            var browser = (log.browser || 'Unknown').replace(/\s+\S+$/, '').trim();

            html += '<tr>' +
                '<td>' + escapeHtml(log.idNumber || '—') + '</td>' +
                '<td>' + escapeHtml(log.fullName || log.username || '—') + '</td>' +
                '<td><span class="role-pill ' + getRoleClass(log.role) + '">' + getRoleLabel(log.role) + '</span></td>' +
                '<td><span class="activity-badge ' + getActionClass(log.action) + '">' + escapeHtml(getActionLabel(log.action)) + '</span></td>' +
                '<td>' + escapeHtml(browser + '/' + (log.os || 'Unknown')) + '</td>' +
                '<td>' + timeIn + '</td>' +
                '<td>' + timeOut + '</td>' +
                '<td><code class="ip-badge">' + escapeHtml(ip) + '</code></td>' +
                '<td>' + escapeHtml(cleanLogDetails(log.details) || '—') + '</td>' +
                '</tr>';
        }
        tbody.innerHTML = html;
    }

    function renderPagination(pagination) {
        var container = document.getElementById('logsPaginationContainer');
        if (!container || !pagination) return;

        var page = pagination.page;
        var totalPages = pagination.totalPages;
        var total = pagination.total;
        var perPage = pagination.limit;
        var start = (page - 1) * perPage + 1;
        var end = Math.min(page * perPage, total);

        var html = '<div class="pagination-info">Showing <strong>' + (total > 0 ? start : 0) + '</strong> to <strong>' + end + '</strong> of <strong>' + total + '</strong> entries</div>';

        html += '<div class="per-page-group"><span>Show</span><select id="perPageSelect">';
        [10, 25, 50].forEach(function(opt) {
            html += '<option value="' + opt + '"' + (perPage === opt ? ' selected' : '') + '>' + opt + '</option>';
        });
        html += '</select><span>per page</span></div>';

        html += '<div class="pagination">';
        if (page > 1) {
            html += '<a class="pagination-link" data-page="' + (page - 1) + '">&laquo; Prev</a>';
        } else {
            html += '<span class="pagination-link disabled">&laquo; Prev</span>';
        }

        var startPage = Math.max(1, page - 2);
        var endPage = Math.min(totalPages, page + 2);

        if (startPage > 1) {
            html += '<a class="pagination-link" data-page="1">1</a>';
            if (startPage > 2) html += '<span class="pagination-link disabled">...</span>';
        }

        for (var i = startPage; i <= endPage; i++) {
            if (i === page) {
                html += '<span class="pagination-link current">' + i + '</span>';
            } else {
                html += '<a class="pagination-link" data-page="' + i + '">' + i + '</a>';
            }
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += '<span class="pagination-link disabled">...</span>';
            html += '<a class="pagination-link" data-page="' + totalPages + '">' + totalPages + '</a>';
        }

        if (page < totalPages) {
            html += '<a class="pagination-link" data-page="' + (page + 1) + '">Next &raquo;</a>';
        } else {
            html += '<span class="pagination-link disabled">Next &raquo;</span>';
        }
        html += '</div>';

        container.innerHTML = html;

        var perPageSelect = document.getElementById('perPageSelect');
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                state.perPage = parseInt(this.value);
                state.page = 1;
                loadLogs();
            });
        }

        container.querySelectorAll('.pagination-link[data-page]').forEach(function(link) {
            link.addEventListener('click', function() {
                state.page = parseInt(this.getAttribute('data-page'));
                loadLogs();
            });
        });
    }

    function collectFilters() {
        state.filters = {
            search: (document.getElementById('logsSearch') || {}).value ? document.getElementById('logsSearch').value.trim() : '',
            role: (document.getElementById('logsRoleFilter') || {}).value || '',
            date_from: (document.getElementById('logsFromDate') || {}).value || '',
            date_to: (document.getElementById('logsToDate') || {}).value || ''
        };
        state.page = 1;
        loadLogs();
    }

    function clearFilters() {
        var s = document.getElementById('logsSearch');
        var r = document.getElementById('logsRoleFilter');
        var fd = document.getElementById('logsFromDate');
        var td = document.getElementById('logsToDate');
        if (s) s.value = '';
        if (r) r.value = '';
        if (fd) fd.value = '';
        if (td) td.value = '';
        state.filters = { search: '', role: '', date_from: '', date_to: '' };
        state.page = 1;
        loadLogs();
    }

    function bindEvents() {
        var searchInput = document.getElementById('logsSearch');
        var btnApply = document.getElementById('btnApplyLogsFilter');
        var btnClear = document.getElementById('btnClearLogsFilter');
        var roleFilter = document.getElementById('logsRoleFilter');
        var fromDate = document.getElementById('logsFromDate');
        var toDate = document.getElementById('logsToDate');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(function() {
                    state.filters.search = searchInput.value.trim();
                    state.page = 1;
                    loadLogs();
                }, 350);
            });
        }

        if (btnApply) btnApply.addEventListener('click', collectFilters);
        if (btnClear) btnClear.addEventListener('click', clearFilters);

        [roleFilter, fromDate, toDate].forEach(function(el) {
            if (el) el.addEventListener('change', function() {
                collectFilters();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        bindEvents();
        loadLogs();
    });
})();
