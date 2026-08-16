<?php
// ============================================================
// LinguaMax — Main Router / Entry Point
// ============================================================
// Set session timeout to 24 hours (86400 seconds)
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(0);
session_start();
require_once __DIR__ . '/includes/functions.php';

$page = $_GET['page'] ?? '';

// If not logged in, show login
if (!isLoggedIn() && !in_array($page, ['login', 'install', 'register'])) {
    $page = 'login';
}

// Route to correct page
switch ($page) {
    case 'login':
        include __DIR__ . '/pages/auth/login.php';
        break;
    case 'register':
        include __DIR__ . '/pages/auth/register.php';
        break;
    case 'install':
        include __DIR__ . '/pages/system/install.php';
        break;
    case 'dashboard':
        requireLogin();
        include __DIR__ . '/pages/student/dashboard.php';
        break;
    case 'classroom':
        requireLogin();
        include __DIR__ . '/pages/student/classroom.php';
        break;
    case 'classroom-view':
        requireLogin();
        include __DIR__ . '/pages/student/classroom_view.php';
        break;
    case 'download':
        requireLogin();
        include __DIR__ . '/pages/student/download.php';
        break;
    case 'lessons':
        requireLogin();
        include __DIR__ . '/pages/learning/lessons.php';
        break;
    case 'lesson':
        requireLogin();
        include __DIR__ . '/pages/learning/lesson_view.php';
        break;
    case 'pronunciation':
        requireLogin();
        include __DIR__ . '/pages/practice/pronunciation.php';
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
    case 'pretest':
        requireLogin();
        include __DIR__ . '/pages/exam/pretest_take.php';
        break;
    case 'pretest-result':
        requireLogin();
        include __DIR__ . '/pages/exam/pretest_result.php';
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
    case 'survey':
        requireLogin();
        include __DIR__ . '/pages/student/survey.php';
        break;
    case 'edit-profile':
        requireLogin();
        include __DIR__ . '/pages/student/edit_profile.php';
        break;
    case 'settings':
        requireLogin();
        include __DIR__ . '/pages/student/settings.php';
        break;
    case 'learning-goals':
        requireLogin();
        include __DIR__ . '/pages/student/learning_goals.php';
        break;
    case 'support':
        requireLogin();
        include __DIR__ . '/pages/student/support.php';
        break;
    case 'logout':
        session_destroy();
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    default:
        if (isLoggedIn()) {
            header('Location: ' . SITE_URL . '/index.php?page=dashboard');
        } else {
            include __DIR__ . '/pages/auth/login.php';
        }
        break;
}
