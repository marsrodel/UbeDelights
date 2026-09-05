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
        if (!val) { ageField.value = ''; return; }
        var bdayDate = new Date(val);
        if (isNaN(bdayDate.getTime())) { ageField.value = ''; return; }
        var today = new Date();
        var age = today.getFullYear() - bdayDate.getFullYear();
        var m = today.getMonth() - bdayDate.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < bdayDate.getDate())) age--;
        if (age < 0) age = 0;
        ageField.value = age;
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
    }

    function submitProfile(e) {
        e.preventDefault();
        var np = document.getElementById('profileNewPassword');
        var rp = document.getElementById('profileConfirmPassword');
        if (np && rp && np.value && np.value !== rp.value) {
            showToast('New password and confirmation do not match.', 'error');
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
                            if (cp) cp.value = '';
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

        var form = document.getElementById('adminProfileForm');
        if (form) form.addEventListener('submit', submitProfile);

        var resetBtn = document.getElementById('profileCancelBtn');
        if (resetBtn) resetBtn.addEventListener('click', resetForm);
    });
})();
