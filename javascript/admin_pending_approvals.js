function viewPendingUser(userId) {
    var user = pendingUsers.find(function(u) { return u.id === userId; });
    if (!user) return;
    var body = document.getElementById('viewPendingBody');
    body.innerHTML =
        '<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">' +
            '<div class="info-item"><div class="info-label">ID Number</div><div class="info-value">' + user.id + '</div></div>' +
            '<div class="info-item"><div class="info-label">Username</div><div class="info-value">' + user.username + '</div></div>' +
            '<div class="info-item"><div class="info-label">Full Name</div><div class="info-value">' + user.fullName + '</div></div>' +
            '<div class="info-item"><div class="info-label">Email</div><div class="info-value">' + user.email + '</div></div>' +
        '</div>';
    document.getElementById('viewPendingModal').classList.add('active');
}

function closeViewPendingModal() {
    document.getElementById('viewPendingModal').classList.remove('active');
}

function approvePendingUser(userId) {
    var user = pendingUsers.find(function(u) { return u.id === userId; });
    if (!user) return;
    document.getElementById('approvalModalTitle').textContent = 'Approve Registration';
    document.getElementById('approvalMessage').textContent = 'Are you sure you want to approve this registration?';
    document.getElementById('approvalUserName').textContent = user.fullName;
    document.getElementById('approvalUserId').value = userId;
    document.getElementById('approvalAction').value = 'approve';
    document.getElementById('approvalConfirmBtn').style.background = 'var(--success)';
    document.getElementById('approvalConfirmBtn').innerHTML = '<i class="fa-solid fa-check"></i> <span>Approve</span>';
    document.getElementById('approvalModal').classList.add('active');
}

function rejectPendingUser(userId) {
    var user = pendingUsers.find(function(u) { return u.id === userId; });
    if (!user) return;
    document.getElementById('approvalModalTitle').textContent = 'Reject Registration';
    document.getElementById('approvalMessage').textContent = 'Are you sure you want to reject and delete this registration request?';
    document.getElementById('approvalUserName').textContent = user.fullName;
    document.getElementById('approvalUserId').value = userId;
    document.getElementById('approvalAction').value = 'reject';
    document.getElementById('approvalConfirmBtn').style.background = 'var(--danger)';
    document.getElementById('approvalConfirmBtn').innerHTML = '<i class="fa-solid fa-xmark"></i> <span>Reject</span>';
    document.getElementById('approvalModal').classList.add('active');
}

document.getElementById('approvalConfirmBtn').addEventListener('click', function() {
    var userId = document.getElementById('approvalUserId').value;
    var action = document.getElementById('approvalAction').value;
    var row = document.querySelector('tr[data-user-id="' + userId + '"]');
    if (row) row.remove();
    closeApprovalModal();
    var t = document.getElementById('toast');
    t.textContent = action === 'approve' ? 'Registration approved!' : 'Registration rejected.';
    t.className = 'toast show';
    setTimeout(function() { t.classList.remove('show'); }, 3000);
});

function closeApprovalModal() {
    document.getElementById('approvalModal').classList.remove('active');
}

document.getElementById('approvalModalCancel').addEventListener('click', closeApprovalModal);
document.getElementById('viewPendingModalClose').addEventListener('click', closeViewPendingModal);
document.getElementById('viewPendingModalCloseBtn').addEventListener('click', closeViewPendingModal);

document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.classList.remove('active');
        }
    });
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(function(m) {
            m.classList.remove('active');
        });
    }
});
