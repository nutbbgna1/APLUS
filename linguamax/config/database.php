<?php
// ============================================================
// LinguaMax — Database Configuration
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'u865886212_english');
define('DB_USER', 'u865886212_english');
define('DB_PASS', 'Linguamax1234@');
define('DB_CHARSET', 'utf8mb4');

// Site config
define('SITE_NAME', 'LinguaMax');
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
$docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
$dirPath = str_replace('\\', '/', dirname(__DIR__));
$webPath = str_replace($docRoot, '', $dirPath);
define('SITE_URL', $protocol . '://' . $_SERVER['HTTP_HOST'] . $webPath);
define('XP_PER_FLASHCARD', 5);
define('XP_PER_GAME', 10);
define('XP_PER_EXAM', 20);
define('XP_PER_READING', 15);
define('XP_PER_DAILY', 15);
define('COINS_PER_STREAK_3', 10);
define('COINS_PER_STREAK_7', 30);
define('COINS_PER_STREAK_30', 100);

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}
