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
        $fname = trim($_POST['fname'] ?? $input['fname'] ?? '');
        $lname = trim($_POST['lname'] ?? $input['lname'] ?? '');
        $nickname = trim($_POST['nickname'] ?? $input['nickname'] ?? '');
        $password = trim($_POST['password'] ?? $input['password'] ?? '');

        if (!$fname || !$lname || !$nickname) {
            jsonResponse(['error' => 'Missing required fields'], 400);
        }

        $profilePicPath = null;
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            // Also check mime type using finfo if possible, but basic type check is okay for now
            if (in_array($_FILES['profile_pic']['type'], $allowedTypes)) {
                $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
                if (empty($ext)) $ext = 'jpg';
                $newFilename = 'user_' . $userId . '_' . time() . '.' . $ext;
                $uploadDir = __DIR__ . '/../assets/uploads/profiles/';
                
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $uploadDir . $newFilename)) {
                    $profilePicPath = $newFilename;
                }
            } else {
                jsonResponse(['error' => 'Invalid file type. Only JPG, PNG, WEBP, GIF are allowed.'], 400);
            }
        }

        try {
            $params = [$fname, $lname, $nickname];
            $sql = "UPDATE users SET fname=?, lname=?, nickname=?";
            
            if ($password) {
                $sql .= ", password=?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }
            if ($profilePicPath) {
                $sql .= ", profile_pic=?";
                $params[] = $profilePicPath;
            }
            
            $sql .= " WHERE id=?";
            $params[] = $userId;
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);

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
