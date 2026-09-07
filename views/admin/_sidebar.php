<?php
$isSuperAdmin = (($_SESSION['auth_role'] ?? '') === 'super_admin');
$activePage = $activePage ?? 'dashboard';
$orderCount = $orderCount ?? 0;
$pendingCount = $pendingCount ?? 0;
$deletionPendingCount = $deletionPendingCount ?? 0;
if ($isSuperAdmin && $connect) {
    $rDel = mysqli_query($connect, "SELECT COUNT(*) AS cnt FROM deletion_requests WHERE status = 'pending'");
    if ($rDel) $deletionPendingCount = mysqli_fetch_assoc($rDel)['cnt'];
}
?>
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <img src="../../images/logo.png" alt="Ube Delights Logo" class="sidebar-logo">
            <div>
                <h2>Ube Delights</h2>
                <span class="sidebar-tag"><?php echo $isSuperAdmin ? 'Super Admin Panel' : 'Admin Panel'; ?></span>
            </div>
        </div>

        <div class="sidebar-profile">
            <div class="admin-chip">
                <div class="admin-avatar"><?php echo strtoupper(substr($_SESSION['auth_first_name'] ?? 'A', 0, 1) . substr($_SESSION['auth_last_name'] ?? 'U', 0, 1)); ?></div>
                <div class="admin-chip-info">
                    <strong><?php echo htmlspecialchars($_SESSION['auth_username'] ?? ''); ?></strong>
                    <small><?php echo $isSuperAdmin ? 'SUPER ADMIN' : 'ADMIN'; ?></small>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a onclick="getAdminDashboard()" class="sidebar-link<?php echo $activePage === 'dashboard' ? ' active' : ''; ?>"><i class="fa-solid fa-gauge-high"></i><span>Dashboard</span></a>
            <?php if (!$isSuperAdmin): ?>
            <a onclick="getAdminProducts()" class="sidebar-link<?php echo $activePage === 'products' ? ' active' : ''; ?>"><i class="fa-solid fa-box"></i><span>Products</span></a>
            <a onclick="getAdminOrders()" class="sidebar-link<?php echo $activePage === 'orders' ? ' active' : ''; ?>"><i class="fa-solid fa-bag-shopping"></i><span>Orders</span><?php if ($orderCount > 0): ?><span class="sidebar-badge"><?php echo $orderCount; ?></span><?php endif; ?></a>
            <?php endif; ?>
            <a onclick="getAdminUserManagement()" class="sidebar-link<?php echo $activePage === 'user_management' ? ' active' : ''; ?>"><i class="fa-solid fa-users-cog"></i><span>User Management</span></a>
            <a onclick="getAdminPendingApprovals()" class="sidebar-link<?php echo $activePage === 'pending_approvals' ? ' active' : ''; ?>"><i class="fa-solid fa-user-clock"></i><span>Pending Approvals</span><?php if ($pendingCount > 0): ?><span class="sidebar-badge"><?php echo $pendingCount; ?></span><?php endif; ?></a>
            <?php if ($isSuperAdmin): ?>
            <a onclick="getAdminDeletionRequests()" class="sidebar-link<?php echo $activePage === 'deletion_requests' ? ' active' : ''; ?>"><i class="fa-solid fa-trash-can-arrow-up"></i><span>Deletion Requests</span><?php if ($deletionPendingCount > 0): ?><span class="sidebar-badge"><?php echo $deletionPendingCount; ?></span><?php endif; ?></a>
            <?php endif; ?>
            <a onclick="getAdminSystemLogs()" class="sidebar-link<?php echo $activePage === 'system_logs' ? ' active' : ''; ?>"><i class="fa-solid fa-list-alt"></i><span>System Logs</span></a>
            <a onclick="getAdminProfile()" class="sidebar-link<?php echo $activePage === 'admin_profile' ? ' active' : ''; ?>"><i class="fa-solid fa-user"></i><span>My Account</span></a>
        </nav>

        <div class="sidebar-footer">
            <a onclick="getAdminLogout()" class="sidebar-logout"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></a>
        </div>
    </aside>
