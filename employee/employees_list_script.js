document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });
    }
});

    document.addEventListener("DOMContentLoaded", function() {
        const alertBox = document.querySelector('.alert');
        const errorInput = document.querySelector('.input-error');

        if (alertBox) {
            // Wait for 5 seconds (5000 milliseconds)
            setTimeout(function() {
                // Smooth fade-out effect for the alert box
                alertBox.style.transition = 'opacity 0.5s ease';
                alertBox.style.opacity = '0';
                
                // Completely hide the alert after the fade finishes
                setTimeout(function() {
                    alertBox.style.display = 'none';
                }, 500);

                // Remove the red border error class from the input/select
                if (errorInput) {
                    errorInput.classList.remove('input-error');
                }
            }, 5000); // 5000ms = 5 seconds
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('filterDropdownBtn');
    const menu = document.getElementById('filterDropdownMenu');

    if (btn && menu) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
        });

        window.addEventListener('click', function(e) {
            if (!menu.contains(e.target) && !btn.contains(e.target)) {
                menu.style.display = 'none';
            }
        });
    }
});