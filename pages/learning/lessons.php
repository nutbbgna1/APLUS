<?php
include __DIR__ . '/../../includes/header.php';
$db = getDB();
$userId = $_SESSION['user_id'];
$level = $_GET['level'] ?? 'all';

$query = "SELECT l.*, (SELECT completed FROM lesson_progress WHERE user_id = ? AND lesson_id = l.id) as completed FROM lessons l";
$params = [$userId];
if ($level !== 'all') { $query .= " WHERE l.level = ?"; $params[] = $level; }
$query .= " ORDER BY l.sort_order";
$stmt = $db->prepare($query);
$stmt->execute($params);
$lessons = $stmt->fetchAll();
?>
<div class="animate-fade-in">
    <h1 style="margin-bottom:8px;">📚 บทเรียน</h1>
    <p style="color:var(--text-secondary);margin-bottom:16px;">เรียนรู้ภาษาอังกฤษทีละขั้น ตั้งแต่ง่ายไปยาก</p>
    <div class="tabs" style="margin-bottom:16px;">
        <a href="?page=lessons&level=all" class="tab-btn <?= $level==='all'?'active':'' ?>">ทั้งหมด</a>
        <a href="?page=lessons&level=beginner" class="tab-btn <?= $level==='beginner'?'active':'' ?>">เริ่มต้น</a>
        <a href="?page=lessons&level=intermediate" class="tab-btn <?= $level==='intermediate'?'active':'' ?>">กลาง</a>
        <a href="?page=lessons&level=advanced" class="tab-btn <?= $level==='advanced'?'active':'' ?>">ขั้นสูง</a>
    </div>
    <div class="card item-list" style="padding:0;">
        <?php foreach ($lessons as $l): ?>
        <a href="?page=lesson&id=<?= $l['id'] ?>" class="item-row">
            <div class="item-icon <?= $l['completed'] ? 'item-icon-success' : 'item-icon-primary' ?>">
                <?= $l['completed'] ? '✅' : $l['sort_order'] ?>
            </div>
            <div class="item-info">
                <div class="item-title"><?= sanitize($l['title']) ?></div>
                <div class="item-desc"><?= sanitize($l['description']) ?></div>
            </div>
            <span class="badge <?= getLevelBadgeClass($l['level']) ?>"><?= $l['level'] ?></span>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
