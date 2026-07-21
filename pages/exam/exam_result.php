<?php
include __DIR__ . '/../../includes/header.php';
$score = intval($_GET['score'] ?? 0);
$total = intval($_GET['total'] ?? 1);
$pct = intval($_GET['pct'] ?? 0);
$color = $pct >= 80 ? 'var(--success)' : ($pct >= 60 ? 'var(--accent)' : 'var(--danger)');
$emoji = $pct >= 80 ? '🎉' : ($pct >= 60 ? '👍' : '💪');
$msg = $pct >= 80 ? 'ยอดเยี่ยม!' : ($pct >= 60 ? 'ดีมาก!' : 'สู้ต่อไป!');
?>
<div class="animate-fade-in text-center" style="padding-top:20px;">
    <div class="score-circle" style="border-color:<?= $color ?>;">
        <div class="score-value" style="color:<?= $color ?>;"><?= $pct ?>%</div>
        <div class="score-label"><?= $score ?>/<?= $total ?> ข้อ</div>
    </div>
    <h1><?= $emoji ?> <?= $msg ?></h1>
    <p style="color:var(--text-secondary);margin-top:8px;">+<?= XP_PER_EXAM ?> XP</p>

    <div class="flex gap-12 justify-center" style="margin-top:24px;">
        <a href="?page=exams" class="btn btn-primary">📝 ทำข้อสอบอื่น</a>
        <a href="?page=dashboard" class="btn btn-outline">🏠 หน้าแรก</a>
    </div>
</div>
<?php if ($pct >= 80): ?>
<script>document.addEventListener('DOMContentLoaded', () => setTimeout(launchConfetti, 500));</script>
<?php endif; ?>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
