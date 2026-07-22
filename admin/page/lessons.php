<?php
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$errorMsg = '';

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete'])) {
            $del_id = $_POST['id'];
            $stmt = $db->prepare("DELETE FROM lessons WHERE id = ?");
            $stmt->execute([$del_id]);
            echo "<script>window.location.href='?page=lessons';</script>";
            exit;
        } else {
            $title = trim($_POST['title'] ?? '');
            $subject = $_POST['subject'] ?? 'อังกฤษ';
            $level = $_POST['level'] ?? 'beginner';
            $description = trim($_POST['description'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $sort_order = (int)($_POST['sort_order'] ?? 0);
            
            if ($action === 'edit' && $id) {
                $stmt = $db->prepare("UPDATE lessons SET title=?, subject=?, level=?, description=?, content=?, sort_order=? WHERE id=?");
                $stmt->execute([$title, $subject, $level, $description, $content, $sort_order, $id]);
            } else {
                $stmt = $db->prepare("INSERT INTO lessons (title, subject, level, description, content, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $subject, $level, $description, $content, $sort_order]);
            }
            echo "<script>window.location.href='?page=lessons';</script>";
            exit;
        }
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

// Fetch categories for dropdown
$stmt = $db->query("SELECT name FROM course_categories ORDER BY id ASC");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (empty($categories)) $categories = ['อังกฤษ'];

if ($action === 'edit' || $action === 'add'):
    $lesson = null;
    if ($action === 'edit' && $id) {
        $stmt = $db->prepare("SELECT * FROM lessons WHERE id = ?");
        $stmt->execute([$id]);
        $lesson = $stmt->fetch();
    }
?>
    <div class="page-header">
        <div>
            <h1 class="page-title"><?= $lesson ? 'Edit Lesson' : 'Add New Lesson' ?></h1>
        </div>
        <a href="?page=lessons" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>

    <?php if ($errorMsg): ?>
    <div style="background: #FEE2E2; color: #DC2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
        Error: <?= htmlspecialchars($errorMsg) ?>
    </div>
    <?php endif; ?>

    <div class="card" style="max-width: 800px;">
        <form method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            <div>
                <label style="display:block; font-weight: 600; margin-bottom: 8px;">Lesson Title</label>
                <input type="text" name="title" required value="<?= htmlspecialchars($lesson['title'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Subject (วิชา)</label>
                    <select name="subject" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                        <?php foreach($categories as $c): ?>
                        <option value="<?= htmlspecialchars($c) ?>" <?= ($lesson['subject'] ?? '') === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Level</label>
                    <select name="level" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                        <option value="beginner" <?= ($lesson['level'] ?? '') === 'beginner' ? 'selected' : '' ?>>Beginner</option>
                        <option value="intermediate" <?= ($lesson['level'] ?? '') === 'intermediate' ? 'selected' : '' ?>>Intermediate</option>
                        <option value="advanced" <?= ($lesson['level'] ?? '') === 'advanced' ? 'selected' : '' ?>>Advanced</option>
                    </select>
                </div>
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Sort Order</label>
                    <input type="number" name="sort_order" value="<?= htmlspecialchars($lesson['sort_order'] ?? 0) ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
            </div>

            <div>
                <label style="display:block; font-weight: 600; margin-bottom: 8px;">Description</label>
                <textarea name="description" rows="3" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;"><?= htmlspecialchars($lesson['description'] ?? '') ?></textarea>
            </div>
            
            <div>
                <label style="display:block; font-weight: 600; margin-bottom: 8px;">Content (HTML supported)</label>
                <textarea name="content" rows="10" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-family: monospace;"><?= htmlspecialchars($lesson['content'] ?? '') ?></textarea>
            </div>
            
            <div style="margin-top: 10px; border-top: 1px solid var(--border); padding-top: 20px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Lesson</button>
            </div>
        </form>
    </div>

<?php else: 
    $stmt = $db->query("SELECT * FROM lessons ORDER BY subject, sort_order ASC, id DESC");
    $lessons = $stmt->fetchAll();
?>
    <div class="page-header">
        <div>
            <h1 class="page-title">Lessons Manager</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Manage learning materials and text content</p>
        </div>
        <a href="?page=lessons&action=add" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add New Lesson</a>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Subject</th>
                    <th>Level</th>
                    <th>Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($lessons as $item): ?>
                <tr>
                    <td>#<?= $item['id'] ?></td>
                    <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                    <td><span style="background: #E0E7FF; color: #4338CA; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;"><?= htmlspecialchars($item['subject']) ?></span></td>
                    <td><?= ucfirst($item['level']) ?></td>
                    <td><?= $item['sort_order'] ?></td>
                    <td>
                        <a href="?page=lessons&action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline" style="margin-right: 5px;"><i class="fa-solid fa-pen"></i></a>
                        <form method="POST" onsubmit="return confirm('Delete this lesson?');" style="display:inline;">
                            <input type="hidden" name="delete" value="1">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-sm" style="background:#EF4444; color:white; border:none; padding:6px 12px; cursor:pointer; border-radius:8px;"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($lessons)): ?>
                <tr><td colspan="6" style="text-align: center; padding: 20px;">No lessons found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
