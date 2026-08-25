document.getElementById('btnChangePassword').addEventListener('click', function() {
    window.location.href = './change_password.php';
});
document.getElementById('btnEditProfile').addEventListener('click', function() {
    showToast('Profile editing coming soon!');
});
function showToast(message) {
    var toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = 'toast';
    requestAnimationFrame(function() { toast.classList.add('show'); });
    setTimeout(function() { toast.classList.remove('show'); }, 2500);
}
