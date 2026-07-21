<?php
include __DIR__ . '/../../includes/header.php';
$db = getDB();

$stmt = $db->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM exam_results WHERE user_id = u.id) as exams_taken,
           (SELECT ROUND(AVG(percentage)) FROM exam_results WHERE user_id = u.id) as avg_score,
           COALESCE(s.current_streak, 0) as streak
    FROM users u 
    LEFT JOIN user_streaks s ON u.id = s.user_id
    WHERE u.role = 'student' 
    ORDER BY u.fname ASC
");
$students = $stmt->fetchAll();
?>
<div class="animate-fade-in">
    <div class="flex justify-between items-center" style="margin-bottom:20px;">
        <h1>👥 จัดการนักเรียน</h1>
        <button class="btn btn-primary" onclick="openStudentModal()">+ เพิ่มนักเรียน</button>
    </div>

    <div class="card table-wrap" style="padding:0;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>รหัส</th>
                    <th>ชื่อ - นามสกุล</th>
                    <th>ชื่อเล่น</th>
                    <th>XP</th>
                    <th>Streak</th>
                    <th>ประวัติสอบ</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $s): ?>
                <tr>
                    <td><strong><?= sanitize($s['code']) ?></strong></td>
                    <td>
                        <div class="flex items-center gap-8">
                            <div class="avatar" style="width:28px;height:28px;font-size:0.75rem;background:<?= $s['avatar_color'] ?>"><?= mb_substr($s['fname'],0,1) ?></div>
                            <?= sanitize($s['fname']) ?> <?= sanitize($s['lname']) ?>
                        </div>
                    </td>
                    <td><?= sanitize($s['nickname']) ?></td>
                    <td style="color:var(--primary);font-weight:700;"><?= number_format($s['xp']) ?></td>
                    <td>🔥 <?= $s['streak'] ?></td>
                    <td>
                        <?= $s['exams_taken'] ?> ครั้ง <br>
                        <span style="font-size:0.75rem;color:var(--text-secondary);">เฉลี่ย: <?= $s['avg_score'] ?: 0 ?>%</span>
                    </td>
                    <td>
                        <button class="btn-ghost" onclick='editStudent(<?= json_encode($s, JSON_HEX_APOS) ?>)'><span class="material-symbols-outlined" style="font-size:18px;">edit</span></button>
                        <button class="btn-ghost" style="color:var(--danger);" onclick="deleteStudent(<?= $s['id'] ?>, '<?= sanitize($s['code']) ?>')"><span class="material-symbols-outlined" style="font-size:18px;">delete</span></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Student Form -->
<div id="studentModal" class="modal-overlay hidden">
    <div class="modal-content">
        <div class="modal-handle"></div>
        <h2 id="modalTitle" style="margin-bottom:16px;">เพิ่มนักเรียน</h2>
        <form id="studentForm" onsubmit="saveStudent(event)">
            <input type="hidden" id="stu_id" name="id" value="0">
            <div class="input-group" style="margin-bottom:12px;">
                <label>รหัสประจำตัว (ใช้ล็อคอิน)</label>
                <input type="text" id="stu_code" class="input-field" required>
            </div>
            <div class="input-group" style="margin-bottom:12px;">
                <label>รหัสผ่าน (เว้นว่างหากไม่ต้องการเปลี่ยนสำหรับแก้ไข)</label>
                <input type="password" id="stu_pass" class="input-field">
            </div>
            <div class="flex gap-12" style="margin-bottom:12px;">
                <div class="input-group w-full">
                    <label>ชื่อจริง</label>
                    <input type="text" id="stu_fname" class="input-field" required>
                </div>
                <div class="input-group w-full">
                    <label>นามสกุล</label>
                    <input type="text" id="stu_lname" class="input-field">
                </div>
            </div>
            <div class="input-group" style="margin-bottom:20px;">
                <label>ชื่อเล่น</label>
                <input type="text" id="stu_nickname" class="input-field" required>
            </div>
            <div class="flex justify-between gap-12">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('studentModal')">ยกเลิก</button>
                <button type="submit" class="btn btn-primary" style="flex:1;">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
<script>
function openStudentModal() {
    document.getElementById('modalTitle').textContent = 'เพิ่มนักเรียน';
    document.getElementById('stu_id').value = '0';
    document.getElementById('stu_code').value = '';
    document.getElementById('stu_code').readOnly = false;
    document.getElementById('stu_pass').value = '';
    document.getElementById('stu_pass').required = true;
    document.getElementById('stu_fname').value = '';
    document.getElementById('stu_lname').value = '';
    document.getElementById('stu_nickname').value = '';
    document.getElementById('studentModal').classList.remove('hidden');
}

function editStudent(data) {
    document.getElementById('modalTitle').textContent = 'แก้ไขข้อมูลนักเรียน';
    document.getElementById('stu_id').value = data.id;
    document.getElementById('stu_code').value = data.code;
    document.getElementById('stu_code').readOnly = true; // Cannot change code
    document.getElementById('stu_pass').value = '';
    document.getElementById('stu_pass').required = false;
    document.getElementById('stu_fname').value = data.fname;
    document.getElementById('stu_lname').value = data.lname;
    document.getElementById('stu_nickname').value = data.nickname;
    document.getElementById('studentModal').classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

async function saveStudent(e) {
    e.preventDefault();
    const id = document.getElementById('stu_id').value;
    const action = id === '0' ? 'add_student' : 'edit_student';
    const payload = {
        action: action,
        id: id,
        code: document.getElementById('stu_code').value,
        password: document.getElementById('stu_pass').value,
        fname: document.getElementById('stu_fname').value,
        lname: document.getElementById('stu_lname').value,
        nickname: document.getElementById('stu_nickname').value
    };

    const res = await apiCall('admin.php', payload);
    if (res && res.success) {
        window.location.reload();
    }
}

async function deleteStudent(id, code) {
    if (confirm(`คุณต้องการลบนักเรียนรหัส ${code} พร้อมประวัติการเรียนทั้งหมดหรือไม่? การกระทำนี้ไม่สามารถย้อนกลับได้`)) {
        const res = await apiCall('admin.php', { action: 'delete_student', id: id });
        if (res && res.success) {
            window.location.reload();
        }
    }
}
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
