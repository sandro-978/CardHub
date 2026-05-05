document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('[data-auto-hide]');

    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.classList.add('d-none');
        }, 3500);
    });
});
