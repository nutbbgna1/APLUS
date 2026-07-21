<?php
include __DIR__ . '/../../includes/header.php';
$db = getDB();

// Stats
$totalStudents = $db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$totalLessons = $db->query("SELECT COUNT(*) FROM lessons")->fetchColumn();
$totalExams = $db->query("SELECT COUNT(*) FROM exams")->fetchColumn();
$totalVocab = $db->query("SELECT COUNT(*) FROM vocabulary")->fetchColumn();
$avgScore = $db->query("SELECT ROUND(AVG(percentage)) FROM exam_results")->fetchColumn() ?: 0;
$activeToday = $db->query("SELECT COUNT(DISTINCT user_id) FROM user_streaks WHERE last_activity_date = CURDATE()")->fetchColumn();
?>
<div class="animate-fade-in">
    <h1 style="margin-bottom:20px;">📊 Admin Dashboard</h1>

    <div class="stats-grid" style="margin-bottom:24px;">
        <div class="card stat-card"><div class="stat-icon" style="background:var(--primary-light);color:var(--primary);"><i class="fa-solid fa-users"></i></div><div class="stat-value"><?= $totalStudents ?></div><div class="stat-label">นักเรียน</div></div>
        <div class="card stat-card"><div class="stat-icon" style="background:var(--success-light);color:var(--success);"><i class="fa-solid fa-book-open-reader"></i></div><div class="stat-value"><?= $totalLessons ?></div><div class="stat-label">บทเรียน</div></div>
        <div class="card stat-card"><div class="stat-icon" style="background:var(--accent-light);color:var(--accent);"><i class="fa-solid fa-file-signature"></i></div><div class="stat-value"><?= $totalExams ?></div><div class="stat-label">ข้อสอบ</div></div>
        <div class="card stat-card"><div class="stat-icon" style="background:var(--secondary-light);color:var(--secondary);"><i class="fa-solid fa-language"></i></div><div class="stat-value"><?= $totalVocab ?></div><div class="stat-label">คำศัพท์</div></div>
    </div>

    <div class="flex gap-12" style="margin-bottom:24px;">
        <div class="card" style="flex:1;text-align:center;">
            <div style="font-size:2rem;">📊</div>
            <div style="font-family:var(--font-display);font-weight:900;font-size:1.5rem;color:var(--success);"><?= $avgScore ?>%</div>
            <div style="font-size:0.8rem;color:var(--text-secondary);">คะแนนเฉลี่ยรวม</div>
        </div>
        <div class="card" style="flex:1;text-align:center;">
            <div style="font-size:2rem;">🟢</div>
            <div style="font-family:var(--font-display);font-weight:900;font-size:1.5rem;color:var(--primary);"><?= $activeToday ?></div>
            <div style="font-size:0.8rem;color:var(--text-secondary);">เข้าเรียนวันนี้</div>
        </div>
    </div>

    <h2 style="margin-bottom:12px;">🏆 Top 5 นักเรียน</h2>
    <div class="card" style="padding:0;overflow:hidden;">
        <?php
        $top5 = getLeaderboard(5);
        foreach ($top5 as $i => $s):
        ?>
        <div class="leaderboard-item">
            <div class="leaderboard-rank <?= $i < 3 ? 'rank-'.($i+1) : 'rank-other' ?>">
                <?= $i < 3 ? ['🥇','🥈','🥉'][$i] : ($i+1) ?>
            </div>
            <div class="avatar" style="background:<?= $s['avatar_color'] ?>"><?= mb_substr($s['fname'], 0, 1) ?></div>
            <div style="flex:1;">
                <div style="font-weight:700;"><?= sanitize($s['nickname']) ?> (<?= $s['code'] ?>)</div>
                <div style="font-size:0.75rem;color:var(--text-secondary);"><?= sanitize($s['fname']) ?> <?= sanitize($s['lname']) ?></div>
            </div>
            <div style="font-family:var(--font-display);font-weight:900;color:var(--primary);"><?= number_format($s['xp']) ?> XP</div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
