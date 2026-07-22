<?php
// ============================================================
// LinguaMax — Settings Page
// ============================================================
include __DIR__ . '/../../includes/header.php';
$user = $currentUser;
$sound = $user['sound_enabled'] ?? 1;
$notifs = $user['notifications_enabled'] ?? 1;
?>

<div class="animate-fade-in" style="padding-bottom: 80px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #38BDF8 0%, #0EA5E9 100%); margin: -20px -20px 20px -20px; padding: 40px 20px 30px; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; color: white; text-align: center; box-shadow: 0 4px 15px rgba(56, 189, 248, 0.2); position: relative;">
        <a href="?page=profile" style="position: absolute; left: 20px; top: 20px; color: white; text-decoration: none; font-size: 1.2rem; background: rgba(255,255,255,0.2); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1 style="color: white; font-weight: 900; margin-bottom: 8px; font-size: 1.8rem;">Settings</h1>
        <p style="opacity: 0.9; font-size: 0.95rem;">Manage your app preferences</p>
    </div>

    <!-- Toggles -->
    <div style="background: white; border-radius: 24px; padding: 8px 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-top: -10px;">
        
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px 0; border-bottom: 1px solid #F1F5F9;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="fa-solid fa-volume-high"></i>
                </div>
                <div>
                    <div style="font-weight: 800; font-size: 1.05rem; color: #1E293B;">Sound Effects</div>
                    <div style="font-size: 0.8rem; font-weight: 600; color: #94A3B8;">Play sounds during lessons</div>
                </div>
            </div>
            <!-- Toggle Switch -->
            <label style="position: relative; display: inline-block; width: 50px; height: 28px;">
                <input type="checkbox" id="soundToggle" style="opacity: 0; width: 0; height: 0;" <?= $sound ? 'checked' : '' ?> onchange="updateSettings()">
                <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: <?= $sound ? '#38BDF8' : '#CBD5E1' ?>; transition: .4s; border-radius: 34px;" id="soundSlider">
                    <span style="position: absolute; content: ''; height: 20px; width: 20px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; transform: translateX(<?= $sound ? '22px' : '0' ?>);" id="soundKnob"></span>
                </span>
            </label>
        </div>

        <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px 0;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div>
                    <div style="font-weight: 800; font-size: 1.05rem; color: #1E293B;">Notifications</div>
                    <div style="font-size: 0.8rem; font-weight: 600; color: #94A3B8;">Daily learning reminders</div>
                </div>
            </div>
            <!-- Toggle Switch -->
            <label style="position: relative; display: inline-block; width: 50px; height: 28px;">
                <input type="checkbox" id="notifToggle" style="opacity: 0; width: 0; height: 0;" <?= $notifs ? 'checked' : '' ?> onchange="updateSettings()">
                <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: <?= $notifs ? '#38BDF8' : '#CBD5E1' ?>; transition: .4s; border-radius: 34px;" id="notifSlider">
                    <span style="position: absolute; content: ''; height: 20px; width: 20px; left: 4px; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%; transform: translateX(<?= $notifs ? '22px' : '0' ?>);" id="notifKnob"></span>
                </span>
            </label>
        </div>
        
    </div>

    <!-- Danger Zone -->
    <div style="margin-top: 32px; padding: 0 20px;">
        <div style="color: #94A3B8; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">Account</div>
        <a href="?page=logout" style="display: flex; align-items: center; gap: 12px; background: white; padding: 16px 20px; border-radius: 16px; color: #EF4444; font-weight: 800; text-decoration: none; border: 1px solid #FEE2E2; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.05);">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            Log Out
        </a>
    </div>

</div>

<script>
async function updateSettings() {
    const soundChk = document.getElementById('soundToggle').checked;
    const notifChk = document.getElementById('notifToggle').checked;
    
    // Animate custom UI toggles
    document.getElementById('soundSlider').style.backgroundColor = soundChk ? '#38BDF8' : '#CBD5E1';
    document.getElementById('soundKnob').style.transform = soundChk ? 'translateX(22px)' : 'translateX(0)';
    
    document.getElementById('notifSlider').style.backgroundColor = notifChk ? '#38BDF8' : '#CBD5E1';
    document.getElementById('notifKnob').style.transform = notifChk ? 'translateX(22px)' : 'translateX(0)';

    try {
        await fetch('<?= SITE_URL ?>/api/user.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'update_settings',
                sound_enabled: soundChk ? 1 : 0,
                notifications_enabled: notifChk ? 1 : 0
            })
        });
    } catch(err) {
        console.error('Failed to save settings');
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
