// resources/js/app-dashboard.js

document.addEventListener('DOMContentLoaded', () => {
    // Auto-hide alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.classList.remove('show');
        }, 5000);
    });

    // Global confirm for .js-confirm elements
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', e => {
            const msg = btn.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(msg)) e.preventDefault();
        });
    });
});