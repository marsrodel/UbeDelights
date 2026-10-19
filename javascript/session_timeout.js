(function () {
    var pathname = window.location.pathname.replace(/\\/g, '/');
    var isAdminPage = pathname.indexOf('/views/admin/') !== -1;
    var isEnabled = isAdminPage || document.body.getAttribute('data-session-active') === 'true';
    if (!isEnabled) return;

    var TIMEOUT_MS = 5 * 60 * 1000;
    var WARNING_MS = 4 * 60 * 1000;
    var HEARTBEAT_MS = 30 * 1000;
    var HEARTBEAT_MIN_INTERVAL_MS = 10 * 1000;
    var RECENT_ACTIVITY_MS = 15 * 1000;
    var ACTIVITY_KEY = 'ube-delights:last-activity';
    var MESSAGE_KEY = 'ube-delights:session-message';
    var activityUrl = new URL(isAdminPage ? '../../server/session_activity.php' : '../server/session_activity.php', document.baseURI).href;
    var appBase = '';
    var appPathIndex = pathname.indexOf('/views/');
    if (appPathIndex >= 0) appBase = pathname.slice(0, appPathIndex);
    var defaultLoginUrl = window.location.origin + appBase + '/views/login.php';
    var lastActivityAt = Date.now();
    var warningTimer = null;
    var expiryTimer = null;
    var countdownTimer = null;
    var heartbeatTimer = null;
    var warningShown = false;
    var expiryRequestPending = false;
    var heartbeatPending = false;
    var lastHeartbeatAt = Date.now();
    var channel = null;
    var warningOverlay = null;
    var continueButton = null;
    var lastPublishedActivityAt = 0;
    var lastTimerUpdateAt = 0;
    var activityPublishTimer = null;

    function writeStoredActivity(timestamp) {
        try {
            localStorage.setItem(ACTIVITY_KEY, String(timestamp));
        } catch (error) {}
    }

    function sendMessage(message) {
        var payload = Object.assign({ sentAt: Date.now() }, message);
        try {
            if (channel) channel.postMessage(payload);
        } catch (error) {}
        try {
            localStorage.setItem(MESSAGE_KEY, JSON.stringify(payload));
        } catch (error) {}
    }

    function showWarning() {
        if (!warningOverlay) {
            warningOverlay = document.createElement('div');
            warningOverlay.id = 'session-timeout-modal';
            warningOverlay.style.cssText = 'display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:99999;justify-content:center;align-items:center;';
            warningOverlay.innerHTML = '<div style="background:#fff;border-radius:12px;padding:32px 28px;max-width:400px;width:90%;text-align:center;box-shadow:0 8px 32px rgba(0,0,0,0.18);">'
                + '<div style="font-size:40px;color:#dc2626;margin-bottom:12px;"><i class="fa-solid fa-clock"></i></div>'
                + '<h3 style="margin:0 0 8px;font-size:18px;color:#1f2937;">Session Expiring Soon</h3>'
                + '<p style="margin:0 0 16px;color:#6b7280;font-size:14px;">Your session will expire in <strong id="session-countdown">60</strong> seconds due to inactivity.</p>'
                + '<button id="session-continue-btn" style="background:#6B21A8;color:#fff;border:none;padding:10px 28px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;">Continue</button>'
                + '</div>';
            document.body.appendChild(warningOverlay);
            continueButton = document.getElementById('session-continue-btn');
            continueButton.addEventListener('click', continueSession);
        }
        warningShown = true;
        warningOverlay.style.display = 'flex';
        updateCountdown();
        if (!countdownTimer) countdownTimer = setInterval(updateCountdown, 1000);
    }

    function updateCountdown() {
        var countdown = document.getElementById('session-countdown');
        if (!countdown) return;
        var remaining = Math.max(0, Math.ceil((lastActivityAt + TIMEOUT_MS - Date.now()) / 1000));
        countdown.textContent = String(remaining);
    }

    function hideWarning() {
        warningShown = false;
        if (warningOverlay) warningOverlay.style.display = 'none';
        if (countdownTimer) {
            clearInterval(countdownTimer);
            countdownTimer = null;
        }
    }

    function redirectToLogin(url) {
        var destination = url || defaultLoginUrl;
        if (window.location.href !== destination) window.location.replace(destination);
    }

    function handleTimeout(url) {
        sendMessage({ type: 'timeout', loginUrl: url || defaultLoginUrl });
        redirectToLogin(url);
    }

    function handleMessage(message) {
        if (!message || typeof message !== 'object') return;
        var messageTime = Number.isFinite(Number(message.at)) ? Math.min(Number(message.at), Date.now()) : 0;
        if (message.type === 'activity' && messageTime > lastActivityAt) {
            lastActivityAt = messageTime;
            writeStoredActivity(lastActivityAt);
            scheduleTimers();
            return;
        }
        if (message.type === 'continue' && messageTime >= lastActivityAt) {
            lastActivityAt = messageTime;
            writeStoredActivity(lastActivityAt);
            hideWarning();
            scheduleTimers();
            return;
        }
        if (message.type === 'timeout') {
            redirectToLogin(message.loginUrl || defaultLoginUrl);
            return;
        }
        if (message.type === 'logout') redirectToLogin(message.loginUrl || defaultLoginUrl);
    }

    function scheduleTimers() {
        if (warningTimer) clearTimeout(warningTimer);
        if (expiryTimer) clearTimeout(expiryTimer);
        var elapsed = Math.max(0, Date.now() - lastActivityAt);
        var warningDelay = WARNING_MS - elapsed;
        if (!warningShown && warningDelay > 0) warningTimer = setTimeout(showWarning, warningDelay);
        else if (!warningShown && warningDelay <= 0) showWarning();
        var expiryDelay = TIMEOUT_MS - elapsed;
        expiryTimer = setTimeout(requestExpiry, Math.max(0, expiryDelay));
        if (warningShown) updateCountdown();
    }

    function noteActivity() {
        var now = Date.now();
        lastActivityAt = now;
        if (now - lastPublishedActivityAt >= 1000) {
            lastPublishedActivityAt = now;
            writeStoredActivity(lastActivityAt);
            sendMessage({ type: 'activity', at: lastActivityAt });
        } else if (!activityPublishTimer) {
            activityPublishTimer = setTimeout(function () {
                activityPublishTimer = null;
                lastPublishedActivityAt = lastActivityAt;
                writeStoredActivity(lastActivityAt);
                sendMessage({ type: 'activity', at: lastActivityAt });
            }, Math.max(1, 1000 - (now - lastPublishedActivityAt)));
        }
        if (now - lastTimerUpdateAt >= 1000) {
            lastTimerUpdateAt = now;
            scheduleTimers();
        }
        sendHeartbeat(true);
    }

    function requestActivity(action) {
        return fetch(activityUrl, {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: new URLSearchParams({ action: action })
        }).then(function (response) {
            return response.json().catch(function () { return {}; }).then(function (data) {
                if (data.code === 'session_timeout') {
                    handleTimeout(data.login_url);
                    return { ok: false, data: data, status: response.status };
                }
                if (response.status === 401 || data.code === 'unauthorized') {
                    sendMessage({ type: 'logout', loginUrl: defaultLoginUrl });
                    redirectToLogin(defaultLoginUrl);
                    return { ok: false, data: data, status: response.status };
                }
                return { ok: response.ok && data.success === true, data: data, status: response.status };
            });
        });
    }

    function continueSession() {
        if (!continueButton || continueButton.disabled) return;
        continueButton.disabled = true;
        requestActivity('continue').then(function (result) {
            if (result.ok) {
                lastActivityAt = Date.now();
                lastHeartbeatAt = lastActivityAt;
                writeStoredActivity(lastActivityAt);
                hideWarning();
                sendMessage({ type: 'continue', at: lastActivityAt });
                scheduleTimers();
            }
        }).catch(function () {}).finally(function () {
            if (continueButton) continueButton.disabled = false;
        });
    }

    function requestExpiry() {
        if (expiryRequestPending) return;
        var remaining = lastActivityAt + TIMEOUT_MS - Date.now();
        if (remaining > 0) {
            expiryTimer = setTimeout(requestExpiry, remaining);
            return;
        }
        expiryRequestPending = true;
        requestActivity('expire').then(function (result) {
            if (result.status === 409 && result.data.code === 'session_active') {
                var retryMs = Math.max(1, Number(result.data.retry_after_seconds) || 1) * 1000;
                var clientDeadline = lastActivityAt + TIMEOUT_MS;
                var serverDeadline = Date.now() + retryMs;
                expiryTimer = setTimeout(requestExpiry, Math.max(100, Math.max(clientDeadline, serverDeadline) - Date.now() + 100));
                return;
            }
            if (!result.ok && result.status !== 401) expiryTimer = setTimeout(requestExpiry, 3000);
        }).catch(function () {
            expiryTimer = setTimeout(requestExpiry, 3000);
        }).finally(function () {
            expiryRequestPending = false;
        });
    }

    function sendHeartbeat(force) {
        var now = Date.now();
        if (heartbeatPending || now - lastActivityAt > RECENT_ACTIVITY_MS) return;
        var minimumInterval = force ? HEARTBEAT_MIN_INTERVAL_MS : HEARTBEAT_MS;
        if (now - lastHeartbeatAt < minimumInterval) return;
        heartbeatPending = true;
        lastHeartbeatAt = now;
        requestActivity('heartbeat').catch(function () {}).finally(function () {
            heartbeatPending = false;
        });
    }

    var initialActivity = Date.now();
    lastActivityAt = initialActivity;
    lastPublishedActivityAt = initialActivity;
    lastTimerUpdateAt = initialActivity;
    writeStoredActivity(initialActivity);
    try {
        localStorage.removeItem(MESSAGE_KEY);
    } catch (error) {}
    if ('BroadcastChannel' in window) {
        try {
            channel = new BroadcastChannel('ube-delights-session');
            channel.addEventListener('message', function (event) { handleMessage(event.data); });
        } catch (error) {}
    }
    window.addEventListener('storage', function (event) {
        if (event.key === ACTIVITY_KEY) {
            var timestamp = Number.isFinite(Number(event.newValue)) ? Math.min(Number(event.newValue), Date.now()) : 0;
            if (Number.isFinite(timestamp) && timestamp > lastActivityAt) {
                lastActivityAt = timestamp;
                scheduleTimers();
            }
        } else if (event.key === MESSAGE_KEY && event.newValue) {
            try { handleMessage(JSON.parse(event.newValue)); } catch (error) {}
        }
    });

    ['mousemove', 'keydown', 'click', 'scroll', 'touchstart', 'pointerdown'].forEach(function (eventName) {
        document.addEventListener(eventName, noteActivity, { passive: true });
    });

    heartbeatTimer = setInterval(function () { sendHeartbeat(false); }, HEARTBEAT_MS);
    sendMessage({ type: 'activity', at: initialActivity });
    scheduleTimers();
})();
