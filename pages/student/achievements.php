<?php
include __DIR__ . '/../../includes/header.php';
$db = getDB();
$userId = $_SESSION['user_id'];

// All badges
$stmt = $db->prepare("
    SELECT b.*, (SELECT COUNT(*) FROM user_badges WHERE user_id = ? AND badge_id = b.id) as earned
    FROM badges b ORDER BY b.sort_order
");
$stmt->execute([$userId]);
$badges = $stmt->fetchAll();

$earned = count(array_filter($badges, fn($b) => $b['earned'] > 0));
$total = count($badges);
?>
<div class="animate-fade-in">
    <h1 style="margin-bottom:8px;">🏅 เหรียญรางวัล</h1>
    <p style="color:var(--text-secondary);margin-bottom:6px;">สะสมเหรียญรางวัลจากการเรียนรู้!</p>
    <div class="badge badge-primary" style="margin-bottom:20px;">🏅 <?= $earned ?>/<?= $total ?> เหรียญ</div>

    <div class="progress-bar" style="margin-bottom:24px;">
        <div class="progress-fill" style="width:<?= $total > 0 ? round($earned/$total*100) : 0 ?>%;background:linear-gradient(90deg,var(--accent),var(--secondary));"></div>
    </div>

    <div class="achievement-grid">
        <?php foreach ($badges as $b): ?>
        <div class="card achievement-card <?= $b['earned'] ? 'earned' : 'locked' ?>"
             style="<?= $b['earned'] ? 'background:linear-gradient(135deg,' . $b['color'] . '11,' . $b['color'] . '22);border-color:' . $b['color'] . '44;' : '' ?>">
            <?php if ($b['earned']): ?>
                <span class="achievement-stamp">✅</span>
            <?php endif; ?>
            <span class="achievement-icon"><?= $b['icon'] ?></span>
            <div class="achievement-name"><?= sanitize($b['name_th']) ?></div>
            <div class="achievement-desc"><?= sanitize($b['description']) ?></div>
            <div style="margin-top:8px;">
                <span class="badge" style="background:<?= $b['color'] ?>22;color:<?= $b['color'] ?>;font-size:0.65rem;">+<?= $b['xp_reward'] ?> XP</span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
