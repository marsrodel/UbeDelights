<?php require_once __DIR__ . '/../../server/admin_auth.php';

$pendingUsers = [];
if ($connect) {
    $sql = "SELECT user_id, username, first_name, middle_name, last_name, extension_name,
                   email, date_of_birth, age, sex,
                   street, barangay, city_municipality, province, country, zip_code
            FROM users WHERE status = 'pending' ORDER BY user_id";
    $result = mysqli_query($connect, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $fullName = trim(implode(' ', array_filter([
                $row['first_name'],
                $row['middle_name'],
                $row['last_name'],
                $row['extension_name']
            ])));
            $pendingUsers[] = [
                'id'       => $row['user_id'],
                'username' => $row['username'],
                'fullName' => $fullName,
                'firstName' => $row['first_name'],
                'middleName' => $row['middle_name'] ?? '',
                'lastName' => $row['last_name'],
                'extensionName' => $row['extension_name'] ?? '',
                'email'    => $row['email'],
                'dob'      => $row['date_of_birth'],
                'age'      => $row['age'],
                'sex'      => $row['sex'],
                'street'   => $row['street'],
                'barangay' => $row['barangay'],
                'city'     => $row['city_municipality'],
                'province' => $row['province'],
                'country'  => $row['country'],
                'zipCode'  => $row['zip_code'],
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

$myPrivileges = ['can_manage_registrations' => 0, 'can_update_accounts' => 0, 'can_request_deletion' => 0, 'can_block' => 0, 'can_reset_password' => 0];
if ($connect && ($_SESSION['auth_role'] ?? '') === 'admin') {
    $rp = mysqli_query($connect, "SELECT can_manage_registrations, can_update_accounts, can_request_deletion, can_block, can_reset_password FROM admin_privileges WHERE idNumber = '" . mysqli_real_escape_string($connect, $_SESSION['auth_user_id']) . "'");
    if ($rp && $rp->num_rows > 0) {
        $myPrivileges = mysqli_fetch_assoc($rp);
    }
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
    <link rel="stylesheet" href="../../css/user_management.css?v=2.0">
</head>
<body class="admin-body">
<?php $activePage = 'pending_approvals'; include '_sidebar.php'; ?>

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
        <div class="modal">
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
    <div class="modal-overlay" id="approvalModal" role="dialog" aria-modal="true">
        <div class="modal" style="max-width:450px;">
            <div class="modal-header" style="border-bottom:none;">
                <h2 id="approvalModalTitle">Approve Registration</h2>
                <button class="modal-close" onclick="closeApprovalModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" style="padding:0 24px 24px;">
                <p id="approvalMessage" style="color:var(--text-secondary); font-size:0.95rem;">Are you sure you want to approve this registration?</p>
                <p id="approvalUserName" style="color:var(--text-secondary); font-size:0.95rem; margin-top:8px;">User: <strong></strong></p>
                <input type="hidden" id="approvalUserId">
                <input type="hidden" id="approvalAction">
            </div>
            <div class="modal-footer" style="border-top:none;">
                <button class="btn-outline" onclick="closeApprovalModal()">Cancel</button>
                <button class="btn-primary" id="approvalConfirmBtn"><i class="fa-solid fa-check"></i> <span id="approvalConfirmText">Yes</span></button>
            </div>
        </div>
    </div>

    <!-- Security Key Confirmation Modal -->
    <div class="modal-overlay" id="approvalPasswordModal" role="dialog" aria-modal="true">
        <div class="modal" style="max-width:450px;">
            <div class="modal-header">
                <h2 id="approvalPasswordTitle">Enter your security key to approve this account.</h2>
                <button class="modal-close" onclick="closeApprovalPasswordModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
                <form class="modal-form">
                <p style="color:var(--text-secondary); font-size:0.82rem;">Enter your account password to confirm this action.</p>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="approvalPasswordInput" placeholder="Enter password">
                </div>
                <p id="approvalPasswordError" class="inline-error"></p>
                <input type="hidden" id="approvalPasswordUserId">
                <input type="hidden" id="approvalPasswordAction">
            </form>
            <div class="modal-footer">
                <button class="btn-outline" onclick="closeApprovalPasswordModal()">Cancel</button>
                <button class="btn-primary" id="approvalPasswordConfirmBtn"><i class="fa-solid fa-check"></i> Confirm</button>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script src="../../javascript/admin-routing.js"></script>
    <script src="../../javascript/admin_security.js"></script>
    <script>var pendingUsers = <?php echo json_encode($pendingUsers); ?>; var myPrivileges = <?php echo json_encode($myPrivileges); ?>; var currentUserRole = <?php echo json_encode($_SESSION['auth_role'] ?? ''); ?>;</script>
    <script src="../../javascript/admin_pending_approvals.js"></script>
    <script src="../../javascript/inspect.js"></script>
</body>
</html>
