<?php
// ============================================================
// Admin — Course Management (List All Courses)
// ============================================================

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_course') {
    $del_id = (int)$_POST['course_id'];
    try {
        $db->beginTransaction();
        $db->prepare("DELETE FROM course_episodes WHERE course_id = ?")->execute([$del_id]);
        $db->prepare("DELETE FROM course_materials WHERE course_id = ?")->execute([$del_id]);
        $db->prepare("DELETE FROM course_enrollments WHERE course_id = ?")->execute([$del_id]);
        $db->prepare("DELETE FROM courses WHERE id = ?")->execute([$del_id]);
        $db->commit();
        echo "<script>window.location.href='?page=courses&deleted=1';</script>";
        exit;
    } catch (Exception $e) {
        $db->rollBack();
    }
}

// Fetch courses with episode count
$courses = $db->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM course_episodes WHERE course_id = c.id) as ep_count,
           (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.id AND status = 'approved') as student_count
    FROM courses c 
    ORDER BY c.id DESC
")->fetchAll();
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <h1 class="page-title" style="font-size: 1.5rem; font-weight: 800; color: #1E293B; margin: 0;">Course Management</h1>
        <p style="color: #94A3B8; font-size: 0.85rem; margin-top: 4px;">จัดการคอร์สเรียนออนไลน์ทั้งหมด</p>
    </div>
    <a href="?page=course_edit" style="background: linear-gradient(135deg, #3B82F6, #2563EB); color: white; padding: 10px 20px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(59,130,246,0.3); transition: all 0.2s;">
        <i class="fa-solid fa-plus"></i> เพิ่มคอร์สใหม่
    </a>
</div>

<?php if (isset($_GET['deleted'])): ?>
<div style="background: #FEE2E2; color: #DC2626; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 0.9rem;">
    <i class="fa-solid fa-trash"></i> ลบคอร์สเรียบร้อยแล้ว
</div>
<?php endif; ?>

<!-- Stats Cards -->
<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
    <div style="background: white; border-radius: 12px; padding: 20px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="font-size: 0.8rem; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">คอร์สทั้งหมด</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #1E293B;"><?= count($courses) ?></div>
    </div>
    <div style="background: white; border-radius: 12px; padding: 20px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="font-size: 0.8rem; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">เผยแพร่แล้ว</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #16A34A;"><?= count(array_filter($courses, fn($c) => $c['is_published'])) ?></div>
    </div>
    <div style="background: white; border-radius: 12px; padding: 20px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        <div style="font-size: 0.8rem; font-weight: 700; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">ฉบับร่าง</div>
        <div style="font-size: 1.8rem; font-weight: 800; color: #F59E0B;"><?= count(array_filter($courses, fn($c) => !$c['is_published'])) ?></div>
    </div>
</div>

<!-- Course Table -->
<div style="background: white; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow: hidden;">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <div style="font-weight: 700; font-size: 1rem; color: #1E293B;">รายการคอร์สทั้งหมด</div>
    </div>
    
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #F8FAFC;">
                <th style="padding: 12px 16px; text-align: left; font-size: 0.8rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">คอร์สเรียน</th>
                <th style="padding: 12px 16px; text-align: center; font-size: 0.8rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">หมวดหมู่</th>
                <th style="padding: 12px 16px; text-align: center; font-size: 0.8rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">ระดับชั้น</th>
                <th style="padding: 12px 16px; text-align: center; font-size: 0.8rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">EP</th>
                <th style="padding: 12px 16px; text-align: center; font-size: 0.8rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">นักเรียน</th>
                <th style="padding: 12px 16px; text-align: center; font-size: 0.8rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">ราคา</th>
                <th style="padding: 12px 16px; text-align: center; font-size: 0.8rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">สถานะ</th>
                <th style="padding: 12px 16px; text-align: center; font-size: 0.8rem; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.5px;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($courses as $c): ?>
            <tr style="border-bottom: 1px solid #F1F5F9; transition: background 0.15s;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">
                <td style="padding: 14px 16px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <?php if ($c['image_url']): ?>
                            <img src="../linguamax/<?= htmlspecialchars($c['image_url']) ?>" style="width: 48px; height: 48px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border);">
                        <?php else: ?>
                            <div style="width: 48px; height: 48px; border-radius: 8px; background: linear-gradient(135deg, #E0E7FF, #C7D2FE); display: flex; align-items: center; justify-content: center; color: #6366F1; font-weight: 800; font-size: 0.9rem;"><?= mb_substr($c['category'], 0, 1) ?></div>
                        <?php endif; ?>
                        <div>
                            <div style="font-weight: 700; color: #1E293B; font-size: 0.9rem; margin-bottom: 2px;"><?= htmlspecialchars($c['title']) ?></div>
                            <div style="font-size: 0.75rem; color: #94A3B8;">โดย <?= htmlspecialchars($c['instructor']) ?></div>
                        </div>
                    </div>
                </td>
                <td style="padding: 14px 16px; text-align: center;">
                    <span style="background: #EEF2FF; color: #4F46E5; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;"><?= htmlspecialchars($c['category']) ?></span>
                </td>
                <td style="padding: 14px 16px; text-align: center;">
                    <span style="background: #FEF3C7; color: #D97706; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;"><?= htmlspecialchars($c['grade_level'] ?: 'ทั้งหมด') ?></span>
                </td>
                <td style="padding: 14px 16px; text-align: center; font-weight: 700; color: #1E293B;"><?= $c['ep_count'] ?></td>
                <td style="padding: 14px 16px; text-align: center; font-weight: 700; color: #1E293B;"><?= $c['student_count'] ?></td>
                <td style="padding: 14px 16px; text-align: center; font-weight: 800; color: #3B82F6;">฿<?= number_format($c['price']) ?></td>
                <td style="padding: 14px 16px; text-align: center;">
                    <?php if($c['is_published']): ?>
                        <span style="background: #DCFCE7; color: #16A34A; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;"><i class="fa-solid fa-circle-check"></i> เผยแพร่</span>
                    <?php else: ?>
                        <span style="background: #FEE2E2; color: #DC2626; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;"><i class="fa-solid fa-eye-slash"></i> ซ่อน</span>
                    <?php endif; ?>
                </td>
                <td style="padding: 14px 16px; text-align: center;">
                    <div style="display: flex; gap: 6px; justify-content: center;">
                        <a href="?page=course_edit&id=<?= $c['id'] ?>" style="background: #F1F5F9; color: #3B82F6; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: all 0.15s;" title="แก้ไข">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <form method="POST" onsubmit="return confirm('⚠️ ลบคอร์สนี้? (จะลบวิดีโอ เอกสาร และข้อมูลลงทะเบียนทั้งหมด)');" style="margin:0;">
                            <input type="hidden" name="action" value="delete_course">
                            <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                            <button type="submit" style="background: #FEE2E2; color: #DC2626; width: 32px; height: 32px; border-radius: 8px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s;" title="ลบ">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($courses)): ?>
            <tr>
                <td colspan="8" style="text-align: center; padding: 48px 20px; color: #94A3B8;">
                    <i class="fa-solid fa-book" style="font-size: 2rem; margin-bottom: 12px; display: block; color: #CBD5E1;"></i>
                    <div style="font-weight: 700; margin-bottom: 4px;">ยังไม่มีคอร์สเรียน</div>
                    <div style="font-size: 0.85rem;">คลิก "เพิ่มคอร์สใหม่" เพื่อเริ่มต้นสร้างคอร์ส</div>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
