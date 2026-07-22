<?php
// ============================================================
// LinguaMax — Flashcard API
// ============================================================
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) jsonResponse(['error' => 'Unauthorized'], 401);

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$userId = $_SESSION['user_id'];
$db = getDB();

switch ($action) {
    case 'review':
        $vocabId = intval($input['vocabulary_id'] ?? 0);
        $quality = intval($input['quality'] ?? 3); // 0-5

        // Get current progress
        $stmt = $db->prepare("SELECT * FROM flashcard_progress WHERE user_id = ? AND vocabulary_id = ?");
        $stmt->execute([$userId, $vocabId]);
        $progress = $stmt->fetch();

        if ($progress) {
            $result = calculateSR($quality, $progress['ease_factor'], $progress['interval_days'], $progress['repetitions']);
            $stmt = $db->prepare("
                UPDATE flashcard_progress SET
                    ease_factor = ?, interval_days = ?, repetitions = ?,
                    next_review = ?, last_reviewed = NOW(),
                    times_correct = times_correct + ?, times_wrong = times_wrong + ?
                WHERE user_id = ? AND vocabulary_id = ?
            ");
            $stmt->execute([
                $result['ease_factor'], $result['interval_days'], $result['repetitions'],
                $result['next_review'],
                $quality >= 3 ? 1 : 0, $quality < 3 ? 1 : 0,
                $userId, $vocabId
            ]);
        } else {
            $result = calculateSR($quality, 2.5, 0, 0);
            $stmt = $db->prepare("
                INSERT INTO flashcard_progress (user_id, vocabulary_id, ease_factor, interval_days, repetitions, next_review, last_reviewed, times_correct, times_wrong)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)
            ");
            $stmt->execute([
                $userId, $vocabId,
                $result['ease_factor'], $result['interval_days'], $result['repetitions'],
                $result['next_review'],
                $quality >= 3 ? 1 : 0, $quality < 3 ? 1 : 0
            ]);
        }

        addXP($userId, XP_PER_FLASHCARD);
        updateStreak($userId);
        $newBadges = checkAndAwardBadges($userId);

        jsonResponse([
            'success' => true,
            'next_review' => $result['next_review'],
            'interval' => $result['interval_days'],
            'new_badges' => $newBadges,
        ]);
        break;

    case 'get_cards':
        $level = $input['level'] ?? 'all';
        $limit = min(intval($input['limit'] ?? 20), 50);

        $query = "
            SELECT v.*, fp.ease_factor, fp.interval_days, fp.repetitions, fp.next_review
            FROM vocabulary v
            LEFT JOIN flashcard_progress fp ON v.id = fp.vocabulary_id AND fp.user_id = ?
            WHERE (fp.next_review <= CURDATE() OR fp.id IS NULL)
        ";
        $params = [$userId];

        if ($level !== 'all') {
            $query .= " AND v.level = ?";
            $params[] = $level;
        }

        $query .= " ORDER BY fp.next_review ASC, RAND() LIMIT ?";
        $params[] = $limit;

        $stmt = $db->prepare($query);
        $stmt->execute($params);
        jsonResponse(['cards' => $stmt->fetchAll()]);
        break;

    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}
