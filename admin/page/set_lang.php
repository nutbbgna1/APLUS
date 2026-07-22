<?php
session_start();
if (isset($_GET['lang']) && in_array($_GET['lang'], ['th', 'en'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$redirect = $_SERVER['HTTP_REFERER'] ?? '?page=dashboard';
header("Location: " . $redirect);
exit;
