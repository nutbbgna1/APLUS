</div><!-- /.page -->

<?php if (!isAdmin()): ?>
<!-- Bottom Navigation (Mobile) -->
<nav class="bottom-nav">
        <a href="?page=dashboard" class="nav-item <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
            <img src="<?= SITE_URL ?>/assets/SVG/School.svg" style="width:24px; height:24px; margin-bottom:4px; filter: drop-shadow(0 2px 2px rgba(0,0,0,0.1));">
            <span>Home</span>
        </a>
        <a href="?page=lessons" class="nav-item <?= in_array($currentPage, ['lessons','lesson','pronunciation','reading']) ? 'active' : '' ?>">
            <img src="<?= SITE_URL ?>/assets/SVG/Open book.svg" style="width:24px; height:24px; margin-bottom:4px; filter: drop-shadow(0 2px 2px rgba(0,0,0,0.1));">
            <span>Learn</span>
        </a>
        <a href="?page=exams" class="nav-item <?= in_array($currentPage, ['exams','exam_take','exam_result']) ? 'active' : '' ?>">
            <img src="<?= SITE_URL ?>/assets/SVG/Test A+.svg" style="width:24px; height:24px; margin-bottom:4px; filter: drop-shadow(0 2px 2px rgba(0,0,0,0.1));">
            <span>Exams</span>
        </a>
        <a href="?page=classroom" class="nav-item <?= $currentPage === 'classroom' ? 'active' : '' ?>">
            <img src="<?= SITE_URL ?>/assets/SVG/University.svg" style="width:24px; height:24px; margin-bottom:4px; filter: drop-shadow(0 2px 2px rgba(0,0,0,0.1));">
            <span>Classroom</span>
        </a>
        <a href="?page=profile" class="nav-item <?= $currentPage === 'profile' ? 'active' : '' ?>">
            <img src="<?= SITE_URL ?>/assets/SVG/Boy Student.svg" style="width:24px; height:24px; margin-bottom:4px; filter: drop-shadow(0 2px 2px rgba(0,0,0,0.1));">
            <span>Profile</span>
        </a>
</nav>
<?php else: ?>
<nav class="bottom-nav">
    <?php $sub = $_GET['sub'] ?? 'dashboard'; ?>
    <a href="?page=admin&sub=dashboard" class="nav-item <?= $sub === 'dashboard' ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fa-solid fa-chart-pie"></i></span> Dash
    </a>
    <a href="?page=admin&sub=students" class="nav-item <?= $sub === 'students' ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fa-solid fa-users"></i></span> นร.
    </a>
    <a href="?page=admin&sub=content" class="nav-item <?= $sub === 'content' ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fa-solid fa-folder-tree"></i></span> เนื้อหา
    </a>
    <a href="?page=admin&sub=exam_permissions" class="nav-item <?= $sub === 'exam_permissions' ? 'active' : '' ?>">
        <span class="nav-icon"><i class="fa-solid fa-key"></i></span> สิทธิ์สอบ
    </a>
    <a href="?page=logout" class="nav-item" style="color: #EF4444;">
        <span class="nav-icon"><i class="fa-solid fa-arrow-right-from-bracket"></i></span> ออก
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
