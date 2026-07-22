<?php
$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$errorMsg = '';

if (!$exam_id) {
    echo "<script>alert('Invalid Exam ID'); window.location.href='?page=exams';</script>";
    exit;
}

// Fetch exam detail
$stmt = $db->prepare("SELECT * FROM exams WHERE id = ?");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch();

if (!$exam) {
    echo "<script>alert('Exam not found'); window.location.href='?page=exams';</script>";
    exit;
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete'])) {
            $del_id = (int)$_POST['id'];
            $stmt = $db->prepare("DELETE FROM exam_questions WHERE id = ? AND exam_id = ?");
            $stmt->execute([$del_id, $exam_id]);
            echo "<script>window.location.href='?page=exam_questions&exam_id=$exam_id';</script>";
            exit;
        } else {
            $question_text = trim($_POST['question_text'] ?? '');
            $choice_1 = trim($_POST['choice_1'] ?? '');
            $choice_2 = trim($_POST['choice_2'] ?? '');
            $choice_3 = trim($_POST['choice_3'] ?? '');
            $choice_4 = trim($_POST['choice_4'] ?? '');
            $correct_choice_num = (int)($_POST['correct_choice'] ?? 1);
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            
            // Map correct choice number to actual text
            $choices = [1 => $choice_1, 2 => $choice_2, 3 => $choice_3, 4 => $choice_4];
            $correct_answer = $choices[$correct_choice_num] ?? $choice_1;

            if ($action === 'edit' && $id) {
                $stmt = $db->prepare("UPDATE exam_questions SET question_text=?, choice_1=?, choice_2=?, choice_3=?, choice_4=?, correct_answer=?, sort_order=? WHERE id=? AND exam_id=?");
                $stmt->execute([$question_text, $choice_1, $choice_2, $choice_3, $choice_4, $correct_answer, $sort_order, $id, $exam_id]);
            } else {
                $stmt = $db->prepare("INSERT INTO exam_questions (exam_id, question_text, choice_1, choice_2, choice_3, choice_4, correct_answer, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$exam_id, $question_text, $choice_1, $choice_2, $choice_3, $choice_4, $correct_answer, $sort_order]);
            }
            echo "<script>window.location.href='?page=exam_questions&exam_id=$exam_id';</script>";
            exit;
        }
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

if ($action === 'edit' || $action === 'add'):
    $item = null;
    if ($action === 'edit' && $id) {
        $stmt = $db->prepare("SELECT * FROM exam_questions WHERE id = ? AND exam_id = ?");
        $stmt->execute([$id, $exam_id]);
        $item = $stmt->fetch();
    }
?>
    <div class="page-header">
        <div>
            <h1 class="page-title"><?= $item ? 'Edit Question' : 'Add Question' ?></h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Exam: <strong><?= htmlspecialchars($exam['title']) ?></strong> (<?= htmlspecialchars($exam['unit']) ?>)</p>
        </div>
        <a href="?page=exam_questions&exam_id=<?= $exam_id ?>" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to Questions</a>
    </div>

    <?php if ($errorMsg): ?>
    <div style="background: #FEE2E2; color: #DC2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
        Error: <?= htmlspecialchars($errorMsg) ?>
    </div>
    <?php endif; ?>

    <div class="card" style="max-width: 800px;">
        <form method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            <div>
                <label style="display:block; font-weight: 600; margin-bottom: 8px;">Question Text / โจทย์คำถาม</label>
                <textarea name="question_text" rows="3" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-family: inherit;"><?= htmlspecialchars($item['question_text'] ?? '') ?></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Choice 1 / ตัวเลือกที่ 1</label>
                    <input type="text" name="choice_1" required value="<?= htmlspecialchars($item['choice_1'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Choice 2 / ตัวเลือกที่ 2</label>
                    <input type="text" name="choice_2" required value="<?= htmlspecialchars($item['choice_2'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Choice 3 / ตัวเลือกที่ 3</label>
                    <input type="text" name="choice_3" required value="<?= htmlspecialchars($item['choice_3'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Choice 4 / ตัวเลือกที่ 4</label>
                    <input type="text" name="choice_4" required value="<?= htmlspecialchars($item['choice_4'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Correct Answer / คำตอบที่ถูกต้อง</label>
                    <select name="correct_choice" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                        <?php
                            $current_correct = 1;
                            if ($item) {
                                if ($item['correct_answer'] === $item['choice_2']) $current_correct = 2;
                                elseif ($item['correct_answer'] === $item['choice_3']) $current_correct = 3;
                                elseif ($item['correct_answer'] === $item['choice_4']) $current_correct = 4;
                            }
                        ?>
                        <option value="1" <?= $current_correct === 1 ? 'selected' : '' ?>>Choice 1 (ตัวเลือกที่ 1)</option>
                        <option value="2" <?= $current_correct === 2 ? 'selected' : '' ?>>Choice 2 (ตัวเลือกที่ 2)</option>
                        <option value="3" <?= $current_correct === 3 ? 'selected' : '' ?>>Choice 3 (ตัวเลือกที่ 3)</option>
                        <option value="4" <?= $current_correct === 4 ? 'selected' : '' ?>>Choice 4 (ตัวเลือกที่ 4)</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Sort Order / ลำดับ</label>
                    <input type="number" name="sort_order" value="<?= htmlspecialchars($item['sort_order'] ?? 0) ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
            </div>
            
            <div style="margin-top: 10px; border-top: 1px solid var(--border); padding-top: 20px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Question</button>
            </div>
        </form>
    </div>

<?php else: 
    $stmt = $db->prepare("SELECT * FROM exam_questions WHERE exam_id = ? ORDER BY sort_order ASC, id ASC");
    $stmt->execute([$exam_id]);
    $questions = $stmt->fetchAll();
?>
    <div class="page-header">
        <div>
            <h1 class="page-title">Exam Questions</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Managing questions for: <strong><?= htmlspecialchars($exam['title']) ?></strong> (<?= htmlspecialchars($exam['unit']) ?>)</p>
        </div>
        <div>
            <a href="?page=exams" class="btn btn-outline" style="margin-right: 10px;"><i class="fa-solid fa-arrow-left"></i> Back to Exams</a>
            <a href="?page=exam_questions&exam_id=<?= $exam_id ?>&action=add" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Question</a>
        </div>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Question</th>
                    <th>Choices</th>
                    <th>Correct Answer</th>
                    <th style="width: 120px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $idx = 1; foreach($questions as $q): ?>
                <tr>
                    <td><strong>#<?= $idx++ ?></strong></td>
                    <td><strong style="font-size: 1rem;"><?= htmlspecialchars($q['question_text']) ?></strong></td>
                    <td style="font-size: 0.85rem; color: var(--text-muted);">
                        1) <?= htmlspecialchars($q['choice_1']) ?><br>
                        2) <?= htmlspecialchars($q['choice_2']) ?><br>
                        3) <?= htmlspecialchars($q['choice_3']) ?><br>
                        4) <?= htmlspecialchars($q['choice_4']) ?>
                    </td>
                    <td><span style="background: #D1FAE5; color: #065F46; padding: 4px 10px; border-radius: 6px; font-weight: 600; font-size: 0.85rem;"><i class="fa-solid fa-check"></i> <?= htmlspecialchars($q['correct_answer']) ?></span></td>
                    <td>
                        <a href="?page=exam_questions&exam_id=<?= $exam_id ?>&action=edit&id=<?= $q['id'] ?>" class="btn btn-sm btn-outline" style="margin-right: 5px;" title="Edit"><i class="fa-solid fa-pen"></i></a>
                        <form method="POST" onsubmit="return confirm('Delete this question?');" style="display:inline;">
                            <input type="hidden" name="delete" value="1">
                            <input type="hidden" name="id" value="<?= $q['id'] ?>">
                            <button type="submit" class="btn btn-sm" style="background:#EF4444; color:white; border:none; padding:6px 12px; cursor:pointer; border-radius:8px;" title="Delete"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($questions)): ?>
                <tr><td colspan="5" style="text-align: center; padding: 30px; color: var(--text-muted);">No questions added for this exam yet. Click "Add Question" above.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
