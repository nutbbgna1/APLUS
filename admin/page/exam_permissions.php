<?php
if (!isset($_SESSION['admin_logged_in'])) {
    die("Unauthorized Access");
}

// Fetch all students
$stmt = $db->query("SELECT id, code, fname, lname, nickname, avatar_color FROM users WHERE role = 'student' ORDER BY fname ASC");
$students = $stmt->fetchAll();

// Fetch all exams
$stmt = $db->query("SELECT id, title, unit, level, access_mode FROM exams ORDER BY unit ASC, id ASC");
$exams = $stmt->fetchAll();

// Group exams by unit
$examsByUnit = [];
foreach ($exams as $e) {
    $unit = $e['unit'] ?: 'Unit 1';
    $examsByUnit[$unit][] = $e;
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Exam Permissions</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Manage which students can access Restricted exams</p>
    </div>
</div>

<div class="card" style="padding:0; overflow: hidden;">
    <table style="width: 100%; border-collapse: collapse; text-align: left;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border);">
                <th style="padding: 15px;">Student Name</th>
                <th style="padding: 15px;">Nickname</th>
                <th style="padding: 15px;">Granted Access</th>
                <th style="padding: 15px; width: 120px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $s): ?>
            <tr style="border-bottom: 1px solid var(--border);">
                <td style="padding: 15px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: <?= $s['avatar_color'] ?>; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 700; color: white;">
                            <?= mb_substr($s['fname'], 0, 1) ?>
                        </div>
                        <div style="font-weight: 600; color: var(--text-main);">
                            <?= htmlspecialchars($s['fname']) ?> <?= htmlspecialchars($s['lname']) ?> 
                            <span style="color: var(--text-muted); font-weight: 400; font-size: 0.85rem;">(<?= htmlspecialchars($s['code']) ?>)</span>
                        </div>
                    </div>
                </td>
                <td style="padding: 15px; color: var(--text-secondary);"><?= htmlspecialchars($s['nickname']) ?></td>
                <td style="padding: 15px;">
                    <?php
                        $stmt = $db->prepare("SELECT COUNT(*) FROM exam_permissions WHERE user_id = ?");
                        $stmt->execute([$s['id']]);
                        $grantedCount = $stmt->fetchColumn();
                    ?>
                    <span style="background: #E0E7FF; color: #4338CA; padding: 4px 10px; border-radius: 20px; font-weight: 700; font-size: 0.85rem;">
                        <?= $grantedCount ?> Exams
                    </span>
                </td>
                <td style="padding: 15px;">
                    <button class="btn btn-sm btn-outline" style="border-radius: 8px;" onclick='openPermissionModal(<?= $s['id'] ?>, <?= json_encode($s['fname']) ?>)'>
                        <i class="fa-solid fa-user-lock"></i> Manage
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($students)): ?>
            <tr><td colspan="4" style="padding: 20px; text-align: center; color: var(--text-muted);">No students found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal for Permissions -->
<div id="permModalOverlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: 16px; width: 100%; max-width: 500px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); transform: scale(0.95); transition: transform 0.2s; max-height: 90vh; display: flex; flex-direction: column;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 id="permModalTitle" style="margin: 0; font-size: 1.25rem;">Manage Permissions</h2>
            <button onclick="closePermissionModal()" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form id="permForm" onsubmit="savePermissions(event)" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
            <input type="hidden" id="perm_user_id" name="user_id" value="0">
            
            <div id="examCheckboxes" style="overflow-y: auto; padding-right: 10px; flex: 1;">
                <?php foreach ($examsByUnit as $unit => $unitExams): ?>
                    <div style="margin-bottom: 16px;">
                        <h3 style="margin-top:0; margin-bottom:12px; color: var(--primary); font-size: 0.95rem; font-weight: 800; border-bottom: 2px solid #F1F5F9; padding-bottom: 4px;">
                            <?= htmlspecialchars($unit) ?>
                        </h3>
                        <?php foreach ($unitExams as $e): ?>
                            <label style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; cursor: pointer; padding: 8px; border-radius: 8px; transition: background 0.2s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox" name="exams[]" value="<?= $e['id'] ?>" class="exam-checkbox" style="width: 18px; height: 18px; accent-color: var(--primary);">
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-size: 0.95rem; font-weight: 600; color: var(--text-main);"><?= htmlspecialchars($e['title']) ?></span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted);">
                                        Level: <?= ucfirst($e['level']) ?> 
                                        &middot; Mode: 
                                        <?php if($e['access_mode'] === 'public'): ?>
                                            <span style="color: #16A34A; font-weight:600;">Public</span>
                                        <?php elseif($e['access_mode'] === 'locked'): ?>
                                            <span style="color: #DC2626; font-weight:600;">Locked</span>
                                        <?php else: ?>
                                            <span style="color: #D97706; font-weight:600;">Restricted</span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
                <?php if(empty($examsByUnit)): ?>
                    <p style="text-align: center; color: var(--text-muted); padding: 20px;">No exams available to grant access to.</p>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border);">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="closePermissionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="flex:1;" id="savePermBtn">Save Permissions</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPermissionModal(userId, name) {
    document.getElementById('permModalTitle').innerText = 'Permissions: ' + name;
    document.getElementById('perm_user_id').value = userId;
    
    // Reset checkboxes
    document.querySelectorAll('.exam-checkbox').forEach(cb => cb.checked = false);
    
    // Fetch current permissions
    fetch(`ajax/exam_permissions.php?action=get&user_id=${userId}`)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                data.exams.forEach(id => {
                    let cb = document.querySelector(`.exam-checkbox[value="${id}"]`);
                    if(cb) cb.checked = true;
                });
            }
            const modal = document.getElementById('permModalOverlay');
            modal.style.display = 'flex';
            setTimeout(() => modal.children[0].style.transform = 'scale(1)', 10);
        })
        .catch(err => {
            alert('Error loading permissions: ' + err.message);
        });
}

function closePermissionModal() {
    const modal = document.getElementById('permModalOverlay');
    modal.children[0].style.transform = 'scale(0.95)';
    setTimeout(() => modal.style.display = 'none', 200);
}

function savePermissions(e) {
    e.preventDefault();
    const btn = document.getElementById('savePermBtn');
    btn.disabled = true;
    btn.innerText = 'Saving...';
    
    const formData = new FormData(e.target);
    formData.append('action', 'save');

    fetch('ajax/exam_permissions.php?action=save', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if(res.success) {
            closePermissionModal();
            window.location.reload();
        } else {
            alert(res.error || 'Failed to save');
            btn.disabled = false;
            btn.innerText = 'Save Permissions';
        }
    })
    .catch(err => {
        alert('Connection error');
        btn.disabled = false;
        btn.innerText = 'Save Permissions';
    });
}
</script>
