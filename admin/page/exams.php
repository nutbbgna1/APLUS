<?php
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$errorMsg = '';

// Fetch categories for dropdown
$stmt = $db->query("SELECT name FROM course_categories ORDER BY id ASC");
$subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (empty($subjects)) $subjects = ['อังกฤษ'];

// Fetch lessons for dropdown
$stmt = $db->query("SELECT id, title FROM lessons ORDER BY id DESC");
$lessonsList = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete'])) {
            $del_id = (int)$_POST['id'];
            $stmt = $db->prepare("DELETE FROM exams WHERE id = ?");
            $stmt->execute([$del_id]);
            echo "<script>window.location.href='?page=exams';</script>";
            exit;
        } else {
            $title = trim($_POST['title'] ?? '');
            $unit = trim($_POST['unit'] ?? 'Unit 1');
            $level = $_POST['level'] ?? 'beginner';
            $subject = $_POST['subject'] ?? 'อังกฤษ';
            $lesson_id = !empty($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : null;
            $time_minutes = (int)($_POST['time_minutes'] ?? 30);
            $total_questions = (int)($_POST['total_questions'] ?? 20);
            
            if ($action === 'edit' && $id) {
                $stmt = $db->prepare("UPDATE exams SET title=?, unit=?, level=?, subject=?, lesson_id=?, time_minutes=?, total_questions=? WHERE id=?");
                $stmt->execute([$title, $unit, $level, $subject, $lesson_id, $time_minutes, $total_questions, $id]);
            } else {
                $stmt = $db->prepare("INSERT INTO exams (title, unit, level, subject, lesson_id, time_minutes, total_questions) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $unit, $level, $subject, $lesson_id, $time_minutes, $total_questions]);
            }
            echo "<script>window.location.href='?page=exams';</script>";
            exit;
        }
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

if ($action === 'edit' || $action === 'add'):
    $item = null;
    if ($action === 'edit' && $id) {
        $stmt = $db->prepare("SELECT * FROM exams WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
    }
?>
    <div class="page-header">
        <div>
            <h1 class="page-title"><?= $item ? 'Edit Exam' : 'Add New Exam' ?></h1>
        </div>
        <a href="?page=exams" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>

    <?php if ($errorMsg): ?>
    <div style="background: #FEE2E2; color: #DC2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
        Error: <?= htmlspecialchars($errorMsg) ?>
    </div>
    <?php endif; ?>

    <div class="card" style="max-width: 800px;">
        <form method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            <div>
                <label style="display:block; font-weight: 600; margin-bottom: 8px;">Exam Title</label>
                <input type="text" name="title" required value="<?= htmlspecialchars($item['title'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Unit / Module</label>
                    <input type="text" name="unit" required value="<?= htmlspecialchars($item['unit'] ?? 'Unit 1') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Link to Lesson (Optional)</label>
                    <select name="lesson_id" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                        <option value="">-- None --</option>
                        <?php foreach($lessonsList as $l): ?>
                        <option value="<?= $l['id'] ?>" <?= ($item['lesson_id'] ?? '') == $l['id'] ? 'selected' : '' ?>>#<?= $l['id'] ?> - <?= htmlspecialchars($l['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Subject</label>
                    <select name="subject" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                        <?php foreach($subjects as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>" <?= ($item['subject'] ?? 'อังกฤษ') === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Level</label>
                    <select name="level" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                        <option value="beginner" <?= ($item['level'] ?? '') === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                        <option value="intermediate" <?= ($item['level'] ?? '') === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                        <option value="advanced" <?= ($item['level'] ?? '') === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Time Limit (Minutes)</label>
                    <input type="number" name="time_minutes" required value="<?= htmlspecialchars($item['time_minutes'] ?? 30) ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Total Questions Displayed</label>
                    <input type="number" name="total_questions" required value="<?= htmlspecialchars($item['total_questions'] ?? 20) ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
            </div>
            
            <div style="margin-top: 10px; border-top: 1px solid var(--border); padding-top: 20px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Exam Setup</button>
            </div>
        </form>
    </div>

<?php else: 
    $stmt = $db->query("SELECT e.*, (SELECT COUNT(*) FROM exam_questions q WHERE q.exam_id = e.id) as q_count FROM exams e ORDER BY e.id DESC");
    $items = $stmt->fetchAll();
?>
    <div class="page-header">
        <div>
            <h1 class="page-title">Exams Manager</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Manage exam setups and tests</p>
        </div>
        <a href="?page=exams&action=add" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add New Exam</a>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Exam Title</th>
                    <th>Unit / Module</th>
                    <th>Subject</th>
                    <th>Level</th>
                    <th>Time Limit</th>
                    <th>Questions Count</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                    <td><?= htmlspecialchars($item['unit']) ?></td>
                    <td><span style="background: #E0E7FF; color: #4338CA; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;"><?= htmlspecialchars($item['subject']) ?></span></td>
                    <td><?= ucfirst($item['level']) ?></td>
                    <td><?= $item['time_minutes'] ?> mins</td>
                    <td>
                        <span style="font-size: 0.85rem; font-weight: 600; color: <?= $item['q_count'] < $item['total_questions'] ? 'var(--danger)' : 'var(--success)' ?>;"><?= $item['q_count'] ?> / <?= $item['total_questions'] ?> items</span>
                    </td>
                    <td>
                        <a href="?page=exam_questions&exam_id=<?= $item['id'] ?>" class="btn btn-sm btn-primary" style="margin-right: 5px;" title="Manage Questions"><i class="fa-solid fa-list-check"></i> Questions</a>
                        <a href="?page=exams&action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline" style="margin-right: 5px;" title="Edit Setup"><i class="fa-solid fa-pen"></i></a>
                        <form method="POST" onsubmit="return confirm('Delete this exam?');" style="display:inline;">
                            <input type="hidden" name="delete" value="1">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-sm" style="background:#EF4444; color:white; border:none; padding:6px 12px; cursor:pointer; border-radius:8px;"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($items)): ?>
                <tr><td colspan="7" style="text-align: center; padding: 20px;">No exams found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
