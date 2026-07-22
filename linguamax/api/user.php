<?php
// ============================================================
// LinguaMax — User API
// Handles CRUD operations for student profile, settings, goals
// ============================================================
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Strict Login Check
if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized Access. Please login.'], 403);
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $input['action'] ?? $_POST['action'] ?? '';
$db = getDB();
$userId = $_SESSION['user_id'];

switch ($action) {
    case 'update_profile':
        $fname = trim($input['fname'] ?? '');
        $lname = trim($input['lname'] ?? '');
        $nickname = trim($input['nickname'] ?? '');
        $password = trim($input['password'] ?? '');

        if (!$fname || !$lname || !$nickname) {
            jsonResponse(['error' => 'Missing required fields'], 400);
        }

        try {
            if ($password) {
                $stmt = $db->prepare("UPDATE users SET fname=?, lname=?, nickname=?, password=? WHERE id=?");
                $stmt->execute([$fname, $lname, $nickname, password_hash($password, PASSWORD_DEFAULT), $userId]);
            } else {
                $stmt = $db->prepare("UPDATE users SET fname=?, lname=?, nickname=? WHERE id=?");
                $stmt->execute([$fname, $lname, $nickname, $userId]);
            }
            jsonResponse(['success' => true]);
        } catch (Exception $e) {
            jsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
        break;

    case 'update_settings':
        $soundEnabled = intval($input['sound_enabled'] ?? 1);
        $notificationsEnabled = intval($input['notifications_enabled'] ?? 1);

        try {
            $stmt = $db->prepare("UPDATE users SET sound_enabled=?, notifications_enabled=? WHERE id=?");
            $stmt->execute([$soundEnabled, $notificationsEnabled, $userId]);
            jsonResponse(['success' => true]);
        } catch (Exception $e) {
            jsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
        break;

    case 'update_goal':
        $dailyGoal = intval($input['daily_xp_goal'] ?? 50);

        try {
            $stmt = $db->prepare("UPDATE users SET daily_xp_goal=? WHERE id=?");
            $stmt->execute([$dailyGoal, $userId]);
            jsonResponse(['success' => true]);
        } catch (Exception $e) {
            jsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
