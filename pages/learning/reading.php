<?php
// ============================================================
// LinguaMax — Reading List
// ============================================================
include __DIR__ . '/../../includes/header.php';
$db = getDB();
$userId = $_SESSION['user_id'];
$level = $_GET['level'] ?? 'all';

$query = "SELECT rp.*, (SELECT COUNT(*) FROM reading_progress WHERE user_id = ? AND passage_id = rp.id) as completed FROM reading_passages rp";
$params = [$userId];
if ($level !== 'all') { $query .= " WHERE rp.level = ?"; $params[] = $level; }
$query .= " ORDER BY rp.level, rp.id";
$stmt = $db->prepare($query);
$stmt->execute($params);
$passages = $stmt->fetchAll();
?>

<div class="animate-fade-in">
    <h1 style="margin-bottom:8px;">📖 Reading</h1>
    <p style="color:var(--text-secondary);margin-bottom:20px;">อ่านเรื่องสั้นแล้วตอบคำถาม ฝึกอ่านจับใจความ!</p>

    <div class="tabs" style="margin-bottom:16px;">
        <a href="?page=reading&level=all" class="tab-btn <?= $level==='all'?'active':'' ?>">ทั้งหมด</a>
        <a href="?page=reading&level=beginner" class="tab-btn <?= $level==='beginner'?'active':'' ?>">เริ่มต้น</a>
        <a href="?page=reading&level=intermediate" class="tab-btn <?= $level==='intermediate'?'active':'' ?>">กลาง</a>
        <a href="?page=reading&level=advanced" class="tab-btn <?= $level==='advanced'?'active':'' ?>">ขั้นสูง</a>
    </div>

    <div class="flex-col gap-12">
        <?php foreach ($passages as $p): ?>
        <a href="?page=reading-view&id=<?= $p['id'] ?>" class="card card-interactive" style="text-decoration:none;color:var(--text);">
            <div class="flex justify-between items-center" style="margin-bottom:8px;">
                <h3><?= sanitize($p['title']) ?></h3>
                <span class="badge <?= getLevelBadgeClass($p['level']) ?>"><?= $p['level'] ?></span>
            </div>
            <?php if ($p['title_th']): ?>
                <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:8px;"><?= sanitize($p['title_th']) ?></p>
            <?php endif; ?>
            <div class="flex justify-between items-center">
                <span style="font-size:0.8rem;color:var(--text-light);">📖 <?= $p['word_count'] ?> คำ</span>
                <?php if ($p['completed'] > 0): ?>
                    <span class="badge badge-success">✅ อ่านแล้ว</span>
                <?php endif; ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
