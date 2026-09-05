(function() {
    'use strict';

    var profile = typeof adminProfile !== 'undefined' ? adminProfile : null;
    var toast = document.getElementById('profileToast');

    function showToast(msg, type) {
        if (!toast) return;
        toast.textContent = msg;
        toast.className = 'profile-toast ' + (type || 'success');
        toast.classList.add('show');
        setTimeout(function() { toast.classList.remove('show'); }, 3000);
    }

    function calculateAge() {
        var bday = document.getElementById('profileBirthdate');
        var ageField = document.getElementById('profileAge');
        if (!bday || !ageField) return;
        var val = bday.value;
        if (!val || val.trim() === '') {
            ageField.value = '';
            clearErrorMessage('profileAge');
            return;
        }
        var bdayDate = new Date(val);
        if (isNaN(bdayDate.getTime())) {
            ageField.value = '';
            showErrorMessage('profileAge', 'Please enter a valid date of birth');
            return;
        }
        var today = new Date();
        if (bdayDate.getTime() > today.getTime()) {
            ageField.value = '0';
            showErrorMessage('profileAge', 'Birthday should not be in the future');
            return;
        }
        var age = today.getFullYear() - bdayDate.getFullYear();
        var m = today.getMonth() - bdayDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < bdayDate.getDate())) age--;
        if (age < 0) age = 0;
        ageField.value = age;
        if (age < 18) {
            showErrorMessage('profileAge', 'You must be at least 18 years old.');
        } else {
            clearErrorMessage('profileAge');
        }
    }

    function populateForm() {
        if (!profile) return;
        setVal('profileFirstName', profile.firstName);
        setVal('profileMiddleName', profile.middleName);
        setVal('profileLastName', profile.lastName);
        setVal('profileExtensionName', profile.extensionName);
        setVal('profileEmail', profile.email);
        setVal('profileUsername', profile.username);
        setVal('profileBirthdate', profile.dob);
        setVal('profileSex', profile.sex);
        setVal('profileStreet', profile.street);
        setVal('profileBarangay', profile.barangay);
        setVal('profileCity', profile.city);
        setVal('profileProvince', profile.province);
        setVal('profileCountry', profile.country);
        setVal('profileZipcode', profile.zipCode);
        originalEmail = profile.email || '';
        originalUsername = profile.username || '';
        calculateAge();
    }

    function setVal(id, val) {
        var el = document.getElementById(id);
        if (el) el.value = val || '';
    }

    function resetForm() {
        populateForm();
        var cp = document.getElementById('profileCurrentPassword');
        var np = document.getElementById('profileNewPassword');
        var rp = document.getElementById('profileConfirmPassword');
        if (cp) cp.value = '';
        if (np) np.value = '';
        if (rp) rp.value = '';
        clearErrorMessage('profileCurrentPassword');
        clearErrorMessage('profileNewPassword');
        clearErrorMessage('profileConfirmPassword');
    }

    function validateAddressField(profileId, registerId) {
        var el = document.getElementById(profileId);
        if (!el) return true;
        if (!el.value || !el.value.trim()) { clearErrorMessage(profileId); return true; }
        var existingErr = document.getElementById(profileId + '-error');
        if (existingErr) existingErr.id = registerId + '-error';
        el.id = registerId;
        var result = validateNoSpacePlace(registerId);
        var errEl = document.getElementById(registerId + '-error');
        if (errEl) errEl.id = profileId + '-error';
        el.id = profileId;
        return result;
    }

    var usernameTimeoutId = null;
    var emailTimeoutId = null;
    var originalEmail = '';
    var originalUsername = '';

    function checkProfileUsernameExists(usernameValue) {
        var el = document.getElementById('profileUsername');
        if (!el) return;
        if (usernameValue === '' || usernameValue.trim() === '') {
            clearErrorMessage('profileUsername');
            return;
        }
        if (usernameValue === originalUsername) { clearErrorMessage('profileUsername'); return; }
        el.setAttribute('data-validating', 'true');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../../server/check_username.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                el.removeAttribute('data-validating');
                if (xhr.status == 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.exists === true) {
                            showErrorMessage('profileUsername', 'Username already exists. Please choose a different username.');
                        } else {
                            clearErrorMessage('profileUsername');
                        }
                    } catch (e) {}
                }
            }
        };
        xhr.send('username=' + encodeURIComponent(usernameValue));
    }

    function checkProfileEmailExists(emailValue) {
        var el = document.getElementById('profileEmail');
        if (!el) return;
        if (emailValue === '' || emailValue.trim() === '') {
            clearErrorMessage('profileEmail');
            return;
        }
        if (emailValue === originalEmail) { clearErrorMessage('profileEmail'); return; }
        if (!validateEmail('profileEmail')) return;
        el.setAttribute('data-validating', 'true');
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../../server/check_email.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                el.removeAttribute('data-validating');
                if (xhr.status == 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.exists === true) {
                            showErrorMessage('profileEmail', 'Email already exists. Please use a different email.');
                        } else {
                            clearErrorMessage('profileEmail');
                        }
                    } catch (e) {}
                }
            }
        };
        xhr.send('email=' + encodeURIComponent(emailValue));
    }

    function validateProfileUsername() {
        var el = document.getElementById('profileUsername');
        if (!el) return true;
        var rawU = el.value;
        if (rawU === '') { clearErrorMessage('profileUsername'); return true; }

        var firstChar = rawU.charAt(0);
        if (/\s/.test(firstChar)) { showErrorMessage('profileUsername', 'Spaces are not allowed in username'); return false; }
        if (firstChar === '_') { showErrorMessage('profileUsername', 'Must not start with a special character.'); return false; }
        if (/[^A-Za-z0-9_]/.test(firstChar)) { showErrorMessage('profileUsername', 'Must not start with a special character.'); return false; }
        if (/[0-9]/.test(firstChar)) { showErrorMessage('profileUsername', 'Must not start with a number.'); return false; }
        if (/[A-Z]/.test(firstChar)) { showErrorMessage('profileUsername', 'Capital letters are not allowed'); return false; }

        var idxUndEarly = rawU.indexOf('_');
        if (idxUndEarly !== -1) {
            var suffixEarly = rawU.slice(idxUndEarly + 1);
            if (suffixEarly && /[^0-9]/.test(suffixEarly)) {
                showErrorMessage('profileUsername', 'Only numbers are allowed after the underscore.');
                return false;
            }
        }

        if (/\s/.test(rawU)) { showErrorMessage('profileUsername', 'Spaces are not allowed in username'); return false; }
        if (/[A-Z]/.test(rawU)) { showErrorMessage('profileUsername', 'Capital letters are not allowed'); return false; }
        if ((rawU.match(/_/g) || []).length > 1) { showErrorMessage('profileUsername', 'Only one underscore "_" is allowed'); return false; }

        var idxUnd = rawU.indexOf('_');
        if (idxUnd === -1) {
            if (/[^a-z0-9_]/.test(rawU)) { showErrorMessage('profileUsername', 'Underscore "_" is the only allowed special character'); return false; }
            if (/^[a-z]/.test(rawU) && rawU.length < 5) {
                showErrorMessage('profileUsername', 'Username format must be ex. xxxxx_12345');
                return false;
            }
            var letters = (rawU.match(/^[a-z]+/) || [''])[0].length;
            if (letters > 10) { showErrorMessage('profileUsername', 'Maximum of 10 letters only'); return false; }
            if (letters < 5) { showErrorMessage('profileUsername', 'Letters must be at least 5 characters long.'); return false; }
            if (/^[a-z]{5,}$/.test(rawU)) { showErrorMessage('profileUsername', 'An underscore "_" is required after the first word.'); return false; }
            if (/^[a-z]{5,}[0-9]+$/.test(rawU)) { showErrorMessage('profileUsername', 'Underscore is missing. Format must be: xxxxx_1234.'); return false; }
        } else {
            var prefix = rawU.slice(0, idxUnd);
            var suffix = rawU.slice(idxUnd + 1);
            if (/[A-Z]/.test(prefix)) { showErrorMessage('profileUsername', 'Capital letters are not allowed'); return false; }
            if (/[^a-z0-9]/.test(prefix)) { showErrorMessage('profileUsername', 'Underscore "_" is the only allowed special character'); return false; }
            if (/\d/.test(prefix)) { showErrorMessage('profileUsername', 'Underscore "_" must be between letters and numbers (e.g. xxxxx_1234).'); return false; }
            if (!/^[a-z]+$/.test(prefix) || prefix.length < 5) { showErrorMessage('profileUsername', 'Letters must be at least 5 characters long.'); return false; }
            if (prefix.length > 10) { showErrorMessage('profileUsername', 'Maximum of 10 letters only'); return false; }
            if (suffix === '') { showErrorMessage('profileUsername', 'Numbers must come after the underscore.'); return false; }
            if (!/^\d+$/.test(suffix)) { showErrorMessage('profileUsername', 'Only numbers are allowed after the underscore.'); return false; }
            if (suffix.length > 6) { showErrorMessage('profileUsername', 'Maximum of 6 numbers only'); return false; }
            if (/\s/.test(rawU)) { showErrorMessage('profileUsername', 'Spaces are not allowed in username'); return false; }
            if (/[^a-z0-9_]/.test(rawU)) { showErrorMessage('profileUsername', 'Underscore "_" is the only allowed special character'); return false; }
        }

        var valid = /^[a-z]{5,}_[0-9]+$/.test(rawU);
        if (!valid) return false;
        clearErrorMessage('profileUsername');
        return true;
    }

    function handleUsernameInput() {
        var el = document.getElementById('profileUsername');
        if (!el) return;
        clearTimeout(usernameTimeoutId);
        var rawU = el.value;
        if (rawU === '') { clearErrorMessage('profileUsername'); return; }
        if (!validateProfileUsername()) return;
        usernameTimeoutId = setTimeout(function() {
            checkProfileUsernameExists(el.value);
        }, 500);
    }

    function validatePasswordMatch() {
        var np = document.getElementById('profileNewPassword');
        var rp = document.getElementById('profileConfirmPassword');
        if (!np || !rp) return;
        if (np.value && rp.value && np.value !== rp.value) {
            showErrorMessage('profileConfirmPassword', 'Passwords do not match');
        } else {
            clearErrorMessage('profileConfirmPassword');
        }
    }

    function submitProfile(e) {
        e.preventDefault();

        var valid = true;
        var firstError = null;

        function check(fieldId, fn) {
            if (!fn()) {
                if (valid) { valid = false; firstError = document.getElementById(fieldId); }
            }
        }

        check('profileFirstName', function() { return validateName('profileFirstName'); });
        check('profileMiddleName', function() { return validateName('profileMiddleName'); });
        check('profileLastName', function() { return validateName('profileLastName'); });
        check('profileExtensionName', function() { return validateName('profileExtensionName'); });
        check('profileEmail', function() { return validateEmail('profileEmail'); });
        check('profileUsername', function() { return validateProfileUsername(); });
        check('profileStreet', function() { return validateAddressField('profileStreet', 'street'); });
        check('profileBarangay', function() { return validateAddressField('profileBarangay', 'brgy'); });
        check('profileCity', function() { return validateAddressField('profileCity', 'city'); });
        check('profileProvince', function() { return validateAddressField('profileProvince', 'province'); });
        check('profileCountry', function() { return validateAddressField('profileCountry', 'country'); });
        check('profileZipcode', function() { return validateZip4('profileZipcode'); });

        var np = document.getElementById('profileNewPassword');
        var rp = document.getElementById('profileConfirmPassword');
        if (np && rp && np.value && np.value !== rp.value) {
            showErrorMessage('profileConfirmPassword', 'Passwords do not match');
            if (valid) { valid = false; firstError = rp; }
        }

        if (!valid) {
            if (firstError) firstError.focus();
            return;
        }

        var form = document.getElementById('adminProfileForm');
        if (!form) return;
        var data = new FormData(form);
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '../../server/admin_update_profile.php', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        var res = JSON.parse(xhr.responseText);
                        if (res.success) {
                            showToast('Profile updated successfully.', 'success');
                            if (cp) document.getElementById('profileCurrentPassword').value = '';
                            if (np) np.value = '';
                            if (rp) rp.value = '';
                        } else {
                            showToast(res.message || 'Failed to update profile.', 'error');
                        }
                    } catch (err) {
                        showToast('An error occurred.', 'error');
                    }
                } else {
                    showToast('Server error.', 'error');
                }
            }
        };
        xhr.send(data);
    }

    document.addEventListener('DOMContentLoaded', function() {
        populateForm();

        var bday = document.getElementById('profileBirthdate');
        if (bday) bday.addEventListener('change', calculateAge);

        var nameFields = ['profileFirstName', 'profileMiddleName', 'profileLastName', 'profileExtensionName'];
        nameFields.forEach(function(id) {
            var el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', function() { validateName(id); });
                el.addEventListener('blur', function() { validateName(id); });
            }
        });

        var emailEl = document.getElementById('profileEmail');
        if (emailEl) {
            emailEl.addEventListener('input', function() {
                clearTimeout(emailTimeoutId);
                if (!validateEmail('profileEmail')) return;
                var val = emailEl.value;
                if (val.trim()) {
                    emailTimeoutId = setTimeout(function() {
                        checkProfileEmailExists(val);
                    }, 500);
                }
            });
            emailEl.addEventListener('blur', function() {
                clearTimeout(emailTimeoutId);
                if (!validateEmail('profileEmail')) return;
                if (emailEl.value.trim()) checkProfileEmailExists(emailEl.value);
            });
        }

        var addressMap = {
            profileStreet: 'street',
            profileBarangay: 'brgy',
            profileCity: 'city',
            profileProvince: 'province',
            profileCountry: 'country'
        };
        Object.keys(addressMap).forEach(function(profileId) {
            var el = document.getElementById(profileId);
            if (el) {
                el.addEventListener('input', function() { validateAddressField(profileId, addressMap[profileId]); });
                el.addEventListener('blur', function() { validateAddressField(profileId, addressMap[profileId]); });
            }
        });

        var usernameEl = document.getElementById('profileUsername');
        if (usernameEl) {
            usernameEl.addEventListener('input', handleUsernameInput);
            usernameEl.addEventListener('blur', function() {
                clearTimeout(usernameTimeoutId);
                validateProfileUsername();
                if (usernameEl.value.trim()) checkProfileUsernameExists(usernameEl.value);
            });
        }

        var zipEl = document.getElementById('profileZipcode');
        if (zipEl) {
            zipEl.addEventListener('input', function() { validateZip4('profileZipcode'); });
            zipEl.addEventListener('blur', function() { validateZip4('profileZipcode'); });
        }

        var npEl = document.getElementById('profileNewPassword');
        var rpEl = document.getElementById('profileConfirmPassword');
        if (npEl) npEl.addEventListener('input', validatePasswordMatch);
        if (rpEl) rpEl.addEventListener('input', validatePasswordMatch);

        var form = document.getElementById('adminProfileForm');
        if (form) form.addEventListener('submit', submitProfile);

        var resetBtn = document.getElementById('profileCancelBtn');
        if (resetBtn) resetBtn.addEventListener('click', resetForm);
    });
})();
