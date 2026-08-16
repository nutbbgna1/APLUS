<?php
session_start();
require_once __DIR__ . '/config/config.php';

$page = $_GET['page'] ?? 'dashboard';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$db = getDB();

$allowed_pages = [
    'dashboard', 'courses', 'course_edit', 'categories', 'orders', 
    'students', 'pos', 'lessons', 'vocabulary', 
    'reading', 'minigames', 'exams', 'exam_questions', 'exam_permissions', 'payment_settings', 'api_settings', 'accounting', 'search', 'logout', 'set_lang', 'read_all', 'surveys', 'calendar'
];

if (!in_array($page, $allowed_pages)) {
    $page = 'dashboard';
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="main-wrapper">
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>
    <div class="main-content">
        <?php
            $page_file = __DIR__ . "/page/{$page}.php";
            if (file_exists($page_file)) {
                include $page_file;
            } else {
                echo "<h2>Page Not Found</h2>";
            }
        ?>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
