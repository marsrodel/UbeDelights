(function() {
    'use strict';

    var state = {
        type: 'authentication',
        page: 1,
        perPage: 10,
        filters: { search: '', role: '', date_from: '', date_to: '' }
    };
    var labels = { system: 'System Logs', authentication: 'Login & Security Logs', activity: 'Activity Logs' };
    var searchTimer = null;
    var activeRequest = null;
    var requestNumber = 0;
    var noticeCallback = null;

    function escapeHtml(str) {
        return String(str == null ? '' : str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function formatLogTimestamp(ts) {
        if (!ts) return '\u2014';
        var d = new Date(ts.replace(' ', 'T'));
        if (isNaN(d)) return escapeHtml(ts);
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var hours = d.getHours();
        var ampm = hours >= 12 ? 'PM' : 'AM';
        return months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear() + '<br><span class="log-time">' +
            (hours % 12 || 12) + ':' + String(d.getMinutes()).padStart(2, '0') + ' ' + ampm + '</span>';
    }

    function cleanLogDetails(details) {
        return String(details || '').replace(/\s*\|\s*Browser:.*$/i, '').trim();
    }

    function getRoleLabel(role) {
        return { super_admin: 'Super Admin', admin: 'Admin', customer: 'Customer' }[role] || 'Unattributed';
    }

    function getRoleClass(role) {
        return { super_admin: 'role-super_admin', admin: 'role-admin', customer: 'role-customer' }[role] || '';
    }

    function getActionLabel(action) {
        return String(action || '').toLowerCase().split('_').map(function(word) {
            return word === 'otp' ? 'OTP' : word.charAt(0).toUpperCase() + word.slice(1);
        }).join(' ');
    }

    function getActionClass(action) {
        var classes = {
            login: 'activity-login', logout: 'activity-logout', session_timeout: 'activity-logout',
            failed_login: 'activity-failed_login', login_blocked: 'activity-block', account_lockout: 'activity-block',
            create_user: 'activity-create', create_account: 'activity-create', account_activated: 'activity-create',
            block_user: 'activity-block', unblock_user: 'activity-unblock', delete_user: 'activity-delete',
            approve_user: 'activity-approve', reject_user: 'activity-reject', product_add: 'activity-create',
            product_delete: 'activity-delete', deletion_request: 'activity-delete',
            otp_verified: 'activity-approve', otp_failed: 'activity-failed_login',
            otp_expired: 'activity-failed_login', otp_locked: 'activity-block'
        };
        return classes[String(action || '').toLowerCase()] || 'activity-update';
    }

    function showSuccessModal(message, callback, title) {
        noticeCallback = typeof callback === 'function' ? callback : null;
        document.getElementById('successModalTitle').textContent = title || 'Success';
        document.getElementById('successModalMessage').textContent = message;
        document.getElementById('successModal').classList.add('active');
        document.body.classList.add('logs-modal-open');
    }
    window.showSuccessModal = showSuccessModal;

    function closeNotice() {
        document.getElementById('successModal').classList.remove('active');
        document.body.classList.remove('logs-modal-open');
        var callback = noticeCallback;
        noticeCallback = null;
        if (callback) callback();
    }

    function buildParams() {
        var p = new URLSearchParams();
        p.set('type', state.type);
        p.set('page', state.page);
        p.set('limit', state.perPage);
        Object.keys(state.filters).forEach(function(key) {
            if (state.filters[key]) p.set(key, state.filters[key]);
        });
        return p.toString();
    }

    function renderHead() {
        var headings = ['User ID', 'Name', 'Role', 'Module', 'Action', 'Browser / OS'];
        headings.push(state.type === 'authentication' ? 'Time In' : 'Timestamp');
        if (state.type === 'authentication') headings.push('Time Out');
        headings.push('IP Address', 'Details');
        var tableScroll = document.querySelector('.logs-table-scroll');
        if (tableScroll) tableScroll.classList.toggle('logs-authentication-view', state.type === 'authentication');
        setActionColumnTier(state.type === 'authentication' ? 'compact' : null);
        document.getElementById('logsTableHead').innerHTML = '<tr>' + headings.map(function(label) {
            return '<th scope="col">' + label + '</th>';
        }).join('') + '</tr>';
    }

    function setActionColumnTier(tier) {
        var tableScroll = document.querySelector('.logs-table-scroll');
        if (!tableScroll) return;
        ['compact', 'medium', 'wide'].forEach(function(size) {
            tableScroll.classList.remove('logs-auth-action-' + size);
        });
        if (tier) tableScroll.classList.add('logs-auth-action-' + tier);
    }

    function updateActionColumnTier() {
        if (state.type !== 'authentication') {
            setActionColumnTier(null);
            return;
        }

        var tbody = document.getElementById('logsTableBody');
        var badges = tbody ? tbody.querySelectorAll('.activity-badge') : [];
        if (!badges.length) {
            setActionColumnTier('compact');
            return;
        }

        var table = document.getElementById('logsTable');
        var tableWidth = table ? table.getBoundingClientRect().width : 0;
        if (!tableWidth) {
            setActionColumnTier('wide');
            return;
        }

        var widestBadge = 0;
        Array.prototype.forEach.call(badges, function(badge) {
            if (!badge.parentNode) return;
            var probe = badge.cloneNode(true);
            probe.classList.add('logs-action-measure');
            badge.parentNode.appendChild(probe);
            widestBadge = Math.max(widestBadge, probe.getBoundingClientRect().width);
            probe.parentNode.removeChild(probe);
        });

        var requiredWidth = widestBadge + 16;
        var requiredPercent = (requiredWidth / tableWidth) * 100;
        setActionColumnTier(requiredPercent <= 8 ? 'compact' : (requiredPercent <= 10 ? 'medium' : 'wide'));
    }

    function loadLogs() {
        var sequence = ++requestNumber;
        if (activeRequest) activeRequest.abort();
        var status = document.getElementById('logsStatus');
        status.textContent = 'Loading ' + labels[state.type].toLowerCase() + '...';
        status.hidden = false;
        document.getElementById('logsTableBody').innerHTML = '';
        document.getElementById('logsTable').hidden = true;
        document.getElementById('emptyLogs').hidden = true;
        document.getElementById('logsPaginationContainer').innerHTML = '';
        renderHead();
        var xhr = new XMLHttpRequest();
        activeRequest = xhr;
        xhr.open('GET', '../../server/admin_get_logs.php?' + buildParams(), true);
        xhr.timeout = 20000;
        xhr.onreadystatechange = function() {
            if (xhr.readyState !== 4 || sequence !== requestNumber) return;
            activeRequest = null;
            status.hidden = true;
            var data = null;
            try { data = JSON.parse(xhr.responseText); } catch (e) { data = null; }
            if (xhr.status === 200 && data && data.success) {
                state.page = data.pagination.page;
                renderTable(data.logs);
                renderPagination(data.pagination);
            } else {
                var message = data && data.message ? data.message : 'Unable to load logs. Please try again.';
                renderTable([]);
                document.getElementById('emptyLogsTitle').textContent = 'Unable to load logs';
                document.getElementById('emptyLogsMessage').textContent = message;
                showSuccessModal(message, null, 'Notice');
            }
        };
        xhr.send();
    }

    function renderTable(logs) {
        var tbody = document.getElementById('logsTableBody');
        var isAuthentication = state.type === 'authentication';
        function cell(label, content) {
            return '<td' + (isAuthentication ? ' data-label="' + escapeHtml(label) + '"' : '') + '>' + content + '</td>';
        }
        document.getElementById('logsTable').hidden = logs.length === 0;
        document.getElementById('emptyLogs').hidden = logs.length !== 0;
        document.getElementById('emptyLogsTitle').textContent = 'No ' + labels[state.type].toLowerCase();
        document.getElementById('emptyLogsMessage').textContent = 'No records match your current filters.';
        tbody.innerHTML = logs.map(function(log) {
            var ip = log.ip_address === '::1' ? '127.0.0.1' : (log.ip_address || '\u2014');
            var browser = (log.browser || 'Unknown').replace(/\s+[0-9.]+$/, '');
            var timestamp = isAuthentication ? log.time_in : log.created_at;
            var timestampLabel = isAuthentication ? 'Time In' : 'Timestamp';
            var html = '<tr>' +
                cell('User ID', escapeHtml(log.idNumber || '\u2014')) +
                cell('Name', escapeHtml(log.fullName || log.username || 'System')) +
                cell('Role', '<span class="role-pill ' + getRoleClass(log.role) + '">' + getRoleLabel(log.role) + '</span>') +
                cell('Module', escapeHtml(log.module || '\u2014')) +
                cell('Action', '<span class="activity-badge ' + getActionClass(log.action) + '">' + escapeHtml(getActionLabel(log.action)) + '</span>') +
                cell('Browser / OS', escapeHtml(browser + ' / ' + (log.os || 'Unknown'))) +
                cell(timestampLabel, formatLogTimestamp(timestamp));
            if (isAuthentication) {
                html += cell('Time Out', log.time_out === 'active' ? '<span class="log-active">Active</span>' : formatLogTimestamp(log.time_out));
            }
            return html + cell('IP Address', '<code class="ip-badge">' + escapeHtml(ip) + '</code>') +
                cell('Details', escapeHtml(cleanLogDetails(log.details) || '\u2014')) + '</tr>';
        }).join('');
        updateActionColumnTier();
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
        clearTimeout(searchTimer);
        state.type = document.getElementById('logsTypeFilter').value;
        state.filters = {
            search: document.getElementById('logsSearch').value.trim(),
            role: document.getElementById('logsRoleFilter').value,
            date_from: document.getElementById('logsFromDate').value,
            date_to: document.getElementById('logsToDate').value
        };
        state.page = 1;
        loadLogs();
    }

    function clearFilters() {
        ['logsSearch', 'logsRoleFilter', 'logsFromDate', 'logsToDate'].forEach(function(id) {
            document.getElementById(id).value = '';
        });
        collectFilters();
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('logsTypeFilter').value = 'authentication';
        document.getElementById('logsSearch').addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(collectFilters, 350);
        });
        ['logsTypeFilter', 'logsRoleFilter', 'logsFromDate', 'logsToDate'].forEach(function(id) {
            document.getElementById(id).addEventListener('change', collectFilters);
        });
        document.getElementById('btnApplyLogsFilter').addEventListener('click', collectFilters);
        document.getElementById('btnClearLogsFilter').addEventListener('click', clearFilters);
        document.getElementById('successModalOkBtn').addEventListener('click', closeNotice);
        document.getElementById('successModal').addEventListener('click', function(event) {
            if (event.target === this) closeNotice();
        });
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') closeNotice();
        });
        loadLogs();
    });
})();
