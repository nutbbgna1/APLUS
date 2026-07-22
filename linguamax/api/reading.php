<?php
// Reading API
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isLoggedIn()) jsonResponse(['error' => 'Unauthorized'], 401);

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$userId = $_SESSION['user_id'];
$db = getDB();

switch ($action) {
    case 'save_progress':
        $passageId = intval($input['passage_id'] ?? 0);
        $score = intval($input['score'] ?? 0);
        $total = intval($input['total'] ?? 0);

        $stmt = $db->prepare("INSERT INTO reading_progress (user_id, passage_id, score, total) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $passageId, $score, $total]);

        addXP($userId, XP_PER_READING);
        updateStreak($userId);

        // Check daily challenge
        $challenge = getTodayChallenge();
        if ($challenge['challenge_type'] === 'reading' && !isDailyChallengeCompleted($userId)) {
            $stmt = $db->prepare("INSERT INTO user_daily_progress (user_id, challenge_date, completed, completed_at) VALUES (?, CURDATE(), TRUE, NOW()) ON DUPLICATE KEY UPDATE completed = TRUE, completed_at = NOW()");
            $stmt->execute([$userId]);
            addXP($userId, XP_PER_DAILY);
        }

        $newBadges = checkAndAwardBadges($userId);
        jsonResponse(['success' => true, 'new_badges' => $newBadges]);
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
