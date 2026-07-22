<?php
include __DIR__ . '/../../includes/header.php';
$score = intval($_GET['score'] ?? 0);
$total = intval($_GET['total'] ?? 1);
$pct = intval($_GET['pct'] ?? 0);
$coins = intval($_GET['coins'] ?? 0);
$color = $pct >= 80 ? 'var(--success)' : ($pct >= 60 ? 'var(--accent)' : 'var(--danger)');
$emoji = $pct >= 80 ? '🎉' : ($pct >= 60 ? '👍' : '💪');
$msg = $pct >= 80 ? 'Excellent!' : ($pct >= 60 ? 'Good Job!' : 'Keep Going!');
?>
<div class="animate-fade-in text-center" style="padding-top:20px;">
    <div class="score-circle" style="border-color:<?= $color ?>;">
        <div class="score-value" style="color:<?= $color ?>;"><?= $pct ?>%</div>
        <div class="score-label"><?= $score ?>/<?= $total ?> Questions</div>
    </div>
    <h1><?= $emoji ?> <?= $msg ?></h1>
    
    <div style="display: flex; gap: 16px; justify-content: center; margin-top: 12px; font-size: 1.1rem;">
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
