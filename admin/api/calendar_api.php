<?php
session_start();
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = getDB();
$action = $_GET['action'] ?? '';

try {
    if ($action === 'get_events') {
        // Fetch events for fullcalendar
        $start = $_GET['start'] ?? '';
        $end = $_GET['end'] ?? '';

        $stmt = $db->prepare("
            SELECT cs.*, c.title as course_name 
            FROM class_schedules cs
            LEFT JOIN courses c ON cs.course_id = c.id
            WHERE start_datetime >= ? AND end_datetime <= ?
        ");
        $stmt->execute([$start, $end]);
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
                    'course_id' => $s['course_id'],
                    'course_name' => $s['course_name'],
                    'notes' => $s['notes']
                ]
            ];
        }
        echo json_encode($events);
        exit;
    }

    if ($action === 'add_event') {
        $title = $_POST['title'] ?? '';
        $course_id = !empty($_POST['course_id']) ? $_POST['course_id'] : null;
        $start = $_POST['start_datetime'] ?? '';
        $end = $_POST['end_datetime'] ?? '';
        $color = $_POST['color'] ?? '#4F46E5';
        $notes = $_POST['notes'] ?? '';
        $students = isset($_POST['students']) ? $_POST['students'] : [];

        $db->beginTransaction();

        $stmt = $db->prepare("INSERT INTO class_schedules (course_id, title, start_datetime, end_datetime, color, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$course_id, $title, $start, $end, $color, $notes]);
        $schedule_id = $db->lastInsertId();

        if (!empty($students) && is_array($students)) {
            $att_stmt = $db->prepare("INSERT INTO class_attendees (schedule_id, student_id) VALUES (?, ?)");
            foreach ($students as $student_id) {
                $att_stmt->execute([$schedule_id, $student_id]);
            }
        }

        $db->commit();
        echo json_encode(['success' => true]);
        exit;
    }

    if ($action === 'delete_event') {
        $id = $_POST['id'] ?? 0;
        $stmt = $db->prepare("DELETE FROM class_schedules WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }
    
    if ($action === 'get_students') {
        // Search students to add
        $stmt = $db->query("SELECT id, username, fname, lname FROM users WHERE role = 'student'");
        $students = $stmt->fetchAll();
        echo json_encode($students);
        exit;
    }

    echo json_encode(['error' => 'Invalid Action']);

} catch (PDOException $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['error' => $e->getMessage()]);
}
