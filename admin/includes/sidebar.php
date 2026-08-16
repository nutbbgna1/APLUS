<div class="sidebar">
    <a href="?page=dashboard" class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-store"></i></div>
        <span>master<br><small style="font-size:0.75rem; color:var(--text-muted); line-height:1;">LinguaMax Admin</small></span>
    </a>
    
    <div class="nav-section">Main Menu</div>
    <a href="?page=dashboard" class="nav-link <?= $page == 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Dashboard</a>
    <a href="?page=courses" class="nav-link <?= $page == 'courses' || $page == 'course_edit' ? 'active' : '' ?>"><i class="fa-solid fa-book"></i> Courses</a>
    <a href="?page=categories" class="nav-link <?= $page == 'categories' ? 'active' : '' ?>"><i class="fa-solid fa-list"></i> Categories</a>
    <a href="?page=students" class="nav-link <?= $page == 'students' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> Students</a>
    <a href="?page=calendar" class="nav-link <?= $page == 'calendar' ? 'active' : '' ?>"><i class="fa-regular fa-calendar-days"></i> Calendar / Schedule</a>
    <a href="?page=surveys" class="nav-link <?= $page == 'surveys' ? 'active' : '' ?>"><i class="fa-solid fa-clipboard-list"></i> Student Surveys</a>
    
    <div class="nav-section">Point of Sales</div>
    <a href="?page=pos" class="nav-link <?= $page == 'pos' ? 'active' : '' ?>"><i class="fa-solid fa-cash-register"></i> Sell Course <span class="badge">POS</span></a>
    <a href="?page=orders" class="nav-link <?= $page == 'orders' ? 'active' : '' ?>"><i class="fa-solid fa-receipt"></i> Orders & Approvals</a>
    <a href="?page=accounting" class="nav-link <?= $page == 'accounting' ? 'active' : '' ?>"><i class="fa-solid fa-wallet"></i> Central Accounting</a>
    
    <div class="nav-section">Content Manager</div>
    <a href="?page=lessons" class="nav-link <?= $page == 'lessons' ? 'active' : '' ?>"><i class="fa-solid fa-person-chalkboard"></i> Lessons</a>
    <a href="?page=vocabulary" class="nav-link <?= $page == 'vocabulary' ? 'active' : '' ?>"><i class="fa-solid fa-font"></i> Vocabulary</a>
    <a href="?page=reading" class="nav-link <?= $page == 'reading' ? 'active' : '' ?>"><i class="fa-solid fa-book-open-reader"></i> Reading</a>
    <a href="?page=minigames" class="nav-link <?= $page == 'minigames' ? 'active' : '' ?>"><i class="fa-solid fa-gamepad"></i> Mini Games</a>
    <a href="?page=exams" class="nav-link <?= $page == 'exams' || $page == 'exam_questions' ? 'active' : '' ?>"><i class="fa-solid fa-file-signature"></i> Exams</a>
    <a href="?page=exam_permissions" class="nav-link <?= $page == 'exam_permissions' ? 'active' : '' ?>"><i class="fa-solid fa-user-lock"></i> Exam Permissions</a>
    <a href="?page=exam_results" class="nav-link <?= $page == 'exam_results' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> Exam Results</a>
    
    <div style="flex:1;"></div>
    <div class="nav-section">Settings</div>
    <a href="?page=payment_settings" class="nav-link <?= $page == 'payment_settings' ? 'active' : '' ?>"><i class="fa-solid fa-money-check-dollar"></i> Payment Settings</a>
    <a href="?page=api_settings" class="nav-link <?= $page == 'api_settings' ? 'active' : '' ?>"><i class="fa-solid fa-robot"></i> AI API Settings</a>
    <a href="#" class="nav-link" style="margin-bottom: 20px;"><i class="fa-solid fa-arrow-right-from-bracket"></i> Log Out</a>
</div>
