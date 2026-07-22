<?php
// ============================================================
// LinguaMax — Learning Goals Page
// ============================================================
include __DIR__ . '/../../includes/header.php';
$user = $currentUser;
$goal = $user['daily_xp_goal'] ?? 50;

$goals = [
    10 => ['title' => 'Casual', 'desc' => '10 XP per day (5 mins)'],
    30 => ['title' => 'Regular', 'desc' => '30 XP per day (10 mins)'],
    50 => ['title' => 'Serious', 'desc' => '50 XP per day (15 mins)'],
    100 => ['title' => 'Intense', 'desc' => '100 XP per day (30 mins)']
];
?>

<div class="animate-fade-in" style="padding-bottom: 80px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #F59E0B 0%, #FBBF24 100%); margin: -20px -20px 20px -20px; padding: 40px 20px 30px; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; color: white; text-align: center; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2); position: relative;">
        <a href="?page=profile" style="position: absolute; left: 20px; top: 20px; color: white; text-decoration: none; font-size: 1.2rem; background: rgba(255,255,255,0.2); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div style="font-size: 2.5rem; margin-bottom: 12px;">🎯</div>
        <h1 style="color: white; font-weight: 900; margin-bottom: 8px; font-size: 1.8rem;">Learning Goals</h1>
        <p style="opacity: 0.9; font-size: 0.95rem;">Set your daily XP target to stay motivated!</p>
    </div>

    <!-- Options -->
    <div class="flex-col gap-12" style="padding: 0 4px;">
        <?php foreach ($goals as $xp => $data): ?>
        <div class="goal-option" onclick="selectGoal(<?= $xp ?>)" style="background: white; border: 2px solid <?= $goal == $xp ? '#F59E0B' : '#F1F5F9' ?>; border-radius: 20px; padding: 20px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 15px <?= $goal == $xp ? 'rgba(245,158,11,0.1)' : 'rgba(0,0,0,0.02)' ?>; <?= $goal == $xp ? 'background: #FFFBEB;' : '' ?>" id="goal-<?= $xp ?>">
            <div>
                <div style="font-weight: 900; font-size: 1.15rem; color: <?= $goal == $xp ? '#B45309' : '#1E293B' ?>; margin-bottom: 4px; transition: color 0.2s;" class="goal-title"><?= $data['title'] ?></div>
                <div style="font-size: 0.85rem; font-weight: 600; color: <?= $goal == $xp ? '#D97706' : '#94A3B8' ?>; transition: color 0.2s;" class="goal-desc"><?= $data['desc'] ?></div>
            </div>
            <div class="check-circle" style="width: 28px; height: 28px; border-radius: 50%; border: 2px solid <?= $goal == $xp ? '#F59E0B' : '#CBD5E1' ?>; background: <?= $goal == $xp ? '#F59E0B' : 'white' ?>; color: white; display: flex; align-items: center; justify-content: center; transition: all 0.2s; font-size: 0.8rem;">
                <i class="fa-solid fa-check" style="<?= $goal == $xp ? 'opacity:1;' : 'opacity:0;' ?>"></i>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div style="padding: 24px 4px; text-align: center;">
        <button onclick="saveGoal()" style="width: 100%; background: #F59E0B; color: white; padding: 18px; border-radius: 30px; border: none; font-weight: 800; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3); cursor: pointer; transition: transform 0.2s;" id="saveBtn">Save Goal</button>
    </div>

</div>

<script>
let currentGoal = <?= $goal ?>;

function selectGoal(xp) {
    currentGoal = xp;
    
    // Reset all
    document.querySelectorAll('.goal-option').forEach(el => {
        el.style.borderColor = '#F1F5F9';
        el.style.background = 'white';
        el.style.boxShadow = '0 4px 15px rgba(0,0,0,0.02)';
        el.querySelector('.goal-title').style.color = '#1E293B';
        el.querySelector('.goal-desc').style.color = '#94A3B8';
        const circle = el.querySelector('.check-circle');
        circle.style.borderColor = '#CBD5E1';
        circle.style.background = 'white';
        circle.querySelector('.fa-check').style.opacity = '0';
    });
    
    // Highlight selected
    const selected = document.getElementById('goal-' + xp);
    selected.style.borderColor = '#F59E0B';
    selected.style.background = '#FFFBEB';
    selected.style.boxShadow = '0 4px 15px rgba(245,158,11,0.1)';
    selected.querySelector('.goal-title').style.color = '#B45309';
    selected.querySelector('.goal-desc').style.color = '#D97706';
    const selCircle = selected.querySelector('.check-circle');
    selCircle.style.borderColor = '#F59E0B';
    selCircle.style.background = '#F59E0B';
    selCircle.querySelector('.fa-check').style.opacity = '1';
}

async function saveGoal() {
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    try {
        const res = await fetch('<?= SITE_URL ?>/api/user.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'update_goal',
                daily_xp_goal: currentGoal
            })
        });
        const result = await res.json();
        if (result.success) {
            alert('Learning goal updated!');
            window.location.href = '?page=profile';
        } else {
            alert('Failed to update goal');
        }
    } catch(err) {
        alert('Connection error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Save Goal';
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
