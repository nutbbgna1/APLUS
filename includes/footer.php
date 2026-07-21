</div><!-- /.page -->

<?php if (!isAdmin()): ?>
<!-- Bottom Navigation (Mobile) -->
<nav class="bottom-nav">
        <a href="?page=dashboard" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i>
            <span>Home</span>
        </a>
        <a href="?page=lessons" class="nav-item <?= in_array($currentPage, ['lessons','lesson']) ? 'active' : '' ?>">
            <i class="fa-solid fa-table-cells-large"></i>
            <span>Topic</span>
        </a>
        <a href="?page=flashcards" class="nav-item <?= $currentPage === 'flashcards' ? 'active' : '' ?>">
            <i class="fa-solid fa-book-open"></i>
            <span>Learning</span>
        </a>
        <a href="?page=games" class="nav-item <?= in_array($currentPage, ['games','exams','exam']) ? 'active' : '' ?>">
            <i class="fa-solid fa-gamepad"></i>
            <span>Practice</span>
        </a>
        <a href="?page=profile" class="nav-item <?= $currentPage === 'profile' ? 'active' : '' ?>">
            <i class="fa-solid fa-user"></i>
            <span>Profile</span>
        </a>
</nav>
<?php else: ?>
<nav class="bottom-nav">
    <?php $sub = $_GET['sub'] ?? 'dashboard'; ?>
    <a href="?page=admin&sub=dashboard" class="nav-item <?= $sub === 'dashboard' ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fa-solid fa-chart-pie"></i></span> Dashboard
    </a>
    <a href="?page=admin&sub=students" class="nav-item <?= $sub === 'students' ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fa-solid fa-users"></i></span> นักเรียน
    </a>
    <a href="?page=admin&sub=content" class="nav-item <?= $sub === 'content' ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fa-solid fa-folder-tree"></i></span> เนื้อหา
    </a>
    <a href="?page=admin&sub=reports" class="nav-item <?= $sub === 'reports' ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fa-solid fa-chart-line"></i></span> รายงาน
    </a>
</nav>
<?php endif; ?>

<!-- Toast container -->
<div id="toast-container"></div>

<!-- Core JS -->
<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
<script src="<?= SITE_URL ?>/assets/js/tts.js"></script>
</body>
</html>
