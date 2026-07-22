<?php
include __DIR__ . '/../../includes/header.php';
$db = getDB();

$tab = $_GET['tab'] ?? 'sentences';

// Fetch Sentences
$stmt = $db->query("SELECT * FROM game_sentences ORDER BY id DESC");
$sentences = $stmt->fetchAll();

// Fetch Fill Blanks
$stmt = $db->query("SELECT * FROM game_fill_blanks ORDER BY id DESC");
$fills = $stmt->fetchAll();
?>
<div class="animate-fade-in">
    <div class="flex justify-between items-center" style="margin-bottom:20px;">
        <div class="flex items-center gap-12">
            <a href="?page=admin&sub=content" class="btn-ghost">← กลับ</a>
            <h1 style="margin:0;"><i class="fa-solid fa-gamepad"></i> จัดการมินิเกม</h1>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-8" style="margin-bottom:20px;">
        <a href="?page=admin&sub=games&tab=sentences" class="btn <?= $tab === 'sentences' ? 'btn-primary' : 'btn-outline' ?>">📝 เรียงประโยค</a>
        <a href="?page=admin&sub=games&tab=fills" class="btn <?= $tab === 'fills' ? 'btn-primary' : 'btn-outline' ?>">✏️ เติมคำ</a>
    </div>

    <?php if ($tab === 'sentences'): ?>
    <!-- Sentences Section -->
    <div class="flex justify-between items-center" style="margin-bottom:16px;">
        <h2 style="margin:0;">📝 เกมเรียงประโยค (Sentence Builder)</h2>
        <button class="btn btn-primary" onclick="openSentenceModal()">+ เพิ่มประโยค</button>
    </div>

    <div class="card table-wrap" style="padding:0;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ประโยคภาษาอังกฤษ</th>
                    <th>คำแปล (ไทย)</th>
                    <th width="100">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sentences as $s): ?>
                <tr>
                    <td><strong><?= sanitize($s['sentence_en']) ?></strong></td>
                    <td><?= sanitize($s['sentence_th']) ?></td>
                    <td>
                        <button class="btn-ghost" onclick='editSentence(<?= json_encode($s, JSON_HEX_APOS) ?>)'>✏️</button>
                        <button class="btn-ghost" style="color:var(--danger);" onclick="deleteSentence(<?= $s['id'] ?>)">🗑️</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($sentences)): ?>
                <tr><td colspan="3" class="text-center" style="padding:20px;">ยังไม่มีข้อมูล</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Sentence Modal -->
    <div id="sentenceModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width:600px;">
            <div class="modal-handle"></div>
            <h2 id="sModalTitle" style="margin-bottom:16px;">เพิ่มประโยค</h2>
            <form onsubmit="saveSentence(event)">
                <input type="hidden" id="s_id" value="0">
                <div class="input-group" style="margin-bottom:12px;">
                    <label>ประโยคภาษาอังกฤษ (ระบบจะสับคำให้เอง)</label>
                    <input type="text" id="s_en" class="input-field" required placeholder="เช่น I go to school every day">
                </div>
                <div class="input-group" style="margin-bottom:20px;">
                    <label>คำแปลภาษาไทย (ไว้บอกใบ้นักเรียน)</label>
                    <input type="text" id="s_th" class="input-field" required placeholder="เช่น ฉันไปโรงเรียนทุกวัน">
                </div>
                <div class="flex justify-between gap-12">
                    <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('sentenceModal')">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary" style="flex:1;">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openSentenceModal() {
        document.getElementById('sModalTitle').textContent = 'เพิ่มประโยค';
        document.getElementById('s_id').value = '0';
        document.getElementById('s_en').value = '';
        document.getElementById('s_th').value = '';
        document.getElementById('sentenceModal').classList.remove('hidden');
    }
    function editSentence(data) {
        document.getElementById('sModalTitle').textContent = 'แก้ไขประโยค';
        document.getElementById('s_id').value = data.id;
        document.getElementById('s_en').value = data.sentence_en;
        document.getElementById('s_th').value = data.sentence_th;
        document.getElementById('sentenceModal').classList.remove('hidden');
    }
    async function saveSentence(e) {
        e.preventDefault();
        const payload = {
            action: 'save_game_sentence',
            id: document.getElementById('s_id').value,
            sentence_en: document.getElementById('s_en').value,
            sentence_th: document.getElementById('s_th').value
        };
        const res = await apiCall('admin.php', payload);
        if (res && res.success) window.location.reload();
    }
    async function deleteSentence(id) {
        if (confirm('ยืนยันการลบประโยคนี้?')) {
            const res = await apiCall('admin.php', { action: 'delete_game_sentence', id });
            if (res && res.success) window.location.reload();
        }
    }
    </script>
    <?php endif; ?>

    <?php if ($tab === 'fills'): ?>
    <!-- Fill Blanks Section -->
    <div class="flex justify-between items-center" style="margin-bottom:16px;">
        <h2 style="margin:0;">✏️ เกมเติมคำ (Fill in the Blank)</h2>
        <button class="btn btn-primary" onclick="openFillModal()">+ เพิ่มโจทย์</button>
    </div>

    <div class="card table-wrap" style="padding:0;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>โจทย์ (ใช้ ___ แทนช่องว่าง)</th>
                    <th>คำตอบที่ถูก</th>
                    <th>ตัวเลือกลวง</th>
                    <th width="100">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($fills as $f): ?>
                <tr>
                    <td><strong><?= sanitize($f['question_text']) ?></strong></td>
                    <td style="color:var(--success);font-weight:700;"><?= sanitize($f['correct_answer']) ?></td>
                    <td><small><?= sanitize($f['choice_1'] . ', ' . $f['choice_2'] . ', ' . $f['choice_3'] . ', ' . $f['choice_4']) ?></small></td>
                    <td>
                        <button class="btn-ghost" onclick='editFill(<?= json_encode($f, JSON_HEX_APOS) ?>)'>✏️</button>
                        <button class="btn-ghost" style="color:var(--danger);" onclick="deleteFill(<?= $f['id'] ?>)">🗑️</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($fills)): ?>
                <tr><td colspan="4" class="text-center" style="padding:20px;">ยังไม่มีข้อมูล</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Fill Modal -->
    <div id="fillModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width:700px;">
            <div class="modal-handle"></div>
            <h2 id="fModalTitle" style="margin-bottom:16px;">เพิ่มโจทย์เติมคำ</h2>
            <form onsubmit="saveFill(event)">
                <input type="hidden" id="f_id" value="0">
                <div class="input-group" style="margin-bottom:12px;">
                    <label>โจทย์ (ใช้ ___ แทนช่องว่าง)</label>
                    <input type="text" id="f_q" class="input-field" required placeholder="เช่น I ___ a student.">
                </div>
                <div class="input-group" style="margin-bottom:12px;">
                    <label>คำตอบที่ถูกต้อง</label>
                    <input type="text" id="f_ans" class="input-field" required placeholder="เช่น am">
                </div>
                
                <label style="display:block;margin-bottom:8px;font-size:0.9rem;font-weight:700;">ตัวเลือก 4 ข้อ (รวมคำตอบที่ถูกด้วย)</label>
                <div class="flex gap-12" style="margin-bottom:12px;">
                    <input type="text" id="f_c1" class="input-field w-full" required placeholder="ตัวเลือก 1">
                    <input type="text" id="f_c2" class="input-field w-full" required placeholder="ตัวเลือก 2">
                </div>
                <div class="flex gap-12" style="margin-bottom:20px;">
                    <input type="text" id="f_c3" class="input-field w-full" required placeholder="ตัวเลือก 3">
                    <input type="text" id="f_c4" class="input-field w-full" required placeholder="ตัวเลือก 4">
                </div>

                <div class="flex justify-between gap-12">
                    <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('fillModal')">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary" style="flex:1;">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openFillModal() {
        document.getElementById('fModalTitle').textContent = 'เพิ่มโจทย์';
        document.getElementById('f_id').value = '0';
        document.getElementById('f_q').value = '';
        document.getElementById('f_ans').value = '';
        document.getElementById('f_c1').value = '';
        document.getElementById('f_c2').value = '';
        document.getElementById('f_c3').value = '';
        document.getElementById('f_c4').value = '';
        document.getElementById('fillModal').classList.remove('hidden');
    }
    function editFill(data) {
        document.getElementById('fModalTitle').textContent = 'แก้ไขโจทย์';
        document.getElementById('f_id').value = data.id;
        document.getElementById('f_q').value = data.question_text;
        document.getElementById('f_ans').value = data.correct_answer;
        document.getElementById('f_c1').value = data.choice_1;
        document.getElementById('f_c2').value = data.choice_2;
        document.getElementById('f_c3').value = data.choice_3;
        document.getElementById('f_c4').value = data.choice_4;
        document.getElementById('fillModal').classList.remove('hidden');
    }
    async function saveFill(e) {
        e.preventDefault();
        const payload = {
            action: 'save_game_fill',
            id: document.getElementById('f_id').value,
            question_text: document.getElementById('f_q').value,
            correct_answer: document.getElementById('f_ans').value,
            choice_1: document.getElementById('f_c1').value,
            choice_2: document.getElementById('f_c2').value,
            choice_3: document.getElementById('f_c3').value,
            choice_4: document.getElementById('f_c4').value
        };
        const res = await apiCall('admin.php', payload);
        if (res && res.success) window.location.reload();
    }
    async function deleteFill(id) {
        if (confirm('ยืนยันการลบโจทย์นี้?')) {
            const res = await apiCall('admin.php', { action: 'delete_game_fill', id });
            if (res && res.success) window.location.reload();
        }
    }
    </script>
    <?php endif; ?>

</div>
<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
<script>
function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
</script>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
