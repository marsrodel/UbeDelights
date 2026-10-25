<?php require_once __DIR__ . '/../../server/admin_auth.php';

$orderCount = 0;
$pendingCount = 0;
if ($connect) {
    $r = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending'");
    if ($r) $orderCount = mysqli_fetch_assoc($r)['cnt'];
    $r2 = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM users WHERE status = 'pending'");
    if ($r2) $pendingCount = mysqli_fetch_assoc($r2)['cnt'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs - Ube Delights Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin_security.css?v=1.5">
    <link rel="stylesheet" href="../../css/logs.css?v=2.2">
    <link rel="stylesheet" href="../../css/admin_selects.css?v=1.1">
</head>
<body class="admin-body">
<?php $activePage = 'system_logs'; include '_sidebar.php'; ?>

    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <h1>Logs</h1>
                <p class="topbar-subtitle">Review system events, login and security records, and account activity.</p>
            </div>
            <div class="topbar-right">
                <div class="notif-wrapper">
                    <button class="notif-btn" id="notifBtn">
                        <i class="fa-solid fa-bell"></i>
                        <span class="notif-badge" id="notifBadge">0</span>
                    </button>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <h4>Notifications</h4>
                            <a id="markAllRead">Mark all read</a>
                        </div>
                        <div class="notif-list" id="notifList"></div>
                    </div>
                </div>
                <span class="topbar-date"><i class="fa-solid fa-calendar-days"></i><?php echo date('F j, Y'); ?></span>
            </div>
        </header>

        <main class="admin-content">
            <div class="card logs-card">
                <div class="filters-grid logs-filters">
                    <div class="filter-field">
                        <label for="logsTypeFilter">Log Type</label>
                        <select id="logsTypeFilter">
                            <option value="authentication" selected>Login &amp; Security Logs</option>
                            <option value="activity">Activity Logs</option>
                            <option value="system">System Logs</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label for="logsSearch">Search</label>
                        <input type="text" id="logsSearch" placeholder="Search username, name, action...">
                    </div>
                    <div class="filter-field">
                        <label for="logsRoleFilter">Role</label>
                        <select id="logsRoleFilter">
                            <option value="">All Roles</option>
                            <?php if ($isSuperAdmin): ?>
                            <option value="super_admin">Super Admin</option>
                            <?php endif; ?>
                            <option value="admin">Admin</option>
                            <option value="customer">Customer</option>
                            <?php if ($isSuperAdmin): ?>
                            <option value="system">Unattributed</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label for="logsFromDate">From Date</label>
                        <input type="date" id="logsFromDate">
                    </div>
                    <div class="filter-field">
                        <label for="logsToDate">To Date</label>
                        <input type="date" id="logsToDate">
                    </div>
                    <div class="filter-buttons">
                        <button type="button" class="btn-primary" id="btnApplyLogsFilter"><i class="fa-solid fa-filter"></i> Apply</button>
                        <button type="button" class="btn-outline" id="btnClearLogsFilter"><i class="fa-solid fa-xmark"></i> Clear</button>
                    </div>
                </div>
                <p id="logsStatus" class="logs-status" role="status" hidden></p>
                <div class="table-container logs-table-scroll">
                    <table class="data-table" id="logsTable">
                        <thead id="logsTableHead">
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Module</th>
                                <th>Action</th>
                                <th>Browser / OS</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>IP Address</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody id="logsTableBody">
                        </tbody>
                    </table>
                </div>
                <div class="empty-state" id="emptyLogs" hidden>
                    <div class="empty-icon"><i class="fa-solid fa-list-check"></i></div>
                    <h3 id="emptyLogsTitle">No login &amp; security logs</h3>
                    <p id="emptyLogsMessage">No login or security records match your current filters.</p>
                </div>
                <div class="pagination-bar" id="logsPaginationContainer">
                </div>
            </div>

        </main>
    </div>

    <div class="modal-overlay logs-notice-overlay" id="successModal" role="dialog" aria-modal="true" aria-labelledby="successModalTitle">
        <div class="modal logs-notice">
            <div class="modal-header"><h2 id="successModalTitle">Notice</h2></div>
            <div class="modal-body"><p id="successModalMessage"></p></div>
            <div class="modal-footer"><button type="button" class="btn-primary" id="successModalOkBtn">OK</button></div>
        </div>
    </div>

    <div class="modal-overlay logs-notice-overlay" id="deletionDetailsModal" role="dialog" aria-modal="true" aria-labelledby="deletionDetailsTitle">
        <div class="modal logs-notice logs-details-modal">
            <div class="modal-header">
                <h2 id="deletionDetailsTitle">Deletion Details</h2>
                <button type="button" class="modal-close" id="deletionDetailsCloseBtn" aria-label="Close details"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="logs-detail-list" id="deletionDetailsBody"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-primary" id="deletionDetailsDoneBtn">Close</button></div>
        </div>
    </div>

    <script src="../../javascript/admin-routing.js?v=3.0"></script>
    <script src="../../javascript/admin_security.js"></script>
    <script src="../../javascript/admin_notifications.js"></script>
    <script src="../../javascript/admin_logs.js?v=2.6"></script>
    <script src="../../javascript/inspect.js"></script>
</body>
</html>
