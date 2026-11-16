document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });
    }
});

function updateFileName(input) {
    const display = document.getElementById('file-name-display');
    if (input.files && input.files[0]) {
        display.textContent = input.files[0].name;
        display.style.color = "#145ce3"; // Brighten text when a file is chosen
    }
}

document.addEventListener("DOMContentLoaded", function() {
    const alertBox = document.querySelector('.alert-danger');
    const errorInput = document.querySelector('.input-error');

    if (alertBox) {
        setTimeout(function() {
            alertBox.style.transition = 'opacity 0.5s ease';
            alertBox.style.opacity = '0';
                
            setTimeout(function() {
                alertBox.style.display = 'none';
            }, 500);

            if (errorInput) {
                errorInput.classList.remove('input-error');
            }
        }, 5000);
    }
});
