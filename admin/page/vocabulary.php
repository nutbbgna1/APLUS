<?php
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete'])) {
            $del_id = $_POST['id'];
            $stmt = $db->prepare("DELETE FROM vocabulary WHERE id = ?");
            $stmt->execute([$del_id]);
            echo "<script>window.location.href='?page=vocabulary';</script>";
            exit;
        } else {
            $word_en = trim($_POST['word_en'] ?? '');
            $word_th = trim($_POST['word_th'] ?? '');
            $pronunciation = trim($_POST['pronunciation'] ?? '');
            $example_sentence = trim($_POST['example_sentence'] ?? '');
            $level = $_POST['level'] ?? 'beginner';
            $category = trim($_POST['category'] ?? 'general');
            $subject = $_POST['subject'] ?? 'อังกฤษ';
            $lesson_id = !empty($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : null;
            
            if ($action === 'edit' && $id) {
                $stmt = $db->prepare("UPDATE vocabulary SET word_en=?, word_th=?, pronunciation=?, example_sentence=?, level=?, category=?, subject=?, lesson_id=? WHERE id=?");
                $stmt->execute([$word_en, $word_th, $pronunciation, $example_sentence, $level, $category, $subject, $lesson_id, $id]);
            } else {
                $stmt = $db->prepare("INSERT INTO vocabulary (word_en, word_th, pronunciation, example_sentence, level, category, subject, lesson_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$word_en, $word_th, $pronunciation, $example_sentence, $level, $category, $subject, $lesson_id]);
            }
            echo "<script>window.location.href='?page=vocabulary';</script>";
            exit;
        }
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

// Fetch categories for dropdown
$stmt = $db->query("SELECT name FROM course_categories ORDER BY id ASC");
$subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (empty($subjects)) $subjects = ['อังกฤษ'];

// Fetch lessons for dropdown
$stmt = $db->query("SELECT id, title FROM lessons ORDER BY id DESC");
$lessonsList = $stmt->fetchAll();

if ($action === 'edit' || $action === 'add'):
    $item = null;
    if ($action === 'edit' && $id) {
        $stmt = $db->prepare("SELECT * FROM vocabulary WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
    }
?>
    <div class="page-header">
        <div>
            <h1 class="page-title"><?= $item ? 'Edit Vocabulary' : 'Add New Vocabulary' ?></h1>
        </div>
        <a href="?page=vocabulary" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>

    <?php if ($errorMsg): ?>
    <div style="background: #FEE2E2; color: #DC2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
        Error: <?= htmlspecialchars($errorMsg) ?>
    </div>
    <?php endif; ?>

    <div class="card" style="max-width: 800px;">
        <form method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Word (English)</label>
                    <input type="text" name="word_en" required value="<?= htmlspecialchars($item['word_en'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Translation (Thai)</label>
                    <input type="text" name="word_th" required value="<?= htmlspecialchars($item['word_th'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Pronunciation (Optional)</label>
                    <input type="text" name="pronunciation" value="<?= htmlspecialchars($item['pronunciation'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Word Category (e.g. Noun, Verb, Animal)</label>
                    <input type="text" name="category" value="<?= htmlspecialchars($item['category'] ?? 'general') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Subject (วิชา)</label>
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

            <div>
                <label style="display:block; font-weight: 600; margin-bottom: 8px;">Example Sentence</label>
                <textarea name="example_sentence" rows="3" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;"><?= htmlspecialchars($item['example_sentence'] ?? '') ?></textarea>
            </div>
            
            <div style="margin-top: 10px; border-top: 1px solid var(--border); padding-top: 20px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Vocabulary</button>
            </div>
        </form>
    </div>

<?php else: 
    $stmt = $db->query("SELECT * FROM vocabulary ORDER BY id DESC");
    $items = $stmt->fetchAll();
?>
    <div class="page-header">
        <div>
            <h1 class="page-title">Vocabulary & Pronunciation</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Manage vocabulary words and phrases</p>
        </div>
        <a href="?page=vocabulary&action=add" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add New Word</a>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Word (EN)</th>
                    <th>Translation (TH)</th>
                    <th>Category</th>
                    <th>Subject</th>
                    <th>Level</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($item['word_en']) ?></strong></td>
                    <td><?= htmlspecialchars($item['word_th']) ?></td>
                    <td><?= htmlspecialchars($item['category']) ?></td>
                    <td><span style="background: #E0E7FF; color: #4338CA; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;"><?= htmlspecialchars($item['subject']) ?></span></td>
                    <td><?= ucfirst($item['level']) ?></td>
                    <td>
                        <a href="?page=vocabulary&action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline" style="margin-right: 5px;"><i class="fa-solid fa-pen"></i></a>
                        <form method="POST" onsubmit="return confirm('Delete this word?');" style="display:inline;">
                            <input type="hidden" name="delete" value="1">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-sm" style="background:#EF4444; color:white; border:none; padding:6px 12px; cursor:pointer; border-radius:8px;"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($items)): ?>
                <tr><td colspan="6" style="text-align: center; padding: 20px;">No vocabulary found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
