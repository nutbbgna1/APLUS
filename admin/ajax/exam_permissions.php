<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Admin Auth Check
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/config.php';
$db = getDB();

// Robust Action Parsing
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if (empty($action) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    if (!empty($raw)) {
        $json = json_decode($raw, true);
        if (is_array($json) && isset($json['action'])) {
            $action = $json['action'];
            $_POST = array_merge($_POST, $json);
        }
    }
}

if ($action === 'get') {
    $user_id = (int)($_GET['user_id'] ?? 0);
    $stmt = $db->prepare("SELECT exam_id FROM exam_permissions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $exams = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo json_encode(['success' => true, 'exams' => $exams]);
    exit;
}

if ($action === 'save') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $exams = $_POST['exams'] ?? [];
    
    if (!$user_id) {
        echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
        exit;
    }
    
    try {
        $db->beginTransaction();
        
        $stmt = $db->prepare("DELETE FROM exam_permissions WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        if (!empty($exams) && is_array($exams)) {
            $stmt = $db->prepare("INSERT INTO exam_permissions (user_id, exam_id) VALUES (?, ?)");
            foreach ($exams as $eid) {
                $stmt->execute([$user_id, (int)$eid]);
            }
        }
        
        $db->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Return debug info if action is still empty
$debugMsg = "Invalid action. Method: " . $_SERVER['REQUEST_METHOD'] . " | POST: " . json_encode($_POST) . " | GET: " . json_encode($_GET);
echo json_encode([
    'success' => false, 
    'error' => $debugMsg
]);
