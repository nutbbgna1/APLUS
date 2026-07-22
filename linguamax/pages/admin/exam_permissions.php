<?php
include __DIR__ . '/../../includes/header.php';
$db = getDB();

// Fetch all students
$stmt = $db->query("SELECT id, code, fname, lname, nickname, avatar_color FROM users WHERE role = 'student' ORDER BY fname ASC");
$students = $stmt->fetchAll();

// Fetch all exams
$stmt = $db->query("SELECT id, title, unit, level FROM exams ORDER BY unit ASC, id ASC");
$exams = $stmt->fetchAll();

// Group exams by unit
$examsByUnit = [];
foreach ($exams as $e) {
    $unit = $e['unit'] ?: 'Unit 1';
    $examsByUnit[$unit][] = $e;
}
?>

<div class="animate-fade-in">
    <div class="flex justify-between items-center" style="margin-bottom:20px;">
        <h1>🔐 จัดการสิทธิ์การสอบ (Exam Permissions)</h1>
    </div>

    <div class="card table-wrap" style="padding:0;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>นักเรียน</th>
                    <th>ชื่อเล่น</th>
                    <th>สิทธิ์การเข้าถึงแบบทดสอบ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $s): ?>
                <tr>
                    <td>
                        <div class="flex items-center gap-8">
                            <div class="avatar" style="width:28px;height:28px;font-size:0.75rem;background:<?= $s['avatar_color'] ?>"><?= mb_substr($s['fname'],0,1) ?></div>
                            <?= sanitize($s['fname']) ?> <?= sanitize($s['lname']) ?> (<?= sanitize($s['code']) ?>)
                        </div>
                    </td>
                    <td><?= sanitize($s['nickname']) ?></td>
                    <td>
                        <?php
                            // Count permissions
                            $stmt = $db->prepare("SELECT COUNT(*) FROM exam_permissions WHERE user_id = ?");
                            $stmt->execute([$s['id']]);
                            $grantedCount = $stmt->fetchColumn();
                        ?>
                        <span style="color:var(--primary); font-weight:700;"><?= $grantedCount ?></span> แบบทดสอบ
                    </td>
                    <td>
                        <button class="btn btn-outline" style="padding: 6px 12px; font-size: 0.85rem;" onclick='openPermissionModal(<?= $s['id'] ?>, "<?= sanitize($s['fname']) ?>")'>จัดการสิทธิ์</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="permModal" class="modal-overlay hidden">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-handle"></div>
        <h2 id="permModalTitle" style="margin-bottom:16px;">จัดการสิทธิ์นักเรียน</h2>
        <form id="permForm" onsubmit="savePermissions(event)">
            <input type="hidden" id="perm_user_id" name="user_id" value="0">
            
            <div id="examCheckboxes" style="max-height: 350px; overflow-y: auto; padding-right: 8px;">
                <?php foreach ($examsByUnit as $unit => $unitExams): ?>
                    <h3 style="margin-top:12px; margin-bottom:8px; color:var(--primary-dark); font-size:1rem; font-weight:800; border-bottom:1px solid #E2E8F0; padding-bottom:4px;"><?= sanitize($unit) ?></h3>
                    <?php foreach ($unitExams as $e): ?>
                        <label class="flex items-center gap-8" style="margin-bottom: 8px; cursor:pointer;">
                            <input type="checkbox" name="exams[]" value="<?= $e['id'] ?>" class="exam-checkbox">
                            <span style="font-size:0.95rem; font-weight:600; color:#1E293B;"><?= sanitize($e['title']) ?> (<?= sanitize($e['level']) ?>)</span>
                        </label>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>

            <div class="flex justify-between gap-12" style="margin-top: 20px;">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('permModal')">ยกเลิก</button>
                <button type="submit" class="btn btn-primary" style="flex:1;">บันทึกสิทธิ์</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPermissionModal(userId, name) {
    document.getElementById('permModalTitle').innerText = 'จัดการสิทธิ์: ' + name;
    document.getElementById('perm_user_id').value = userId;
    
    // Reset checkboxes
    document.querySelectorAll('.exam-checkbox').forEach(cb => cb.checked = false);
    
    // Fetch current permissions
    fetch(`<?= SITE_URL ?>/api/admin.php?action=get_exam_permissions&user_id=${userId}`)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                data.exams.forEach(id => {
                    let cb = document.querySelector(`.exam-checkbox[value="${id}"]`);
                    if(cb) cb.checked = true;
                });
            }
            document.getElementById('permModal').classList.remove('hidden');
        });
}

function savePermissions(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    // Manual JSON construction to ensure array is passed properly
    let exams = [];
    formData.getAll('exams[]').forEach(v => exams.push(v));

    const data = {
        action: 'save_exam_permissions',
        user_id: formData.get('user_id'),
        exams: exams
    };

    fetch('<?= SITE_URL ?>/api/admin.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(res => {
        if(res.success) {
            closeModal('permModal');
            showToast('บันทึกสิทธิ์สำเร็จแล้ว', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            showToast(res.error || 'เกิดข้อผิดพลาด', 'error');
        }
    });
}
</script>
