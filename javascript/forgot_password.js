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
    var actions = container.querySelector('.form-actions');
    if (actions) {
      container.insertBefore(div, actions);
    } else {
      container.appendChild(div);
    }
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
  function getPasswordStrength(pw) {
    var s = (pw || '').replace(/\s+/g, '');
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

  function hasSpace(str) { return /\s/.test(str || ''); }

  function initPasswordStrength() {
    var newPw = document.getElementById('newPassword');
    var confirmPw = document.getElementById('confirmPassword');
    var strengthEl = document.getElementById('strengthIndicator');
    var matchEl = document.getElementById('confirmMatch');
    if (!newPw) return;

    function updateStrength() {
      if (!strengthEl) return;
      var val = newPw.value || '';
      var strength = getPasswordStrength(val);

      if (!val) {
        strengthEl.textContent = '';
        strengthEl.className = 'strength-text';
        clearFieldError('newPassword');
        return;
      }

      if (hasSpace(val)) {
        strengthEl.textContent = '';
        strengthEl.className = 'strength-text';
        showFieldError('newPassword', 'Spaces are not allowed in password.');
        return;
      }
      if (val.length < 8) {
        showFieldError('newPassword', 'Password must be at least 8 characters long.');
        strengthEl.textContent = strength ? strength + ' Password' : '';
        strengthEl.className = 'strength-text' + (strength ? ' ' + strength.toLowerCase() : '');
        return;
      }
      if (val.length > 50) {
        showFieldError('newPassword', 'Password cannot exceed 50 characters.');
        return;
      }
      if (!/[A-Z]/.test(val)) {
        showFieldError('newPassword', 'Password must contain at least 1 uppercase letter.');
        strengthEl.textContent = strength ? strength + ' Password' : '';
        strengthEl.className = 'strength-text' + (strength ? ' ' + strength.toLowerCase() : '');
        return;
      }
      if (!/[a-z]/.test(val)) {
        showFieldError('newPassword', 'Password must contain at least 1 lowercase letter.');
        strengthEl.textContent = strength ? strength + ' Password' : '';
        strengthEl.className = 'strength-text' + (strength ? ' ' + strength.toLowerCase() : '');
        return;
      }
      if (!/[0-9]/.test(val)) {
        showFieldError('newPassword', 'Password must contain at least 1 number.');
        strengthEl.textContent = strength ? strength + ' Password' : '';
        strengthEl.className = 'strength-text' + (strength ? ' ' + strength.toLowerCase() : '');
        return;
      }
      if (!/[^A-Za-z0-9]/.test(val)) {
        showFieldError('newPassword', 'Password must contain at least 1 special character.');
        strengthEl.textContent = strength ? strength + ' Password' : '';
        strengthEl.className = 'strength-text' + (strength ? ' ' + strength.toLowerCase() : '');
        return;
      }

      clearFieldError('newPassword');
      strengthEl.textContent = strength + ' Password';
      strengthEl.className = 'strength-text ' + strength.toLowerCase();
    }

    function updateMatch() {
      if (!matchEl) return;
      var p1 = newPw.value || '';
      var p2 = confirmPw ? confirmPw.value : '';
      if (!p2) {
        matchEl.textContent = '';
        matchEl.style.color = '';
        return;
      }
      if (p1 === p2) {
        matchEl.textContent = 'Password Matched';
        matchEl.style.color = '#16a34a';
        clearFieldError('confirmPassword');
      } else {
        matchEl.textContent = 'Password does not match';
        matchEl.style.color = '#dc2626';
      }
    }

    newPw.addEventListener('input', function () {
      updateStrength();
      updateMatch();
    });
    if (confirmPw) {
      confirmPw.addEventListener('input', function () {
        if (!confirmPw.value) {
          clearFieldError('confirmPassword');
        }
        updateMatch();
        if (hasSpace(confirmPw.value)) {
          showFieldError('confirmPassword', 'Spaces are not allowed in password.');
        } else {
          var errEl = document.getElementById('confirmPassword-error');
          if (errEl && /spaces/i.test(errEl.textContent || '')) {
            errEl.parentNode && errEl.parentNode.removeChild(errEl);
          }
        }
      });
    }

    updateStrength();
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

  /* ── Step 3: Security Question Group Validation ── */
  function buildGroups() {
    var groups = [];
    for (var i = 1; i <= 3; i++) {
      var select = document.getElementById('sq_q' + i);
      var input = document.getElementById('sq_a' + i);
      if (!select || !input) continue;
      var feedback = document.createElement('small');
      feedback.className = 'answer-feedback';
      input.parentNode.parentNode.appendChild(feedback);
      var error = document.createElement('small');
      error.className = 'field-error';
      input.parentNode.parentNode.appendChild(error);
      groups.push({ select: select, input: input, feedback: feedback, error: error });
    }
    return groups;
  }

  function validateGroup(g, triggeredBySubmit) {
    var question = g.select.value;
    var answer = g.input.value.trim();

    if (!triggeredBySubmit) {
      if (question && answer) {
        g.error.textContent = '';
        g.error.style.display = 'none';
      }
      return true;
    }

    var message = '';
    if (!question) {
      message = 'Select a question.';
    } else if (answer === '') {
      message = 'Answer is required.';
    }

    g.error.textContent = message;
    g.error.style.display = message ? 'block' : 'none';
    return message === '';
  }

  function clearFeedback(g) {
    if (g.feedback.textContent === 'Wrong answer') {
      g.feedback.textContent = '';
      g.feedback.style.display = 'none';
      g.input.style.borderColor = '';
    }
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

    var fpForm = document.getElementById('fp-form');
    if (fpForm) {
      fpForm.setAttribute('novalidate', 'novalidate');
    }

    /* ── Step 3: AJAX Verify ── */
    var step3VerifyBtn = document.querySelector('#step-3 .btn-submit');
    if (step3VerifyBtn) {
      var groups = buildGroups();

      groups.forEach(function (g) {
        g.input.addEventListener('input', function () {
          validateGroup(g, false);
          clearFeedback(g);
        });
        g.select.addEventListener('change', function () {
          validateGroup(g, false);
          g.error.textContent = '';
          g.error.style.display = 'none';
        });
      });

      step3VerifyBtn.addEventListener('click', async function (e) {
        e.preventDefault();

        var allValid = true;
        groups.forEach(function (g) {
          if (!validateGroup(g, true)) allValid = false;
        });
        if (!allValid) {
          clearBlockError('step-3');
          return;
        }

        var questions = groups.map(function (g) { return g.select.value; });
        var answers = groups.map(function (g) { return g.input.value.trim(); });

        step3VerifyBtn.disabled = true;
        step3VerifyBtn.textContent = 'Verifying...';

        try {
          var res = await fetch('forgot_password.php?step=3', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ fp_action: 'verify_security', questions: questions, answers: answers }),
            credentials: 'same-origin'
          });
          var data = await res.json();

          if (data.success) {
            window.location.href = 'forgot_password.php?step=4';
            return;
          }

          var answerResults = data.answerResults || [];
          groups.forEach(function (g, idx) {
            g.feedback.style.display = 'block';
            if (answerResults[idx]) {
              g.feedback.textContent = 'Correct answer';
              g.feedback.style.color = 'green';
              g.input.style.borderColor = '#28a745';
            } else {
              g.feedback.textContent = 'Wrong answer';
              g.feedback.style.color = 'red';
              g.input.style.borderColor = '#dc3545';
            }
          });

          showBlockError('step-3', data.message || 'You need at least 2 correct answers to proceed.');
        } catch (err) {
          showBlockError('step-3', 'Something went wrong. Please try again.');
        } finally {
          step3VerifyBtn.disabled = false;
          step3VerifyBtn.textContent = 'Verify';
        }
      });
    }

    /* ── Step 4: Password Submit Guard ── */
    var step4SubmitBtn = document.querySelector('#step-4 .btn-submit');
    if (step4SubmitBtn) {
      step4SubmitBtn.addEventListener('click', function (e) {
        var newPw = document.getElementById('newPassword');
        var confirmPw = document.getElementById('confirmPassword');
        if (!newPw || !confirmPw) return;

        var pVal = newPw.value || '';
        var cVal = confirmPw.value || '';

        clearFieldError('newPassword');
        clearFieldError('confirmPassword');
        clearBlockError('step-4');

        if (!pVal || !cVal) {
          e.preventDefault();
          showBlockError('step-4', 'Please fill in both password fields.');
          return;
        }
        if (hasSpace(pVal) || hasSpace(cVal)) {
          e.preventDefault();
          showBlockError('step-4', 'Spaces are not allowed in password.');
          return;
        }
        if (pVal !== cVal) {
          e.preventDefault();
          showBlockError('step-4', 'Passwords do not match.');
          return;
        }
        var passErr = document.getElementById('newPassword-error');
        if (passErr) {
          e.preventDefault();
          return;
        }
        if (pVal.length < 8) {
          e.preventDefault();
          showBlockError('step-4', 'Password must be at least 8 characters.');
          return;
        }
      });
    }

    /* Show success modal if redirected after password reset */
    var params = new URLSearchParams(window.location.search);
    if (params.get('reset') === 'success') {
      var overlay = document.getElementById('successModal');
      if (overlay) overlay.classList.add('show');
    }
  });
})();
