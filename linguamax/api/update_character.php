<?php
// ============================================================
// LinguaMax — API: Update Character Selection
// ============================================================
session_start();
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$characterId = trim($input['character_id'] ?? '');

if (empty($characterId)) {
    echo json_encode(['success' => false, 'error' => 'No character ID provided']);
    exit;
}

$userId = $_SESSION['user_id'];
$db = getDB();

try {
    $stmt = $db->prepare("UPDATE users SET character_id = ? WHERE id = ?");
    $stmt->execute([$characterId, $userId]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
