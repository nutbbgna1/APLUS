<?php
include __DIR__ . '/../../includes/header.php';
$score = intval($_GET['score'] ?? 0);
$total = intval($_GET['total'] ?? 1);
$pct = intval($_GET['pct'] ?? 0);
$coins = intval($_GET['coins'] ?? 0);
$exam_id = intval($_GET['exam_id'] ?? 0);

$assess_msg = '';
if ($exam_id > 0) {
    $db = getDB();
    $stmt = $db->prepare("SELECT assess_excellent, assess_good, assess_poor FROM exams WHERE id = ?");
    $stmt->execute([$exam_id]);
    $examInfo = $stmt->fetch();
    
    if ($examInfo) {
        if ($pct >= 80) {
            $assess_msg = $examInfo['assess_excellent'] ?: 'ยอดเยี่ยม (Excellent)';
        } elseif ($pct >= 50) {
            $assess_msg = $examInfo['assess_good'] ?: 'ดี (Good)';
        } else {
            $assess_msg = $examInfo['assess_poor'] ?: 'ควรปรับปรุง (Needs Improvement)';
        }
    }
}

$color = $pct >= 80 ? 'var(--success)' : ($pct >= 50 ? 'var(--accent)' : 'var(--danger)');
$emoji = $pct >= 80 ? '🎉' : ($pct >= 50 ? '👍' : '💪');
$msg = $pct >= 80 ? 'Excellent!' : ($pct >= 50 ? 'Good Job!' : 'Keep Going!');

if (empty($assess_msg)) {
    $assess_msg = $msg;
}
?>
<div class="animate-fade-in text-center" style="padding-top:20px;">
    <div class="score-circle" style="border-color:<?= $color ?>;">
        <div class="score-value" style="color:<?= $color ?>;"><?= $pct ?>%</div>
        <div class="score-label"><?= $score ?>/<?= $total ?> Questions</div>
    </div>
    
    <div style="background: #F8FAFC; border: 1px solid #E2E8F0; padding: 15px 20px; border-radius: 12px; display: inline-block; margin-top: 15px;">
        <h2 style="margin: 0; font-size: 1.25rem; color: #1E293B;">ผลการประเมิน: <span style="color: <?= $color ?>;"><?= htmlspecialchars($assess_msg) ?></span> <?= $emoji ?></h2>
    </div>
    
    <div style="display: flex; gap: 16px; justify-content: center; margin-top: 20px; font-size: 1.1rem;">
        <p style="color:var(--text-secondary); margin: 0; font-weight: 700;">+<?= XP_PER_EXAM ?> XP</p>
        <?php if($coins > 0): ?>
            <p style="color:#F59E0B; margin: 0; font-weight: 700;">+<?= $coins ?> Coins</p>
        <?php endif; ?>
    </div>

    <div class="flex gap-12 justify-center" style="margin-top:24px;">
        <a href="?page=exams" class="btn btn-primary">📝 Take Another Exam</a>
        <a href="?page=dashboard" class="btn btn-outline">🏠 Dashboard</a>
    </div>
</div>
<?php if ($pct >= 80): ?>
<script>document.addEventListener('DOMContentLoaded', () => setTimeout(launchConfetti, 500));</script>
<?php endif; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
