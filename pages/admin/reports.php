<?php
include __DIR__ . '/../../includes/header.php';
$db = getDB();

$stmt = $db->query("
    SELECT er.*, e.title, u.fname, u.lname, u.code 
    FROM exam_results er
    JOIN exams e ON er.exam_id = e.id
    JOIN users u ON er.user_id = u.id
    ORDER BY er.completed_at DESC
    LIMIT 50
");
$recentExams = $stmt->fetchAll();
?>
<div class="animate-fade-in">
    <h1 style="margin-bottom:20px;">📈 รายงานผลการเรียน</h1>

    <h2 style="margin-bottom:12px;">ประวัติการทำข้อสอบล่าสุด (50 รายการ)</h2>
    <div class="card table-wrap" style="padding:0;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>วันที่ - เวลา</th>
                    <th>นักเรียน</th>
                    <th>ข้อสอบ</th>
                    <th>คะแนน</th>
                    <th>คิดเป็น %</th>
                    <th>เวลาที่ใช้</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentExams as $r): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($r['completed_at'])) ?></td>
                    <td><?= sanitize($r['fname']) ?> <?= sanitize($r['lname']) ?> <br><span style="font-size:0.75rem;color:var(--text-secondary);"><?= $r['code'] ?></span></td>
                    <td><?= sanitize($r['title']) ?></td>
                    <td><?= $r['score'] ?> / <?= $r['total'] ?></td>
                    <td>
                        <span style="font-weight:700;color:<?= $r['percentage'] >= 80 ? 'var(--success)' : ($r['percentage'] >= 60 ? 'var(--accent)' : 'var(--danger)') ?>;">
                            <?= $r['percentage'] ?>%
                        </span>
                    </td>
                    <td><?= floor($r['time_spent']/60) ?>m <?= $r['time_spent']%60 ?>s</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
