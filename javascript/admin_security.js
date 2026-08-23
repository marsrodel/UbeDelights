/**
 * Admin Security JavaScript
 * Handles modals, filters, toast notifications, and user management interactions
 */

(function() {
    'use strict';

    // Global toast element
    var toast = document.getElementById('toast');

    function showToast(message, type) {
        if (!toast) return;
        toast.textContent = message;
        toast.className = 'toast' + (type ? ' ' + type : '');
        requestAnimationFrame(function() { toast.classList.add('show'); });
        setTimeout(function() { toast.classList.remove('show'); }, 3000);
    }
    window.adminToast = showToast;

    // ==========================================
    // MODAL HELPERS
    // ==========================================
    function openModal(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        var modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    // Close modal on overlay click
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.show').forEach(function(modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            });
        }
    });

    // Close buttons
    document.querySelectorAll('.modal-close, [id$="ModalClose"], [id$="ModalCancel"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var modal = btn.closest('.modal-overlay');
            if (modal) {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        });
    });

    // ==========================================
    // TOAST NOTIFICATION
    // ==========================================
    function showToast(message, type) {
        var toast = document.getElementById('toast');
        if (!toast) return;
        toast.textContent = message;
        toast.className = 'toast' + (type ? ' ' + type : '');
        requestAnimationFrame(function() { toast.classList.add('show'); });
        setTimeout(function() { toast.classList.remove('show'); }, 3000);
    }
    window.adminToast = showToast;

    // ==========================================
    // COMMON MODAL HANDLERS
    // ==========================================
    function setupModalClose(modalId) {
        var modal = document.getElementById(modalId);
        if (!modal) return;
        
        var closeBtn = modal.querySelector('.modal-close');
        var cancelBtn = modal.querySelector('[id$="ModalCancel"]');
        var overlay = modal;
        
        [closeBtn, modal.querySelector('[id$="ModalCancel"]')].forEach(function(btn) {
            if (btn) btn.addEventListener('click', function() { closeModal(modal.id); });
        });
        
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeModal(modal.id);
        });
    }

    // ==========================================
    // ADD USER MODAL
    // ==========================================
    var btnAddUser = document.getElementById('btnAddUser');
    var addUserModal = document.getElementById('addUserModal');
    var addUserForm = document.getElementById('addUserForm');
    var addUserModalClose = document.getElementById('addUserModalClose');
    var addUserModalCancel = document.getElementById('addUserModalCancel');
    var addUserSubmitBtn = document.getElementById('addUserSubmitBtn');

    if (btnAddUser && addUserModal) {
        btnAddUser.addEventListener('click', function() {
            addUserForm.reset();
            document.getElementById('addUserModalTitle').textContent = 'Add New User';
            addUserSubmitBtn.textContent = 'Add User';
            document.getElementById('addUserModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        });
    }

    // ==========================================
    // EDIT USER MODAL
    // ==========================================
    var editUserModal = document.getElementById('editUserModal');
    var editUserForm = document.getElementById('editUserForm');
    var editUserModalClose = document.getElementById('editUserModalClose');
    var editUserModalCancel = document.getElementById('editUserModalCancel');
    var editUserSubmitBtn = document.getElementById('editUserSubmitBtn');
    var editPasswordSection = document.getElementById('editPasswordSection');

    // ==========================================
    // VIEW USER MODAL
    // ==========================================
    var viewUserModal = document.getElementById('viewUserModal');
    var viewUserBody = document.getElementById('viewUserBody');
    var viewUserModalClose = document.getElementById('viewUserModalClose');
    var viewUserModalCloseBtn = document.getElementById('viewUserModalCloseBtn');
    var viewEditBtn = document.getElementById('viewEditBtn');

    // ==========================================
    // BLOCK/UNBLOCK MODAL
    // ==========================================
    var blockModal = document.getElementById('blockModal');
    var blockModalClose = document.getElementById('blockModalClose');
    var blockModalCancel = document.getElementById('blockModalCancel');
    var blockConfirmBtn = document.getElementById('blockConfirmBtn');
    var blockActionText = document.getElementById('blockActionText');
    var blockUserName = document.getElementById('blockUserName');
    var blockUserId = document.getElementById('blockUserId');
    var blockActionType = document.getElementById('blockActionType');
    var blockConfirmText = document.getElementById('blockConfirmText');

    // ==========================================
    // DELETE MODAL
    // ==========================================
    var deleteModal = document.getElementById('deleteModal');
    var deleteModalClose = document.getElementById('deleteModalClose');
    var deleteModalCancel = document.getElementById('deleteModalCancel');
    var deleteConfirmBtn = document.getElementById('deleteConfirmBtn');
    var deleteUserName = document.getElementById('deleteUserName');
    var deleteUserId = document.getElementById('deleteUserId');
    var deleteReason = document.getElementById('deleteReason');

    // ==========================================
    // APPROVAL MODAL
    // ==========================================
    var approvalModal = document.getElementById('approvalModal');
    var approvalModalClose = document.getElementById('approvalModalClose');
    var approvalModalCancel = document.getElementById('approvalModalCancel');
    var approvalConfirmBtn = document.getElementById('approvalConfirmBtn');
    var approvalMessage = document.getElementById('approvalMessage');
    var approvalUserName = document.getElementById('approvalUserName');
    var approvalUserId = document.getElementById('approvalUserId');
    var approvalAction = document.getElementById('approvalAction');
    var approvalConfirmText = document.getElementById('approvalConfirmText');

    // ==========================================
    // VIEW PENDING MODAL
    // ==========================================
    var viewPendingModal = document.getElementById('viewPendingModal');
    var viewPendingBody = document.getElementById('viewPendingBody');
    var viewPendingModalClose = document.getElementById('viewPendingModalClose');
    var viewPendingModalCloseBtn = document.getElementById('viewPendingModalCloseBtn');

    // ==========================================
    // COMMON MODAL SETUP
    // ==========================================
    [
        { modal: addUserModal, close: addUserModalClose, cancel: addUserModalCancel },
        { modal: editUserModal, close: editUserModalClose, cancel: editUserModalCancel },
        { modal: viewUserModal, close: viewUserModalClose, cancel: viewUserModalCloseBtn },
        { modal: blockModal, close: blockModalClose, cancel: blockModalCancel },
        { modal: deleteModal, close: deleteModalClose, cancel: deleteModalCancel },
        { modal: approvalModal, close: approvalModalClose, cancel: approvalModalCancel },
        { modal: viewPendingModal, close: viewPendingModalClose, cancel: viewPendingModalCloseBtn }
    ].forEach(function(m) {
        if (m.modal) {
            [m.close, m.cancel].forEach(function(btn) {
                if (btn) btn.addEventListener('click', function() { closeModal(m.modal.id); });
            });
            m.modal.addEventListener('click', function(e) {
                if (e.target === m.modal) closeModal(m.modal.id);
            });
        }
    });

    // Also setup approval modal
    if (approvalModal) {
        [approvalModalClose, approvalModalCancel].forEach(function(btn) {
            if (btn) btn.addEventListener('click', function() { closeModal('approvalModal'); });
        });
        approvalModal.addEventListener('click', function(e) {
            if (e.target === approvalModal) closeModal('approvalModal');
        });
    }

    // ==========================================
    // IMAGE PREVIEW
    // ==========================================
    var productImageInput = document.getElementById('productImage');
    var imagePreview = document.getElementById('imagePreview');
    var previewPlaceholder = document.querySelector('.preview-placeholder');

    if (productImageInput) {
        productImageInput.addEventListener('change', function() {
            var file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                    if (previewPlaceholder) previewPlaceholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.src = '';
                imagePreview.style.display = 'none';
                var ph = document.querySelector('.preview-placeholder');
                if (ph) ph.style.display = 'block';
            }
        });
    }

    // ==========================================
    // FORM VALIDATION
    // ==========================================
    function validateRequired(form, fields) {
        for (var i = 0; i < fields.length; i++) {
            var field = fields[i];
            var input = document.getElementById(field);
            if (input && !input.value.trim()) {
                return field + ' is required';
            }
        }
        return null;
    }

    function validateEmail(email) {
        var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function validatePasswordMatch(pass, confirm) {
        return pass === confirm;
    }

    // Password strength indicator
    function updatePasswordStrength(inputId, barId, labelId) {
        var input = document.getElementById(inputId);
        var bar = document.getElementById(barId);
        var label = document.getElementById(labelId);
        if (!input || !bar || !label) return;

        input.addEventListener('input', function() {
            var val = this.value;
            var score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[!@#$%^&*()_+]/.test(val)) score++;

            var levels = [
                { w: '20%', c: '#e17055', t: 'Weak' },
                { w: '45%', c: '#fdcb6e', t: 'Fair' },
                { w: '70%', c: '#eab308', t: 'Good' },
                { w: '100%', c: '#00b894', t: 'Strong' }
            ];
            var l = levels[Math.min(score - 1, 3)] || levels[0];
            bar.style.width = l.w;
            bar.style.background = l.c;
            label.textContent = l.t + ' password';
            label.style.color = l.c;
        });
    }

    // ==========================================
    // PASSWORD TOGGLE
    // ==========================================
    function togglePassword(fieldId, toggleEl) {
        var input = document.getElementById(fieldId);
        if (!input) return;
        input.type = input.type === 'password' ? 'text' : 'password';
        var eyeIcon = toggleEl.querySelector('.eye-icon');
        var eyeOffIcon = toggleEl.querySelector('.eye-off-icon');
        if (eyeIcon && eyeOffIcon) {
            eyeIcon.style.display = input.type === 'text' ? 'none' : 'block';
            eyeOffIcon.style.display = input.type === 'text' ? 'block' : 'none';
        }
    }
    window.togglePassword = togglePassword;

    // ==========================================
    // CONFIRM DIALOG
    // ==========================================
    function confirmAction(message, callback) {
        if (confirm(message)) {
            callback();
        }
    }

    // ==========================================
    // EXPORT COMMON FUNCTIONS
    // ==========================================
    window.adminCloseModal = closeModal;
    window.adminOpenModal = openModal;
    window.adminShowToast = showToast;
    window.adminConfirm = confirmAction;
    window.adminValidateRequired = validateRequired;
    window.adminValidateEmail = validateEmail;
    window.adminValidatePasswordMatch = validatePasswordMatch;
    window.adminUpdatePasswordStrength = updatePasswordStrength;
    window.adminTogglePassword = togglePassword;