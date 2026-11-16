const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('passwordInput');

togglePassword.addEventListener('click', function () {
    // Check the current input type
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    
    // Switch it
    passwordInput.setAttribute('type', type);
    
    // Optional: Toggle opacity or color of the icon when active so the user knows it's toggled
    this.classList.toggle('active');
});