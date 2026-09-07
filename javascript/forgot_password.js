(function () {
  'use strict';

  /* ── Step Navigation ── */
  function showStep(n) {
    document.querySelectorAll('.fp-step').forEach(function (el) {
      el.classList.remove('active');
    });
    var target = document.getElementById('step-' + n);
    if (target) target.classList.add('active');
  }

  /* ── Error Helpers ── */
  function showFieldError(fieldId, message) {
    clearFieldError(fieldId);
    var host = document.getElementById(fieldId);
    if (!host) return;
    var err = document.createElement('div');
    err.id = fieldId + '-error';
    err.className = 'inline-error';
    err.textContent = message;
    var wrapper = host.closest('.password-wrapper');
    var container = wrapper ? wrapper.parentNode : host.parentNode;
    var after = wrapper || host;
    container.insertBefore(err, after.nextSibling);
  }

  function clearFieldError(fieldId) {
    var el = document.getElementById(fieldId + '-error');
    if (el && el.parentNode) el.parentNode.removeChild(el);
  }

  function showBlockError(containerId, message) {
    var container = document.getElementById(containerId);
    if (!container) return;
    var existing = container.querySelector('.block-error');
    if (existing) existing.remove();
    var div = document.createElement('div');
    div.className = 'block-error inline-error';
    div.textContent = message;
    container.appendChild(div);
  }

  function clearBlockError(containerId) {
    var container = document.getElementById(containerId);
    if (!container) return;
    var existing = container.querySelector('.block-error');
    if (existing) existing.remove();
  }

  /* ── OTP Input Handling ── */
  function initOTPInputs() {
    var boxes = document.querySelectorAll('.otp-box');
    if (!boxes.length) return;

    boxes.forEach(function (box, i) {
      box.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value && i < boxes.length - 1) {
          boxes[i + 1].focus();
        }
      });

      box.addEventListener('keydown', function (e) {
        if (e.key === 'Backspace' && !this.value && i > 0) {
          boxes[i - 1].focus();
        }
      });

      box.addEventListener('paste', function (e) {
        e.preventDefault();
        var paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
        boxes.forEach(function (b, j) {
          if (paste[j]) b.value = paste[j];
        });
        if (paste.length >= 6) boxes[5].focus();
      });

      box.addEventListener('focus', function () {
        this.select();
      });
    });
  }

  /* ── OTP Timer ── */
  var timerInterval = null;

  function startOTPTimer(expiryTimestamp) {
    var display = document.getElementById('otpTimerDisplay');
    var timerWrap = display ? display.closest('.otp-timer') : null;
    var verifyBtn = document.getElementById('verifyOtpBtn');
    if (!display || !expiryTimestamp) return;

    clearInterval(timerInterval);

    function tick() {
      var remaining = Math.max(0, expiryTimestamp - Math.floor(Date.now() / 1000));
      var mins = Math.floor(remaining / 60);
      var secs = remaining % 60;
      display.textContent = String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');

      if (remaining <= 0) {
        clearInterval(timerInterval);
        if (timerWrap) timerWrap.classList.add('expired');
        if (verifyBtn) {
          verifyBtn.disabled = true;
          verifyBtn.style.cursor = 'not-allowed';
        }
        var errEl = document.getElementById('otpError');
        if (errEl) {
          errEl.textContent = 'OTP has expired. Please resend.';
          errEl.classList.add('show');
        }
      }
    }

    tick();
    timerInterval = setInterval(tick, 1000);
  }

  function resetTimer() {
    clearInterval(timerInterval);
    var display = document.getElementById('otpTimerDisplay');
    var timerWrap = display ? display.closest('.otp-timer') : null;
    var verifyBtn = document.getElementById('verifyOtpBtn');
    if (timerWrap) timerWrap.classList.remove('expired');
    if (verifyBtn) {
      verifyBtn.disabled = false;
      verifyBtn.style.cursor = '';
    }
  }

  /* ── Show / Hide OTP Code ── */
  var otpCodeVisible = false;

  function toggleOTPVisibility() {
    otpCodeVisible = !otpCodeVisible;
    var boxes = document.querySelectorAll('.otp-box');
    var link = document.getElementById('showCodeBtn');
    boxes.forEach(function (b) {
      b.type = otpCodeVisible ? 'text' : 'password';
    });
    if (link) link.textContent = otpCodeVisible ? 'Hide code' : 'Show code';
  }

  /* ── Eye Toggle (generic) ── */
  function initEyeToggles() {
    document.querySelectorAll('.password-wrapper i').forEach(function (icon) {
      icon.addEventListener('click', function () {
        var input = this.closest('.password-wrapper').querySelector('input');
        if (!input) return;
        var isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
      });
    });
  }

  /* ── Password Strength (Step 4) ── */
  function checkStrength(pw) {
    if (!pw) return '';
    var score = 0;
    if (pw.length >= 8) score++;
    if (pw.length >= 10) score++;
    if (/[A-Z]/.test(pw)) score++;
    if (/[0-9]/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;
    if (score <= 2) return 'Weak';
    if (score <= 3) return 'Medium';
    return 'Strong';
  }

  function initPasswordStrength() {
    var newPw = document.getElementById('newPassword');
    var strengthEl = document.getElementById('strengthIndicator');
    if (!newPw || !strengthEl) return;

    newPw.addEventListener('input', function () {
      var s = checkStrength(this.value);
      strengthEl.textContent = s ? 'Strength: ' + s : '';
      strengthEl.className = 'strength-text';
      if (s === 'Weak') strengthEl.classList.add('weak');
      else if (s === 'Medium') strengthEl.classList.add('medium');
      else if (s === 'Strong') strengthEl.classList.add('strong');
    });
  }

  /* ── Step 3: Question Dropdown Duplicate Prevention ── */
  function initQuestionDropdowns() {
    var s1 = document.getElementById('sq_q1');
    var s2 = document.getElementById('sq_q2');
    var s3 = document.getElementById('sq_q3');
    if (!s1 || !s2 || !s3) return;

    function refreshDisables() {
      var chosen = [s1.value, s2.value, s3.value].filter(function (v) { return v && v !== ''; });
      [s1, s2, s3].forEach(function (sel) {
        Array.prototype.forEach.call(sel.options, function (opt) {
          if (!opt.value) return;
          opt.disabled = chosen.indexOf(opt.value) !== -1 && sel.value !== opt.value;
        });
      });
    }

    [s1, s2, s3].forEach(function (sel) {
      sel.addEventListener('change', refreshDisables);
    });
    refreshDisables();
  }

  /* ── Step 3: Real-time answer validation ── */
  function validateAnswer(fieldId) {
    var el = document.getElementById(fieldId);
    if (!el) return true;
    var raw = (el.value || '');
    if (/^\s|\s$/.test(raw)) {
      showFieldError(fieldId, 'No leading or trailing spaces');
      return false;
    }
    var v = raw.trim();
    if (v === '') { clearFieldError(fieldId); return false; }
    if (!/^[A-Za-z0-9 .'-]+$/.test(v)) {
      showFieldError(fieldId, "Only letters, numbers, spaces, periods, apostrophes, or hyphens allowed.");
      return false;
    }
    if (v.length < 3) { showFieldError(fieldId, 'Must be at least 3 characters'); return false; }
    if (v.length > 50) { showFieldError(fieldId, 'Maximum of 50 characters only'); return false; }
    clearFieldError(fieldId);
    return true;
  }

  /* ── Server-side Errors from URL ── */
  function handleURLErrors() {
    var params = new URLSearchParams(window.location.search);
    var error = params.get('error');
    var step = params.get('step');

    if (!error) return;

    if (step === '1' || !step) {
      if (error === 'empty_id') showFieldError('id_number', 'This field is required');
      else if (error === 'invalid_id') showFieldError('id_number', 'Please enter ID in format: xxxx-xxxx');
      else if (error === 'unknown_id') showFieldError('id_number', 'ID not found');
      else if (error === 'rate_limit') {
        var errEl = document.getElementById('otpError');
        if (errEl) { errEl.textContent = 'Too many OTP requests. Try again in an hour.'; errEl.classList.add('show'); }
      }
    }

    if (step === '2') {
      if (error === 'otp_empty') {
        var errEl = document.getElementById('otpError');
        if (errEl) { errEl.textContent = 'Please enter the 6-digit OTP.'; errEl.classList.add('show'); }
      }
      else if (error === 'otp_wrong') {
        var errEl = document.getElementById('otpError');
        if (errEl) { errEl.textContent = 'Invalid OTP. Please try again.'; errEl.classList.add('show'); }
      }
      else if (error === 'otp_expired') {
        var errEl = document.getElementById('otpError');
        if (errEl) { errEl.textContent = 'OTP has expired. Please resend.'; errEl.classList.add('show'); }
      }
      else if (error === 'otp_none') {
        var errEl = document.getElementById('otpError');
        if (errEl) { errEl.textContent = 'No active OTP found. Request a new one.'; errEl.classList.add('show'); }
      }
      else if (error === 'otp_locked') {
        var errEl = document.getElementById('otpError');
        if (errEl) { errEl.textContent = 'Too many failed attempts. Request a new OTP.'; errEl.classList.add('show'); }
      }
      else if (error === 'rate_limit') {
        var errEl = document.getElementById('otpError');
        if (errEl) { errEl.textContent = 'Too many OTP requests. Try again in an hour.'; errEl.classList.add('show'); }
      }
    }

    if (step === '3') {
      if (error === 'empty_answers') {
        ['sq_a1', 'sq_a2', 'sq_a3'].forEach(function (id) {
          showFieldError(id, 'This field is required');
        });
      }
      else if (error === 'not_enough') {
        showBlockError('step-3', 'Please answer at least 2 questions correctly.');
      }
      else if (error === 'duplicate_questions') {
        showBlockError('step-3', 'Each security question must be different.');
      }
    }

    if (step === '4') {
      if (error === 'empty') showBlockError('step-4', 'Please fill in both password fields.');
      else if (error === 'mismatch') showBlockError('step-4', 'Passwords do not match.');
      else if (error === 'weak') showBlockError('step-4', 'Password must be at least 8 characters.');
      else if (error === 'save') showBlockError('step-4', 'Failed to save. Please try again.');
    }
  }

  /* ── Verify OTP Button ── */
  function handleVerifyOTP() {
    var boxes = document.querySelectorAll('.otp-box');
    var otp = Array.from(boxes).map(function (b) { return b.value; }).join('');
    var errEl = document.getElementById('otpError');

    if (otp.length < 6) {
      if (errEl) { errEl.textContent = 'Please enter the 6-digit OTP.'; errEl.classList.add('show'); }
      return;
    }

    var hiddenInput = document.querySelector('#step-2 input[name="otp_code"]');
    if (!hiddenInput) {
      hiddenInput = document.createElement('input');
      hiddenInput.type = 'hidden';
      hiddenInput.name = 'otp_code';
      document.getElementById('step-2').appendChild(hiddenInput);
    }
    hiddenInput.value = otp;

    var form = document.getElementById('fp-form');
    if (form) {
      var actionInput = document.querySelector('#step-2 input[name="fp_action"]');
      if (!actionInput) {
        actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'fp_action';
        actionInput.value = 'verify_otp';
        document.getElementById('step-2').appendChild(actionInput);
      }
      actionInput.value = 'verify_otp';
      form.submit();
    }
  }

  /* ── Init ── */
  document.addEventListener('DOMContentLoaded', function () {
    var formCard = document.querySelector('.form-card');
    var initialStep = parseInt(formCard ? formCard.dataset.step : '1', 10) || 1;

    showStep(initialStep);
    initOTPInputs();
    initEyeToggles();
    initPasswordStrength();
    initQuestionDropdowns();
    handleURLErrors();

    var expiryStr = document.getElementById('otpTimerDisplay');
    if (expiryStr && expiryStr.dataset.expiry) {
      var expiry = parseInt(expiryStr.dataset.expiry, 10);
      if (expiry > 0) startOTPTimer(expiry);
    }

    document.querySelectorAll('.btn-prev').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var goto = parseInt(this.dataset.goto, 10);
        if (goto) showStep(goto);
      });
    });

    var verifyBtn = document.getElementById('verifyOtpBtn');
    if (verifyBtn) verifyBtn.addEventListener('click', handleVerifyOTP);

    var showCodeBtn = document.getElementById('showCodeBtn');
    if (showCodeBtn) showCodeBtn.addEventListener('click', toggleOTPVisibility);

    var resendBtn = document.getElementById('resendBtn');
    if (resendBtn) {
      resendBtn.addEventListener('click', function () {
        var form = document.getElementById('fp-form');
        if (!form) return;
        var actionInput = document.querySelector('#step-2 input[name="fp_action"]');
        if (!actionInput) {
          actionInput = document.createElement('input');
          actionInput.type = 'hidden';
          actionInput.name = 'fp_action';
          document.getElementById('step-2').appendChild(actionInput);
        }
        actionInput.value = 'resend_otp';
        form.submit();
      });
    }

    ['sq_a1', 'sq_a2', 'sq_a3'].forEach(function (id) {
      var el = document.getElementById(id);
      if (!el) return;
      el.addEventListener('input', function () { validateAnswer(id); });
      el.addEventListener('blur', function () { validateAnswer(id); });
    });

    var fpForm = document.getElementById('fp-form');
    if (fpForm) {
      fpForm.setAttribute('novalidate', 'novalidate');
    }

    /* Show success modal if redirected after password reset */
    var params = new URLSearchParams(window.location.search);
    if (params.get('reset') === 'success') {
      var overlay = document.getElementById('successModal');
      if (overlay) overlay.classList.add('show');
    }
  });
})();
