/**
 * Admin Logs JavaScript
 * Handles logs filtering, pagination, and display for system logs
 */

(function() {
    'use strict';

    // ==========================================
    // COMMON UTILITIES
    // ==========================================
    function showToast(message, type) {
        var toast = document.getElementById('toast');
        if (!toast) return;
        toast.textContent = message;
        toast.className = 'toast' + (type ? ' ' + type : '');
        requestAnimationFrame(function() { toast.classList.add('show'); });
        setTimeout(function() { toast.classList.remove('show'); }, 3000);
    }

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&')
            .replace(/</g, '<')
            .replace(/>/g, '>')
            .replace(/"/g, '"')
            .replace(/'/g, '&#039;');
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        var d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function formatTime(dateStr) {
        if (!dateStr) return '-';
        var d = new Date(dateStr);
        return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    }

    function getActionBadgeClass(action) {
        var classes = {
            'LOGIN': 'action-create',
            'LOGOUT': 'action-delete',
            'CREATE_USER': 'action-create',
            'UPDATE_USER': 'action-update',
            'BLOCK_USER': 'action-block',
            'UNBLOCK_USER': 'action-unblock',
            'DELETE_USER': 'action-delete',
            'APPROVE_USER': 'action-approve',
            'REJECT_USER': 'action-reject',
            'FAILED_LOGIN': 'action-failed_login'
        };
        return classes[action] || 'action-create';
    }

    function getActionLabel(action) {
        var labels = {
            'LOGIN': 'Login',
            'LOGOUT': 'Logout',
            'CREATE_USER': 'Create User',
            'UPDATE_USER': 'Update User',
            'BLOCK_USER': 'Block User',
            'UNBLOCK_USER': 'Unblock User',
            'DELETE_USER': 'Delete User',
            'APPROVE_USER': 'Approve User',
            'REJECT_USER': 'Reject User',
            'FAILED_LOGIN': 'Failed Login'
        };
        return labels[action] || action;
    }

    function formatDeviceBrowser(device, browser) {
        var os_name = device || 'Unknown';
        if (os_name.indexOf('Device: ') === 0) os_name = os_name.substr(8);
        os_name = os_name.replace(/ PC| Computer/, '');
        if (os_name === 'Localhost Development' || os_name === 'Localhost') os_name = 'Windows';
        
        var browser_raw = browser || 'Unknown';
        var browser_name = browser_raw.replace(/\s+[\d\.]+$/, '');
        browser_name = browser_name.replace(/Google |Mozilla |Microsoft /, '');
        if (!browser_name || browser_name === 'Unknown') browser_name = 'Browser';
        
        return '<span style="font-weight:600;">' + escapeHtml(os_name) + '</span> <span style="color:#d1d5db;">/</span> ' + escapeHtml(browser_name);
    }

    // ==========================================
    // SYSTEM LOGS
    // ==========================================
    var logsState = {
        page: 1,
        limit: 10,
        filters: {
            search: '',
            action: '',
            user: '',
            role: '',
            from_date: '',
            to_date: ''
        }
    };

    function initSystemLogs() {
        var logsTableBody = document.getElementById('logsTableBody');
        var logsPagination = document.getElementById('logsPaginationContainer');
        var logsEmpty = document.getElementById('emptyLogs');
        var logsTotalCount = document.getElementById('logsTotalCount');
        var statsElements = {
            total: document.getElementById('statTotalLogs'),
            create: document.getElementById('statCreateLogs'),
            update: document.getElementById('statUpdateLogs'),
            delete: document.getElementById('statDeleteLogs')
        };

        // Filter elements
        var searchInput = document.getElementById('logsSearch');
        var actionFilter = document.getElementById('logsActionFilter');
        var roleFilter = document.getElementById('logsRoleFilter');
        var fromDate = document.getElementById('logsFromDate');
        var toDate = document.getElementById('logsToDate');
        var btnApply = document.getElementById('btnApplyLogsFilter');
        var btnClear = document.getElementById('btnClearLogsFilter');

        // Load initial data
        loadSystemLogs();

        // Event listeners
        if (btnApply) btnApply.addEventListener('click', function() {
            logsState.page = 1;
            applyFilters();
        });

        if (btnClear) btnClear.addEventListener('click', function() {
            clearFilters();
        });

        [searchInput, actionFilter, roleFilter, fromDate, toDate].forEach(function(el) {
            if (el) el.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    logsState.page = 1;
                    applyFilters();
                }
            });
        });

        function applyFilters() {
            logsState.filters = {
                search: searchInput ? searchInput.value.trim() : '',
                action: actionFilter ? actionFilter.value : '',
                role: roleFilter ? roleFilter.value : '',
                from_date: fromDate ? fromDate.value : '',
                to_date: toDate ? toDate.value : ''
            };
            logsState.page = 1;
            loadSystemLogs();
        }

        function clearFilters() {
            if (searchInput) searchInput.value = '';
            if (actionFilter) actionFilter.value = '';
            if (roleFilter) roleFilter.value = '';
            if (fromDate) fromDate.value = '';
            if (toDate) toDate.value = '';
            logsState.filters = { search: '', action: '', role: '', from_date: '', to_date: '' };
            logsState.page = 1;
            loadSystemLogs();
        }

        function loadSystemLogs() {
            var data = new FormData();
            data.append('action', 'get_logs');
            data.append('page', logsState.page);
            data.append('limit', logsState.limit);
            data.append('search', logsState.filters.search);
            data.append('action_filter', logsState.filters.action);
            data.append('role_filter', logsState.filters.role);
            data.append('from_date', logsState.filters.from_date);
            data.append('to_date', logsState.filters.to_date);

            fetch('../../server/user_logs.php', {
                method: 'POST',
                body: data
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    renderLogsTable(data.logs);
                    renderPagination(data.pagination);
                    updateStats(data.logs);
                    if (logsEmpty) logsEmpty.style.display = data.logs.length === 0 ? '' : 'none';
                    if (logsTotalCount) logsTotalCount.textContent = '(' + data.pagination.total_logs + ' total)';
                } else {
                    showToast(data.message || 'Failed to load logs', 'error');
                }
            })
            .catch(function() { showToast('Failed to load logs', 'error'); });
        }

        function renderLogsTable(logs) {
            if (!logsTableBody) return;
            
            if (logs.length === 0) {
                logsTableBody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:40px; color:#6b7280;">No logs found</td></tr>';
                return;
            }

            var html = '';
            for (var i = 0; i < logs.length; i++) {
                var log = logs[i];
                var badgeClass = getActionBadgeClass(log.action);
                var actionLabel = getActionLabel(log.action);
                
                // Format device/browser
                var deviceInfo = formatDeviceBrowser(log.device, log.browser);
                
                // Format IP
                var ip = log.ip_address || 'N/A';
                if (ip === '::1') ip = '127.0.0.1';
                
                html += '<tr>' +
                    '<td>' +
                        '<div style="font-weight: 700; color: var(--accent);">' + escapeHtml(log.user_name || 'System') + '</div>' +
                        '<div style="font-size: 11px; color: var(--text-muted);">ID: ' + escapeHtml(log.idNo || 'N/A') + '</div>' +
                    '</td>' +
                    '<td>' +
                        (log.user_role ? '<span class="role-badge role-' + escapeHtml(log.user_role) + '" style="font-size: 11px; padding: 2px 8px;">' + escapeHtml(log.user_role) + '</span>' : 
                         '<span style="color: var(--text-muted); font-size: 12px;">System</span>') +
                    '</td>' +
                    '<td>' +
                        '<span class="action-badge ' + badgeClass + '">' + actionLabel + '</span>' +
                    '</td>' +
                    '<td style="font-size: 13px; color: var(--text-secondary); max-width: 300px;">' + escapeHtml(log.description || '') + '</td>' +
                    '<td style="color: var(--text-secondary); font-size: 13px;">' + deviceInfo + '</td>' +
                    '<td>' +
                        '<code style="background: #f3f4f6; padding: 2px 6px; border-radius: 4px; font-size: 12px; color: var(--text-secondary);">' + escapeHtml(ip) + '</code>' +
                    '</td>' +
                    '<td style="font-size: 13px;">' + formatDate(log.created_at) + '<div style="font-size: 11px; color: var(--text-muted);">' + formatTime(log.created_at) + '</div></td>' +
                '</tr>';
            }
            logsTableBody.innerHTML = html;
        }

        function renderPagination(pagination) {
            if (!logsPagination) return;
            
            var page = pagination.current_page;
            var total = pagination.total_pages;
            var baseParams = 'page={page}&search=' + encodeURIComponent(logsState.filters.search) +
                '&action_filter=' + encodeURIComponent(logsState.filters.action) +
                '&role_filter=' + encodeURIComponent(logsState.filters.role) +
                '&from_date=' + encodeURIComponent(logsState.filters.from_date) +
                '&to_date=' + encodeURIComponent(logsState.filters.to_date) +
                '&limit=' + logsState.limit;

            var html = '<div class="pagination" style="display:flex; gap:8px; flex-wrap:wrap;">';
            
            if (page > 1) {
                html += '<a class="pagination-link" href="?page=' + (page - 1) + '&search=' + encodeURIComponent(logsState.filters.search) +
                    '&action_filter=' + encodeURIComponent(logsState.filters.action) +
                    '&role_filter=' + encodeURIComponent(logsState.filters.role) +
                    '&from_date=' + encodeURIComponent(logsState.filters.from_date) +
                    '&to_date=' + encodeURIComponent(logsState.filters.to_date) +
                    '&limit=' + logsState.limit + '">&laquo; Previous</a>';
            }

            var start = Math.max(1, page - 2);
            var end = Math.min(total, page + 2);
            
            if (start > 1) {
                html += '<a class="pagination-link" href="?page=1&' + baseParams.slice(6).replace('{page}', '1') + '">1</a>';
                if (start > 2) html += '<span class="pagination-link" style="cursor:default; pointer-events:none;">…</span>';
            }
            
            for (var i = start; i <= end; i++) {
                if (i === page) {
                    html += '<span class="pagination-link current">' + i + '</span>';
                } else {
                    html += '<a class="pagination-link" href="?page=' + i + '&search=' + encodeURIComponent(logsState.filters.search) +
                        '&action_filter=' + encodeURIComponent(logsState.filters.action) +
                        '&role_filter=' + encodeURIComponent(logsState.filters.role) +
                        '&from_date=' + encodeURIComponent(logsState.filters.from_date) +
                        '&to_date=' + encodeURIComponent(logsState.filters.to_date) +
                        '&limit=' + logsState.limit + '">' + i + '</a>';
                }
            }
            
            if (end < total) {
                if (end < total - 1) html += '<span class="pagination-link" style="cursor:default; pointer-events:none;">…</span>';
                html += '<a class="pagination-link" href="?page=' + total + '&search=' + encodeURIComponent(logsState.filters.search) +
                    '&action_filter=' + encodeURIComponent(logsState.filters.action) +
                    '&role_filter=' + encodeURIComponent(logsState.filters.role) +
                    '&from_date=' + encodeURIComponent(logsState.filters.from_date) +
                    '&to_date=' + encodeURIComponent(logsState.filters.to_date) +
                    '&limit=' + logsState.limit + '">' + total + '</a>';
            }
            
            if (page < total) {
                html += '<a class="pagination-link" href="?page=' + (page + 1) + '&search=' + encodeURIComponent(logsState.filters.search) +
                    '&action_filter=' + encodeURIComponent(logsState.filters.action) +
                    '&role_filter=' + encodeURIComponent(logsState.filters.role) +
                    '&from_date=' + encodeURIComponent(logsState.filters.from_date) +
                    '&to_date=' + encodeURIComponent(logsState.filters.to_date) +
                    '&limit=' + logsState.limit + '">Next &raquo;</a>';
            }
            
            html += '</div>';
            logsPagination.innerHTML = html;
        }

        function updateStats(logs) {
            var counts = { total: logs.length, create: 0, update: 0, delete: 0 };
            for (var i = 0; i < logs.length; i++) {
                var action = logs[i].action;
                if (action.indexOf('CREATE') !== -1 || action === 'APPROVE_USER') counts.create++;
                else if (action.indexOf('UPDATE') !== -1 || action.indexOf('BLOCK') !== -1 || action.indexOf('UNBLOCK') !== -1) counts.update++;
                else if (action.indexOf('DELETE') !== -1 || action.indexOf('REJECT') !== -1) counts.delete++;
            }
            if (statsElements.total) statsElements.total.textContent = counts.total;
            if (statsElements.create) statsElements.create.textContent = counts.create;
            if (statsElements.update) statsElements.update.textContent = counts.update;
            if (statsElements.delete) statsElements.delete.textContent = counts.delete;
        }
    }

    // ==========================================
    // INITIALIZATION
    // ==========================================
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize based on current page
        var path = window.location.pathname;
        if (path.indexOf('system_logs.php') !== -1) {
            initSystemLogs();
        }
    });
})();
