const getAdminDashboard = () => {
    window.location.href = "./dashboard.php";
};

const getAdminProducts = () => {
    window.location.href = "./products.php";
};

const getAdminOrders = () => {
    window.location.href = "./orders.php";
};

const getAdminUserManagement = () => {
    window.location.href = "./user_management.php";
};

const getAdminPendingApprovals = () => {
    window.location.href = "./pending_approvals.php";
};

const getAdminSystemLogs = () => {
    window.location.href = "./system_logs.php";
};

const getAdminDeletionRequests = () => {
    window.location.href = "./deletion_requests.php";
};

const getAdminProfile = () => {
    window.location.href = "./admin_profile.php";
};

const getAdminLogout = () => {
    window.location.href = "../../server/logout.php";
};

/* ── Inactivity Auto-Logout (15 min timeout, 60s warning) ── */
(function () {
    var TIMEOUT_MS = 5 * 60 * 1000;
    var WARNING_MS = 4 * 60 * 1000;
    var LOGOUT_URL = '../../server/logout.php';
    var REFRESH_URL = '../../server/admin_auth.php';
    var idleTimer = null;
    var warningTimer = null;
    var countdownInterval = null;
    var warningShown = false;

    function createWarningModal() {
        if (document.getElementById('session-timeout-modal')) return;
        var overlay = document.createElement('div');
        overlay.id = 'session-timeout-modal';
        overlay.style.cssText = 'display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:99999;justify-content:center;align-items:center;';
        overlay.innerHTML = '<div style="background:#fff;border-radius:12px;padding:32px 28px;max-width:400px;width:90%;text-align:center;box-shadow:0 8px 32px rgba(0,0,0,0.18);">'
            + '<div style="font-size:40px;color:#dc2626;margin-bottom:12px;"><i class="fa-solid fa-clock"></i></div>'
            + '<h3 style="margin:0 0 8px;font-size:18px;color:#1f2937;">Session Expiring Soon</h3>'
            + '<p style="margin:0 0 16px;color:#6b7280;font-size:14px;">Your session will expire in <strong id="session-countdown">60</strong> seconds due to inactivity.</p>'
            + '<button id="session-continue-btn" style="background:#6B21A8;color:#fff;border:none;padding:10px 28px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">Continue</button>'
            + '</div>';
        document.body.appendChild(overlay);
        document.getElementById('session-continue-btn').addEventListener('click', function () {
            extendSession();
        });
    }

    function showWarning() {
        if (warningShown) return;
        warningShown = true;
        createWarningModal();
        var modal = document.getElementById('session-timeout-modal');
        var countdownEl = document.getElementById('session-countdown');
        modal.style.display = 'flex';
        var remaining = 60;
        countdownEl.textContent = remaining;
        countdownInterval = setInterval(function () {
            remaining--;
            countdownEl.textContent = remaining;
            if (remaining <= 0) {
                clearInterval(countdownInterval);
                window.location.href = LOGOUT_URL;
            }
        }, 1000);
    }

    function hideWarning() {
        warningShown = false;
        var modal = document.getElementById('session-timeout-modal');
        if (modal) modal.style.display = 'none';
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
        }
    }

    function resetTimers() {
        hideWarning();
        if (idleTimer) clearTimeout(idleTimer);
        if (warningTimer) clearTimeout(warningTimer);
        warningTimer = setTimeout(showWarning, WARNING_MS);
        idleTimer = setTimeout(function () {
            window.location.href = LOGOUT_URL;
        }, TIMEOUT_MS);
    }

    var events = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];
    events.forEach(function (evt) {
        document.addEventListener(evt, function () {
            if (!warningShown) {
                resetTimers();
            } else {
                if (countdownInterval) clearInterval(countdownInterval);
                var countdownEl = document.getElementById('session-countdown');
                var remaining = 60;
                if (countdownEl) countdownEl.textContent = remaining;
                countdownInterval = setInterval(function () {
                    remaining--;
                    if (countdownEl) countdownEl.textContent = remaining;
                    if (remaining <= 0) {
                        clearInterval(countdownInterval);
                        window.location.href = LOGOUT_URL;
                    }
                }, 1000);
            }
        }, { passive: true });
    });

    resetTimers();

    function extendSession() {
        hideWarning();
        fetch(REFRESH_URL, { credentials: 'same-origin' }).catch(function () {});
        resetTimers();
    }
})();
