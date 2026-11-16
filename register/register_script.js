// --- Password Toggle ---
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('passwordInput');
const eyeIcon = document.getElementById('eyeIcon');

togglePassword.addEventListener('click', function () {
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    
    if (type === 'text') {
        eyeIcon.classList.remove('ri-eye-close-line');
        eyeIcon.classList.add('ri-eye-line');
    } else {
        eyeIcon.classList.remove('ri-eye-line');
        eyeIcon.classList.add('ri-eye-close-line');
    }
});

// --- Confirm Password Toggle ---
const toggleConfirm = document.getElementById('toggleConfirm');
const confirmInput = document.getElementById('confirmInput');
const confirmEyeIcon = document.getElementById('confirmEyeIcon');

toggleConfirm.addEventListener('click', function () {
    const type = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
    confirmInput.setAttribute('type', type);
    
    if (type === 'text') {
        confirmEyeIcon.classList.remove('ri-eye-close-line');
        confirmEyeIcon.classList.add('ri-eye-line');
    } else {
        confirmEyeIcon.classList.remove('ri-eye-line');
        confirmEyeIcon.classList.add('ri-eye-close-line');
    }
});

document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.getElementById("sidebar");
    const sidebarToggle = document.getElementById("sidebarToggle");

    // 1. Restore saved sidebar state on page load
    if (localStorage.getItem("sidebarCollapsed") === "true") {
        sidebar.classList.add("collapsed");
    }

    // 2. Toggle collapsed state when button is clicked
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener("click", () => {
            sidebar.classList.toggle("collapsed");

            // Save the current state to localStorage
            const isCollapsed = sidebar.classList.contains("collapsed");
            localStorage.setItem("sidebarCollapsed", isCollapsed);
        });
    }
});


