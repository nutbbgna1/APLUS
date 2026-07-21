<?php
include __DIR__ . '/../../includes/header.php';
$userId = $_SESSION['user_id'];
$leaders = getLeaderboard(20);

// Find current user rank
$myRank = 0;
foreach ($leaders as $i => $l) { if ($l['id'] == $userId) { $myRank = $i + 1; break; } }
?>
<div class="animate-fade-in">
    <h1 style="margin-bottom:8px;">🏆 Leaderboard</h1>
    <p style="color:var(--text-secondary);margin-bottom:20px;">อันดับ XP สูงสุดของเพื่อนๆ</p>

    <?php if ($myRank > 0): ?>
    <div class="card" style="margin-bottom:20px;border-left:4px solid var(--primary);">
        <div class="flex items-center gap-12">
            <div class="leaderboard-rank rank-other">#<?= $myRank ?></div>
            <div>
                <div style="font-weight:700;">อันดับของคุณ</div>
                <div style="font-size:0.85rem;color:var(--text-secondary);"><?= number_format($currentUser['xp']) ?> XP</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Top 3 Podium -->
    <?php if (count($leaders) >= 3): ?>
    <div class="flex justify-center items-end gap-8" style="margin-bottom:24px;">
        <!-- 2nd Place -->
        <div class="text-center" style="flex:1;">
            <div class="avatar" style="width:50px;height:50px;font-size:1.2rem;margin:0 auto 8px;background:<?= $leaders[1]['avatar_color'] ?>">
                <?= mb_substr($leaders[1]['fname'], 0, 1) ?>
            </div>
            <div style="font-family:var(--font-display);font-weight:800;font-size:0.85rem;"><?= sanitize($leaders[1]['nickname']) ?></div>
            <div style="font-size:0.75rem;color:var(--text-secondary);"><?= number_format($leaders[1]['xp']) ?> XP</div>
            <div class="leaderboard-rank rank-2" style="margin:6px auto 0;">🥈</div>
        </div>
        <!-- 1st Place -->
        <div class="text-center" style="flex:1;">
            <div style="font-size:1.5rem;margin-bottom:4px;">👑</div>
            <div class="avatar" style="width:60px;height:60px;font-size:1.5rem;margin:0 auto 8px;background:<?= $leaders[0]['avatar_color'] ?>;box-shadow:0 0 20px rgba(255,215,0,0.4);">
                <?= mb_substr($leaders[0]['fname'], 0, 1) ?>
            </div>
            <div style="font-family:var(--font-display);font-weight:900;font-size:1rem;"><?= sanitize($leaders[0]['nickname']) ?></div>
            <div style="font-size:0.8rem;color:var(--accent);"><?= number_format($leaders[0]['xp']) ?> XP</div>
            <div class="leaderboard-rank rank-1" style="margin:6px auto 0;">🥇</div>
        </div>
        <!-- 3rd Place -->
        <div class="text-center" style="flex:1;">
            <div class="avatar" style="width:50px;height:50px;font-size:1.2rem;margin:0 auto 8px;background:<?= $leaders[2]['avatar_color'] ?>">
                <?= mb_substr($leaders[2]['fname'], 0, 1) ?>
            </div>
            <div style="font-family:var(--font-display);font-weight:800;font-size:0.85rem;"><?= sanitize($leaders[2]['nickname']) ?></div>
            <div style="font-size:0.75rem;color:var(--text-secondary);"><?= number_format($leaders[2]['xp']) ?> XP</div>
            <div class="leaderboard-rank rank-3" style="margin:6px auto 0;">🥉</div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Full List -->
    <div class="card" style="padding:0;overflow:hidden;">
        <?php foreach ($leaders as $i => $l): ?>
        <div class="leaderboard-item <?= $l['id'] == $userId ? 'leaderboard-me' : '' ?>">
            <div class="leaderboard-rank <?= $i < 3 ? 'rank-' . ($i+1) : 'rank-other' ?>">
                <?= $i < 3 ? ['🥇','🥈','🥉'][$i] : ($i + 1) ?>
            </div>
            <div class="avatar" style="background:<?= $l['avatar_color'] ?>"><?= mb_substr($l['fname'], 0, 1) ?></div>
            <div style="flex:1;min-width:0;">
                <div style="font-family:var(--font-display);font-weight:700;font-size:0.9rem;">
                    <?= sanitize($l['nickname']) ?>
                    <?= $l['id'] == $userId ? '<span style="font-size:0.7rem;color:var(--primary);">(คุณ)</span>' : '' ?>
                </div>
                <div style="font-size:0.75rem;color:var(--text-secondary);">
                    🔥 <?= $l['current_streak'] ?> วัน · 🏅 <?= $l['badges_count'] ?> เหรียญ
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-family:var(--font-display);font-weight:900;color:var(--primary);"><?= number_format($l['xp']) ?></div>
                <div style="font-size:0.7rem;color:var(--text-secondary);">XP</div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
