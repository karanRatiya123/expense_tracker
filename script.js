/**
 * ApexSpend — Dashboard Toast Helper
 * Auth flows have been removed from this build. The dashboard uses
 * showToast() for inline confirmations. All app logic lives in app.js.
 */

function showToast(message, type = 'info') {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    let icon = 'fa-circle-info';
    if (type === 'success') icon = 'fa-circle-check';
    if (type === 'error')   icon = 'fa-circle-exclamation';
    toast.innerHTML = `<i class="fa-solid ${icon}" aria-hidden="true"></i><span>${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(6px)';
        setTimeout(() => toast.remove(), 250);
    }, 3200);
}