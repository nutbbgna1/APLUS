<?php
// ============================================================
// LinguaMax — API: Fetch Activity Log
// ============================================================
session_start();
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$month = intval($_GET['month'] ?? date('m'));
$year = intval($_GET['year'] ?? date('Y'));

try {
    $db = getDB();
    // Fetch activities for the specified month
    $stmt = $db->prepare("
        SELECT id, activity_type, activity_details, created_at, DATE(created_at) as activity_date, TIME(created_at) as activity_time
        FROM user_activity_log 
        WHERE user_id = ? 
        AND MONTH(created_at) = ? 
        AND YEAR(created_at) = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$userId, $month, $year]);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Group by date
    $grouped = [];
    foreach ($activities as $act) {
        $date = $act['activity_date'];
        if (!isset($grouped[$date])) {
            $grouped[$date] = [];
        }
        $grouped[$date][] = [
            'id' => $act['id'],
            'type' => $act['activity_type'],
            'details' => $act['activity_details'],
            'time' => date('H:i', strtotime($act['activity_time']))
        ];
    }

    echo json_encode([
        'success' => true,
        'month' => $month,
        'year' => $year,
        'data' => $grouped
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
