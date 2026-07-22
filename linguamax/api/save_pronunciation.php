<?php
// ============================================================
// LinguaMax — API: Save Pronunciation Progress
// ============================================================
session_start();
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$averageScore = isset($data['average_score']) ? intval($data['average_score']) : 0;

if ($averageScore < 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid score']);
    exit;
}

$userId = $_SESSION['user_id'];

// Calculate Rewards
// e.g. Base XP + bonus for high scores
$xpEarned = 50 + ($averageScore >= 80 ? 50 : 0);
// Coins = 1 for every 10 points
$coinsEarned = max(0, round($averageScore / 10));

try {
    // Add rewards
    addXP($userId, $xpEarned);
    addCoins($userId, $coinsEarned);
    
    // Update daily streak
    updateStreak($userId);
    
    // Log daily progress
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO user_activity_log (user_id, activity_type, activity_details) VALUES (?, 'pronunciation', 'Completed Pronunciation Practice')");
    $stmt->execute([$userId]);

    echo json_encode([
        'success' => true,
        'xp_earned' => $xpEarned,
        'coins_earned' => $coinsEarned
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
