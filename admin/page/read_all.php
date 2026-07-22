<?php
$type = $_GET['type'] ?? '';
if ($type === 'notifications') {
    $db->exec("UPDATE admin_notifications SET is_read=1 WHERE is_read=0");
} elseif ($type === 'messages') {
    $db->exec("UPDATE admin_messages SET is_read=1 WHERE is_read=0");
}
$redirect = $_SERVER['HTTP_REFERER'] ?? '?page=dashboard';
header("Location: " . $redirect);
exit;
