(function() {
    'use strict';

    var SERVER = '../server/complete_account_handler.php';

    // ════════════════════════════════════════════
    //  HELPERS
    // ════════════════════════════════════════════
    function showStep(n) {
        document.querySelectorAll('.ca-step').forEach(function(s) { s.style.display = 'none'; s.classList.remove('active'); });
        var target = document.getElementById('step-' + n);
        if (target) { target.style.display = 'block'; target.classList.add('active'); }
    }

    function showBlockError(elId, msg) {
        var el = document.getElementById(elId);
        if (el) { el.textContent = msg; }
    }

    function clearBlockError(elId) {
        var el = document.getElementById(elId);
        if (el) { el.textContent = ''; }
    }

    function showSuccessModal(message, callback) {
        document.getElementById('successModalTitle').textContent = 'Success';
        document.getElementById('successModalMessage').textContent = message;
        document.getElementById('successModal').classList.add('active');
        document.getElementById('successModalOkBtn').onclick = function() {
            document.getElementById('successModal').classList.remove('active');
            if (callback) callback();
        };
    }

    function ajaxPost(data, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', SERVER, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var res;
                try { res = JSON.parse(xhr.responseText); } catch(e) { res = { success: false, message: 'Invalid response' }; }
                callback(res);
            } else {
                callback({ success: false, message: 'Server error. Please try again.' });
            }
        };
        xhr.onerror = function() { callback({ success: false, message: 'Network error. Please try again.' }); };
        xhr.send(data);
    }

    // ════════════════════════════════════════════
    //  STEP 1: OTP VERIFICATION
    // ════════════════════════════════════════════
    var otpBoxes = document.querySelectorAll('.otp-box');
    var otpCodeVisible = false;
    var timerInterval = null;

    function initOTPInputs() {
        otpBoxes.forEach(function(box) {
            box.addEventListener('input', function(e) {
                var val = this.value.replace(/[^0-9]/g, '');
                this.value = val;
                if (val && this.dataset.index < 5) {
                    otpBoxes[parseInt(this.dataset.index) + 1].focus();
                }
            });
            box.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !this.value && this.dataset.index > 0) {
                    otpBoxes[parseInt(this.dataset.index) - 1].focus();
                }
            });
            box.addEventListener('paste', function(e) {
                e.preventDefault();
                var paste = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
                var start = parseInt(this.dataset.index);
                for (var i = 0; i < paste.length && start + i < 6; i++) {
                    otpBoxes[start + i].value = paste[i];
                }
                var lastIdx = Math.min(start + paste.length - 1, 5);
                otpBoxes[lastIdx].focus();
            });
            box.addEventListener('focus', function() { this.select(); });
        });
    }

    function getOTPCode() {
        return Array.from(otpBoxes).map(function(b) { return b.value; }).join('');
    }

    function toggleOTPVisibility() {
        otpCodeVisible = !otpCodeVisible;
        otpBoxes.forEach(function(b) { b.type = otpCodeVisible ? 'text' : 'password'; });
        document.getElementById('showCodeBtn').textContent = otpCodeVisible ? 'Hide code' : 'Show code';
    }

    function startOTPTimer(expiryTimestamp) {
        if (timerInterval) clearInterval(timerInterval);
        var display = document.getElementById('otpTimerDisplay');
        var timerEl = display ? display.closest('.otp-timer') : null;

        function update() {
            var now = Math.floor(Date.now() / 1000);
            var remaining = expiryTimestamp - now;
            if (remaining <= 0) {
                clearInterval(timerInterval);
                display.textContent = '00:00';
                if (timerEl) timerEl.classList.add('expired');
                var verifyBtn = document.getElementById('verifyOtpBtn');
                if (verifyBtn) { verifyBtn.disabled = true; }
                var otpErr = document.getElementById('otpError');
                if (otpErr) { otpErr.textContent = 'OTP has expired. Please resend.'; otpErr.classList.add('show'); }
                return;
            }
            var m = Math.floor(remaining / 60);
            var s = remaining % 60;
            display.textContent = String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        }
        update();
        timerInterval = setInterval(update, 1000);
    }

    function handleVerifyOTP() {
        var code = getOTPCode();
        var otpErr = document.getElementById('otpError');
        if (code.length < 6) {
            if (otpErr) { otpErr.textContent = 'Please enter the 6-digit OTP.'; otpErr.classList.add('show'); }
            return;
        }
        if (otpErr) { otpErr.textContent = ''; otpErr.classList.remove('show'); }
        var btn = document.getElementById('verifyOtpBtn');
        btn.disabled = true;
        btn.textContent = 'Verifying...';

        var fd = new FormData();
        fd.append('action', 'verify_otp');
        fd.append('otp_code', code);

        ajaxPost(fd, function(res) {
            btn.disabled = false;
            btn.textContent = 'Verify OTP';
            if (res.success) {
                if (timerInterval) clearInterval(timerInterval);
                document.getElementById('step1-section').style.display = 'none';
                document.getElementById('steps23-section').style.display = 'flex';
                showStep(2);
            } else {
                if (otpErr) { otpErr.textContent = res.message; otpErr.classList.add('show'); }
            }
        });
    }

    function handleResendOTP() {
        var otpErr = document.getElementById('otpError');
        if (otpErr) { otpErr.textContent = ''; otpErr.classList.remove('show'); }
        var btn = document.getElementById('resendBtn');
        btn.disabled = true;
        btn.textContent = 'Sending...';

        var fd = new FormData();
        fd.append('action', 'resend_otp');

        ajaxPost(fd, function(res) {
            btn.disabled = false;
            btn.textContent = 'Resend OTP';
            if (res.success) {
                otpBoxes.forEach(function(b) { b.value = ''; });
                otpBoxes[0].focus();
                startOTPTimer(Math.floor(Date.now() / 1000) + 300);
                if (otpErr) {
                    otpErr.textContent = 'A new OTP has been sent to your email.';
                    otpErr.style.color = '#10b981';
                    otpErr.classList.add('show');
                    setTimeout(function() { otpErr.classList.remove('show'); otpErr.style.color = ''; }, 3000);
                }
            } else {
                if (otpErr) { otpErr.textContent = res.message; otpErr.classList.add('show'); }
            }
        });
    }

    // ════════════════════════════════════════════
    //  STEP 2: PERSONAL INFORMATION
    //  Uses global functions from register.js:
    //  - validateName(fieldId)
    //  - validateExtensionName(fieldId)
    //  - validateNoSpacePlace(fieldId, label)
    //  - validateZip4(fieldId)
    //  - calculateAge()
    //  - showErrorMessage(fieldId, message)
    //  - clearErrorMessage(fieldId)
    // ════════════════════════════════════════════
    function validateStep2() {
        var valid = true;
        var firstEmpty = null;

        var requiredFields = [
            { id: 'fname', label: 'First Name' },
            { id: 'lname', label: 'Last Name' },
            { id: 'bday', label: 'Date of Birth' },
            { id: 'sex', label: 'Sex' },
            { id: 'street', label: 'Purok/Street' },
            { id: 'brgy', label: 'Barangay' },
            { id: 'city', label: 'City/Municipality' },
            { id: 'province', label: 'Province' },
            { id: 'country', label: 'Country' },
            { id: 'zipcode', label: 'Zip Code' }
        ];

        requiredFields.forEach(function(f) {
            var el = document.getElementById(f.id);
            if (!el) return;
            var val = el.value.trim();
            if (!val) {
                clearErrorMessage(f.id);
                showErrorMessage(f.id, 'This field is required');
                valid = false;
                if (!firstEmpty) firstEmpty = el;
            }
        });

        if (!valid) {
            return false;
        }

        // Validate name fields using register.js functions
        if (!validateName('fname')) { document.getElementById('fname').focus(); return false; }
        if (!validateName('lname')) { document.getElementById('lname').focus(); return false; }
        if (!validateExtensionName('ename')) { document.getElementById('ename').focus(); return false; }

        // Validate address fields using register.js functions
        var placeFields = [
            { id: 'street', label: 'Purok/Street' },
            { id: 'brgy', label: 'Barangay' },
            { id: 'city', label: 'City/Municipality' },
            { id: 'province', label: 'Province' },
            { id: 'country', label: 'Country' }
        ];
        for (var i = 0; i < placeFields.length; i++) {
            if (!validateNoSpacePlace(placeFields[i].id, placeFields[i].label)) {
                document.getElementById(placeFields[i].id).focus();
                return false;
            }
        }

        // Validate zip code using register.js function
        if (!validateZip4('zipcode')) { document.getElementById('zipcode').focus(); return false; }

        // Validate bday
        var bday = document.getElementById('bday').value;
        if (bday) {
            var dob = new Date(bday);
            var today = new Date();
            if (dob > today) {
                showErrorMessage('bday', 'Birthday should not be in the future');
                document.getElementById('bday').focus();
                return false;
            }
            var age = today.getFullYear() - dob.getFullYear();
            var m = today.getMonth() - dob.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
            if (age < 18) {
                showErrorMessage('bday', 'You must be at least 18 years old to register, please try again.');
                document.getElementById('bday').focus();
                return false;
            }
        }

        // Validate sex
        var sex = document.getElementById('sex').value;
        if (!sex) {
            showErrorMessage('sex', 'This field is required');
            document.getElementById('sex').focus();
            return false;
        }

        return true;
    }

    function submitStep2() {
        if (!validateStep2()) return;

        var btn = document.getElementById('step2NextBtn');
        btn.disabled = true;
        btn.innerHTML = 'Saving... <i class="fa-solid fa-spinner fa-spin"></i>';

        var fd = new FormData();
        fd.append('action', 'save_personal');
        fd.append('fname', document.getElementById('fname').value.trim());
        fd.append('mname', document.getElementById('mname').value.trim());
        fd.append('lname', document.getElementById('lname').value.trim());
        fd.append('ename', document.getElementById('ename').value.trim());
        fd.append('bday', document.getElementById('bday').value);
        fd.append('sex', document.getElementById('sex').value);
        fd.append('street', document.getElementById('street').value.trim());
        fd.append('brgy', document.getElementById('brgy').value.trim());
        fd.append('city', document.getElementById('city').value.trim());
        fd.append('province', document.getElementById('province').value.trim());
        fd.append('country', document.getElementById('country').value.trim());
        fd.append('zipcode', document.getElementById('zipcode').value.trim());

        ajaxPost(fd, function(res) {
            btn.disabled = false;
            btn.innerHTML = 'Next <i class="fa-solid fa-arrow-right"></i>';
            if (res.success) {
                document.getElementById('indicator-step2').classList.remove('active');
                document.getElementById('indicator-step2').classList.add('completed');
                document.getElementById('indicator-step2').querySelector('span').textContent = '\u2713';
                document.getElementById('indicator-step3').classList.add('active');
                showStep(3);
            } else {
                showBlockError('step2Error', res.message);
            }
        });
    }

    // ════════════════════════════════════════════
    //  STEP 3: PASSWORD & SECURITY QUESTIONS
    //  Uses global functions from register.js:
    //  - getPasswordStrength(p)
    //  - showErrorMessage(fieldId, message)
    //  - clearErrorMessage(fieldId)
    // ════════════════════════════════════════════
    var QUESTIONS_1 = [
        'What was the name of your first pet?',
        'What was the make of your first vehicle?',
        'What is your favorite travel destination?'
    ];
    var QUESTIONS_2 = [
        'What is your favorite flower?',
        'What is your favorite subject in school?',
        'What is your favorite color?'
    ];
    var QUESTIONS_3 = [
        "What is your oldest sibling's first name?",
        "What is your best friend's name?",
        "What is your favorite childhood nickname?"
    ];

    function populateSQ() {
        var s1 = document.getElementById('q1');
        var s2 = document.getElementById('q2');
        var s3 = document.getElementById('q3');
        function populate(sel, questions) {
            if (!sel) return;
            sel.innerHTML = '<option value="" disabled selected>-Select a question-</option>' + questions.map(function(q) {
                return '<option value="' + q.replace(/"/g, '&quot;') + '">' + q + '</option>';
            }).join('');
        }
        populate(s1, QUESTIONS_1);
        populate(s2, QUESTIONS_2);
        populate(s3, QUESTIONS_3);

        function refreshDisables() {
            if (!s1 || !s2 || !s3) return;
            var chosen = [s1.value, s2.value, s3.value].filter(function(v) { return v && v !== ''; });
            [s1, s2, s3].forEach(function(sel) {
                if (!sel) return;
                Array.prototype.forEach.call(sel.options, function(opt) {
                    if (!opt.value) return;
                    opt.disabled = chosen.indexOf(opt.value) !== -1 && sel.value !== opt.value;
                });
            });
        }
        [s1, s2, s3].forEach(function(sel) { sel && sel.addEventListener('change', refreshDisables); });
        refreshDisables();
    }

    function getPasswordStrength(p) {
        var s = (p || '').replace(/\s+/g, '');
        if (!s) return '';
        var types = 0;
        if (/[a-z]/.test(s)) types++;
        if (/[A-Z]/.test(s)) types++;
        if (/[0-9]/.test(s)) types++;
        if (/[^A-Za-z0-9]/.test(s)) types++;
        if (s.length < 8 || types < 2) return 'Weak';
        if (s.length >= 12 && types >= 4) return 'Strong';
        return 'Medium';
    }

    function initPasswordValidation() {
        var passEl = document.getElementById('pass');
        var repassEl = document.getElementById('repass');
        var passStrengthSpan = document.getElementById('pass-strength');
        var repassMatchSpan = document.getElementById('repass-match');

        function hasSpace(str) { return /\s/.test(str || ''); }

        function updatePasswordStrength() {
            if (!passStrengthSpan || !passEl) return;
            var val = passEl.value || '';
            var strength = getPasswordStrength(val);
            if (!strength) {
                passStrengthSpan.textContent = '';
                passStrengthSpan.style.color = '';
                passStrengthSpan.style.fontSize = '12px';
                if (hasSpace(val)) {
                    showErrorMessage('pass', 'Spaces are not allowed in password.');
                    return;
                }
                clearErrorMessage('pass');
                return;
            }
            passStrengthSpan.textContent = strength ? (strength + ' Password') : '';
            passStrengthSpan.style.color = strength === 'Strong' ? '#16a34a' : (strength === 'Medium' ? '#f59e0b' : '#dc2626');
            passStrengthSpan.style.fontSize = '11px';

            if (hasSpace(val)) { showErrorMessage('pass', 'Spaces are not allowed in password.'); return; }
            if (val.length < 8) { showErrorMessage('pass', 'Password must be at least 8 characters long.'); return; }
            if (val.length > 50) { showErrorMessage('pass', 'Password cannot exceed 50 characters.'); return; }
            if (!/[A-Z]/.test(val)) { showErrorMessage('pass', 'Password must contain at least 1 uppercase letter.'); return; }
            if (!/[a-z]/.test(val)) { showErrorMessage('pass', 'Password must contain at least 1 lowercase letter.'); return; }
            if (!/[0-9]/.test(val)) { showErrorMessage('pass', 'Password must contain at least 1 number.'); return; }
            if (!/[^A-Za-z0-9]/.test(val)) { showErrorMessage('pass', 'Password must contain at least 1 special character.'); return; }

            if (strength === 'Strong') {
                passStrengthSpan.textContent = 'Strong Password';
                passStrengthSpan.style.color = '#16a34a';
            } else if (strength === 'Medium') {
                passStrengthSpan.textContent = 'Medium Password';
                passStrengthSpan.style.color = '#f59e0b';
            } else {
                passStrengthSpan.textContent = 'Weak Password';
                passStrengthSpan.style.color = '#dc2626';
            }
            passStrengthSpan.style.fontSize = '11px';
            clearErrorMessage('pass');
        }

        function updatePasswordMatch() {
            if (!repassMatchSpan || !passEl || !repassEl) return;
            var p1 = passEl.value || '';
            var p2 = repassEl.value || '';
            if (!p2) { repassMatchSpan.textContent = ''; repassMatchSpan.style.color = ''; repassMatchSpan.style.fontSize = '11px'; return; }
            if (p1 === p2) { repassMatchSpan.textContent = 'Password Matched'; repassMatchSpan.style.color = '#16a34a'; repassMatchSpan.style.fontSize = '10px'; }
            else { repassMatchSpan.textContent = 'Password does not match'; repassMatchSpan.style.color = '#dc2626'; repassMatchSpan.style.fontSize = '10px'; }
        }

        function validatePasswordMismatchError() {
            if (!passEl || !repassEl) return;
            var p1 = passEl.value || '';
            var p2 = repassEl.value || '';
            if (p2 && p1 !== p2) {
                showErrorMessage('repass', '');
            } else {
                var err = document.getElementById('repass-error');
                if (err && /password does not match/i.test(err.textContent || '')) {
                    err.parentNode && err.parentNode.removeChild(err);
                }
            }
        }

        if (passEl) {
            passEl.addEventListener('input', function() {
                updatePasswordStrength();
                updatePasswordMatch();
                validatePasswordMismatchError();
            });
        }

        if (repassEl) {
            repassEl.addEventListener('input', function() {
                if (((this.value || '').trim() === '')) {
                    clearErrorMessage('repass');
                }
                updatePasswordMatch();
                validatePasswordMismatchError();
            });
        }
    }

    function initEyeToggles() {
        ['eyeicon-pass', 'eyeicon-repass', 'eyeicon-a1', 'eyeicon-a2', 'eyeicon-a3'].forEach(function(id) {
            var eye = document.getElementById(id);
            if (!eye) return;
            eye.onclick = function() {
                var input = eye.previousElementSibling;
                if (!input) return;
                if (input.type === 'password') {
                    input.type = 'text';
                    eye.classList.remove('fa-eye-slash');
                    eye.classList.add('fa-eye');
                } else {
                    input.type = 'password';
                    eye.classList.remove('fa-eye');
                    eye.classList.add('fa-eye-slash');
                }
            };
        });
    }

    function validateAnswer(fieldId) {
        var el = document.getElementById(fieldId);
        if (!el) return true;
        var val = el.value.trim();
        clearErrorMessage(fieldId);
        if (!val) {
            showErrorMessage(fieldId, 'This field is required');
            return false;
        }
        if (val.length < 3) {
            showErrorMessage(fieldId, 'Must be at least 3 characters');
            return false;
        }
        if (val.length > 50) {
            showErrorMessage(fieldId, 'Must not exceed 50 characters');
            return false;
        }
        if (/^[.\s]/.test(val)) {
            showErrorMessage(fieldId, 'Don\'t start with a space or dot');
            return false;
        }
        return true;
    }

    function validateStep3() {
        var valid = true;
        var firstEmpty = null;

        var passEl = document.getElementById('pass');
        var repassEl = document.getElementById('repass');

        clearErrorMessage('pass');
        clearErrorMessage('repass');

        if (!passEl.value) {
            showErrorMessage('pass', 'This field is required');
            valid = false;
            if (!firstEmpty) firstEmpty = passEl;
        } else {
            var strength = getPasswordStrength(passEl.value);
            if (strength === 'Weak') {
                showErrorMessage('pass', 'Password is too weak. Use 8+ chars with uppercase, lowercase, number, and special character.');
                valid = false;
                if (!firstEmpty) firstEmpty = passEl;
            }
        }

        if (!repassEl.value) {
            showErrorMessage('repass', 'This field is required');
            valid = false;
            if (!firstEmpty) firstEmpty = repassEl;
        } else if (passEl.value && repassEl.value !== passEl.value) {
            showErrorMessage('repass', 'Passwords do not match');
            valid = false;
            if (!firstEmpty) firstEmpty = repassEl;
        }

        ['q1', 'q2', 'q3'].forEach(function(qId) {
            var el = document.getElementById(qId);
            clearErrorMessage(qId);
            if (!el.value) {
                showErrorMessage(qId, 'Please select a question');
                valid = false;
                if (!firstEmpty) firstEmpty = el;
            }
        });

        ['a1', 'a2', 'a3'].forEach(function(aId) {
            if (!validateAnswer(aId)) {
                valid = false;
                var el = document.getElementById(aId);
                if (el && !firstEmpty) firstEmpty = el;
            }
        });

        var q1 = document.getElementById('q1').value;
        var q2 = document.getElementById('q2').value;
        var q3 = document.getElementById('q3').value;
        if (q1 && q2 && q3) {
            if (new Set([q1, q2, q3]).size < 3) {
                showBlockError('step3Error', 'Security questions must be unique.');
                valid = false;
            }
        }

        return valid;
    }

    function submitStep3() {
        if (!validateStep3()) return;

        var btn = document.getElementById('completeSetupBtn');
        btn.disabled = true;
        btn.textContent = 'Setting up...';

        var fd = new FormData();
        fd.append('action', 'complete_setup');
        fd.append('pass', document.getElementById('pass').value);
        fd.append('q1', document.getElementById('q1').value);
        fd.append('a1', document.getElementById('a1').value.trim());
        fd.append('q2', document.getElementById('q2').value);
        fd.append('a2', document.getElementById('a2').value.trim());
        fd.append('q3', document.getElementById('q3').value);
        fd.append('a3', document.getElementById('a3').value.trim());

        ajaxPost(fd, function(res) {
            btn.disabled = false;
            btn.textContent = 'Complete Setup';
            if (res.success) {
                showSuccessModal(res.message || 'Account setup completed successfully!', function() {
                    window.location.href = res.redirect || './index.php';
                });
            } else {
                showBlockError('step3Error', res.message);
            }
        });
    }

    // ════════════════════════════════════════════
    //  INITIALIZATION
    // ════════════════════════════════════════════
    document.addEventListener('DOMContentLoaded', function() {
        initOTPInputs();
        initPasswordValidation();
        initEyeToggles();
        populateSQ();

        var expiryTs = parseInt(document.getElementById('otpTimerDisplay').getAttribute('data-expiry'), 10);
        if (expiryTs) startOTPTimer(expiryTs);

        document.getElementById('showCodeBtn').addEventListener('click', toggleOTPVisibility);
        document.getElementById('verifyOtpBtn').addEventListener('click', handleVerifyOTP);
        document.getElementById('resendBtn').addEventListener('click', handleResendOTP);
        document.getElementById('step2NextBtn').addEventListener('click', submitStep2);
        document.getElementById('step3BackBtn').addEventListener('click', function() {
            document.getElementById('indicator-step3').classList.remove('active');
            document.getElementById('indicator-step2').classList.add('active');
            document.getElementById('indicator-step2').classList.remove('completed');
            document.getElementById('indicator-step2').querySelector('span').textContent = '1';
            showStep(2);
        });
        document.getElementById('completeSetupBtn').addEventListener('click', submitStep3);

        // Form-level event handlers (matches register.js pattern exactly)
        var step23Section = document.getElementById('steps23-section');
        if (step23Section) {
            function clearAllRequiredErrors() {
                var errs = step23Section.querySelectorAll('[id$="-error"]');
                for (var i = 0; i < errs.length; i++) {
                    if (/this field is required/i.test(errs[i].textContent || '')) {
                        errs[i].parentNode && errs[i].parentNode.removeChild(errs[i]);
                    }
                }
            }
            step23Section.addEventListener('input', function() {
                clearAllRequiredErrors();
            }, true);
            step23Section.addEventListener('change', function() {
                clearAllRequiredErrors();
            }, true);
            step23Section.addEventListener('focusin', function() {
                clearAllRequiredErrors();
            }, true);
            step23Section.addEventListener('focusout', function(ev) {
                var t = ev.target;
                if (!t) return;
                var tid = t.id || '';
                if (tid === 'fname' || tid === 'mname' || tid === 'lname' ||
                    tid === 'street' || tid === 'brgy' || tid === 'city' || tid === 'province' || tid === 'country') {
                    var raw = t.value || '';
                    var trimmed = raw.trim();
                    if (/^\s/.test(raw)) {
                        showErrorMessage(tid, 'Spaces should not be inputted first');
                        return;
                    }
                    if (tid === 'street' && /^Purok\s+\d+\s+$/.test(raw)) {
                        return;
                    }
                    if (trimmed !== '' && /\s$/.test(raw)) {
                        var errEl2 = document.getElementById(tid + '-error');
                        if (errEl2 && !/unnecessary space at the end/i.test(errEl2.textContent || '')) {
                            return;
                        }
                        showErrorMessage(tid, "Don't leave unnecessary space at the end.");
                    }
                }
            }, true);
        }

        // Wire up live validation for name fields (using register.js's validateName)
        var nameFields = ['fname', 'mname', 'lname'];
        for (var nf = 0; nf < nameFields.length; nf++) {
            (function(fid) {
                var el = document.getElementById(fid);
                if (!el) return;
                el.addEventListener('input', function() { validateName(fid); });
                el.addEventListener('keyup', function() { validateName(fid); });
                el.addEventListener('change', function() {
                    var raw = el.value || '';
                    var trimmed = raw.trim();
                    if (trimmed !== '' && /\s$/.test(raw)) {
                        showErrorMessage(fid, "Don't leave unnecessary space at the end.");
                        return;
                    }
                    validateName(fid);
                });
                el.addEventListener('blur', function() {
                    var raw = el.value || '';
                    var trimmed = raw.trim();
                    if (trimmed !== '' && /\s$/.test(raw)) {
                        showErrorMessage(fid, "Don't leave unnecessary space at the end.");
                        return;
                    }
                    validateName(fid);
                });
                el.addEventListener('keydown', function(e) {
                    if (e.key === 'Tab' && document.getElementById(fid + '-error')) {
                        e.preventDefault();
                        el.focus();
                    }
                });
            })(nameFields[nf]);
        }

        // Extension name validation (using register.js's validateExtensionName)
        var extField = document.getElementById('ename');
        if (extField) {
            extField.addEventListener('focus', function() {
                var raw = this.value || '';
                if (raw === '') { clearErrorMessage('ename'); }
                else { validateExtensionName('ename'); }
            });
            extField.addEventListener('input', function() {
                var raw = this.value || '';
                var trimmed = raw.trim();
                if (raw === '') { showErrorMessage('ename', ''); return; }
                if (/\s/.test(raw) && trimmed === '') { showErrorMessage('ename', 'Spaces are not allowed'); return; }
                validateExtensionName('ename');
            });
            extField.addEventListener('blur', function() {
                var raw = this.value || '';
                var trimmed = raw.trim();
                if (raw === '') { clearErrorMessage('ename'); return; }
                if (trimmed === '') { showErrorMessage('ename', 'Spaces are not allowed'); return; }
                validateExtensionName('ename');
            });
        }

        // Address field validation (using register.js's validateNoSpacePlace)
        var placeFields = [
            { id: 'street', label: 'Purok/Street' },
            { id: 'brgy', label: 'Barangay' },
            { id: 'city', label: 'City/Municipality' },
            { id: 'province', label: 'Province' },
            { id: 'country', label: 'Country' }
        ];
        for (var p = 0; p < placeFields.length; p++) {
            (function(cfg) {
                var el = document.getElementById(cfg.id);
                if (!el) return;
                el.addEventListener('input', function() {
                    validateNoSpacePlace(cfg.id, cfg.label);
                });
                el.addEventListener('blur', function() {
                    var v = (this.value || '').trim();
                    if (v === '') { clearErrorMessage(cfg.id); return; }
                    validateNoSpacePlace(cfg.id, cfg.label);
                });
            })(placeFields[p]);
        }

        // Zip code validation (using register.js's validateZip4)
        var zipEl = document.getElementById('zipcode');
        if (zipEl) {
            zipEl.addEventListener('input', function() {
                this.value = this.value.replace(/[^\d]/g, '');
                validateZip4('zipcode');
            });
            zipEl.addEventListener('blur', function() {
                var v = (this.value || '').trim();
                if (v === '') { clearErrorMessage('zipcode'); return; }
                validateZip4('zipcode');
            });
        }

        // SQ answer validation
        // Clear dropdown errors on selection
        ['q1', 'q2', 'q3'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', function() { clearErrorMessage(id); });
            }
        });

        // Real-time answer validation on typing (skip "required" — only validate when has content)
        ['a1', 'a2', 'a3'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', function() {
                    var val = (this.value || '').trim();
                    if (val === '') { clearErrorMessage(id); return; }
                    if (val.length < 3) { showErrorMessage(id, 'Must be at least 3 characters'); return; }
                    if (val.length > 50) { showErrorMessage(id, 'Must not exceed 50 characters'); return; }
                    if (/^[.\s]/.test(val)) { showErrorMessage(id, "Don't start with a space or dot"); return; }
                    clearErrorMessage(id);
                });
            }
        });

        // Close modal on overlay click
        document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) overlay.classList.remove('active');
            });
        });
    });
})();
