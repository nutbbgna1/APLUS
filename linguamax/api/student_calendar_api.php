<?php
session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = getDB();
$student_id = $_SESSION['user_id'];
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';

try {
    $stmt = $db->prepare("
        SELECT cs.*, c.title as course_name 
        FROM class_schedules cs
        JOIN class_attendees ca ON cs.id = ca.schedule_id
        LEFT JOIN courses c ON cs.course_id = c.id
        WHERE ca.student_id = ? AND cs.start_datetime >= ? AND cs.end_datetime <= ?
    ");
    $stmt->execute([$student_id, $start, $end]);
    $schedules = $stmt->fetchAll();

    $events = [];
    foreach ($schedules as $s) {
        $events[] = [
            'id' => $s['id'],
            'title' => $s['title'],
            'start' => $s['start_datetime'],
            'end' => $s['end_datetime'],
            'color' => $s['color'],
            'extendedProps' => [
                'course_name' => $s['course_name'],
                'notes' => $s['notes']
            ]
        ];
    }
    
    echo json_encode($events);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
