<?php
// ============================================================
// LinguaMax — Games API
// ============================================================
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) jsonResponse(['error' => 'Unauthorized'], 401);

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$userId = $_SESSION['user_id'];
$db = getDB();

switch ($action) {
    case 'save_score':
        $gameType = $input['game_type'] ?? '';
        $score = intval($input['score'] ?? 0);
        $timeSpent = intval($input['time_spent'] ?? 0);

        if (!in_array($gameType, ['match_pairs', 'sentence_order', 'fill_blank'])) {
            jsonResponse(['error' => 'Invalid game type'], 400);
        }

        $stmt = $db->prepare("INSERT INTO game_scores (user_id, game_type, score, max_score, time_spent) VALUES (?, ?, ?, 100, ?)");
        $stmt->execute([$userId, $gameType, $score, $timeSpent]);

        addXP($userId, XP_PER_GAME);
        updateStreak($userId);
        $newBadges = checkAndAwardBadges($userId);

        // Check daily challenge
        $challenge = getTodayChallenge();
        if ($challenge['challenge_type'] === 'game' && !isDailyChallengeCompleted($userId)) {
            if ($score >= 70) {
                $stmt = $db->prepare("INSERT INTO user_daily_progress (user_id, challenge_date, completed, completed_at) VALUES (?, CURDATE(), TRUE, NOW()) ON DUPLICATE KEY UPDATE completed = TRUE, completed_at = NOW()");
                $stmt->execute([$userId]);
                addXP($userId, XP_PER_DAILY);
            }
        }

        jsonResponse(['success' => true, 'new_badges' => $newBadges]);
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
