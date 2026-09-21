(function () {
    var isSuperAdmin = document.body.classList.contains('admin-body') &&
        (typeof currentAdminRole !== 'undefined' ? currentAdminRole === 'super_admin' : false);

    var PHP_PATH = (typeof notifBasePath !== 'undefined') ? notifBasePath : '../../server';

    var btn = document.getElementById('notifBtn');
    var dropdown = document.getElementById('notifDropdown');
    var badge = document.getElementById('notifBadge');
    var list = document.getElementById('notifList');
    var markAllBtn = document.getElementById('markAllRead');
    var open = false;

    if (!btn || !dropdown) return;

    function toggleDropdown() {
        open = !open;
        dropdown.style.display = open ? 'block' : 'none';
        if (open) {
            fetchNotifications();
        }
    }

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        toggleDropdown();
    });

    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
            open = false;
            dropdown.style.display = 'none';
        }
    });

    if (markAllBtn) {
        markAllBtn.addEventListener('click', function () {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', PHP_PATH + '/admin_mark_notifications_read.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                fetchNotifications();
            };
            xhr.send('');
        });
    }

    function fetchNotifications() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', PHP_PATH + '/admin_get_notifications.php?limit=20', true);
        xhr.onload = function () {
            if (xhr.status !== 200) return;
            try {
                var data = JSON.parse(xhr.responseText);
                if (!data.success) return;
                renderNotifications(data.notifications);
                updateBadge(data.unread_count);
            } catch (e) {}
        };
        xhr.send();
    }

    function renderNotifications(notifs) {
        if (!list) return;
        if (notifs.length === 0) {
            list.innerHTML = '<div class="notif-empty">No notifications yet</div>';
            return;
        }
        var html = '';
        for (var i = 0; i < notifs.length; i++) {
            var n = notifs[i];
            var unreadClass = n.is_read ? '' : ' unread';
            var icon = getNotifIcon(n.action_type);
            var time = formatNotifTime(n.created_at);
            html += '<div class="notif-item' + unreadClass + '">'
                + '<div class="notif-item-icon"><i class="fa-solid ' + icon + '"></i></div>'
                + '<div class="notif-item-body">'
                + '<div class="notif-item-title">' + escapeHtml(n.title) + '</div>'
                + '<div class="notif-item-msg">' + escapeHtml(n.message) + '</div>'
                + '<div class="notif-item-time">' + time + '</div>'
                + '</div>'
                + '</div>';
        }
        list.innerHTML = html;
    }

    function updateBadge(count) {
        if (!badge) return;
        badge.textContent = count;
        badge.style.display = count > 0 ? 'flex' : 'none';
    }

    function getNotifIcon(type) {
        var icons = {
            'auth': 'fa-right-to-bracket',
            'security': 'fa-shield-halved',
            'account': 'fa-user-gear'
        };
        return icons[type] || 'fa-bell';
    }

    function formatNotifTime(timestamp) {
        if (!timestamp) return '';
        var d = new Date(timestamp.replace(' ', 'T') + 'Z');
        var now = new Date();
        var diffMs = now - d;
        var diffSec = Math.floor(diffMs / 1000);
        var diffMin = Math.floor(diffSec / 60);
        var diffHr = Math.floor(diffMin / 60);
        var diffDay = Math.floor(diffHr / 24);

        if (diffMin < 1) return 'Just now';
        if (diffMin < 60) return diffMin + 'm ago';
        if (diffHr < 24) return diffHr + 'h ago';
        if (diffDay < 7) return diffDay + 'd ago';
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    fetchNotifications();
    setInterval(fetchNotifications, 30000);
})();
