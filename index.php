<?php
// ============================================================
// LinguaMax — Main Router / Entry Point
// ============================================================
session_start();
require_once __DIR__ . '/includes/functions.php';

$page = $_GET['page'] ?? '';

// If not logged in, show login
if (!isLoggedIn() && !in_array($page, ['login', 'install'])) {
    $page = 'login';
}

// Route to correct page
switch ($page) {
    case 'login':
        include __DIR__ . '/pages/auth/login.php';
        break;
    case 'install':
        include __DIR__ . '/pages/system/install.php';
        break;
    case 'dashboard':
        requireLogin();
        include __DIR__ . '/pages/student/dashboard.php';
        break;
    case 'lessons':
        requireLogin();
        include __DIR__ . '/pages/learning/lessons.php';
        break;
    case 'lesson':
        requireLogin();
        include __DIR__ . '/pages/learning/lesson_view.php';
        break;
    case 'flashcards':
        requireLogin();
        include __DIR__ . '/pages/practice/flashcards.php';
        break;
    case 'games':
        requireLogin();
        include __DIR__ . '/pages/practice/games.php';
        break;
    case 'reading':
        requireLogin();
        include __DIR__ . '/pages/learning/reading.php';
        break;
    case 'reading-view':
        requireLogin();
        include __DIR__ . '/pages/learning/reading_view.php';
        break;
    case 'exams':
        requireLogin();
        include __DIR__ . '/pages/exam/exams.php';
        break;
    case 'exam':
        requireLogin();
        include __DIR__ . '/pages/exam/exam_take.php';
        break;
    case 'exam-result':
        requireLogin();
        include __DIR__ . '/pages/exam/exam_result.php';
        break;
    case 'leaderboard':
        requireLogin();
        include __DIR__ . '/pages/student/leaderboard.php';
        break;
    case 'achievements':
        requireLogin();
        include __DIR__ . '/pages/student/achievements.php';
        break;
    case 'profile':
        requireLogin();
        include __DIR__ . '/pages/student/profile.php';
        break;
    case 'admin':
        requireAdmin();
        $sub = $_GET['sub'] ?? 'dashboard';
        include __DIR__ . '/pages/admin/' . basename($sub) . '.php';
        break;
    case 'logout':
        session_destroy();
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    default:
        if (isLoggedIn()) {
            if (isAdmin()) {
                header('Location: ' . SITE_URL . '/index.php?page=admin&sub=dashboard');
            } else {
                header('Location: ' . SITE_URL . '/index.php?page=dashboard');
            }
        } else {
            include __DIR__ . '/pages/auth/login.php';
        }
        break;
}
