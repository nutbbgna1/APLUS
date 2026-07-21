<?php
include __DIR__ . '/../../includes/header.php';
$db = getDB();
$stmt = $db->prepare("SELECT * FROM exams ORDER BY level, id");
$stmt->execute();
$exams = $stmt->fetchAll();
?>
<div class="animate-fade-in">
    <h1 style="margin-bottom:16px;">📝 ข้อสอบ</h1>
    <div class="flex-col gap-12">
        <?php foreach ($exams as $e): ?>
        <div class="card">
            <div class="flex justify-between items-center" style="margin-bottom:8px;">
                <h3><?= sanitize($e['title']) ?></h3>
                <span class="badge <?= getLevelBadgeClass($e['level']) ?>"><?= $e['level'] ?></span>
            </div>
            <p style="font-size:0.85rem;color:var(--text-secondary);margin-bottom:12px;"><?= $e['total_questions'] ?> ข้อ · <?= $e['time_minutes'] ?> นาที</p>
            <a href="?page=exam&id=<?= $e['id'] ?>" class="btn btn-primary btn-block">🚀 เริ่มทำข้อสอบ</a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
