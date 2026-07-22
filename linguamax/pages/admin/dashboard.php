<?php
include __DIR__ . '/../../includes/header.php';
$db = getDB();

$settingsFile = __DIR__ . '/../../includes/global_settings.json';
$globalSettings = ['block_desktop' => false];
if (file_exists($settingsFile)) {
    $globalSettings = array_merge($globalSettings, json_decode(file_get_contents($settingsFile), true));
}
$isDesktopBlocked = $globalSettings['block_desktop'];

// Stats
$totalStudents = $db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$totalLessons = $db->query("SELECT COUNT(*) FROM lessons")->fetchColumn();
$totalExams = $db->query("SELECT COUNT(*) FROM exams")->fetchColumn();
$totalVocab = $db->query("SELECT COUNT(*) FROM vocabulary")->fetchColumn();
$avgScore = $db->query("SELECT ROUND(AVG(percentage)) FROM exam_results")->fetchColumn() ?: 0;
$activeToday = $db->query("SELECT COUNT(DISTINCT user_id) FROM user_streaks WHERE last_activity_date = CURDATE()")->fetchColumn();
?>
<div class="animate-fade-in" style="padding-bottom: 80px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #1E293B 0%, #334155 100%); margin: -20px -20px 20px -20px; padding: 40px 20px 30px; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; color: white; box-shadow: 0 4px 15px rgba(30, 41, 59, 0.2); display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="color: white; font-weight: 900; margin-bottom: 4px; font-size: 1.8rem;">Admin Portal</h1>
            <p style="opacity: 0.8; font-size: 0.9rem; margin: 0;">Manage LinguaMax seamlessly</p>
        </div>
        <a href="?page=logout" style="background: rgba(239, 68, 68, 0.15); color: #FCA5A5; padding: 10px 16px; border-radius: 12px; font-weight: 700; text-decoration: none; border: 1px solid rgba(239, 68, 68, 0.3); display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
        </a>
    </div>

    <!-- Quick Actions -->
    <h2 style="font-size: 1.1rem; font-weight: 800; color: #1E293B; margin-bottom: 12px; padding-left: 4px;">Quick Actions</h2>
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 24px;">
        <a href="?page=admin&sub=students" style="background: white; border-radius: 20px; padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; text-decoration: none; border: 1px solid #F1F5F9; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="width: 50px; height: 50px; background: #E8F4FE; color: #38BDF8; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fa-solid fa-users"></i>
            </div>
            <div style="font-weight: 800; color: #1E293B; font-size: 0.95rem;">Manage Students</div>
        </a>
        <a href="?page=admin&sub=content" style="background: white; border-radius: 20px; padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; text-decoration: none; border: 1px solid #F1F5F9; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="width: 50px; height: 50px; background: #DCFCE7; color: #16A34A; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fa-solid fa-folder-tree"></i>
            </div>
            <div style="font-weight: 800; color: #1E293B; font-size: 0.95rem;">Manage Content</div>
        </a>
        <a href="?page=admin&sub=exam_permissions" style="background: white; border-radius: 20px; padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; text-decoration: none; border: 1px solid #F1F5F9; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="width: 50px; height: 50px; background: #F3E8FF; color: #8CB3FF; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fa-solid fa-key"></i>
            </div>
            <div style="font-weight: 800; color: #1E293B; font-size: 0.95rem;">Exam Access</div>
        </a>
        <a href="?page=admin&sub=reports" style="background: white; border-radius: 20px; padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; text-decoration: none; border: 1px solid #F1F5F9; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="width: 50px; height: 50px; background: #FEF3C7; color: #D97706; border-radius: 16px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                <i class="fa-solid fa-chart-line"></i>
            </div>
            <div style="font-weight: 800; color: #1E293B; font-size: 0.95rem;">View Reports</div>
        </a>
    </div>

    <!-- System Settings -->
    <h2 style="font-size: 1.1rem; font-weight: 800; color: #1E293B; margin-bottom: 12px; padding-left: 4px;">System Settings</h2>
    <div style="background: white; border-radius: 20px; padding: 20px; margin-bottom: 24px; border: 1px solid #F1F5F9; box-shadow: 0 4px 15px rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: space-between;">
        <div>
            <div style="font-weight: 800; color: #1E293B; font-size: 1rem; margin-bottom: 4px;">Block Desktop Access (Student)</div>
            <div style="font-size: 0.8rem; color: #64748B;">Force students to use Tablet or Mobile.</div>
        </div>
        <label style="position: relative; display: inline-block; width: 50px; height: 28px;">
            <input type="checkbox" id="blockDesktopToggle" style="opacity: 0; width: 0; height: 0;" <?= $isDesktopBlocked ? 'checked' : '' ?> onchange="toggleDesktopBlock()">
            <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: <?= $isDesktopBlocked ? '#10B981' : '#CBD5E1' ?>; transition: .4s; border-radius: 34px;" id="blockDesktopBg">
                <span style="position: absolute; content: ''; height: 20px; width: 20px; left: <?= $isDesktopBlocked ? '26px' : '4px' ?>; bottom: 4px; background-color: white; transition: .4s; border-radius: 50%;" id="blockDesktopKnob"></span>
            </span>
        </label>
    </div>
    
    <script>
    async function toggleDesktopBlock() {
        const toggle = document.getElementById('blockDesktopToggle');
        const bg = document.getElementById('blockDesktopBg');
        const knob = document.getElementById('blockDesktopKnob');
        
        if (toggle.checked) {
            bg.style.backgroundColor = '#10B981';
            knob.style.left = '26px';
        } else {
            bg.style.backgroundColor = '#CBD5E1';
            knob.style.left = '4px';
        }

        try {
            const formData = new FormData();
            formData.append('action', 'toggle_desktop_block');
            formData.append('block_desktop', toggle.checked ? '1' : '0');
            
            await fetch('<?= SITE_URL ?>/api/admin.php', {
                method: 'POST',
                body: formData
            });
        } catch (err) {
            console.error(err);
        }
    }
    </script>

    <!-- Overview Stats -->
    <h2 style="font-size: 1.1rem; font-weight: 800; color: #1E293B; margin-bottom: 12px; padding-left: 4px;">Platform Overview</h2>
    <div class="stats-grid" style="margin-bottom:24px;">
        <div class="card stat-card" style="padding:16px;"><div class="stat-icon" style="background:var(--primary-light);color:var(--primary);"><i class="fa-solid fa-users"></i></div><div class="stat-value" style="font-size:1.4rem;"><?= $totalStudents ?></div><div class="stat-label">นักเรียน</div></div>
        <div class="card stat-card" style="padding:16px;"><div class="stat-icon" style="background:var(--success-light);color:var(--success);"><i class="fa-solid fa-book-open-reader"></i></div><div class="stat-value" style="font-size:1.4rem;"><?= $totalLessons ?></div><div class="stat-label">บทเรียน</div></div>
        <div class="card stat-card" style="padding:16px;"><div class="stat-icon" style="background:var(--accent-light);color:var(--accent);"><i class="fa-solid fa-file-signature"></i></div><div class="stat-value" style="font-size:1.4rem;"><?= $totalExams ?></div><div class="stat-label">ข้อสอบ</div></div>
        <div class="card stat-card" style="padding:16px;"><div class="stat-icon" style="background:var(--secondary-light);color:var(--secondary);"><i class="fa-solid fa-language"></i></div><div class="stat-value" style="font-size:1.4rem;"><?= $totalVocab ?></div><div class="stat-label">คำศัพท์</div></div>
    </div>

    <div class="flex gap-12" style="margin-bottom:24px;">
        <div class="card" style="flex:1;text-align:center;">
            <div style="font-size:2rem; margin-bottom:4px;">🎯</div>
            <div style="font-family:var(--font-display);font-weight:900;font-size:1.5rem;color:var(--success);"><?= $avgScore ?>%</div>
            <div style="font-size:0.8rem;color:var(--text-secondary); font-weight:600;">คะแนนเฉลี่ยรวม</div>
        </div>
        <div class="card" style="flex:1;text-align:center;">
            <div style="font-size:2rem; margin-bottom:4px;">🚀</div>
            <div style="font-family:var(--font-display);font-weight:900;font-size:1.5rem;color:var(--primary);"><?= $activeToday ?></div>
            <div style="font-size:0.8rem;color:var(--text-secondary); font-weight:600;">เข้าเรียนวันนี้</div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
