<?php
// Survey Submit API
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Unauthorized'], 401);
}

$inputStr = $_POST['payload'] ?? file_get_contents('php://input');
$input = json_decode($inputStr, true);
if (!$input) {
    $method = $_SERVER['REQUEST_METHOD'];
    jsonResponse(['error' => 'Invalid JSON. Method: ' . $method . '. Received: ' . substr($inputStr, 0, 100)], 400);
}

$userId = $_SESSION['user_id'];
$db = getDB();

try {
    // Check if already submitted
    $checkStmt = $db->prepare("SELECT id FROM user_surveys WHERE user_id = ?");
    $checkStmt->execute([$userId]);
    if ($checkStmt->fetch()) {
        jsonResponse(['error' => 'Survey already submitted'], 400);
    }

    $answersJson = json_encode($input);

    $stmt = $db->prepare("INSERT INTO user_surveys (user_id, answers_json) VALUES (?, ?)");
    $stmt->execute([$userId, $answersJson]);
    
    // Give 50 coins and 50 XP as a reward for completing survey
    addCoins($userId, 50);
    addXP($userId, 50);

    jsonResponse(['success' => true, 'message' => 'Survey submitted successfully']);
} catch (PDOException $e) {
    jsonResponse(['error' => $e->getMessage()], 500);
}
