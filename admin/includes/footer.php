<style>
/* Dropdown Styles */
.dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    border: 1px solid var(--border);
    margin-top: 10px;
    z-index: 1000;
    overflow: hidden;
    min-width: 150px;
    text-align: left;
}
.dropdown-menu.show {
    display: block;
    animation: fadeInDown 0.2s ease-out;
}
.dropdown-header {
    padding: 12px 15px;
    background: #F8FAFC;
    border-bottom: 1px solid var(--border);
    font-weight: 700;
    font-size: 0.9rem;
    color: var(--text);
}
.dropdown-content {
    max-height: 300px;
    overflow-y: auto;
}
.dropdown-item {
    display: block;
    padding: 10px 15px;
    color: var(--text);
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s;
}
.dropdown-item:hover {
    background: #F1F5F9;
}
.dropdown-footer {
    display: block;
    padding: 10px;
    text-align: center;
    background: #F8FAFC;
    border-top: 1px solid var(--border);
    color: #3B82F6;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
}
.dropdown-footer:hover {
    text-decoration: underline;
}
.dropdown-trigger {
    position: relative;
    cursor: pointer;
}
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Dark Mode Overrides (Basic example) */
body.dark-mode {
    --bg-color: #0F172A;
    --surface: #1E293B;
    --text: #F8FAFC;
    --text-muted: #94A3B8;
    --border: #334155;
    background: var(--bg-color);
    color: var(--text);
}
body.dark-mode .sidebar, body.dark-mode .topbar, body.dark-mode .card, body.dark-mode .dropdown-menu {
    background: var(--surface);
    border-color: var(--border);
}
body.dark-mode .search-bar, body.dark-mode .search-bar input {
    background: #0F172A;
    color: white;
}
body.dark-mode .dropdown-header, body.dark-mode .dropdown-footer {
    background: #0F172A;
}
body.dark-mode .dropdown-item:hover {
    background: #334155;
}
</style>

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
