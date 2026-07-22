<?php
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete'])) {
            $del_id = $_POST['id'];
            $stmt = $db->prepare("DELETE FROM reading_passages WHERE id = ?");
            $stmt->execute([$del_id]);
            echo "<script>window.location.href='?page=reading';</script>";
            exit;
        } else {
            $title = trim($_POST['title'] ?? '');
            $title_th = trim($_POST['title_th'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $level = $_POST['level'] ?? 'beginner';
            $category = trim($_POST['category'] ?? 'story');
            $subject = $_POST['subject'] ?? 'อังกฤษ';
            $word_count = (int)($_POST['word_count'] ?? 0);
            
            // If word_count is 0, auto-calculate rough word count (spaces)
            if ($word_count === 0 && !empty($content)) {
                $word_count = str_word_count(strip_tags($content));
            }
            
            if ($action === 'edit' && $id) {
                $stmt = $db->prepare("UPDATE reading_passages SET title=?, title_th=?, content=?, level=?, category=?, subject=?, word_count=? WHERE id=?");
                $stmt->execute([$title, $title_th, $content, $level, $category, $subject, $word_count, $id]);
            } else {
                $stmt = $db->prepare("INSERT INTO reading_passages (title, title_th, content, level, category, subject, word_count) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $title_th, $content, $level, $category, $subject, $word_count]);
            }
            echo "<script>window.location.href='?page=reading';</script>";
            exit;
        }
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

$stmt = $db->query("SELECT name FROM course_categories ORDER BY id ASC");
$subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (empty($subjects)) $subjects = ['อังกฤษ'];

if ($action === 'edit' || $action === 'add'):
    $item = null;
    if ($action === 'edit' && $id) {
        $stmt = $db->prepare("SELECT * FROM reading_passages WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
    }
?>
    <div class="page-header">
        <div>
            <h1 class="page-title"><?= $item ? 'Edit Reading Passage' : 'Add New Reading Passage' ?></h1>
        </div>
        <a href="?page=reading" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
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
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Title (English)</label>
                    <input type="text" name="title" required value="<?= htmlspecialchars($item['title'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Title (Thai Translation - Optional)</label>
                    <input type="text" name="title_th" value="<?= htmlspecialchars($item['title_th'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 20px;">
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
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Category (Genre)</label>
                    <input type="text" name="category" value="<?= htmlspecialchars($item['category'] ?? 'story') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Word Count</label>
                    <input type="number" name="word_count" placeholder="0 = Auto" value="<?= htmlspecialchars($item['word_count'] ?? 0) ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
            </div>
            
            <div>
                <label style="display:block; font-weight: 600; margin-bottom: 8px;">Content (Passage Text)</label>
                <textarea name="content" rows="12" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; line-height: 1.6;"><?= htmlspecialchars($item['content'] ?? '') ?></textarea>
            </div>
            
            <div style="margin-top: 10px; border-top: 1px solid var(--border); padding-top: 20px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Reading Passage</button>
            </div>
        </form>
    </div>

<?php else: 
    $stmt = $db->query("SELECT * FROM reading_passages ORDER BY id DESC");
    $items = $stmt->fetchAll();
?>
    <div class="page-header">
        <div>
            <h1 class="page-title">Reading Manager</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Manage articles, stories, and reading comprehension texts</p>
        </div>
        <a href="?page=reading&action=add" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add New Passage</a>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Genre</th>
                    <th>Subject</th>
                    <th>Level</th>
                    <th>Words</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($item['title']) ?></strong>
                        <div style="font-size:0.8rem; color:var(--text-muted);"><?= htmlspecialchars($item['title_th']) ?></div>
                    </td>
                    <td><?= htmlspecialchars($item['category']) ?></td>
                    <td><span style="background: #E0E7FF; color: #4338CA; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;"><?= htmlspecialchars($item['subject']) ?></span></td>
                    <td><?= ucfirst($item['level']) ?></td>
                    <td><?= $item['word_count'] ?> words</td>
                    <td>
                        <a href="?page=reading&action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline" style="margin-right: 5px;"><i class="fa-solid fa-pen"></i></a>
                        <form method="POST" onsubmit="return confirm('Delete this passage?');" style="display:inline;">
                            <input type="hidden" name="delete" value="1">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-sm" style="background:#EF4444; color:white; border:none; padding:6px 12px; cursor:pointer; border-radius:8px;"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($items)): ?>
                <tr><td colspan="6" style="text-align: center; padding: 20px;">No reading passages found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
