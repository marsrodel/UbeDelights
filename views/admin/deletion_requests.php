<?php require_once __DIR__ . '/../../server/admin_auth.php';

if ($_SESSION['auth_role'] !== 'super_admin') {
    header('Location: ./dashboard.php');
    exit();
}

$deletionRequests = [];
if ($connect) {
    $sql = "SELECT dr.id, dr.target_id_number, dr.requested_by, dr.reason, dr.status,
                   dr.reviewed_by, dr.reviewed_at, dr.created_at,
                   t.username AS target_username, t.first_name AS target_first_name,
                   t.middle_name AS target_middle_name, t.last_name AS target_last_name,
                   t.extension_name AS target_extension_name, t.role AS target_role,
                   r.username AS reviewer_username,
                   CONCAT(rb.first_name, ' ', IFNULL(rb.middle_name, ''), ' ', rb.last_name) AS requester_name
            FROM deletion_requests dr
            JOIN users t ON t.user_id = dr.target_id_number
            LEFT JOIN users r ON r.user_id = dr.reviewed_by
            LEFT JOIN users rb ON rb.user_id = dr.requested_by
            ORDER BY FIELD(dr.status, 'pending', 'rejected', 'approved'), dr.created_at DESC";
    $result = mysqli_query($connect, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $targetName = trim(implode(' ', array_filter([
                $row['target_first_name'],
                $row['target_middle_name'],
                $row['target_last_name'],
                $row['target_extension_name']
            ])));
            $deletionRequests[] = [
                'id'              => $row['id'],
                'targetId'        => $row['target_id_number'],
                'targetName'      => $targetName,
                'targetUsername'   => $row['target_username'],
                'targetRole'      => $row['target_role'],
                'requestedBy'     => trim($row['requester_name']),
                'reason'          => $row['reason'],
                'status'          => $row['status'],
                'reviewedBy'      => $row['reviewer_username'] ?? '',
                'reviewedAt'      => $row['reviewed_at'] ?? '',
                'createdAt'       => $row['created_at'],
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
    <title>Deletion Requests - Ube Delights Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../../css/admin_security.css?v=1.5">
</head>
<body class="admin-body">
<?php $activePage = 'deletion_requests'; include '_sidebar.php'; ?>

    <div class="admin-main">
        <header class="admin-topbar">
            <div>
                <h1>Deletion Requests</h1>
                <p class="topbar-subtitle">Administrator-submitted account deletion requests awaiting your review.</p>
            </div>
            <div class="topbar-right">
                <span class="topbar-date"><i class="fa-solid fa-calendar-days"></i><?php echo date('F j, Y'); ?></span>
            </div>
        </header>

        <main class="admin-content">
            <div class="card">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px;">
                    <h2 style="margin:0; font-size:1.1rem; font-weight:700; color:var(--text-primary);">
                        <i class="fa-solid fa-trash-can-arrow-up" style="color:var(--accent);"></i> Deletion Requests
                    </h2>
                </div>
                <div class="table-container">
                    <table class="data-table" id="drTable">
                        <thead>
                            <tr>
                                <th>TARGET ACCOUNT</th>
                                <th>REASON</th>
                                <th>REQUESTED BY</th>
                                <th>REQUESTED</th>
                                <th>STATUS</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody id="drTableBody">
                        </tbody>
                    </table>
                </div>
                <div class="pagination-bar" id="drPagination">
                    <div class="pagination-info" id="drPaginationInfo"></div>
                    <div class="pagination" id="drPaginationLinks"></div>
                </div>
            </div>

            <div class="empty-state" id="emptyDr" style="display:none;">
                <div class="empty-icon">📋</div>
                <h3>No deletion requests</h3>
                <p>No deletion requests match your current filters.</p>
            </div>
        </main>
    </div>

    <!-- Approve Confirmation Modal -->
    <div class="modal-overlay" id="approveDrModal" role="dialog" aria-modal="true">
        <div class="modal" style="max-width:450px;">
            <div class="modal-header" style="border-bottom:none;">
                <h2>Approve Deletion Request</h2>
                <button class="modal-close" onclick="closeModal('approveDrModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" style="padding:0 24px 24px;">
                <p style="color:var(--text-secondary); font-size:0.95rem;">Approving this request will <strong>block</strong> the target account. The user will no longer be able to log in.</p>
                <p id="approveDrUserName" style="color:var(--text-secondary); font-size:0.95rem; margin-top:8px;">Target: <strong></strong></p>
                <input type="hidden" id="approveDrId">
            </div>
            <div class="modal-footer" style="border-top:none;">
                <button class="btn-outline" onclick="closeModal('approveDrModal')">Cancel</button>
                <button class="btn-primary" id="approveDrConfirmBtn"><i class="fa-solid fa-check"></i> Approve</button>
            </div>
        </div>
    </div>

    <!-- Reject Confirmation Modal -->
    <div class="modal-overlay" id="rejectDrModal" role="dialog" aria-modal="true">
        <div class="modal" style="max-width:450px;">
            <div class="modal-header" style="border-bottom:none;">
                <h2>Reject Deletion Request</h2>
                <button class="modal-close" onclick="closeModal('rejectDrModal')"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" style="padding:0 24px 24px;">
                <p style="color:var(--text-secondary); font-size:0.95rem;">Rejecting this request will dismiss it. The target account will remain active.</p>
                <p id="rejectDrUserName" style="color:var(--text-secondary); font-size:0.95rem; margin-top:8px;">Target: <strong></strong></p>
                <input type="hidden" id="rejectDrId">
            </div>
            <div class="modal-footer" style="border-top:none;">
                <button class="btn-outline" onclick="closeModal('rejectDrModal')">Cancel</button>
                <button class="btn-primary" id="rejectDrConfirmBtn" style="background:var(--danger, #e17055);"><i class="fa-solid fa-xmark"></i> Reject</button>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script src="../../javascript/admin-routing.js"></script>
    <script src="../../javascript/admin_security.js"></script>
    <script>var deletionRequests = <?php echo json_encode($deletionRequests); ?>; var currentUsername = <?php echo json_encode($_SESSION['auth_username'] ?? ''); ?>;</script>
    <script src="../../javascript/admin_deletion_requests.js"></script>
    <script src="../../javascript/inspect.js"></script>
</body>
</html>
