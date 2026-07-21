<?php
// Exams API
session_start();
require_once __DIR__ . '/../includes/functions.php';
if (!isLoggedIn()) jsonResponse(['error' => 'Unauthorized'], 401);

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$userId = $_SESSION['user_id'];
$db = getDB();

switch ($action) {
    case 'submit':
        $examId = intval($input['exam_id'] ?? 0);
        $score = intval($input['score'] ?? 0);
        $total = intval($input['total'] ?? 0);
        $pct = floatval($input['percentage'] ?? 0);
        $timeSpent = intval($input['time_spent'] ?? 0);
        $answers = json_encode($input['answers'] ?? []);

        $stmt = $db->prepare("INSERT INTO exam_results (user_id, exam_id, score, total, percentage, time_spent, answers_json) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $examId, $score, $total, $pct, $timeSpent, $answers]);

        addXP($userId, XP_PER_EXAM);
        updateStreak($userId);

        // Check daily challenge
        $challenge = getTodayChallenge();
        if ($challenge['challenge_type'] === 'quiz' && !isDailyChallengeCompleted($userId)) {
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
