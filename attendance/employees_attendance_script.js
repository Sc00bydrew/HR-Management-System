// Auto-fill position input when selecting an Employee ID
    function updatePosition(selectElement, positionInputId) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const position = selectedOption.getAttribute('data-position') || '';
        document.getElementById(positionInputId).value = position;
    }

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