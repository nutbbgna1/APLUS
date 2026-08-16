<?php
// ============================================================
// Admin Panel — Database & System Configuration
// ============================================================

if (!defined('ADMIN_DB_HOST')) define('ADMIN_DB_HOST', 'localhost');
if (!defined('ADMIN_DB_NAME')) define('ADMIN_DB_NAME', 'u292923614_english');
if (!defined('ADMIN_DB_USER')) define('ADMIN_DB_USER', 'u292923614_english');
if (!defined('ADMIN_DB_PASS')) define('ADMIN_DB_PASS', 'Linguamax1234@');
if (!defined('ADMIN_DB_CHARSET')) define('ADMIN_DB_CHARSET', 'utf8mb4');

if (!function_exists('getDB')) {
    function getDB()
    {
        static $pdo = null;
        if ($pdo === null) {
            try {
                // Try Production Credentials First
                $dsn = "mysql:host=" . ADMIN_DB_HOST . ";dbname=" . ADMIN_DB_NAME . ";charset=" . ADMIN_DB_CHARSET;
                $pdo = new PDO($dsn, ADMIN_DB_USER, ADMIN_DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                // Fallback attempt for Local XAMPP (root user, empty password)
                try {
                    $dsn = "mysql:host=" . ADMIN_DB_HOST . ";dbname=" . ADMIN_DB_NAME . ";charset=" . ADMIN_DB_CHARSET;
                    $pdo = new PDO($dsn, 'root', '', [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]);
                } catch (PDOException $e2) {
                    die(json_encode(['error' => 'Admin Database Connection Failed (Prod Error: ' . $e->getMessage() . ') (Local Error: ' . $e2->getMessage() . ')']));
                }
            }
        }
        return $pdo;
    }
}
