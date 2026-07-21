<?php
// ============================================================
// LinguaMax — Helper Functions
// ============================================================
require_once __DIR__ . '/../config/database.php';

// ── AUTH HELPERS ─────────────────────────────────────────────
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// ── XP & COINS ──────────────────────────────────────────────
function addXP($userId, $amount) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE users SET xp = xp + ? WHERE id = ?");
    $stmt->execute([$amount, $userId]);
}

function addCoins($userId, $amount) {
    $db = getDB();
    $stmt = $db->prepare("UPDATE users SET coins = coins + ? WHERE id = ?");
    $stmt->execute([$amount, $userId]);
}

function getLevelFromXP($xp) {
    if ($xp >= 1000) return 'advanced';
    if ($xp >= 400) return 'intermediate';
    return 'beginner';
}

// ── STREAK ──────────────────────────────────────────────────
function updateStreak($userId) {
    $db = getDB();
    $today = date('Y-m-d');
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    $stmt = $db->prepare("SELECT * FROM user_streaks WHERE user_id = ?");
    $stmt->execute([$userId]);
    $streak = $stmt->fetch();

    if (!$streak) {
        $stmt = $db->prepare("INSERT INTO user_streaks (user_id, current_streak, longest_streak, last_activity_date) VALUES (?, 1, 1, ?)");
        $stmt->execute([$userId, $today]);
        return 1;
    }

    if ($streak['last_activity_date'] === $today) {
        return $streak['current_streak'];
    }

    if ($streak['last_activity_date'] === $yesterday) {
        $newStreak = $streak['current_streak'] + 1;
        $longest = max($newStreak, $streak['longest_streak']);
        $stmt = $db->prepare("UPDATE user_streaks SET current_streak = ?, longest_streak = ?, last_activity_date = ? WHERE user_id = ?");
        $stmt->execute([$newStreak, $longest, $today, $userId]);

        // Streak rewards
        if ($newStreak == 3) addCoins($userId, COINS_PER_STREAK_3);
        if ($newStreak == 7) addCoins($userId, COINS_PER_STREAK_7);
        if ($newStreak == 30) addCoins($userId, COINS_PER_STREAK_30);

        return $newStreak;
    }

    // Streak broken
    $stmt = $db->prepare("UPDATE user_streaks SET current_streak = 1, last_activity_date = ? WHERE user_id = ?");
    $stmt->execute([$today, $userId]);
    return 1;
}

function getStreak($userId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM user_streaks WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch() ?: ['current_streak' => 0, 'longest_streak' => 0, 'last_activity_date' => null];
}

// ── SPACED REPETITION (SM-2) ────────────────────────────────
function calculateSR($quality, $easeFactor, $interval, $repetitions) {
    // quality: 0-5 (0-2 = fail, 3 = hard, 4 = good, 5 = easy)
    if ($quality >= 3) {
        if ($repetitions == 0) {
            $interval = 1;
        } elseif ($repetitions == 1) {
            $interval = 6;
        } else {
            $interval = round($interval * $easeFactor);
        }
        $repetitions++;
        $easeFactor = $easeFactor + (0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02));
        if ($easeFactor < 1.3) $easeFactor = 1.3;
    } else {
        $repetitions = 0;
        $interval = 1;
        // Keep ease factor unchanged on fail
    }

    return [
        'ease_factor' => round($easeFactor, 2),
        'interval_days' => $interval,
        'repetitions' => $repetitions,
        'next_review' => date('Y-m-d', strtotime("+{$interval} days")),
    ];
}

// ── BADGE CHECKER ───────────────────────────────────────────
function checkAndAwardBadges($userId) {
    $db = getDB();
    $newBadges = [];

    // Get all badges not yet earned
    $stmt = $db->prepare("
        SELECT b.* FROM badges b
        LEFT JOIN user_badges ub ON b.id = ub.badge_id AND ub.user_id = ?
        WHERE ub.id IS NULL
    ");
    $stmt->execute([$userId]);
    $unearned = $stmt->fetchAll();

    foreach ($unearned as $badge) {
        $earned = false;

        switch ($badge['requirement_type']) {
            case 'lessons_completed':
                $stmt2 = $db->prepare("SELECT COUNT(*) as cnt FROM lesson_progress WHERE user_id = ? AND completed = TRUE");
                $stmt2->execute([$userId]);
                $earned = $stmt2->fetch()['cnt'] >= $badge['requirement_value'];
                break;

            case 'vocab_learned':
                $stmt2 = $db->prepare("SELECT COUNT(*) as cnt FROM flashcard_progress WHERE user_id = ? AND repetitions >= 1");
                $stmt2->execute([$userId]);
                $earned = $stmt2->fetch()['cnt'] >= $badge['requirement_value'];
                break;

            case 'exams_passed':
                $stmt2 = $db->prepare("SELECT COUNT(*) as cnt FROM exam_results WHERE user_id = ? AND percentage >= 60");
                $stmt2->execute([$userId]);
                $earned = $stmt2->fetch()['cnt'] >= $badge['requirement_value'];
                break;

            case 'perfect_score':
                $stmt2 = $db->prepare("SELECT COUNT(*) as cnt FROM exam_results WHERE user_id = ? AND percentage >= 100");
                $stmt2->execute([$userId]);
                $earned = $stmt2->fetch()['cnt'] >= $badge['requirement_value'];
                break;

            case 'streak_days':
                $streak = getStreak($userId);
                $earned = $streak['longest_streak'] >= $badge['requirement_value'];
                break;

            case 'games_played':
                $stmt2 = $db->prepare("SELECT COUNT(*) as cnt FROM game_scores WHERE user_id = ?");
                $stmt2->execute([$userId]);
                $earned = $stmt2->fetch()['cnt'] >= $badge['requirement_value'];
                break;

            case 'readings_completed':
                $stmt2 = $db->prepare("SELECT COUNT(DISTINCT passage_id) as cnt FROM reading_progress WHERE user_id = ?");
                $stmt2->execute([$userId]);
                $earned = $stmt2->fetch()['cnt'] >= $badge['requirement_value'];
                break;
        }

        if ($earned) {
            $stmt2 = $db->prepare("INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)");
            $stmt2->execute([$userId, $badge['id']]);
            addXP($userId, $badge['xp_reward']);
            $newBadges[] = $badge;
        }
    }

    return $newBadges;
}

// ── STATS HELPERS ───────────────────────────────────────────
function getUserStats($userId) {
    $db = getDB();

    // Lessons completed
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM lesson_progress WHERE user_id = ? AND completed = TRUE");
    $stmt->execute([$userId]);
    $lessonsCompleted = $stmt->fetch()['cnt'];

    // Total lessons
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM lessons");
    $stmt->execute();
    $totalLessons = $stmt->fetch()['cnt'];

    // Average exam score
    $stmt = $db->prepare("SELECT AVG(percentage) as avg_score, COUNT(*) as cnt FROM exam_results WHERE user_id = ?");
    $stmt->execute([$userId]);
    $examData = $stmt->fetch();
    $avgScore = $examData['avg_score'] ? round($examData['avg_score']) : 0;
    $examsCount = $examData['cnt'];

    // Vocab learned
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM flashcard_progress WHERE user_id = ? AND repetitions >= 1");
    $stmt->execute([$userId]);
    $vocabLearned = $stmt->fetch()['cnt'];

    // Games played
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM game_scores WHERE user_id = ?");
    $stmt->execute([$userId]);
    $gamesPlayed = $stmt->fetch()['cnt'];

    // Badges earned
    $stmt = $db->prepare("SELECT COUNT(*) as cnt FROM user_badges WHERE user_id = ?");
    $stmt->execute([$userId]);
    $badgesEarned = $stmt->fetch()['cnt'];

    return [
        'lessons_completed' => $lessonsCompleted,
        'total_lessons' => $totalLessons,
        'avg_score' => $avgScore,
        'exams_count' => $examsCount,
        'vocab_learned' => $vocabLearned,
        'games_played' => $gamesPlayed,
        'badges_earned' => $badgesEarned,
        'progress_pct' => $totalLessons > 0 ? round(($lessonsCompleted / $totalLessons) * 100) : 0,
    ];
}

// ── DAILY CHALLENGE ─────────────────────────────────────────
function getTodayChallenge() {
    $db = getDB();
    $today = date('Y-m-d');

    $stmt = $db->prepare("SELECT * FROM daily_challenges WHERE challenge_date = ?");
    $stmt->execute([$today]);
    $challenge = $stmt->fetch();

    if (!$challenge) {
        // Auto-generate
        $types = ['flashcard', 'quiz', 'game', 'reading'];
        $type = $types[array_rand($types)];
        $titles = [
            'flashcard' => '🃏 ท่องศัพท์ 10 คำ',
            'quiz' => '📝 ตอบคำถาม 5 ข้อ',
            'game' => '🎮 เล่นเกมจับคู่',
            'reading' => '📖 อ่านเรื่องสั้น 1 เรื่อง',
        ];
        $descs = [
            'flashcard' => 'ทบทวนศัพท์วันนี้ ท่องให้ครบ 10 คำ!',
            'quiz' => 'ตอบคำถามให้ถูก 5 ข้อ!',
            'game' => 'เล่นเกมจับคู่คำศัพท์ให้ได้คะแนนเกิน 70!',
            'reading' => 'อ่านเรื่องสั้นแล้วตอบคำถามให้ครบ!',
        ];

        $stmt = $db->prepare("INSERT INTO daily_challenges (challenge_date, challenge_type, title, description, xp_reward) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$today, $type, $titles[$type], $descs[$type], XP_PER_DAILY]);

        $challenge = [
            'id' => $db->lastInsertId(),
            'challenge_date' => $today,
            'challenge_type' => $type,
            'title' => $titles[$type],
            'description' => $descs[$type],
            'xp_reward' => XP_PER_DAILY,
        ];
    }

    return $challenge;
}

function isDailyChallengeCompleted($userId) {
    $db = getDB();
    $today = date('Y-m-d');
    $stmt = $db->prepare("SELECT completed FROM user_daily_progress WHERE user_id = ? AND challenge_date = ?");
    $stmt->execute([$userId, $today]);
    $row = $stmt->fetch();
    return $row && $row['completed'];
}

// ── LEADERBOARD ─────────────────────────────────────────────
function getLeaderboard($limit = 10) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT u.id, u.code, u.fname, u.lname, u.nickname, u.xp, u.coins, u.level, u.avatar_color,
               COALESCE(s.current_streak, 0) as current_streak,
               (SELECT COUNT(*) FROM user_badges WHERE user_id = u.id) as badges_count
        FROM users u
        LEFT JOIN user_streaks s ON u.id = s.user_id
        WHERE u.role = 'student'
        ORDER BY u.xp DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

// ── MISC ────────────────────────────────────────────────────
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function sanitize($str) {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function getLevelBadgeClass($level) {
    switch ($level) {
        case 'beginner': return 'badge-success';
        case 'intermediate': return 'badge-accent';
        case 'advanced': return 'badge-primary';
        default: return 'badge-info';
    }
}

function getLevelName($level) {
    switch ($level) {
        case 'beginner': return 'เริ่มต้น';
        case 'intermediate': return 'กลาง';
        case 'advanced': return 'ขั้นสูง';
        default: return $level;
    }
}
