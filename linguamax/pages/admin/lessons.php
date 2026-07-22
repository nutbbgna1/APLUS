<?php
include __DIR__ . '/../../includes/header.php';
$db = getDB();

$stmt = $db->query("SELECT * FROM lessons ORDER BY sort_order ASC, id ASC");
$lessons = $stmt->fetchAll();
?>
<div class="animate-fade-in">
    <div class="flex justify-between items-center" style="margin-bottom:20px;">
        <div class="flex items-center gap-12">
            <a href="?page=admin&sub=content" class="btn-ghost">← กลับ</a>
            <h1 style="margin:0;">📖 จัดการบทเรียน</h1>
        </div>
        <button class="btn btn-primary" onclick="openLessonModal()">+ เพิ่มบทเรียน</button>
    </div>

    <div class="card table-wrap" style="padding:0;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ลำดับ</th>
                    <th>ระดับ</th>
                    <th>ชื่อบทเรียน</th>
                    <th>รายละเอียด</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lessons as $l): ?>
                <tr>
                    <td><?= $l['sort_order'] ?></td>
                    <td><span class="badge badge-<?= $l['level'] === 'beginner' ? 'success' : ($l['level'] === 'intermediate' ? 'accent' : 'primary') ?>"><?= $l['level'] ?></span></td>
                    <td style="font-weight:700;"><?= sanitize($l['title']) ?></td>
                    <td><?= sanitize($l['description']) ?></td>
                    <td>
                        <button class="btn-ghost" onclick='editLesson(<?= json_encode($l, JSON_HEX_APOS) ?>)'><span class="material-symbols-outlined" style="font-size:18px;">edit</span></button>
                        <button class="btn-ghost" style="color:var(--danger);" onclick="deleteLesson(<?= $l['id'] ?>)"><span class="material-symbols-outlined" style="font-size:18px;">delete</span></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Lesson Form -->
<div id="lessonModal" class="modal-overlay hidden">
    <div class="modal-content" style="max-width:800px;">
        <div class="modal-handle"></div>
        <h2 id="modalTitle" style="margin-bottom:16px;">เพิ่มบทเรียน</h2>
        <form id="lessonForm" onsubmit="saveLesson(event)">
            <input type="hidden" id="l_id" name="id" value="0">
            <div class="flex gap-12" style="margin-bottom:12px;">
                <div class="input-group w-full">
                    <label>ชื่อบทเรียน</label>
                    <input type="text" id="l_title" class="input-field" required>
                </div>
                <div class="input-group" style="width:120px;">
                    <label>ลำดับ (เลข)</label>
                    <input type="number" id="l_sort" class="input-field" value="1" min="1" required>
                </div>
            </div>
            <div class="input-group" style="margin-bottom:12px;">
                <label>รายละเอียดสั้นๆ</label>
                <input type="text" id="l_desc" class="input-field">
            </div>
            <div class="input-group" style="margin-bottom:12px;">
                <label>ระดับความยาก</label>
                <select id="l_level" class="input-field">
                    <option value="beginner">Beginner</option>
                    <option value="intermediate">Intermediate</option>
                    <option value="advanced">Advanced</option>
                </select>
            </div>
            <div class="input-group" style="margin-bottom:20px;">
                <label>เนื้อหาบทเรียน (รองรับ HTML)</label>
                <textarea id="l_content" class="input-field" rows="6" placeholder="<p>เนื้อหา...</p>"></textarea>
            </div>
            <div class="flex justify-between gap-12">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('lessonModal')">ยกเลิก</button>
                <button type="submit" class="btn btn-primary" style="flex:1;">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
<script>
function openLessonModal() {
    document.getElementById('modalTitle').textContent = 'เพิ่มบทเรียน';
    document.getElementById('l_id').value = '0';
    document.getElementById('l_title').value = '';
    document.getElementById('l_sort').value = '1';
    document.getElementById('l_desc').value = '';
    document.getElementById('l_level').value = 'beginner';
    document.getElementById('l_content').value = '';
    document.getElementById('lessonModal').classList.remove('hidden');
}

function editLesson(data) {
    document.getElementById('modalTitle').textContent = 'แก้ไขบทเรียน';
    document.getElementById('l_id').value = data.id;
    document.getElementById('l_title').value = data.title;
    document.getElementById('l_sort').value = data.sort_order;
    document.getElementById('l_desc').value = data.description;
    document.getElementById('l_level').value = data.level;
    document.getElementById('l_content').value = data.content;
    document.getElementById('lessonModal').classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

async function saveLesson(e) {
    e.preventDefault();
    const payload = {
        action: 'save_lesson',
        id: document.getElementById('l_id').value,
        title: document.getElementById('l_title').value,
        sort_order: document.getElementById('l_sort').value,
        description: document.getElementById('l_desc').value,
        level: document.getElementById('l_level').value,
        content: document.getElementById('l_content').value
    };
    const res = await apiCall('admin.php', payload);
    if (res && res.success) window.location.reload();
}

async function deleteLesson(id) {
    if (confirm('ยืนยันการลบบทเรียนนี้? การลบจะทำให้ข้อมูลย่อย (เช่น คำศัพท์ ข้อสอบ) ในบทนี้หายไปด้วย')) {
        const res = await apiCall('admin.php', { action: 'delete_lesson', id: id });
        if (res && res.success) window.location.reload();
    }
}
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
