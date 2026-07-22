<?php
include __DIR__ . '/../../includes/header.php';
$db = getDB();

$passageId = intval($_GET['passage_id'] ?? 0);

if ($passageId > 0) {
    // Show Questions for specific reading passage
    $stmt = $db->prepare("SELECT * FROM reading_passages WHERE id = ?");
    $stmt->execute([$passageId]);
    $passage = $stmt->fetch();
    
    if (!$passage) {
        echo "<div class='page'>ไม่พบเรื่องสั้น</div>";
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM reading_questions WHERE passage_id = ?");
    $stmt->execute([$passageId]);
    $questions = $stmt->fetchAll();
    ?>
    <div class="animate-fade-in">
        <div class="flex justify-between items-center" style="margin-bottom:20px;">
            <div class="flex items-center gap-12">
                <a href="?page=admin&sub=reading" class="btn-ghost">← กลับไปเรื่องสั้น</a>
                <h1 style="margin:0;">📝 คำถาม: <?= sanitize($passage['title']) ?></h1>
            </div>
            <button class="btn btn-primary" onclick="openRQModal()">+ เพิ่มคำถาม</button>
        </div>

        <div class="card table-wrap" style="padding:0;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>คำถาม</th>
                        <th>ตัวเลือกที่ถูก</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $q): ?>
                    <tr>
                        <td><strong><?= sanitize($q['question_text']) ?></strong></td>
                        <td style="color:var(--success);font-weight:700;">
                            <?php
                            $ansIndex = $q['correct_answer'];
                            $choices = [$q['choice_a'], $q['choice_b'], $q['choice_c'], $q['choice_d']];
                            echo sanitize($choices[$ansIndex] ?? '-');
                            ?>
                        </td>
                        <td>
                            <button class="btn-ghost" onclick='editRQ(<?= json_encode($q, JSON_HEX_APOS) ?>)'><span class="material-symbols-outlined" style="font-size:18px;">edit</span></button>
                            <button class="btn-ghost" style="color:var(--danger);" onclick="deleteRQ(<?= $q['id'] ?>)"><span class="material-symbols-outlined" style="font-size:18px;">delete</span></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($questions)): ?>
                    <tr><td colspan="3" class="text-center" style="padding:20px;">ยังไม่มีคำถาม</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Reading Question -->
    <div id="rqModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width:700px;">
            <div class="modal-handle"></div>
            <h2 id="rqModalTitle" style="margin-bottom:16px;">เพิ่มคำถาม</h2>
            <form id="rqForm" onsubmit="saveRQ(event)">
                <input type="hidden" id="rq_id" name="id" value="0">
                <input type="hidden" id="rq_passage_id" value="<?= $passageId ?>">
                
                <div class="input-group" style="margin-bottom:16px;">
                    <label>คำถาม</label>
                    <input type="text" id="rq_text" class="input-field" required>
                </div>

                <div class="flex gap-12" style="margin-bottom:12px;">
                    <div class="input-group w-full">
                        <label>ตัวเลือก A</label>
                        <input type="text" id="rq_ca" class="input-field" required>
                    </div>
                    <div class="input-group w-full">
                        <label>ตัวเลือก B</label>
                        <input type="text" id="rq_cb" class="input-field">
                    </div>
                </div>
                
                <div class="flex gap-12" style="margin-bottom:12px;">
                    <div class="input-group w-full">
                        <label>ตัวเลือก C</label>
                        <input type="text" id="rq_cc" class="input-field">
                    </div>
                    <div class="input-group w-full">
                        <label>ตัวเลือก D</label>
                        <input type="text" id="rq_cd" class="input-field">
                    </div>
                </div>

                <div class="input-group" style="margin-bottom:20px;">
                    <label>เฉลยที่ถูกต้อง</label>
                    <select id="rq_correct" class="input-field">
                        <option value="0">A</option>
                        <option value="1">B</option>
                        <option value="2">C</option>
                        <option value="3">D</option>
                    </select>
                </div>

                <div class="flex justify-between gap-12">
                    <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('rqModal')">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary" style="flex:1;">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="<?= SITE_URL ?>/assets/js/app.js"></script>
    <script>
    function openRQModal() {
        document.getElementById('rqModalTitle').textContent = 'เพิ่มคำถาม';
        document.getElementById('rq_id').value = '0';
        document.getElementById('rq_text').value = '';
        document.getElementById('rq_ca').value = '';
        document.getElementById('rq_cb').value = '';
        document.getElementById('rq_cc').value = '';
        document.getElementById('rq_cd').value = '';
        document.getElementById('rq_correct').value = '0';
        document.getElementById('rqModal').classList.remove('hidden');
    }

    function editRQ(data) {
        document.getElementById('rqModalTitle').textContent = 'แก้ไขคำถาม';
        document.getElementById('rq_id').value = data.id;
        document.getElementById('rq_text').value = data.question_text;
        document.getElementById('rq_ca').value = data.choice_a;
        document.getElementById('rq_cb').value = data.choice_b || '';
        document.getElementById('rq_cc').value = data.choice_c || '';
        document.getElementById('rq_cd').value = data.choice_d || '';
        document.getElementById('rq_correct').value = data.correct_answer;
        document.getElementById('rqModal').classList.remove('hidden');
    }

    function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

    async function saveRQ(e) {
        e.preventDefault();
        const payload = {
            action: 'save_reading_question',
            id: document.getElementById('rq_id').value,
            passage_id: document.getElementById('rq_passage_id').value,
            question_text: document.getElementById('rq_text').value,
            choice_a: document.getElementById('rq_ca').value,
            choice_b: document.getElementById('rq_cb').value,
            choice_c: document.getElementById('rq_cc').value,
            choice_d: document.getElementById('rq_cd').value,
            correct_answer: document.getElementById('rq_correct').value
        };
        const res = await apiCall('admin.php', payload);
        if (res && res.success) window.location.reload();
    }

    async function deleteRQ(id) {
        if (confirm('ยืนยันการลบคำถามนี้?')) {
            const res = await apiCall('admin.php', { action: 'delete_reading_question', id: id });
            if (res && res.success) window.location.reload();
        }
    }
    </script>
    <?php
} else {
    // Show Passages List
    $stmt = $db->query("
        SELECT p.*,
        (SELECT COUNT(*) FROM reading_questions WHERE passage_id = p.id) as q_count
        FROM reading_passages p 
        ORDER BY p.level, p.id DESC
    ");
    $passages = $stmt->fetchAll();
    ?>
    <div class="animate-fade-in">
        <div class="flex justify-between items-center" style="margin-bottom:20px;">
            <div class="flex items-center gap-12">
                <a href="?page=admin&sub=content" class="btn-ghost">← กลับ</a>
                <h1 style="margin:0;">📚 จัดการเรื่องสั้น</h1>
            </div>
            <button class="btn btn-primary" onclick="openReadingModal()">+ เพิ่มเรื่องสั้น</button>
        </div>

        <div class="card table-wrap" style="padding:0;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>เรื่องสั้น</th>
                        <th>ระดับ</th>
                        <th>คำถามท้ายบท</th>
                        <th>จัดการคำถาม</th>
                        <th>จัดการเรื่อง</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($passages as $p): ?>
                    <tr>
                        <td style="font-weight:700;"><?= sanitize($p['title']) ?></td>
                        <td><span class="badge badge-<?= $p['level'] === 'beginner' ? 'success' : ($p['level'] === 'intermediate' ? 'accent' : 'primary') ?>"><?= $p['level'] ?></span></td>
                        <td><?= $p['q_count'] ?> ข้อ</td>
                        <td>
                            <a href="?page=admin&sub=reading&passage_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline"><span class="material-symbols-outlined" style="font-size:18px;">edit</span> คำถาม</a>
                        </td>
                        <td>
                            <button class="btn-ghost" onclick='editReading(<?= json_encode($p, JSON_HEX_APOS) ?>)'><span class="material-symbols-outlined" style="font-size:18px;">edit</span></button>
                            <button class="btn-ghost" style="color:var(--danger);" onclick="deleteReading(<?= $p['id'] ?>)"><span class="material-symbols-outlined" style="font-size:18px;">delete</span></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Reading Form -->
    <div id="readingModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width:800px;">
            <div class="modal-handle"></div>
            <h2 id="rModalTitle" style="margin-bottom:16px;">เพิ่มเรื่องสั้น</h2>
            <form id="readingForm" onsubmit="saveReading(event)">
                <input type="hidden" id="r_id" name="id" value="0">
                
                <div class="flex gap-12" style="margin-bottom:12px;">
                    <div class="input-group w-full">
                        <label>ชื่อเรื่อง (Title)</label>
                        <input type="text" id="r_title" class="input-field" required>
                    </div>
                    <div class="input-group" style="width:150px;">
                        <label>ระดับ</label>
                        <select id="r_level" class="input-field">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                </div>

                <div class="input-group" style="margin-bottom:20px;">
                    <label>เนื้อเรื่อง (รองรับ HTML)</label>
                    <textarea id="r_content" class="input-field" rows="8" required placeholder="<p>กาลครั้งหนึ่งนานมาแล้ว...</p>"></textarea>
                </div>

                <div class="flex justify-between gap-12">
                    <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('readingModal')">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary" style="flex:1;">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <script src="<?= SITE_URL ?>/assets/js/app.js"></script>
    <script>
    function openReadingModal() {
        document.getElementById('rModalTitle').textContent = 'เพิ่มเรื่องสั้น';
        document.getElementById('r_id').value = '0';
        document.getElementById('r_title').value = '';
        document.getElementById('r_level').value = 'beginner';
        document.getElementById('r_content').value = '';
        document.getElementById('readingModal').classList.remove('hidden');
    }

    function editReading(data) {
        document.getElementById('rModalTitle').textContent = 'แก้ไขเรื่องสั้น';
        document.getElementById('r_id').value = data.id;
        document.getElementById('r_title').value = data.title;
        document.getElementById('r_level').value = data.level;
        document.getElementById('r_content').value = data.content;
        document.getElementById('readingModal').classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    async function saveReading(e) {
        e.preventDefault();
        const payload = {
            action: 'save_passage',
            id: document.getElementById('r_id').value,
            title: document.getElementById('r_title').value,
            level: document.getElementById('r_level').value,
            content: document.getElementById('r_content').value
        };
        const res = await apiCall('admin.php', payload);
        if (res && res.success) window.location.reload();
    }

    async function deleteReading(id) {
        if (confirm('ยืนยันการลบเรื่องสั้นนี้? คำถามท้ายเรื่องจะถูกลบไปด้วย')) {
            const res = await apiCall('admin.php', { action: 'delete_passage', id: id });
            if (res && res.success) window.location.reload();
        }
    }
    </script>
    <?php
}
include __DIR__ . '/../../includes/footer.php';
?>
