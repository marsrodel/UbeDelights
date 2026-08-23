/**
 * Admin Logs JavaScript
 * Handles client-side filtering, pagination, and display for system logs
 */

(function() {
    'use strict';

    var state = {
        page: 1,
        perPage: 10,
        filters: { search: '', role: '', from_date: '', to_date: '' }
    };

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        var d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function formatTime(dateStr) {
        if (!dateStr) return '-';
        var d = new Date(dateStr);
        return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    }

    function getActivityInfo(action) {
        var map = {
            'LOGIN':         { cls: 'activity-login', icon: 'fa-right-to-bracket', label: 'Login' },
            'LOGOUT':        { cls: 'activity-logout', icon: 'fa-right-from-bracket', label: 'Logout' },
            'FAILED_LOGIN':  { cls: 'activity-failed_login', icon: 'fa-triangle-exclamation', label: 'Failed Login' },
            'CREATE_USER':   { cls: 'activity-create', icon: 'fa-user-plus', label: 'Create User' },
            'UPDATE_USER':   { cls: 'activity-update', icon: 'fa-user-pen', label: 'Update User' },
            'BLOCK_USER':    { cls: 'activity-block', icon: 'fa-user-slash', label: 'Block User' },
            'UNBLOCK_USER':  { cls: 'activity-unblock', icon: 'fa-user-check', label: 'Unblock User' },
            'DELETE_USER':   { cls: 'activity-delete', icon: 'fa-trash-can', label: 'Delete User' },
            'APPROVE_USER':  { cls: 'activity-approve', icon: 'fa-user-check', label: 'Approve User' },
            'REJECT_USER':   { cls: 'activity-reject', icon: 'fa-user-xmark', label: 'Reject User' }
        };
        return map[action] || { cls: 'activity-login', icon: 'fa-circle-info', label: action };
    }

    function getRoleClass(role) {
        var map = { 'admin': 'role-admin', 'super_admin': 'role-super_admin', 'customer': 'role-customer' };
        return map[role] || 'role-customer';
    }

    function getRoleLabel(role) {
        var map = { 'admin': 'Admin', 'super_admin': 'Super Admin', 'customer': 'Customer' };
        return map[role] || role;
    }

    function filterLogs(logs) {
        var f = state.filters;
        return logs.filter(function(log) {
            if (f.search) {
                var s = f.search.toLowerCase();
                var name = (log.user_name || '').toLowerCase();
                var id = (log.idNo || '').toLowerCase();
                if (name.indexOf(s) === -1 && id.indexOf(s) === -1) return false;
            }
            if (f.role && log.user_role !== f.role) return false;
            if (f.from_date) {
                var logDate = log.created_at ? log.created_at.split(' ')[0] : '';
                if (logDate < f.from_date) return false;
            }
            if (f.to_date) {
                var logDate2 = log.created_at ? log.created_at.split(' ')[0] : '';
                if (logDate2 > f.to_date) return false;
            }
            return true;
        });
    }

    function renderTable(logs) {
        var tbody = document.getElementById('logsTableBody');
        var emptyEl = document.getElementById('emptyLogs');
        if (!tbody) return;

        if (logs.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted);">No logs found</td></tr>';
            if (emptyEl) emptyEl.style.display = '';
            return;
        }
        if (emptyEl) emptyEl.style.display = 'none';

        var html = '';
        for (var i = 0; i < logs.length; i++) {
            var log = logs[i];
            var act = getActivityInfo(log.action);
            var roleClass = getRoleClass(log.user_role);
            var roleLabel = getRoleLabel(log.user_role);
            var ip = log.ip_address === '::1' ? '127.0.0.1' : (log.ip_address || 'N/A');

            html += '<tr>' +
                '<td><div class="log-user-cell">' + escapeHtml(log.user_name || 'System') + '</div><div class="log-id-cell">ID: ' + escapeHtml(log.idNo || 'N/A') + '</div></td>' +
                '<td><span class="role-pill ' + roleClass + '">' + roleLabel + '</span></td>' +
                '<td style="font-size:13px; color:var(--text-secondary);">' + formatDate(log.created_at) + '</td>' +
                '<td style="font-size:13px; color:var(--text-secondary);">' + formatTime(log.created_at) + '</td>' +
                '<td class="device-cell"><span style="font-weight:600;">' + escapeHtml(log.device || 'Unknown') + '</span> / ' + escapeHtml(log.browser || 'Unknown') + '</td>' +
                '<td><code class="ip-badge">' + escapeHtml(ip) + '</code></td>' +
                '<td><span class="activity-badge ' + act.cls + '"><i class="fas ' + act.icon + '" style="font-size:10px;"></i> ' + act.label + '</span>' +
                (log.description ? '<div class="activity-desc">' + escapeHtml(log.description) + '</div>' : '') +
                '</td></tr>';
        }
        tbody.innerHTML = html;
    }

    function renderPagination(total) {
        var container = document.getElementById('logsPaginationContainer');
        if (!container) return;

        var page = state.page;
        var perPage = state.perPage;
        var totalPages = Math.ceil(total / perPage);
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

        document.getElementById('perPageSelect').addEventListener('change', function() {
            state.perPage = parseInt(this.value);
            state.page = 1;
            render(allLogs);
        });

        container.querySelectorAll('.pagination-link[data-page]').forEach(function(link) {
            link.addEventListener('click', function() {
                state.page = parseInt(this.getAttribute('data-page'));
                render(allLogs);
            });
        });
    }

    function render(logs) {
        var filtered = filterLogs(logs);
        var start = (state.page - 1) * state.perPage;
        var paged = filtered.slice(start, start + state.perPage);
        renderTable(paged);
        renderPagination(filtered.length);

        var countEl = document.getElementById('logsTotalCount');
        if (countEl) countEl.textContent = '(' + filtered.length + ' total)';
    }

    window.initSystemLogs = function(logs) {
        allLogs = logs;

        var searchInput = document.getElementById('logsSearch');
        var roleFilter = document.getElementById('logsRoleFilter');
        var fromDate = document.getElementById('logsFromDate');
        var toDate = document.getElementById('logsToDate');
        var btnApply = document.getElementById('btnApplyLogsFilter');
        var btnClear = document.getElementById('btnClearLogsFilter');

        if (btnApply) btnApply.addEventListener('click', function() {
            state.filters = {
                search: searchInput ? searchInput.value.trim() : '',
                role: roleFilter ? roleFilter.value : '',
                from_date: fromDate ? fromDate.value : '',
                to_date: toDate ? toDate.value : ''
            };
            state.page = 1;
            render(allLogs);
        });

        if (btnClear) btnClear.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (roleFilter) roleFilter.value = '';
            if (fromDate) fromDate.value = '';
            if (toDate) toDate.value = '';
            state.filters = { search: '', role: '', from_date: '', to_date: '' };
            state.page = 1;
            render(allLogs);
        });

        [searchInput, roleFilter, fromDate, toDate].forEach(function(el) {
            if (el) el.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    state.filters = {
                        search: searchInput ? searchInput.value.trim() : '',
                        role: roleFilter ? roleFilter.value : '',
                        from_date: fromDate ? fromDate.value : '',
                        to_date: toDate ? toDate.value : ''
                    };
                    state.page = 1;
                    render(allLogs);
                }
            });
        });

        render(allLogs);
    };
})();
