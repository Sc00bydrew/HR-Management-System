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
    const fileNameDisplay = document.getElementById('file-name-display');
    if (input.files && input.files[0]) {
        fileNameDisplay.textContent = "Selected: " + input.files[0].name;
        fileNameDisplay.style.color = "#145CE3";
    }
}

const salaryInput = document.getElementById('salary-input');
    if (salaryInput) {
        salaryInput.addEventListener('input', function (e) {
            // Remove all non-digit characters except decimals if needed, or keep it strictly integers/digits up to 6 digits
            let value = this.value.replace(/\D/g, '');
            
            // Limit to 6 digits max
            if (value.length > 6) {
                value = value.slice(0, 6);
            }
            
            // Format with commas (e.g., 123456 -> 123,456)
            if (value.length > 0) {
                this.value = Number(value).toLocaleString();
            } else {
                this.value = '';
            }
        });
    }