<?php
$action = $_GET['action'] ?? 'list';
$game_type = $_GET['game_type'] ?? 'sentences'; // sentences, blanks
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$errorMsg = '';

// Fetch categories for dropdown
$stmt = $db->query("SELECT name FROM course_categories ORDER BY id ASC");
$subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (empty($subjects)) $subjects = ['อังกฤษ'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete'])) {
            $del_id = (int)$_POST['id'];
            if ($game_type === 'sentences') {
                $stmt = $db->prepare("DELETE FROM game_sentences WHERE id = ?");
            } else {
                $stmt = $db->prepare("DELETE FROM game_fill_blanks WHERE id = ?");
            }
            $stmt->execute([$del_id]);
            echo "<script>window.location.href='?page=minigames&game_type=$game_type';</script>";
            exit;
        } else {
            if ($game_type === 'sentences') {
                $sentence_en = trim($_POST['sentence_en'] ?? '');
                $sentence_th = trim($_POST['sentence_th'] ?? '');
                $subject = $_POST['subject'] ?? 'อังกฤษ';
                
                if ($action === 'edit' && $id) {
                    $stmt = $db->prepare("UPDATE game_sentences SET sentence_en=?, sentence_th=?, subject=? WHERE id=?");
                    $stmt->execute([$sentence_en, $sentence_th, $subject, $id]);
                } else {
                    $stmt = $db->prepare("INSERT INTO game_sentences (sentence_en, sentence_th, subject) VALUES (?, ?, ?)");
                    $stmt->execute([$sentence_en, $sentence_th, $subject]);
                }
            } else {
                $question_text = trim($_POST['question_text'] ?? '');
                $correct_answer = trim($_POST['correct_answer'] ?? '');
                $choice_1 = trim($_POST['choice_1'] ?? '');
                $choice_2 = trim($_POST['choice_2'] ?? '');
                $choice_3 = trim($_POST['choice_3'] ?? '');
                $choice_4 = trim($_POST['choice_4'] ?? '');
                $subject = $_POST['subject'] ?? 'อังกฤษ';
                
                if ($action === 'edit' && $id) {
                    $stmt = $db->prepare("UPDATE game_fill_blanks SET question_text=?, correct_answer=?, choice_1=?, choice_2=?, choice_3=?, choice_4=?, subject=? WHERE id=?");
                    $stmt->execute([$question_text, $correct_answer, $choice_1, $choice_2, $choice_3, $choice_4, $subject, $id]);
                } else {
                    $stmt = $db->prepare("INSERT INTO game_fill_blanks (question_text, correct_answer, choice_1, choice_2, choice_3, choice_4, subject) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$question_text, $correct_answer, $choice_1, $choice_2, $choice_3, $choice_4, $subject]);
                }
            }
            echo "<script>window.location.href='?page=minigames&game_type=$game_type';</script>";
            exit;
        }
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

if ($action === 'edit' || $action === 'add'):
    $item = null;
    if ($action === 'edit' && $id) {
        if ($game_type === 'sentences') {
            $stmt = $db->prepare("SELECT * FROM game_sentences WHERE id = ?");
        } else {
            $stmt = $db->prepare("SELECT * FROM game_fill_blanks WHERE id = ?");
        }
        $stmt->execute([$id]);
        $item = $stmt->fetch();
    }
?>
    <div class="page-header">
        <div>
            <h1 class="page-title"><?= $item ? 'Edit Game Item' : 'Add New Game Item' ?></h1>
        </div>
        <a href="?page=minigames&game_type=<?= $game_type ?>" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>

    <?php if ($errorMsg): ?>
    <div style="background: #FEE2E2; color: #DC2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
        Error: <?= htmlspecialchars($errorMsg) ?>
    </div>
    <?php endif; ?>

    <div class="card" style="max-width: 800px;">
        <form method="POST" style="display: flex; flex-direction: column; gap: 20px;">
            
            <?php if ($game_type === 'sentences'): ?>
                <!-- Sentences Form -->
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Sentence (English)</label>
                    <input type="text" name="sentence_en" required value="<?= htmlspecialchars($item['sentence_en'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 5px;">* The game will automatically split this sentence into scrambled words.</div>
                </div>
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Translation (Thai)</label>
                    <input type="text" name="sentence_th" required value="<?= htmlspecialchars($item['sentence_th'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
            <?php else: ?>
                <!-- Fill in the blanks Form -->
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Question Text (Use "___" for blank)</label>
                    <input type="text" name="question_text" required placeholder="Ex. The cat is ___ the table." value="<?= htmlspecialchars($item['question_text'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div>
                    <label style="display:block; font-weight: 600; margin-bottom: 8px;">Correct Answer</label>
                    <input type="text" name="correct_answer" required value="<?= htmlspecialchars($item['correct_answer'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label style="display:block; font-weight: 600; margin-bottom: 8px;">Choice 1</label>
                        <input type="text" name="choice_1" required value="<?= htmlspecialchars($item['choice_1'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display:block; font-weight: 600; margin-bottom: 8px;">Choice 2</label>
                        <input type="text" name="choice_2" required value="<?= htmlspecialchars($item['choice_2'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display:block; font-weight: 600; margin-bottom: 8px;">Choice 3</label>
                        <input type="text" name="choice_3" required value="<?= htmlspecialchars($item['choice_3'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                    </div>
                    <div>
                        <label style="display:block; font-weight: 600; margin-bottom: 8px;">Choice 4</label>
                        <input type="text" name="choice_4" required value="<?= htmlspecialchars($item['choice_4'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                    </div>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">* One of the choices MUST exactly match the Correct Answer.</div>
            <?php endif; ?>

            <div>
                <label style="display:block; font-weight: 600; margin-bottom: 8px;">Subject</label>
                <select name="subject" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                    <?php foreach($subjects as $c): ?>
                    <option value="<?= htmlspecialchars($c) ?>" <?= ($item['subject'] ?? 'อังกฤษ') === $c ? 'selected' : '' ?>><?= htmlspecialchars($c) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="margin-top: 10px; border-top: 1px solid var(--border); padding-top: 20px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save</button>
            </div>
        </form>
    </div>

<?php else: 
    if ($game_type === 'sentences') {
        $stmt = $db->query("SELECT * FROM game_sentences ORDER BY id DESC");
    } else {
        $stmt = $db->query("SELECT * FROM game_fill_blanks ORDER BY id DESC");
    }
    $items = $stmt->fetchAll();
?>
    <div class="page-header">
        <div>
            <h1 class="page-title">Mini Games Manager</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Manage game content and questions</p>
        </div>
        <a href="?page=minigames&game_type=<?= $game_type ?>&action=add" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add New Item</a>
    </div>

    <div style="margin-bottom: 20px; display: flex; gap: 10px;">
        <a href="?page=minigames&game_type=sentences" class="btn <?= $game_type === 'sentences' ? 'btn-primary' : 'btn-outline' ?>">Sentence Building (เรียงประโยค)</a>
        <a href="?page=minigames&game_type=blanks" class="btn <?= $game_type === 'blanks' ? 'btn-primary' : 'btn-outline' ?>">Fill in the Blanks (เติมคำในช่องว่าง)</a>
    </div>

    <div class="card">
        <table>
            <thead>
                <?php if ($game_type === 'sentences'): ?>
                <tr>
                    <th>Sentence (EN)</th>
                    <th>Translation (TH)</th>
                    <th>Subject</th>
                    <th>Actions</th>
                </tr>
                <?php else: ?>
                <tr>
                    <th>Question</th>
                    <th>Answer</th>
                    <th>Choices</th>
                    <th>Subject</th>
                    <th>Actions</th>
                </tr>
                <?php endif; ?>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                <tr>
                    <?php if ($game_type === 'sentences'): ?>
                        <td><strong><?= htmlspecialchars($item['sentence_en']) ?></strong></td>
                        <td><?= htmlspecialchars($item['sentence_th']) ?></td>
                        <td><span style="background: #E0E7FF; color: #4338CA; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;"><?= htmlspecialchars($item['subject']) ?></span></td>
                    <?php else: ?>
                        <td><strong><?= htmlspecialchars($item['question_text']) ?></strong></td>
                        <td><span style="color: var(--secondary); font-weight: 600;"><?= htmlspecialchars($item['correct_answer']) ?></span></td>
                        <td style="font-size: 0.85rem; color: var(--text-muted);">
                            1) <?= htmlspecialchars($item['choice_1']) ?><br>
                            2) <?= htmlspecialchars($item['choice_2']) ?><br>
                            3) <?= htmlspecialchars($item['choice_3']) ?><br>
                            4) <?= htmlspecialchars($item['choice_4']) ?>
                        </td>
                        <td><span style="background: #E0E7FF; color: #4338CA; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600;"><?= htmlspecialchars($item['subject']) ?></span></td>
                    <?php endif; ?>
                    <td>
                        <a href="?page=minigames&game_type=<?= $game_type ?>&action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline" style="margin-right: 5px;"><i class="fa-solid fa-pen"></i></a>
                        <form method="POST" onsubmit="return confirm('Delete this item?');" style="display:inline;">
                            <input type="hidden" name="delete" value="1">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-sm" style="background:#EF4444; color:white; border:none; padding:6px 12px; cursor:pointer; border-radius:8px;"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($items)): ?>
                <tr><td colspan="5" style="text-align: center; padding: 20px;">No game items found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
