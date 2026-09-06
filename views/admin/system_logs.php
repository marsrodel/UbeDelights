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
    <title>System Logs - Ube Delights Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin_security.css?v=1.5">
</head>
<body class="admin-body">
<?php $activePage = 'system_logs'; include '_sidebar.php'; ?>

    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <h1>System Logs</h1>
                <p class="topbar-subtitle">Track user activity and system events.</p>
            </div>
            <div class="topbar-right">
                <span class="topbar-date"><i class="fa-solid fa-calendar-days"></i><?php echo date('F j, Y'); ?></span>
            </div>
        </header>

        <main class="admin-content">
            <div class="card logs-card">
                <div class="filters-grid" style="padding:18px 18px 14px; border-bottom:1px solid var(--border);">
                    <div class="filter-field">
                        <label>Search</label>
                        <input type="text" id="logsSearch" placeholder="Search username, name, action...">
                    </div>
                    <div class="filter-field">
                        <label>Role</label>
                        <select id="logsRoleFilter">
                            <option value="">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="customer">Customer</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label>From Date</label>
                        <input type="date" id="logsFromDate">
                    </div>
                    <div class="filter-field">
                        <label>To Date</label>
                        <input type="date" id="logsToDate">
                    </div>
                    <div class="filter-buttons">
                        <button class="btn-primary" id="btnApplyLogsFilter" style="padding:10px 18px; font-size:0.85rem;"><i class="fa-solid fa-filter"></i> Apply</button>
                        <button class="btn-outline" id="btnClearLogsFilter" style="padding:10px 18px; font-size:0.85rem;"><i class="fa-solid fa-xmark"></i> Clear</button>
                    </div>
                </div>
                <div class="table-container">
                    <table class="data-table" id="logsTable">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Role</th>
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
                <div class="pagination-bar" id="logsPaginationContainer">
                </div>
            </div>

            <div class="empty-state" id="emptyLogs" style="display:none;">
                <div class="empty-icon">📋</div>
                <h3>No system logs</h3>
                <p>Activity records will appear here once users start logging in.</p>
            </div>

        </main>
    </div>

    <div class="toast" id="toast"></div>

    <script src="../../javascript/admin-routing.js"></script>
    <script src="../../javascript/admin_security.js"></script>
    <script src="../../javascript/admin_logs.js"></script>
    <script src="../../javascript/inspect.js"></script>
</body>
</html>
