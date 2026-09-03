<?php require_once __DIR__ . '/../../server/admin_auth.php';

$pendingUsers = [];
if ($connect) {
    $sql = "SELECT user_id, username, CONCAT(first_name, ' ', IFNULL(CONCAT(middle_name, ' '), ''), last_name) AS fullName, email FROM users WHERE status = 'pending' ORDER BY user_id";
    $result = mysqli_query($connect, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $pendingUsers[] = [
                'id'       => $row['user_id'],
                'username' => $row['username'],
                'fullName' => trim($row['fullName']),
                'email'    => $row['email'],
            ];
        }
    }
}

$orderCount = 0;
$pendingCount = count($pendingUsers);
if ($connect) {
    $r = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM orders WHERE status = 'pending'");
    if ($r) $orderCount = mysqli_fetch_assoc($r)['cnt'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approvals - Ube Delights Admin</title>
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
            <a onclick="getAdminPendingApprovals()" class="sidebar-link active"><i class="fa-solid fa-user-clock"></i><span>Pending Approvals</span><?php if ($pendingCount > 0): ?><span class="sidebar-badge"><?php echo $pendingCount; ?></span><?php endif; ?></a>
            <a onclick="getAdminSystemLogs()" class="sidebar-link"><i class="fa-solid fa-list-alt"></i><span>System Logs</span></a>
        </nav>

        <div class="sidebar-footer">
            <a onclick="getAdminLogout()" class="sidebar-logout"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <h1>Pending Approvals</h1>
                <p class="topbar-subtitle">Review and approve new user registrations.</p>
            </div>
            <div class="topbar-right">
                <span class="topbar-date"><i class="fa-solid fa-calendar-days"></i><?php echo date('F j, Y'); ?></span>
            </div>
        </header>

        <main class="admin-content">

            <div class="users-section">
                <div class="card">
                    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
                        <h2 style="margin:0; font-size:1.1rem; font-weight:700; color:var(--text-primary);">
                            <i class="fa-solid fa-user-clock" style="color:var(--accent);"></i> Pending Accounts
                        </h2>
                        <form method="GET" class="filters-bar" style="margin:0; padding:0; background:none; border:none; gap:10px;">
                            <div class="search-box" style="min-width:220px; max-width:280px;">
                                <i class="fa-solid fa-search"></i>
                                <input type="text" name="search" value="" placeholder="Search pending...">
                            </div>
                            <button type="submit" class="btn-primary" style="padding:10px 18px; font-size:0.85rem;">Search</button>
                        </form>
                    </div>
                    <div class="table-container">
                        <table class="data-table" id="pendingTable">
                            <thead>
                                <tr>
                                    <th>ID NUMBER</th>
                                    <th>USERNAME</th>
                                    <th>FULL NAME</th>
                                    <th>EMAIL</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                                <tbody id="pendingTableBody">
                                </tbody>
                        </table>
                    </div>
                    <div class="pagination-bar" id="pendingPagination">
                        <div class="pagination-info" id="pendingPaginationInfo"></div>
                        <div class="pagination" id="pendingPaginationLinks"></div>
                    </div>
                </div>
            </div>

            <div class="empty-state" id="emptyPending" style="display:none;">
                <div class="empty-icon">👥</div>
                <h3>No pending registrations</h3>
                <p>All caught up! No pending user registrations.</p>
            </div>
        </main>
    </div>

    <!-- View Pending User Modal -->
    <div class="modal-overlay" id="viewPendingModal" role="dialog" aria-modal="true" aria-labelledby="viewPendingModalTitle">
        <div class="modal" style="max-width: 650px;">
            <div class="modal-header">
                <h2 id="viewPendingModalTitle">Registration Details</h2>
                <button class="modal-close" id="viewPendingModalClose" aria-label="Close modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" id="viewPendingBody">
                <div style="text-align: center; padding: 40px;">
                    <i class="fa-solid fa-spinner fa-spin" style="font-size: 24px; color: var(--accent);"></i>
                    <p style="margin-top: 10px; color: var(--text-secondary);">Loading user data...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn-outline" id="viewPendingModalCloseBtn">Close</button>
            </div>
        </div>
    </div>

    <!-- Approve/Reject Confirmation Modal -->
    <div class="modal-overlay" id="approvalModal" role="dialog" aria-modal="true" aria-labelledby="approvalModalTitle">
        <div class="modal" style="max-width: 500px;">
            <div class="modal-header">
                <h2 id="approvalModalTitle">Approve Registration</h2>
                <button class="modal-close" id="approvalModalClose" aria-label="Close modal"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <p id="approvalMessage">Are you sure you want to approve this registration?</p>
                <p style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 8px;">User: <strong id="approvalUserName"></strong></p>
                <input type="hidden" id="approvalUserId">
                <input type="hidden" id="approvalAction">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" id="approvalModalCancel">Cancel</button>
                <button type="button" class="btn-primary" id="approvalConfirmBtn"><i class="fa-solid fa-check"></i> <span id="approvalConfirmText">Approve</span></button>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script src="../../javascript/admin-routing.js"></script>
    <script src="../../javascript/admin_security.js"></script>
    <script>var pendingUsers = <?php echo json_encode($pendingUsers); ?>;</script>
    <script src="../../javascript/admin_pending_approvals.js"></script>
    <script src="../../javascript/inspect.js"></script>
</body>
</html>
