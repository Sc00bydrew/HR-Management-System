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
    const filterBtn = document.getElementById("historyDropdownBtn");
    const filterMenu = document.getElementById("historyDropdownMenu");

    if (filterBtn && filterMenu) {
        filterBtn.addEventListener("click", function(e) {
            e.stopPropagation();
            filterMenu.classList.toggle("show");
        });

        document.addEventListener("click", function(e) {
            if (!filterBtn.contains(e.target) && !filterMenu.contains(e.target)) {
                filterMenu.classList.remove("show");
            }
        });
    }
});