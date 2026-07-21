<?php
include __DIR__ . '/../../includes/header.php';
$user = $currentUser;
$stats = getUserStats($user['id']);
$streak = getStreak($user['id']);
$db = getDB();

// Exam history
$stmt = $db->prepare("SELECT er.*, e.title FROM exam_results er JOIN exams e ON er.exam_id = e.id WHERE er.user_id = ? ORDER BY er.completed_at DESC LIMIT 10");
$stmt->execute([$user['id']]);
$examHistory = $stmt->fetchAll();

// Earned badges
$stmt = $db->prepare("SELECT b.* FROM badges b JOIN user_badges ub ON b.id = ub.badge_id WHERE ub.user_id = ? ORDER BY ub.earned_at DESC");
$stmt->execute([$user['id']]);
$myBadges = $stmt->fetchAll();
?>
<div class="animate-fade-in">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-avatar" style="background:<?= $user['avatar_color'] ?>;">
            <?= mb_substr($user['fname'], 0, 1) ?>
        </div>
        <h1 style="color:#fff;"><?= sanitize($user['fname']) ?> <?= sanitize($user['lname']) ?></h1>
        <p style="color:rgba(255,255,255,0.8);">ชื่อเล่น: <?= sanitize($user['nickname']) ?> · รหัส: <?= sanitize($user['code']) ?></p>
        <div class="flex gap-8 justify-center" style="margin-top:12px;">
            <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;">⭐ <?= number_format($user['xp']) ?> XP</span>
            <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;">🪙 <?= number_format($user['coins']) ?></span>
            <span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;">🔥 <?= $streak['current_streak'] ?> วัน</span>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid" style="margin-bottom:24px;">
        <div class="card stat-card">
            <div class="stat-value" style="color:var(--primary);"><?= $stats['lessons_completed'] ?></div>
            <div class="stat-label">บทเรียน</div>
        </div>
        <div class="card stat-card">
            <div class="stat-value" style="color:var(--success);"><?= $stats['avg_score'] ?>%</div>
            <div class="stat-label">คะแนนเฉลี่ย</div>
        </div>
        <div class="card stat-card">
            <div class="stat-value" style="color:var(--accent);"><?= $stats['vocab_learned'] ?></div>
            <div class="stat-label">คำศัพท์</div>
        </div>
        <div class="card stat-card">
            <div class="stat-value" style="color:var(--secondary);"><?= $stats['games_played'] ?></div>
            <div class="stat-label">เกม</div>
        </div>
    </div>

    <!-- Badges -->
    <?php if (!empty($myBadges)): ?>
    <div class="section-title">
        <h2>🏅 เหรียญรางวัล</h2>
        <a href="?page=achievements" class="section-link">ดูทั้งหมด →</a>
    </div>
    <div class="flex gap-8 flex-wrap" style="margin-bottom:24px;">
        <?php foreach ($myBadges as $b): ?>
        <div class="card" style="padding:12px;text-align:center;min-width:70px;">
            <div style="font-size:1.5rem;"><?= $b['icon'] ?></div>
            <div style="font-size:0.7rem;font-weight:700;"><?= sanitize($b['name_th']) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Exam History -->
    <h2 style="margin-bottom:12px;">📊 ประวัติสอบ</h2>
    <?php if (!empty($examHistory)): ?>
    <div class="card" style="padding:0;overflow:hidden;margin-bottom:24px;">
        <?php foreach ($examHistory as $eh): ?>
        <div class="item-row" style="cursor:default;">
            <div class="item-icon <?= $eh['percentage'] >= 80 ? 'item-icon-success' : ($eh['percentage'] >= 60 ? 'item-icon-accent' : 'item-icon-secondary') ?>">
                <?= $eh['percentage'] >= 80 ? '🌟' : ($eh['percentage'] >= 60 ? '👍' : '💪') ?>
            </div>
            <div class="item-info">
                <div class="item-title"><?= sanitize($eh['title']) ?></div>
                <div class="item-desc"><?= $eh['score'] ?>/<?= $eh['total'] ?> ข้อ · <?= date('d/m/Y', strtotime($eh['completed_at'])) ?></div>
            </div>
            <span style="font-family:var(--font-display);font-weight:900;color:<?= $eh['percentage'] >= 80 ? 'var(--success)' : ($eh['percentage'] >= 60 ? 'var(--accent)' : 'var(--danger)') ?>;">
                <?= $eh['percentage'] ?>%
            </span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="card empty-state">
        <span class="empty-emoji">📝</span>
        <div class="empty-title">ยังไม่มีประวัติสอบ</div>
        <p>ลองทำข้อสอบดูสิ!</p>
    </div>
    <?php endif; ?>

    <a href="?page=logout" class="btn btn-outline btn-block" style="margin-top:8px;">🚪 ออกจากระบบ</a>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
