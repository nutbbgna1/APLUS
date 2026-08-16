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

// Secure physical path inside linguamax
$file_to_serve = __DIR__ . '/../../' . $rel_path;
// Normalize the path to prevent directory traversal
$file_to_serve = realpath($file_to_serve);

// Ensure the file is inside the linguamax directory
$base_dir = realpath(__DIR__ . '/../../');
$is_file_missing = (!$file_to_serve || strpos($file_to_serve, $base_dir) !== 0 || !file_exists($file_to_serve));

if ($is_file_missing) {
    // Attempt automatic recovery from the old folder structure
    // Use dirname() to completely avoid '../' which triggers Hostinger's ModSecurity WAF
    $document_root = $_SERVER['DOCUMENT_ROOT']; // e.g. .../English_web/linguamax
    
    // Check if the document root is actually linguamax
    if (basename($document_root) === 'linguamax') {
        $parent_dir = dirname($document_root); // e.g. .../English_web
        $old_file_path = $parent_dir . '/' . $rel_path;
        
        if (file_exists($old_file_path) && is_file($old_file_path)) {
            // Found it! Let's copy it to the new location so it works permanently
            $new_dir = dirname(__DIR__ . '/../../' . $rel_path);
            if (!is_dir($new_dir)) {
                mkdir($new_dir, 0777, true);
            }
            if (copy($old_file_path, __DIR__ . '/../../' . $rel_path)) {
                // Successfully recovered! Set the file_to_serve to the new copied file
                $file_to_serve = realpath(__DIR__ . '/../../' . $rel_path);
            }
        }
    }
}

if (!$file_to_serve || !file_exists($file_to_serve)) {
    die("Error: File not found. Please contact the administrator to re-upload this document.");
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
