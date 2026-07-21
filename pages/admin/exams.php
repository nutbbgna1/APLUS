<?php
include __DIR__ . '/../../includes/header.php';
$db = getDB();

$examId = intval($_GET['exam_id'] ?? 0);
$lessons = $db->query("SELECT id, title FROM lessons ORDER BY sort_order")->fetchAll();

if ($examId > 0) {
    // Show Questions for specific exam
    $stmt = $db->prepare("SELECT * FROM exams WHERE id = ?");
    $stmt->execute([$examId]);
    $exam = $stmt->fetch();
    
    if (!$exam) {
        echo "<div class='page'>ไม่พบข้อสอบ</div>";
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM questions WHERE exam_id = ?");
    $stmt->execute([$examId]);
    $questions = $stmt->fetchAll();
    ?>
    
    <div class="animate-fade-in">
        <div class="flex justify-between items-center" style="margin-bottom:20px;">
            <div class="flex items-center gap-12">
                <a href="?page=admin&sub=exams" class="btn-ghost">← กลับไปชุดข้อสอบ</a>
                <h1 style="margin:0;">📝 คำถาม: <?= sanitize($exam['title']) ?></h1>
            </div>
            <button class="btn btn-primary" onclick="openQuestionModal()">+ เพิ่มคำถาม</button>
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
                            $choices = [$q['choice_a'], $q['choice_b'], $q['choice_c'], $q['choice_d'], $q['choice_e']];
                            echo sanitize($choices[$ansIndex] ?? '-');
                            ?>
                        </td>
                        <td>
                            <button class="btn-ghost" onclick='editQuestion(<?= json_encode($q, JSON_HEX_APOS) ?>)'><span class="material-symbols-outlined" style="font-size:18px;">edit</span></button>
                            <button class="btn-ghost" style="color:var(--danger);" onclick="deleteQuestion(<?= $q['id'] ?>)"><span class="material-symbols-outlined" style="font-size:18px;">delete</span></button>
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

    <!-- Modal: Question -->
    <div id="questionModal" class="modal-overlay hidden">
        <div class="modal-content" style="max-width:700px;">
            <div class="modal-handle"></div>
            <h2 id="qModalTitle" style="margin-bottom:16px;">เพิ่มคำถาม</h2>
            <form id="questionForm" onsubmit="saveQuestion(event)">
                <input type="hidden" id="q_id" name="id" value="0">
                <input type="hidden" id="q_exam_id" value="<?= $examId ?>">
                
                <div class="input-group" style="margin-bottom:16px;">
                    <label>คำถาม</label>
                    <input type="text" id="q_text" class="input-field" required>
                </div>

                <div class="flex gap-12" style="margin-bottom:12px;">
                    <div class="input-group w-full">
                        <label>ตัวเลือก A</label>
                        <input type="text" id="q_ca" class="input-field" required>
                    </div>
                    <div class="input-group w-full">
                        <label>ตัวเลือก B</label>
                        <input type="text" id="q_cb" class="input-field">
                    </div>
                </div>
                
                <div class="flex gap-12" style="margin-bottom:12px;">
                    <div class="input-group w-full">
                        <label>ตัวเลือก C</label>
                        <input type="text" id="q_cc" class="input-field">
                    </div>
                    <div class="input-group w-full">
                        <label>ตัวเลือก D</label>
                        <input type="text" id="q_cd" class="input-field">
                    </div>
                </div>

                <div class="flex gap-12" style="margin-bottom:20px;">
                    <div class="input-group w-full">
                        <label>เฉลยที่ถูกต้อง</label>
                        <select id="q_correct" class="input-field">
                            <option value="0">A</option>
                            <option value="1">B</option>
                            <option value="2">C</option>
                            <option value="3">D</option>
                        </select>
                    </div>
                    <div class="input-group w-full">
                        <label>ระดับ (ดึงมาจากข้อสอบได้)</label>
                        <select id="q_level" class="input-field">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-between gap-12">
                    <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('questionModal')">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary" style="flex:1;">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="<?= SITE_URL ?>/assets/js/app.js"></script>
    <script>
    function openQuestionModal() {
        document.getElementById('qModalTitle').textContent = 'เพิ่มคำถาม';
        document.getElementById('q_id').value = '0';
        document.getElementById('q_text').value = '';
        document.getElementById('q_ca').value = '';
        document.getElementById('q_cb').value = '';
        document.getElementById('q_cc').value = '';
        document.getElementById('q_cd').value = '';
        document.getElementById('q_correct').value = '0';
        document.getElementById('q_level').value = 'beginner';
        document.getElementById('questionModal').classList.remove('hidden');
    }

    function editQuestion(data) {
        document.getElementById('qModalTitle').textContent = 'แก้ไขคำถาม';
        document.getElementById('q_id').value = data.id;
        document.getElementById('q_text').value = data.question_text;
        document.getElementById('q_ca').value = data.choice_a;
        document.getElementById('q_cb').value = data.choice_b || '';
        document.getElementById('q_cc').value = data.choice_c || '';
        document.getElementById('q_cd').value = data.choice_d || '';
        document.getElementById('q_correct').value = data.correct_answer;
        document.getElementById('q_level').value = data.level;
        document.getElementById('questionModal').classList.remove('hidden');
    }

    function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

    async function saveQuestion(e) {
        e.preventDefault();
        const payload = {
            action: 'save_question',
            id: document.getElementById('q_id').value,
            exam_id: document.getElementById('q_exam_id').value,
            question_text: document.getElementById('q_text').value,
            choice_a: document.getElementById('q_ca').value,
            choice_b: document.getElementById('q_cb').value,
            choice_c: document.getElementById('q_cc').value,
            choice_d: document.getElementById('q_cd').value,
            correct_answer: document.getElementById('q_correct').value,
            level: document.getElementById('q_level').value
        };
        const res = await apiCall('admin.php', payload);
        if (res && res.success) window.location.reload();
    }

    async function deleteQuestion(id) {
        if (confirm('ยืนยันการลบคำถามนี้?')) {
            const res = await apiCall('admin.php', { action: 'delete_question', id: id });
            if (res && res.success) window.location.reload();
        }
    }
    </script>
    <?php
} else {
    // Show Exams List
    $stmt = $db->query("
        SELECT e.*, l.title as lesson_title,
        (SELECT COUNT(*) FROM questions WHERE exam_id = e.id) as q_count
        FROM exams e
        LEFT JOIN lessons l ON e.lesson_id = l.id
        ORDER BY e.level, e.id
    ");
    $exams = $stmt->fetchAll();
    ?>
    <div class="animate-fade-in">
        <div class="flex justify-between items-center" style="margin-bottom:20px;">
            <div class="flex items-center gap-12">
                <a href="?page=admin&sub=content" class="btn-ghost">← กลับ</a>
                <h1 style="margin:0;">📝 จัดการชุดข้อสอบ</h1>
            </div>
            <button class="btn btn-primary" onclick="openExamModal()">+ เพิ่มชุดข้อสอบ</button>
        </div>

        <div class="card table-wrap" style="padding:0;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ชื่อข้อสอบ</th>
                        <th>บทเรียน</th>
                        <th>ระดับ</th>
                        <th>จำนวนข้อ</th>
                        <th>เวลา (นาที)</th>
                        <th>จัดการคำถาม</th>
                        <th>ตั้งค่า</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($exams as $e): ?>
                    <tr>
                        <td style="font-weight:700;"><?= sanitize($e['title']) ?></td>
                        <td style="font-size:0.8rem;"><?= $e['lesson_title'] ? sanitize($e['lesson_title']) : '-' ?></td>
                        <td><span class="badge badge-<?= $e['level'] === 'beginner' ? 'success' : ($e['level'] === 'intermediate' ? 'accent' : 'primary') ?>"><?= $e['level'] ?></span></td>
                        <td><?= $e['q_count'] ?> / <?= $e['total_questions'] ?></td>
                        <td><?= $e['time_minutes'] ?></td>
                        <td>
                            <a href="?page=admin&sub=exams&exam_id=<?= $e['id'] ?>" class="btn btn-sm btn-outline"><span class="material-symbols-outlined" style="font-size:18px;">edit</span> คำถาม</a>
                        </td>
                        <td>
                            <button class="btn-ghost" onclick='editExam(<?= json_encode($e, JSON_HEX_APOS) ?>)'><span class="material-symbols-outlined" style="font-size:18px;">edit</span></button>
                            <button class="btn-ghost" style="color:var(--danger);" onclick="deleteExam(<?= $e['id'] ?>)"><span class="material-symbols-outlined" style="font-size:18px;">delete</span></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Exam -->
    <div id="examModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-handle"></div>
            <h2 id="eModalTitle" style="margin-bottom:16px;">เพิ่มชุดข้อสอบ</h2>
            <form id="examForm" onsubmit="saveExam(event)">
                <input type="hidden" id="e_id" name="id" value="0">
                
                <div class="input-group" style="margin-bottom:12px;">
                    <label>ชื่อชุดข้อสอบ</label>
                    <input type="text" id="e_title" class="input-field" required>
                </div>

                <div class="flex gap-12" style="margin-bottom:12px;">
                    <div class="input-group w-full">
                        <label>ระดับ</label>
                        <select id="e_level" class="input-field">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                    <div class="input-group w-full">
                        <label>บทเรียน (ถ้ามี)</label>
                        <select id="e_lesson" class="input-field">
                            <option value="">-- ไม่ระบุ --</option>
                            <?php foreach ($lessons as $l): ?>
                            <option value="<?= $l['id'] ?>"><?= sanitize($l['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="flex gap-12" style="margin-bottom:20px;">
                    <div class="input-group w-full">
                        <label>เวลาที่ให้ทำ (นาที)</label>
                        <input type="number" id="e_time" class="input-field" value="10" required>
                    </div>
                    <div class="input-group w-full">
                        <label>สุ่มคำถามมาออกกี่ข้อ</label>
                        <input type="number" id="e_total" class="input-field" value="10" required>
                    </div>
                </div>

                <div class="flex justify-between gap-12">
                    <button type="button" class="btn btn-outline" style="flex:1;" onclick="closeModal('examModal')">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary" style="flex:1;">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="<?= SITE_URL ?>/assets/js/app.js"></script>
    <script>
    function openExamModal() {
        document.getElementById('eModalTitle').textContent = 'เพิ่มชุดข้อสอบ';
        document.getElementById('e_id').value = '0';
        document.getElementById('e_title').value = '';
        document.getElementById('e_level').value = 'beginner';
        document.getElementById('e_lesson').value = '';
        document.getElementById('e_time').value = '10';
        document.getElementById('e_total').value = '10';
        document.getElementById('examModal').classList.remove('hidden');
    }

    function editExam(data) {
        document.getElementById('eModalTitle').textContent = 'แก้ไขชุดข้อสอบ';
        document.getElementById('e_id').value = data.id;
        document.getElementById('e_title').value = data.title;
        document.getElementById('e_level').value = data.level;
        document.getElementById('e_lesson').value = data.lesson_id || '';
        document.getElementById('e_time').value = data.time_minutes;
        document.getElementById('e_total').value = data.total_questions;
        document.getElementById('examModal').classList.remove('hidden');
    }

    function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

    async function saveExam(e) {
        e.preventDefault();
        const payload = {
            action: 'save_exam',
            id: document.getElementById('e_id').value,
            title: document.getElementById('e_title').value,
            level: document.getElementById('e_level').value,
            lesson_id: document.getElementById('e_lesson').value,
            time_minutes: document.getElementById('e_time').value,
            total_questions: document.getElementById('e_total').value
        };
        const res = await apiCall('admin.php', payload);
        if (res && res.success) window.location.reload();
    }

    async function deleteExam(id) {
        if (confirm('ยืนยันการลบชุดข้อสอบ? คำถามทั้งหมดในนี้จะถูกลบไปด้วย')) {
            const res = await apiCall('admin.php', { action: 'delete_exam', id: id });
            if (res && res.success) window.location.reload();
        }
    }
    </script>
    <?php
}
include __DIR__ . '/../../includes/footer.php';
?>
