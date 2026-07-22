<div class="page-header">
    <div>
        <h1 class="page-title">Category Management</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Manage main categories and sub-categories</p>
    </div>
</div>

<?php
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        try {
            if ($action === 'add_cat') {
                $name = trim($_POST['cat_name']);
                if ($name) {
                    $stmt = $db->prepare("INSERT INTO course_categories (name) VALUES (?)");
                    $stmt->execute([$name]);
                }
            } elseif ($action === 'delete_cat') {
                $id = (int)$_POST['id'];
                $stmt = $db->prepare("DELETE FROM course_categories WHERE id = ?");
                $stmt->execute([$id]);
            } elseif ($action === 'add_sub') {
                $cat_id = (int)$_POST['cat_id'];
                $name = trim($_POST['sub_name']);
                if ($name && $cat_id) {
                    $stmt = $db->prepare("INSERT INTO course_subcategories (category_id, name) VALUES (?, ?)");
                    $stmt->execute([$cat_id, $name]);
                }
            } elseif ($action === 'delete_sub') {
                $id = (int)$_POST['id'];
                $stmt = $db->prepare("DELETE FROM course_subcategories WHERE id = ?");
                $stmt->execute([$id]);
            }
        } catch (Exception $e) {
            echo '<div style="background:#FEE2E2; color:#DC2626; padding:12px; margin-bottom:20px; border-radius:8px;">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        
        echo "<script>window.location.href='?page=categories';</script>";
        exit;
    }
}

// Fetch all categories
$stmt = $db->query("SELECT * FROM course_categories ORDER BY id ASC");
$categories = $stmt->fetchAll();

// Fetch all sub-categories
$stmt = $db->query("SELECT * FROM course_subcategories ORDER BY id ASC");
$subcats_raw = $stmt->fetchAll();
$subcats = [];
foreach ($subcats_raw as $s) {
    $subcats[$s['category_id']][] = $s;
}
?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start;">

    <!-- Categories List -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">Main Categories (หมวดหมู่หลัก)</div>
        </div>
        
        <form method="POST" style="display:flex; gap:10px; margin-bottom: 20px;">
            <input type="hidden" name="action" value="add_cat">
            <input type="text" name="cat_name" placeholder="New Category Name" required style="flex:1; padding:10px; border:1px solid var(--border); border-radius:8px; outline:none;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add</button>
        </form>

        <div style="display:flex; flex-direction:column; gap:12px;">
            <?php foreach($categories as $cat): ?>
            <div style="border:1px solid var(--border); border-radius:12px; padding:16px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 12px;">
                    <strong style="font-size: 1.1rem; color: var(--text-main);"><i class="fa-solid fa-folder"></i> <?= htmlspecialchars($cat['name']) ?></strong>
                    <form method="POST" onsubmit="return confirm('Are you sure? This will delete all its sub-categories too.');" style="margin:0;">
                        <input type="hidden" name="action" value="delete_cat">
                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                        <button type="submit" class="btn btn-sm" style="background:#FEE2E2; color:#DC2626;"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </div>
                
                <!-- Sub Categories -->
                <div style="background:#F8FAFC; padding:12px; border-radius:8px;">
                    <div style="font-size:0.8rem; font-weight:700; color:var(--text-muted); margin-bottom:8px;">Sub-Categories:</div>
                    
                    <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom: 12px;">
                        <?php if(isset($subcats[$cat['id']])): ?>
                            <?php foreach($subcats[$cat['id']] as $sub): ?>
                                <div style="background:white; border:1px solid var(--border); padding:4px 10px; border-radius:20px; font-size:0.85rem; font-weight:600; display:flex; align-items:center; gap:6px;">
                                    <?= htmlspecialchars($sub['name']) ?>
                                    <form method="POST" onsubmit="return confirm('Delete this sub-category?');" style="margin:0;">
                                        <input type="hidden" name="action" value="delete_sub">
                                        <input type="hidden" name="id" value="<?= $sub['id'] ?>">
                                        <button type="submit" style="background:none; border:none; color:var(--text-muted); cursor:pointer;"><i class="fa-solid fa-times"></i></button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="font-size:0.85rem; color:var(--text-muted);">ไม่มีหมวดย่อย</div>
                        <?php endif; ?>
                    </div>
                    
                    <form method="POST" style="display:flex; gap:8px;">
                        <input type="hidden" name="action" value="add_sub">
                        <input type="hidden" name="cat_id" value="<?= $cat['id'] ?>">
                        <input type="text" name="sub_name" placeholder="Add sub-category" required style="flex:1; padding:6px 10px; border:1px solid var(--border); border-radius:6px; font-size:0.85rem; outline:none;">
                        <button type="submit" class="btn btn-sm btn-outline"><i class="fa-solid fa-plus"></i></button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-circle-info"></i> How it works</div>
        </div>
        <div style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6;">
            <p style="margin-bottom: 10px;">The categories created here will be automatically available when adding or editing a Course.</p>
            <p style="margin-bottom: 10px;"><strong>Main Category:</strong> Represents the primary subject (e.g. อังกฤษ, คณิต).</p>
            <p style="margin-bottom: 10px;"><strong>Sub Category:</strong> Represents the specific focus (e.g. IELTS, TOEIC, Grammar).</p>
            <p>Students can filter courses using these categories in the Classroom.</p>
        </div>
    </div>
</div>
