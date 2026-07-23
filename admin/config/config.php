<?php
// ============================================================
// Admin Panel — Database & System Configuration
// ============================================================

if (!defined('ADMIN_DB_HOST')) define('ADMIN_DB_HOST', '127.0.0.1');
if (!defined('ADMIN_DB_NAME')) define('ADMIN_DB_NAME', 'u865886212_english');
if (!defined('ADMIN_DB_USER')) define('ADMIN_DB_USER', 'root');
if (!defined('ADMIN_DB_PASS')) define('ADMIN_DB_PASS', '');
if (!defined('ADMIN_DB_CHARSET')) define('ADMIN_DB_CHARSET', 'utf8mb4');

if (!function_exists('getDB')) {
    function getDB()
    {
        static $pdo = null;
        if ($pdo === null) {
            try {
                $dsn = "mysql:host=" . ADMIN_DB_HOST . ";dbname=" . ADMIN_DB_NAME . ";charset=" . ADMIN_DB_CHARSET;
                $pdo = new PDO($dsn, ADMIN_DB_USER, ADMIN_DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                // Fallback attempt with u865886212_english user if root fails
                try {
                    $dsn = "mysql:host=" . ADMIN_DB_HOST . ";dbname=" . ADMIN_DB_NAME . ";charset=" . ADMIN_DB_CHARSET;
                    $pdo = new PDO($dsn, 'u865886212_english', 'Linguamax1234@', [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                } catch (PDOException $e2) {
                    die(json_encode(['error' => 'Admin Database Connection Failed: ' . $e2->getMessage()]));
                }
            }
        }
        return $pdo;
    }
}
