document.addEventListener('DOMContentLoaded', function() {
    'use strict';

    if (typeof allUsers === 'undefined') return;

    var state = { page: 1, perPage: 10, filters: { search: '', role: '', status: '' } };

    // ── Helpers ──

    function escapeHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    function getRoleBadge(role) {
        var map = { 'super_admin': 'role-super_admin', 'admin': 'role-admin', 'customer': 'role-customer' };
        var label = { 'super_admin': 'Super Admin', 'admin': 'Admin', 'customer': 'Customer' };
        return '<span class="role-pill ' + (map[role]||'') + '">' + (label[role]||role) + '</span>';
    }

    function getStatusBadge(status) {
        var map = { 'active': 'status-active', 'blocked': 'status-blocked', 'pending': 'status-pending', 'incomplete': 'status-incomplete' };
        return '<span class="status-pill ' + (map[status]||'') + '">' + (status||'').charAt(0).toUpperCase() + (status||'').slice(1) + '</span>';
    }

    function getActions(user) {
        var html = '<div style="display:flex; gap:6px;">';
        html += '<button class="um-action-btn btn-view" onclick="viewUser(\'' + user.id + '\')" title="View"><i class="fa-solid fa-eye"></i></button>';
        html += '<button class="um-action-btn btn-edit" onclick="editUser(\'' + user.id + '\')" title="Edit"><i class="fa-solid fa-pen"></i></button>';
        if (user.status === 'active') {
            html += '<button class="um-action-btn btn-block" onclick="blockUser(\'' + user.id + '\')" title="Block"><i class="fa-solid fa-lock"></i></button>';
        } else if (user.status === 'blocked') {
            html += '<button class="um-action-btn btn-unblock" onclick="unblockUser(\'' + user.id + '\')" title="Unblock"><i class="fa-solid fa-lock-open"></i></button>';
        }
        if (typeof currentUserRole !== 'undefined' && currentUserRole === 'super_admin') {
            html += '<button class="um-action-btn btn-delete" onclick="deleteUser(\'' + user.id + '\')" title="Delete"><i class="fa-solid fa-trash"></i></button>';
        }
        html += '</div>';
        return html;
    }

    // ── Filtering ──

    function filterUsers(users) {
        var f = state.filters;
        return users.filter(function(u) {
            if (f.search) {
                var s = f.search.toLowerCase();
                if (u.username.toLowerCase().indexOf(s) === -1 && u.fullName.toLowerCase().indexOf(s) === -1 && u.email.toLowerCase().indexOf(s) === -1 && u.id.toLowerCase().indexOf(s) === -1) return false;
            }
            if (f.role && u.role !== f.role) return false;
            if (f.status && u.status !== f.status) return false;
            return true;
        });
    }

    // ── Rendering ──

    function renderTable(users) {
        var tbody = document.getElementById('usersTableBody');
        var emptyEl = document.getElementById('emptyUsers');
        if (!tbody) return;
        if (users.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted);">No users found</td></tr>';
            if (emptyEl) emptyEl.style.display = '';
            return;
        }
        if (emptyEl) emptyEl.style.display = 'none';
        var html = '';
        for (var i = 0; i < users.length; i++) {
            var u = users[i];
            html += '<tr>' +
                '<td class="cell-id">' + escapeHtml(u.id) + '</td>' +
                '<td>' + escapeHtml(u.username) + '</td>' +
                '<td class="cell-strong">' + escapeHtml(u.fullName) + '</td>' +
                '<td class="cell-muted">' + escapeHtml(u.email) + '</td>' +
                '<td>' + getRoleBadge(u.role) + '</td>' +
                '<td>' + getStatusBadge(u.status) + '</td>' +
                '<td>' + getActions(u) + '</td>' +
            '</tr>';
        }
        tbody.innerHTML = html;
    }

    function renderPagination(total) {
        var container = document.getElementById('paginationContainer');
        if (!container) return;
        var page = state.page, perPage = state.perPage;
        var totalPages = Math.ceil(total / perPage);

        var html = '<div class="pagination">';
        var s = Math.max(1, page - 2), e = Math.min(totalPages, page + 2);
        for (var i = s; i <= e; i++) {
            if (i === page) html += '<span class="pagination-link current">' + i + '</span>';
            else html += '<a class="pagination-link" data-page="' + i + '">' + i + '</a>';
        }
        if (page < totalPages) html += '<a class="pagination-link" data-page="' + (page+1) + '">&raquo; Next</a>';
        else html += '<span class="pagination-link disabled">&raquo; Next</span>';
        html += '</div>';
        container.innerHTML = html;

        container.querySelectorAll('.pagination-link[data-page]').forEach(function(link) {
            link.addEventListener('click', function() { state.page = parseInt(this.getAttribute('data-page')); render(); });
        });
    }

    function render() {
        var filtered = filterUsers(allUsers);
        var start = (state.page - 1) * state.perPage;
        renderTable(filtered.slice(start, start + state.perPage));
        renderPagination(filtered.length);
    }

    // ── View / Edit / Block / Unblock / Delete ──

    window.viewUser = function(id) {
        var u = allUsers.find(function(x) { return x.id === id; });
        if (!u) return;
        document.getElementById('viewUserBody').innerHTML =
            '<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">' +
                '<div class="info-item"><div class="info-label">ID Number</div><div class="info-value">' + u.id + '</div></div>' +
                '<div class="info-item"><div class="info-label">Username</div><div class="info-value">' + u.username + '</div></div>' +
                '<div class="info-item"><div class="info-label">Full Name</div><div class="info-value">' + u.fullName + '</div></div>' +
                '<div class="info-item"><div class="info-label">Email</div><div class="info-value">' + u.email + '</div></div>' +
                '<div class="info-item"><div class="info-label">Role</div><div class="info-value">' + getRoleBadge(u.role) + '</div></div>' +
                '<div class="info-item"><div class="info-label">Status</div><div class="info-value">' + getStatusBadge(u.status) + '</div></div>' +
            '</div>';
        document.getElementById('viewUserModal').classList.add('active');
    };

    window.editUser = function(id) {
        var u = allUsers.find(function(x) { return x.id === id; });
        if (!u) return;
        document.getElementById('editUserId').value = u.id;
        document.getElementById('editFullName').value = u.fullName;
        document.getElementById('editEmail').value = u.email;
        document.getElementById('editRole').value = u.role;
        document.getElementById('editStatus').value = u.status;
        document.getElementById('editUserModal').classList.add('active');
    };

    window.blockUser = function(id) {
        var u = allUsers.find(function(x) { return x.id === id; });
        if (!u) return;
        document.getElementById('blockModalTitle').textContent = 'Block User';
        document.getElementById('blockModalMessage').textContent = 'Are you sure you want to block this user?';
        document.getElementById('blockModalUserName').textContent = u.fullName;
        document.getElementById('blockUserId').value = id;
        document.getElementById('blockAction').value = 'block';
        document.getElementById('blockConfirmBtn').style.background = 'var(--danger)';
        document.getElementById('blockConfirmText').textContent = 'Block';
        document.getElementById('blockModal').classList.add('active');
    };

    window.unblockUser = function(id) {
        var u = allUsers.find(function(x) { return x.id === id; });
        if (!u) return;
        document.getElementById('blockModalTitle').textContent = 'Unblock User';
        document.getElementById('blockModalMessage').textContent = 'Are you sure you want to unblock this user?';
        document.getElementById('blockModalUserName').textContent = u.fullName;
        document.getElementById('blockUserId').value = id;
        document.getElementById('blockAction').value = 'unblock';
        document.getElementById('blockConfirmBtn').style.background = 'var(--success)';
        document.getElementById('blockConfirmText').textContent = 'Unblock';
        document.getElementById('blockModal').classList.add('active');
    };

    window.deleteUser = function(id) {
        var u = allUsers.find(function(x) { return x.id === id; });
        if (!u) return;
        if (!confirm('Are you sure you want to delete ' + u.fullName + '? This action cannot be undone.')) return;
        allUsers = allUsers.filter(function(x) { return x.id !== id; });
        render();
        showToast('User deleted successfully.');
    };

    window.closeModal = function(id) { document.getElementById(id).classList.remove('active'); };

    function showToast(msg) {
        var t = document.getElementById('toast');
        t.textContent = msg; t.className = 'toast show';
        setTimeout(function() { t.classList.remove('show'); }, 3000);
    }

    // ── Add User: ID generation ──

    function generateNextId() {
        var year = new Date().getFullYear();
        var maxSeq = 0;
        for (var i = 0; i < allUsers.length; i++) {
            var parts = allUsers[i].id.split('-');
            if (parts[0] === String(year)) {
                var seq = parseInt(parts[1], 10);
                if (seq > maxSeq) maxSeq = seq;
            }
        }
        var next = maxSeq + 1;
        var suffix = String(next).padStart(4, '0');
        return year + '-' + suffix;
    }

    // ── Add User: clear required-field errors ──

    function clearAllRequiredErrors() {
        var errs = document.querySelectorAll('#addUserModal [id$="-error"]');
        for (var i = 0; i < errs.length; i++) {
            if (/this field is required/i.test(errs[i].textContent || '')) {
                errs[i].parentNode && errs[i].parentNode.removeChild(errs[i]);
            }
        }
    }

    // ── Add User: duplicate checkers (admin paths) ──

    function checkEmailExistsAdmin(emailValue) {
        var emailInput = document.getElementById('email');
        if (!emailInput) return;
        if (emailValue === '' || emailValue.trim() === '') { clearErrorMessage('email'); return; }
        if (typeof validateEmail === 'function' && !validateEmail('email')) return;
        emailInput.setAttribute('data-validating', 'true');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../../server/check_email.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                emailInput.removeAttribute('data-validating');
                if (xhr.status == 200) {
                    try {
                        var resp = JSON.parse(xhr.responseText);
                        if (resp.exists === true) {
                            showErrorMessage('email', 'Email already exists. Please use a different email.');
                        } else {
                            var errEl = document.getElementById('email-error');
                            if (errEl && /already exists/i.test(errEl.textContent || '')) clearErrorMessage('email');
                        }
                    } catch (e) {}
                }
            }
        };
        xhr.send('email=' + encodeURIComponent(emailValue));
    }

    function checkUsernameExistsAdmin(usernameValue) {
        var userInput = document.getElementById('user');
        if (!userInput) return;
        if (usernameValue === '' || usernameValue.trim() === '') { clearErrorMessage('user'); return; }
        userInput.setAttribute('data-validating', 'true');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../../server/check_username.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                userInput.removeAttribute('data-validating');
                if (xhr.status == 200) {
                    try {
                        var resp = JSON.parse(xhr.responseText);
                        if (resp.exists === true) {
                            showErrorMessage('user', 'Username already exists. Please choose a different username.');
                        } else {
                            var errEl = document.getElementById('user-error');
                            if (errEl && /already exists/i.test(errEl.textContent || '')) clearErrorMessage('user');
                        }
                    } catch (e) {}
                }
            }
        };
        xhr.send('username=' + encodeURIComponent(usernameValue));
    }

    var passDupTimeoutId;
    function checkPasswordExistsAdmin(password) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../../server/check_password.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                try {
                    var resp = JSON.parse(xhr.responseText || '{}');
                    if (resp && resp.exists === true) {
                        showErrorMessage('pass', 'This password is already used. Please choose a different password.');
                    } else {
                        var errEl = document.getElementById('pass-error');
                        if (errEl && /already used/i.test(errEl.textContent || '')) clearErrorMessage('pass');
                    }
                } catch (e) {}
            }
        };
        xhr.send('password=' + encodeURIComponent(password));
    }

    // ── Add User: wire up duplicate checkers ──

    (function() {
        var emailEl = document.getElementById('email');
        var userEl = document.getElementById('user');
        var passEl = document.getElementById('pass');
        var emailTimeoutId, userTimeoutId;

        if (emailEl) {
            emailEl.addEventListener('input', function() {
                clearTimeout(emailTimeoutId);
                if (typeof validateEmail === 'function' && validateEmail('email')) {
                    var val = this.value;
                    emailTimeoutId = setTimeout(function() { checkEmailExistsAdmin(val); }, 500);
                }
            });
            emailEl.addEventListener('blur', function() {
                clearTimeout(emailTimeoutId);
                if (typeof validateEmail === 'function' && validateEmail('email')) {
                    checkEmailExistsAdmin(this.value);
                }
            });
        }

        if (userEl) {
            userEl.addEventListener('input', function() {
                clearTimeout(userTimeoutId);
                var raw = this.value;
                if (/^[a-z]{5,}_[0-9]+$/.test(raw)) {
                    var val = raw;
                    userTimeoutId = setTimeout(function() { checkUsernameExistsAdmin(val); }, 500);
                }
            });
            userEl.addEventListener('blur', function() {
                clearTimeout(userTimeoutId);
                if (/^[a-z]{5,}_[0-9]+$/.test(this.value)) {
                    checkUsernameExistsAdmin(this.value);
                }
            });
        }

        if (passEl) {
            passEl.addEventListener('input', function() {
                clearTimeout(passDupTimeoutId);
                var v = this.value || '';
                var hasUpper = /[A-Z]/.test(v);
                var hasLower = /[a-z]/.test(v);
                var hasDigit = /[0-9]/.test(v);
                var hasSpecial = /[^A-Za-z0-9]/.test(v);
                var strength = 'Weak';
                if (v.length >= 8 && hasUpper && hasLower && hasDigit && hasSpecial) strength = 'Strong';
                else if (v.length >= 6 && ((hasUpper && hasLower) || (hasUpper && hasDigit) || (hasLower && hasDigit))) strength = 'Medium';
                if (strength !== 'Weak' && v.length >= 8 && !/\s/.test(v)) {
                    passDupTimeoutId = setTimeout(function() { checkPasswordExistsAdmin(v); }, 500);
                }
            });
        }
    })();

    // ── Add User: open modal + submit ──

    document.getElementById('btnAddUser').addEventListener('click', function() {
        var fields = ['fname', 'mname', 'lname', 'ename', 'bday', 'age', 'sex', 'email',
            'street', 'brgy', 'city', 'province', 'country', 'zipcode',
            'user', 'pass', 'repass'];
        for (var i = 0; i < fields.length; i++) {
            var el = document.getElementById(fields[i]);
            if (el) el.value = '';
            var err = document.getElementById(fields[i] + '-error');
            if (err && err.parentNode) err.parentNode.removeChild(err);
        }
        var hint1 = document.getElementById('pass-strength');
        if (hint1) hint1.textContent = '';
        var hint2 = document.getElementById('repass-match');
        if (hint2) hint2.textContent = '';
        var eyeIcon = document.getElementById('eyeicon-register');
        if (eyeIcon) { eyeIcon.className = 'fa-solid fa-eye-slash'; }
        var passField = document.getElementById('pass');
        if (passField) passField.type = 'password';
        document.getElementById('id').value = 'Loading...';
        document.getElementById('addUserModal').classList.add('active');

        fetch('../../server/generate_id.php')
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('id').value = data.id || '0000-0000';
            })
            .catch(function() {
                document.getElementById('id').value = generateNextId();
            });
    });

    document.getElementById('addUserModal').addEventListener('focusin', function() {
        clearAllRequiredErrors();
    });
    document.getElementById('addUserModal').addEventListener('input', function() {
        clearAllRequiredErrors();
    });
    document.getElementById('addUserModal').addEventListener('change', function() {
        clearAllRequiredErrors();
    });

    document.getElementById('addUserSubmit').addEventListener('click', function() {
        var prevErrors = document.querySelectorAll('#addUserModal [id$="-error"]');
        for (var c = 0; c < prevErrors.length; c++) {
            prevErrors[c].parentNode && prevErrors[c].parentNode.removeChild(prevErrors[c]);
        }
        var requiredFields = ['fname','lname','bday','age','sex','email','street','brgy','city','province','country','zipcode','user','role','pass','repass'];
        var anyEmpty = false;
        for (var i = 0; i < requiredFields.length; i++) {
            var f = document.getElementById(requiredFields[i]);
            if (!f) continue;
            var v = (f.value || '').trim();
            if (f.tagName === 'SELECT') v = f.value;
            if (v === '') {
                showErrorMessage(requiredFields[i], 'This field is required');
                anyEmpty = true;
            }
        }
        if (anyEmpty) return;

        var fn = document.getElementById('fname').value.trim();
        var mn = document.getElementById('mname').value.trim();
        var ln = document.getElementById('lname').value.trim();
        var en = document.getElementById('ename').value.trim();
        var fullName = fn + (mn ? ' ' + mn : '') + ' ' + ln + (en ? ' ' + en : '');
        var newUser = {
            id: document.getElementById('id').value.trim(),
            username: document.getElementById('user').value.trim(),
            fullName: fullName.replace(/\s+/g, ' ').trim(),
            email: document.getElementById('email').value.trim(),
            role: document.getElementById('role').value,
            status: 'active'
        };
        allUsers.unshift(newUser);
        closeModal('addUserModal');
        state.page = 1;
        render();
        showToast('User added successfully.');
    });

    // ── Search / Filter wiring ──

    document.getElementById('btnSearchUsers').addEventListener('click', function() {
        state.filters.search = document.getElementById('userSearch').value.trim();
        state.filters.role = document.getElementById('roleFilter').value;
        state.filters.status = document.getElementById('statusFilter').value;
        state.page = 1; render();
    });
    document.getElementById('userSearch').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') { state.filters.search = this.value.trim(); state.page = 1; render(); }
    });
    document.getElementById('roleFilter').addEventListener('change', function() { state.filters.role = this.value; state.page = 1; render(); });
    document.getElementById('statusFilter').addEventListener('change', function() { state.filters.status = this.value; state.page = 1; render(); });

    // ── Edit / Block confirm handlers ──

    document.getElementById('editUserSave').addEventListener('click', function() {
        var id = document.getElementById('editUserId').value;
        var u = allUsers.find(function(x) { return x.id === id; });
        if (u) {
            u.fullName = document.getElementById('editFullName').value;
            u.email = document.getElementById('editEmail').value;
            u.role = document.getElementById('editRole').value;
            u.status = document.getElementById('editStatus').value;
        }
        closeModal('editUserModal');
        render();
        showToast('User updated successfully.');
    });

    document.getElementById('blockConfirmBtn').addEventListener('click', function() {
        var id = document.getElementById('blockUserId').value;
        var action = document.getElementById('blockAction').value;
        var u = allUsers.find(function(x) { return x.id === id; });
        if (u) u.status = action === 'block' ? 'blocked' : 'active';
        closeModal('blockModal');
        render();
        showToast(action === 'block' ? 'User blocked.' : 'User unblocked.');
    });

    // ── Modal overlay + Escape ──

    document.querySelectorAll('.modal-overlay').forEach(function(o) {
        o.addEventListener('click', function(e) { if (e.target === o) o.classList.remove('active'); });
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.active').forEach(function(m) { m.classList.remove('active'); });
    });

    // ── Initial render ──

    render();
});
