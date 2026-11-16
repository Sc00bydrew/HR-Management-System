function updatePosition(selectElement) {
    const selectedOption = selectElement.options[selectElement.selectedIndex];
    const position = selectedOption.getAttribute('data-position') || '';
    document.getElementById('position_input').value = position;
}

document.addEventListener("DOMContentLoaded", function() {
        const alertBox = document.querySelector('.alert');
        const errorInputs = document.querySelectorAll('.input-error');

        if (alertBox) {
            // Wait for 5 seconds (5000 milliseconds)
            setTimeout(function() {
                // Add the fade-out class to trigger the CSS transition
                alertBox.classList.add('fade-out');
                
                // Completely hide the alert after the 0.5s fade finishes
                setTimeout(function() {
                    alertBox.style.display = 'none';
                }, 500);

                // Remove the red border error class from all input fields
                errorInputs.forEach(function(input) {
                    input.classList.remove('input-error');
                });
            }, 5000); // 5000ms = 5 seconds
        }
    });