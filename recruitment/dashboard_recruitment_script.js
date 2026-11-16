document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });
    }
});

setTimeout(function() {
    const alertBox = document.querySelector('.alert-success');
    if (alertBox) {
        // Smooth fade-out transition
        alertBox.style.transition = 'opacity 0.5s ease';
        alertBox.style.opacity = '0';
            
        // Remove element from layout completely after fade finishes
        setTimeout(function() {
            alertBox.remove();
        }, 500);
    }
}, 5000); // 5000 milliseconds = 5 seconds