<?php require_once __DIR__ . '/../../server/admin_auth.php';

$logs = [];
if ($connect) {
    $sql = "SELECT l.username AS user_name, u.user_id AS idNo, IFNULL(u.role, 'system') AS user_role, l.action, l.description, IFNULL(l.device, 'Unknown') AS device, IFNULL(l.browser, 'Unknown') AS browser, l.ip_address, l.created_at
            FROM user_logs l
            LEFT JOIN users u ON l.username = u.username
            ORDER BY l.created_at DESC";
    $result = mysqli_query($connect, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $logs[] = [
                'user_name'  => $row['user_name'] ?? 'System',
                'idNo'       => $row['idNo'] ?? 'N/A',
                'user_role'  => $row['user_role'],
                'action'     => $row['action'],
                'description'=> $row['description'],
                'device'     => $row['device'],
                'browser'    => $row['browser'],
                'ip_address' => $row['ip_address'] ?? 'N/A',
                'created_at' => $row['created_at'],
            ];
        }
    }
}

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
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <img src="../../images/logo.png" alt="Ube Delights" class="sidebar-logo">
            <div>
                <h2>Ube Delights</h2>
                <span class="sidebar-tag">Admin Panel</span>
            </div>
        </div>

        <div class="sidebar-profile">
            <div class="admin-chip">
                <div class="admin-avatar">AU</div>
                <div class="admin-chip-info">
                    <strong><?php echo htmlspecialchars($currentUser['username']); ?></strong>
                    <small>ADMIN</small>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a onclick="getAdminDashboard()" class="sidebar-link"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
            <a onclick="getAdminProducts()" class="sidebar-link"><i class="fa-solid fa-box"></i><span>Products</span></a>
            <a onclick="getAdminOrders()" class="sidebar-link"><i class="fa-solid fa-bag-shopping"></i><span>Orders</span><?php if ($orderCount > 0): ?><span class="sidebar-badge"><?php echo $orderCount; ?></span><?php endif; ?></a>
            <a onclick="getAdminUserManagement()" class="sidebar-link"><i class="fa-solid fa-users-cog"></i><span>User Management</span></a>
            <a onclick="getAdminPendingApprovals()" class="sidebar-link"><i class="fa-solid fa-user-clock"></i><span>Pending Approvals</span><?php if ($pendingCount > 0): ?><span class="sidebar-badge"><?php echo $pendingCount; ?></span><?php endif; ?></a>
            <a onclick="getAdminSystemLogs()" class="sidebar-link active"><i class="fa-solid fa-list-alt"></i><span>System Logs</span></a>
        </nav>

        <div class="sidebar-footer">
            <a onclick="getAdminLogout()" class="sidebar-logout"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <h1>System Logs</h1>
            <div class="topbar-right">
                <span class="topbar-date"><i class="fa-solid fa-calendar-days"></i><?php echo date('F j, Y'); ?></span>
            </div>
        </header>

        <main class="admin-content">
            <div class="filters-card">
                <div class="filters-title"><i class="fa-solid fa-filter"></i> Filters</div>
                <div class="filters-grid">
                    <div class="filter-field">
                        <label>User Name</label>
                        <input type="text" id="logsSearch" placeholder="Username...">
                    </div>
                    <div class="filter-field">
                        <label>Role</label>
                        <select id="logsRoleFilter">
                            <option value="">All Roles</option>
                            <option value="super_admin">Super Admin</option>
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
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="section-header">
                        <h2><i class="fa-solid fa-clipboard-list" style="color:var(--accent);"></i> Activity Records</h2>
                        <span class="count" id="logsTotalCount">(<?php echo count($logs); ?> total)</span>
                    </div>
                </div>
                <div class="table-container">
                    <table class="data-table" id="logsTable">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Role</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Device / Browser</th>
                                <th>IP Address</th>
                                <th>Activity</th>
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
                <h3>No logs found</h3>
                <p>No logs match your current filters.</p>
            </div>
        </main>
    </div>

    <div class="toast" id="toast"></div>

    <script src="../../javascript/admin-routing.js"></script>
    <script src="../../javascript/admin_security.js"></script>
    <script src="../../javascript/admin_logs.js"></script>
    <script>var allLogs = <?php echo json_encode($logs); ?>;</script>
    <script src="../../javascript/inspect.js"></script>
</body>
</html>
