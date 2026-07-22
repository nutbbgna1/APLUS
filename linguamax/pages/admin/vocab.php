<?php
include __DIR__ . '/../../includes/header.php';
$db = getDB();

// Fetch vocab along with lesson title
$stmt = $db->query("
    SELECT v.*, l.title as lesson_title 
    FROM vocabulary v 
    LEFT JOIN lessons l ON v.lesson_id = l.id 
    ORDER BY v.id DESC
");
$vocab = $stmt->fetchAll();

// Fetch lessons for dropdown
$lessons = $db->query("SELECT id, title FROM lessons ORDER BY sort_order")->fetchAll();
?>
<div class="animate-fade-in">
    <div class="flex justify-between items-center" style="margin-bottom:20px;">
        <div class="flex items-center gap-12">
            <a href="?page=admin&sub=content" class="btn-ghost">← กลับ</a>
            <h1 style="margin:0;">🃏 จัดการคำศัพท์</h1>
        </div>
        <button class="btn btn-primary" onclick="openVocabModal()">+ เพิ่มคำศัพท์</button>
    </div>

    <div class="card table-wrap" style="padding:0;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>คำศัพท์ (EN)</th>
                    <th>คำแปล (TH)</th>
                    <th>คำอ่าน</th>
                    <th>ระดับ</th>
                    <th>บทเรียน</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vocab as $v): ?>
                <tr>
                    <td style="font-weight:700;color:var(--primary);"><?= sanitize($v['word_en']) ?></td>
                    <td><?= sanitize($v['word_th']) ?></td>
                    <td style="color:var(--text-secondary);"><?= sanitize($v['phonetics']) ?></td>
                    <td><span class="badge badge-<?= $v['level'] === 'beginner' ? 'success' : ($v['level'] === 'intermediate' ? 'accent' : 'primary') ?>"><?= $v['level'] ?></span></td>
                    <td style="font-size:0.8rem;"><?= $v['lesson_title'] ? sanitize($v['lesson_title']) : '-' ?></td>
                    <td>
                        <button class="btn-ghost" onclick='editVocab(<?= json_encode($v, JSON_HEX_APOS) ?>)'><span class="material-symbols-outlined" style="font-size:18px;">edit</span></button>
                        <button class="btn-ghost" style="color:var(--danger);" onclick="deleteVocab(<?= $v['id'] ?>)"><span class="material-symbols-outlined" style="font-size:18px;">delete</span></button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Vocab Form -->
<div id="vocabModal" class="modal-overlay hidden">
    <div class="modal-content">
        <div class="modal-handle"></div>
        <h2 id="modalTitle" style="margin-bottom:16px;">เพิ่มคำศัพท์</h2>
        <form id="vocabForm" onsubmit="saveVocab(event)">
            <input type="hidden" id="v_id" name="id" value="0">
            
            <div class="flex gap-12" style="margin-bottom:12px;">
                <div class="input-group w-full">
                    <label>คำศัพท์ (EN)</label>
                    <input type="text" id="v_en" class="input-field" required>
                </div>
                <div class="input-group w-full">
                    <label>คำแปล (TH)</label>
                    <input type="text" id="v_th" class="input-field" required>
                </div>
            </div>

            <div class="input-group" style="margin-bottom:12px;">
                <label>คำอ่าน (Phonetics) - ข้ามได้</label>
                <input type="text" id="v_pho" class="input-field" placeholder="เช่น /ˈæpl/">
            </div>

            <div class="input-group" style="margin-bottom:12px;">
                <label>ประโยคตัวอย่าง (ข้ามได้)</label>
                <input type="text" id="v_ex" class="input-field">
            </div>

            <div class="flex gap-12" style="margin-bottom:20px;">
                <div class="input-group w-full">
                    <label>ระดับ</label>
                    <select id="v_level" class="input-field">
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>
                <div class="input-group w-full">
                    <label>ผูกกับบทเรียน (ข้ามได้)</label>
                    <select id="v_lesson" class="input-field">
                        <option value="">-- ไม่ระบุ --</option>
                        <?php foreach ($lessons as $l): ?>
                        <option value="<?= $l['id'] ?>"><?= sanitize($l['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex justify-between gap-12">
                <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('vocabModal')">ยกเลิก</button>
                <button type="submit" class="btn btn-primary" style="flex:1;">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
<script>
function openVocabModal() {
    document.getElementById('modalTitle').textContent = 'เพิ่มคำศัพท์';
    document.getElementById('v_id').value = '0';
    document.getElementById('v_en').value = '';
    document.getElementById('v_th').value = '';
    document.getElementById('v_pho').value = '';
    document.getElementById('v_ex').value = '';
    document.getElementById('v_level').value = 'beginner';
    document.getElementById('v_lesson').value = '';
    document.getElementById('vocabModal').classList.remove('hidden');
}

function editVocab(data) {
    document.getElementById('modalTitle').textContent = 'แก้ไขคำศัพท์';
    document.getElementById('v_id').value = data.id;
    document.getElementById('v_en').value = data.word_en;
    document.getElementById('v_th').value = data.word_th;
    document.getElementById('v_pho').value = data.phonetics || '';
    document.getElementById('v_ex').value = data.example_sentence || '';
    document.getElementById('v_level').value = data.level;
    document.getElementById('v_lesson').value = data.lesson_id || '';
    document.getElementById('vocabModal').classList.remove('hidden');
}

function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}

async function saveVocab(e) {
    e.preventDefault();
    const payload = {
        action: 'save_vocab',
        id: document.getElementById('v_id').value,
        word_en: document.getElementById('v_en').value,
        word_th: document.getElementById('v_th').value,
        phonetics: document.getElementById('v_pho').value,
        example_sentence: document.getElementById('v_ex').value,
        level: document.getElementById('v_level').value,
        lesson_id: document.getElementById('v_lesson').value,
        category: 'general'
    };
    const res = await apiCall('admin.php', payload);
    if (res && res.success) window.location.reload();
}

async function deleteVocab(id) {
    if (confirm('ยืนยันการลบคำศัพท์นี้?')) {
        const res = await apiCall('admin.php', { action: 'delete_vocab', id: id });
        if (res && res.success) window.location.reload();
    }
}
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
