

<script>
// Handle Dropdowns
document.addEventListener('click', function(e) {
    // Close all dropdowns
    const dropdowns = document.querySelectorAll('.dropdown-menu');
    let clickedInsideDropdown = false;
    
    // Check if clicked inside a trigger
    const trigger = e.target.closest('.dropdown-trigger');
    
    if (trigger) {
        clickedInsideDropdown = true;
        const targetId = trigger.getAttribute('data-target');
        const targetMenu = document.getElementById(targetId);
        
        // Toggle current dropdown
        const isShowing = targetMenu.classList.contains('show');
        
        // Hide all first
        dropdowns.forEach(d => d.classList.remove('show'));
        
        // If it wasn't showing, show it now
        if (!isShowing) {
            targetMenu.classList.add('show');
        }
    } else {
        // Clicked outside any trigger, close all
        dropdowns.forEach(d => d.classList.remove('show'));
    }
});

// Prevent dropdown clicks from closing itself immediately
document.querySelectorAll('.dropdown-menu').forEach(menu => {
    menu.addEventListener('click', function(e) {
        e.stopPropagation();
    });
});

// Handle Theme Toggle
function toggleTheme() {
    const isDark = document.body.classList.toggle('dark-mode');
    localStorage.setItem('admin_theme', isDark ? 'dark' : 'light');
    
    // Switch Icon
    const icon = document.querySelector('#themeToggle i');
    if(isDark) {
        icon.classList.remove('fa-gear'); // Or fa-moon
        icon.classList.add('fa-sun');
    } else {
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-gear'); // Or fa-moon
    }
}

// Load Theme on start
if (localStorage.getItem('admin_theme') === 'dark') {
    document.body.classList.add('dark-mode');
    document.querySelector('#themeToggle i').classList.remove('fa-gear');
    document.querySelector('#themeToggle i').classList.add('fa-sun');
}
</script>

</body>
</html>
