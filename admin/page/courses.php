<div class="page-header">
    <div>
        <h1 class="page-title">Course Management</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Manage courses, subjects, and grade levels</p>
    </div>
    <a href="?page=course_edit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add New Course</a>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = (int)$_POST['id'];
    $stmt = $db->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    
    // Also delete enrollments
    $stmt = $db->prepare("DELETE FROM course_enrollments WHERE course_id = ?");
    $stmt->execute([$id]);
    
    echo "<script>window.location.href='?page=courses';</script>";
    exit;
}

// Fetch courses
$stmt = $db->query("SELECT * FROM courses ORDER BY id DESC");
$courses = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">All Courses</div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Course Title</th>
                <th>Category (วิชา)</th>
                <th>Grade Level (ชั้นเรียน)</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($courses as $c): ?>
            <tr>
                <td>#<?= $c['id'] ?></td>
                <td>
                    <div style="font-weight: 700; color: var(--text-main);"><?= htmlspecialchars($c['title']) ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">Instructor: <?= htmlspecialchars($c['instructor']) ?></div>
                </td>
                <td><span style="background: #F1F5F9; color: var(--primary); padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;"><?= htmlspecialchars($c['category']) ?></span></td>
                <td><span style="background: #FFFBEB; color: #D97706; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;"><?= htmlspecialchars($c['grade_level'] ?: 'ทั้งหมด') ?></span></td>
                <td><strong style="color: var(--primary);">฿<?= number_format($c['price']) ?></strong></td>
                <td>
                    <?php if($c['is_published']): ?>
                        <span style="background:#DCFCE7;color:#16A34A;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;">Published</span>
                    <?php else: ?>
                        <span style="background:#FEE2E2;color:#DC2626;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;">Draft</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="?page=course_edit&id=<?= $c['id'] ?>" class="btn btn-sm btn-outline" style="margin-right: 5px;"><i class="fa-solid fa-pen"></i></a>
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this course and all its enrollments?');" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn btn-sm" style="background:#EF4444; color:white;"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($courses)): ?>
            <tr><td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">No courses found. Click "Add New Course" to create one.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
