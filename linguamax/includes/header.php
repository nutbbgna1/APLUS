<?php
// ============================================================
// LinguaMax — Shared Header
// ============================================================
$currentUser = getCurrentUser();
$currentPage = $_GET['page'] ?? 'dashboard';
$streak = isLoggedIn() ? getStreak($_SESSION['user_id']) : ['current_streak' => 0];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="description" content="LinguaMax — แพลตฟอร์มเรียนภาษาอังกฤษออนไลน์ เรียนสนุก เล่นเกม ท่องศัพท์ ฟังเสียง Native Speaker">
    <title>LinguaMax — English Learning Platform</title>
    <link rel="icon" type="image/png" href="<?= SITE_URL ?>/../favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Sarabun:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css?v=1.0.0">
</head>
<body>
<?php include_once __DIR__ . '/device_check.php'; ?>

<!-- Background Bubbles -->
<div class="bg-bubbles">
    <span></span><span></span><span></span><span></span>
</div>

<!-- Header -->
<div class="header">
    <div class="header-inner">
        <a href="<?= SITE_URL ?>/index.php?page=dashboard" class="logo">
            <div class="logo-icon">E</div>
            LinguaMax
        </a>

        <?php if (!isAdmin()): ?>
        <!-- Desktop Nav (Student) -->
        <nav class="desktop-nav">
            <a href="?page=dashboard" class="<?= $currentPage === 'dashboard' ? 'active' : '' ?>"><img src="<?= SITE_URL ?>/assets/SVG/School.svg" style="width:22px; height:22px; margin-right:4px;"> Home</a>
            <a href="?page=lessons" class="<?= $currentPage === 'lessons' || $currentPage === 'lesson' ? 'active' : '' ?>"><img src="<?= SITE_URL ?>/assets/SVG/Open book.svg" style="width:22px; height:22px; margin-right:4px;"> Lessons</a>
            <a href="?page=pronunciation" class="<?= $currentPage === 'pronunciation' ? 'active' : '' ?>"><img src="<?= SITE_URL ?>/assets/SVG/Brain.svg" style="width:22px; height:22px; margin-right:4px;"> Pronunciation</a>
            <a href="?page=games" class="<?= $currentPage === 'games' ? 'active' : '' ?>"><img src="<?= SITE_URL ?>/assets/SVG/Rocket.svg" style="width:22px; height:22px; margin-right:4px;"> Games</a>
            <a href="?page=reading" class="<?= $currentPage === 'reading' ? 'active' : '' ?>"><img src="<?= SITE_URL ?>/assets/SVG/Blackboard Reading.svg" style="width:22px; height:22px; margin-right:4px;"> Reading</a>
            <a href="?page=exams" class="<?= $currentPage === 'exams' ? 'active' : '' ?>"><img src="<?= SITE_URL ?>/assets/SVG/Test A+.svg" style="width:22px; height:22px; margin-right:4px;"> Exams</a>
            <a href="?page=classroom" class="<?= $currentPage === 'classroom' ? 'active' : '' ?>"><img src="<?= SITE_URL ?>/assets/SVG/University.svg" style="width:22px; height:22px; margin-right:4px;"> Classroom</a>
        </nav>

        <!-- Header Stats -->
        <div class="header-stats">
            <div class="header-stat stat-xp"><i class="fa-solid fa-star" style="color:var(--accent);"></i> <?= number_format($currentUser['xp'] ?? 0) ?> XP</div>
            <div class="header-stat stat-coins"><i class="fa-solid fa-coins" style="color:var(--accent);"></i> <?= number_format($currentUser['coins'] ?? 0) ?></div>
            <div class="header-stat stat-streak"><i class="fa-solid fa-fire" style="color:var(--danger);"></i> <?= $streak['current_streak'] ?></div>
        </div>
        <?php else: ?>
        <!-- Desktop Nav (Admin) -->
        <nav class="desktop-nav">
            <?php $sub = $_GET['sub'] ?? 'dashboard'; ?>
            <a href="?page=admin&sub=dashboard" class="<?= $sub === 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            <a href="?page=admin&sub=students" class="<?= $sub === 'students' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> Students</a>
            <a href="?page=admin&sub=content" class="<?= $sub === 'content' ? 'active' : '' ?>"><i class="fa-solid fa-folder-tree"></i> Content</a>
            <a href="?page=admin&sub=reports" class="<?= $sub === 'reports' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> Reports</a>
        </nav>
        <?php endif; ?>

        <a href="?page=<?= isAdmin() ? 'logout' : 'profile' ?>" class="user-pill">
            <div class="avatar" style="background: <?= !empty($currentUser['profile_pic']) ? 'url(\'' . SITE_URL . '/assets/uploads/profiles/' . htmlspecialchars($currentUser['profile_pic']) . '\') center/cover' : htmlspecialchars($currentUser['avatar_color'] ?? '#6C63FF') ?>; color: <?= !empty($currentUser['profile_pic']) ? 'transparent' : 'white' ?>;">
                <?= empty($currentUser['profile_pic']) ? mb_substr($currentUser['fname'] ?? 'A', 0, 1) : '' ?>
            </div>
            <?= $currentUser['nickname'] ?? 'Admin' ?>
        </a>
    </div>
</div>

<div class="page">
