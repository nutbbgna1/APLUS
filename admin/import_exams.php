<?php
require_once __DIR__ . '/config/config.php';
$db = getDB();

try {
    $stmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'openrouter_api_key'");
    $apiKey = $stmt->fetchColumn();
} catch (Exception $e) {
    die("No API Key");
}

echo "API Key loaded: " . substr($apiKey, 0, 10) . "...\n";
