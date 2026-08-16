<?php
// ============================================================
// LinguaMax — Secure File Downloader
// ============================================================
require_once __DIR__ . '/../../config/database.php';

// Ensure user is logged in
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

$db = getDB();
$mat_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch material info
$stmt = $db->prepare("SELECT * FROM course_materials WHERE id = ?");
$stmt->execute([$mat_id]);
$mat = $stmt->fetch();

if (!$mat) {
    die("File not found in database.");
}

$course_id = $mat['course_id'];

// Check enrollment (to ensure they bought the course)
$stmt = $db->prepare("SELECT * FROM course_enrollments WHERE user_id = ? AND course_id = ? AND status = 'approved'");
$stmt->execute([$user_id, $course_id]);
if (!$stmt->fetch()) {
    die("You do not have permission to download this file. Please purchase the course first.");
}

// Get the relative file path from DB
$rel_path = $mat['file_url']; // e.g., "uploads/courses/materials/mat_123.pdf"

// Try multiple possible physical locations to find the file
$possible_paths = [
    // 1. New location: inside linguamax/uploads/
    __DIR__ . '/../../' . $rel_path,
    
    // 2. Old location: in the root English_web/uploads/
    __DIR__ . '/../../../' . $rel_path,
    
    // 3. Fallback absolute paths based on typical setup
    $_SERVER['DOCUMENT_ROOT'] . '/' . $rel_path,
    $_SERVER['DOCUMENT_ROOT'] . '/linguamax/' . $rel_path
];

$file_to_serve = null;
foreach ($possible_paths as $path) {
    if (file_exists($path) && is_file($path)) {
        $file_to_serve = $path;
        break;
    }
}

if (!$file_to_serve) {
    die("Error: File physically not found on the server. Please contact the administrator to re-upload it.");
}

// Force the download
$file_name = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $mat['title']) . '.pdf';
$file_size = filesize($file_to_serve);

header('Content-Description: File Transfer');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . $file_size);

// Read the file and send to output
readfile($file_to_serve);
exit;
