<?php
// ============================================================
// Admin — Course Edit (Create / Update / Episodes / Materials)
// ============================================================

$id = $_GET['id'] ?? null;
$course = null;
$errorMsg = '';
$successMsg = '';

if ($id) {
    $stmt = $db->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    $course = $stmt->fetch();
    if (!$course) {
        echo "<script>window.location.href='?page=courses';</script>";
        exit;
    }
}

// ── Handle: Save Course Info ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'save_course') {
    try {
        $course_code  = trim($_POST['course_code'] ?? '');
        $title        = trim($_POST['title'] ?? '');
        $price        = (float)($_POST['price'] ?? 0);
        $category     = trim($_POST['category'] ?? '');
        $sub_category = trim($_POST['sub_category'] ?? '');
        $grade_level  = trim($_POST['grade_level'] ?? 'ทั้งหมด');
        $course_month = trim($_POST['course_month'] ?? '');
        $instructor   = trim($_POST['instructor'] ?? 'Admin');
        $description  = trim($_POST['description'] ?? '');
        $is_published = isset($_POST['is_published']) ? 1 : 0;

        if (empty($title)) throw new Exception("กรุณากรอกชื่อคอร์ส");
        if (empty($category)) throw new Exception("กรุณาเลือกหมวดหมู่");

        // Handle Image Upload
        $image_url = $course['image_url'] ?? null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['name'] !== '') {
            if ($_FILES['cover_image']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("อัปโหลดรูปปกล้มเหลว (Error: " . $_FILES['cover_image']['error'] . ")");
            }
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            $ext = strtolower(pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed)) {
                throw new Exception("รองรับเฉพาะไฟล์ JPG, PNG, WEBP, GIF เท่านั้น");
            }
            if ($_FILES['cover_image']['size'] > 2 * 1024 * 1024) {
                throw new Exception("ไฟล์รูปปกต้องมีขนาดไม่เกิน 2MB");
            }
            $imgName = 'course_' . time() . '_' . rand(100,999) . '.' . $ext;
            $uploadDir = __DIR__ . '/../../uploads/courses/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            if (!move_uploaded_file($_FILES['cover_image']['tmp_name'], $uploadDir . $imgName)) {
                throw new Exception("ไม่สามารถบันทึกรูปภาพได้");
            }
            $image_url = 'uploads/courses/' . $imgName;
        }

        if ($course) {
            $stmt = $db->prepare("UPDATE courses SET course_code=?, title=?, price=?, category=?, sub_category=?, grade_level=?, course_month=?, instructor=?, description=?, is_published=?, image_url=? WHERE id=?");
            $stmt->execute([$course_code, $title, $price, $category, $sub_category, $grade_level, $course_month, $instructor, $description, $is_published, $image_url, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO courses (course_code, title, price, category, sub_category, grade_level, course_month, instructor, description, is_published, image_url) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$course_code, $title, $price, $category, $sub_category, $grade_level, $course_month, $instructor, $description, $is_published, $image_url]);
            $id = $db->lastInsertId();
        }
        echo "<script>window.location.href='?page=course_edit&id=$id&success=1';</script>";
        exit;
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

// ── Handle: Add Episode ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'add_episode') {
    try {
        if (!$id) throw new Exception("กรุณาบันทึกข้อมูลคอร์สก่อน");
        $ep_num   = (int)$_POST['episode_number'];
        $ep_title = trim($_POST['ep_title'] ?? '');
        $duration = trim($_POST['duration'] ?? '');
        $is_locked = isset($_POST['is_locked']) ? 1 : 0;

        if (empty($ep_title)) throw new Exception("กรุณากรอกชื่อบทเรียน");

        // Extract YouTube video ID
        $ep_youtube = trim($_POST['ep_youtube'] ?? '');
        $video_url = '';
        if (!empty($ep_youtube)) {
            preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $ep_youtube, $match);
            $video_url = $match[1] ?? $ep_youtube;
        }

        $stmt = $db->prepare("INSERT INTO course_episodes (course_id, episode_number, title, duration, video_url, is_locked) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$id, $ep_num, $ep_title, $duration, $video_url, $is_locked]);

        echo "<script>window.location.href='?page=course_edit&id=$id&success=1';</script>";
        exit;
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

// ── Handle: Delete Episode ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'delete_episode') {
    $ep_id = (int)$_POST['episode_id'];
    $db->prepare("DELETE FROM course_episodes WHERE id = ? AND course_id = ?")->execute([$ep_id, $id]);
    echo "<script>window.location.href='?page=course_edit&id=$id&success=1';</script>";
    exit;
}

// ── Handle: Add Material ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'add_material') {
    try {
        if (!$id) throw new Exception("กรุณาบันทึกข้อมูลคอร์สก่อน");
        $mat_ep_num = (int)$_POST['mat_episode_number'];
        $mat_title  = trim($_POST['mat_title'] ?? '');

        if (empty($mat_title)) throw new Exception("กรุณากรอกชื่อเอกสาร");

        $mat_file_url = '';
        $mat_size_mb = 0;
        if (isset($_FILES['mat_file']) && $_FILES['mat_file']['name'] !== '') {
            if ($_FILES['mat_file']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("อัปโหลดเอกสารล้มเหลว (Error: " . $_FILES['mat_file']['error'] . ")");
            }
            $ext = strtolower(pathinfo($_FILES['mat_file']['name'], PATHINFO_EXTENSION));
            $docName = 'mat_' . time() . '_' . rand(100,999) . '.' . $ext;
            $matDir = __DIR__ . '/../../uploads/courses/materials/';
            if (!is_dir($matDir)) mkdir($matDir, 0777, true);
            if (!move_uploaded_file($_FILES['mat_file']['tmp_name'], $matDir . $docName)) {
                throw new Exception("ไม่สามารถบันทึกเอกสารได้");
            }
            $mat_file_url = 'uploads/courses/materials/' . $docName;
            $mat_size_mb = round(filesize($matDir . $docName) / 1048576, 2);
        }

        $stmt = $db->prepare("INSERT INTO course_materials (course_id, episode_number, title, file_url, size_mb) VALUES (?,?,?,?,?)");
        $stmt->execute([$id, $mat_ep_num, $mat_title, $mat_file_url, $mat_size_mb]);

        echo "<script>window.location.href='?page=course_edit&id=$id&success=1';</script>";
        exit;
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

// ── Handle: Delete Material ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'delete_material') {
    $mat_id = (int)$_POST['material_id'];
    $db->prepare("DELETE FROM course_materials WHERE id = ? AND course_id = ?")->execute([$mat_id, $id]);
    echo "<script>window.location.href='?page=course_edit&id=$id&success=1';</script>";
    exit;
}

// ── Handle: Add Student Manually ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'add_student') {
    try {
        if (!$id) throw new Exception("กรุณาบันทึกข้อมูลคอร์สก่อน");
        $student_username = trim($_POST['student_username'] ?? '');
        if (empty($student_username)) throw new Exception("กรุณากรอกชื่อผู้ใช้ของนักเรียน");

        // Find user
        $stmt = $db->prepare("SELECT id, fname, lname FROM users WHERE username = ?");
        $stmt->execute([$student_username]);
        $student = $stmt->fetch();
        if (!$student) throw new Exception("ไม่พบนักเรียนที่มีชื่อผู้ใช้ \"$student_username\"");

        // Check if already enrolled
        $stmt = $db->prepare("SELECT id FROM course_enrollments WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$student['id'], $id]);
        if ($stmt->fetch()) {
            // Update to approved
            $db->prepare("UPDATE course_enrollments SET status = 'approved', approved_at = NOW() WHERE user_id = ? AND course_id = ?")->execute([$student['id'], $id]);
        } else {
            $db->prepare("INSERT INTO course_enrollments (user_id, course_id, status, approved_at) VALUES (?, ?, 'approved', NOW())")->execute([$student['id'], $id]);
        }
        echo "<script>window.location.href='?page=course_edit&id=$id&success=1';</script>";
        exit;
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

// ── Handle: Remove Student ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_action'] ?? '') === 'remove_student') {
    $enrollment_id = (int)$_POST['enrollment_id'];
    $db->prepare("DELETE FROM course_enrollments WHERE id = ? AND course_id = ?")->execute([$enrollment_id, $id]);
    echo "<script>window.location.href='?page=course_edit&id=$id&success=1';</script>";
    exit;
}

// ── Success message ──
if (isset($_GET['success'])) {
    $successMsg = "บันทึกข้อมูลเรียบร้อยแล้ว";
}

// ── Fetch categories ──
$catDb = $db->query("SELECT * FROM course_categories ORDER BY id ASC")->fetchAll();
$subcatDb = $db->query("SELECT s.*, c.name as cat_name FROM course_subcategories s JOIN course_categories c ON s.category_id = c.id")->fetchAll();
$subcatsByCat = [];
foreach ($subcatDb as $s) {
    $subcatsByCat[$s['cat_name']][] = $s['name'];
}

// ── Fetch episodes, materials & enrollments for existing course ──
$episodes = [];
$materials = [];
$enrollments = [];
if ($id) {
    $episodes = $db->prepare("SELECT * FROM course_episodes WHERE course_id = ? ORDER BY episode_number ASC");
    $episodes->execute([$id]);
    $episodes = $episodes->fetchAll();

    $materials = $db->prepare("SELECT * FROM course_materials WHERE course_id = ? ORDER BY episode_number ASC");
    $materials->execute([$id]);
    $materials = $materials->fetchAll();

    $enrollments = $db->prepare("SELECT e.*, u.username, u.fname, u.lname FROM course_enrollments e JOIN users u ON e.user_id = u.id WHERE e.course_id = ? ORDER BY e.approved_at DESC");
    $enrollments->execute([$id]);
    $enrollments = $enrollments->fetchAll();
}

$grades = ['ทั้งหมด', 'ป.4', 'ป.5', 'ป.6', 'ม.1', 'ม.2', 'ม.3', 'ม.4', 'ม.5', 'ม.6'];
?>

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <h1 style="font-size: 1.5rem; font-weight: 800; color: #1E293B; margin: 0;"><?= $course ? 'แก้ไขคอร์ส' : 'เพิ่มคอร์สใหม่' ?></h1>
    <a href="?page=courses" style="background: white; border: 1px solid var(--border); padding: 8px 16px; border-radius: 8px; color: #64748B; text-decoration: none; font-size: 0.9rem; font-weight: 600; display: flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-arrow-left"></i> ย้อนกลับ
    </a>
</div>

<!-- Messages -->
<?php if ($errorMsg): ?>
<div style="background: #FEE2E2; color: #DC2626; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 8px;">
    <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMsg) ?>
</div>
<?php endif; ?>
<?php if ($successMsg): ?>
<div style="background: #DCFCE7; color: #16A34A; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 8px;">
    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($successMsg) ?>
</div>
<?php endif; ?>

<!-- ═══════════════════════════════════════════════════ -->
<!-- SECTION 1: Course Information Form                 -->
<!-- ═══════════════════════════════════════════════════ -->
<div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--border); margin-bottom: 24px;">
    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
        <i class="fa-solid fa-info-circle" style="color: #3B82F6; font-size: 1.1rem;"></i>
        <h2 style="font-size: 1.1rem; font-weight: 700; color: #1E293B; margin: 0;">ข้อมูลคอร์ส</h2>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="form_action" value="save_course">

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <!-- Course Code -->
            <div>
                <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 6px;">รหัสคอร์ส</label>
                <input type="text" name="course_code" value="<?= htmlspecialchars($course['course_code'] ?? '') ?>" placeholder="เช่น ENG-P4-01" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; outline: none;">
            </div>
            <!-- Title -->
            <div>
                <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 6px;">ชื่อคอร์ส <span style="color:#EF4444;">*</span></label>
                <input type="text" name="title" value="<?= htmlspecialchars($course['title'] ?? '') ?>" required placeholder="เช่น คอร์สอังกฤษเข้มข้น ป.4" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; outline: none;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <!-- Category -->
            <div>
                <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 6px;">หมวดหมู่ (วิชา) <span style="color:#EF4444;">*</span></label>
                <select name="category" id="catSelect" required onchange="updateSubcats()" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; outline: none; background: white;">
                    <option value="">-- เลือกหมวดหมู่ --</option>
                    <?php foreach($catDb as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['name']) ?>" <?= ($course['category'] ?? '') === $cat['name'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Sub Category -->
            <div>
                <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 6px;">หมวดย่อย</label>
                <select name="sub_category" id="subcatSelect" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; outline: none; background: white;">
                    <option value="">-- ไม่ระบุ --</option>
                </select>
            </div>
            <!-- Grade Level -->
            <div>
                <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 6px;">ระดับชั้น</label>
                <select name="grade_level" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; outline: none; background: white;">
                    <?php foreach($grades as $g): ?>
                        <option value="<?= $g ?>" <?= ($course['grade_level'] ?? 'ทั้งหมด') === $g ? 'selected' : '' ?>><?= $g ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <!-- Price -->
            <div>
                <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 6px;">ราคา (บาท)</label>
                <input type="number" name="price" value="<?= $course['price'] ?? 0 ?>" min="0" step="0.01" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; outline: none;">
            </div>
            <!-- Instructor -->
            <div>
                <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 6px;">ผู้สอน</label>
                <input type="text" name="instructor" value="<?= htmlspecialchars($course['instructor'] ?? '') ?>" placeholder="เช่น อ.ผู้เชี่ยวชาญ" style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; outline: none;">
            </div>
            <!-- Course Month -->
            <div>
                <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 6px;">เดือนที่เรียน</label>
                <input type="text" name="course_month" value="<?= htmlspecialchars($course['course_month'] ?? '') ?>" placeholder="เช่น ม.ค. - มี.ค." style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; outline: none;">
            </div>
        </div>

        <!-- Description -->
        <div style="margin-bottom: 16px;">
            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 6px;">คำอธิบายคอร์ส</label>
            <textarea name="description" rows="3" placeholder="อธิบายเนื้อหาคอร์สโดยย่อ..." style="width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; outline: none; resize: vertical; font-family: inherit;"><?= htmlspecialchars($course['description'] ?? '') ?></textarea>
        </div>

        <!-- Cover Image -->
        <div style="margin-bottom: 20px;">
            <label style="display: block; font-weight: 700; font-size: 0.85rem; color: #334155; margin-bottom: 6px;">รูปปกคอร์ส (JPG, PNG, WEBP — ไม่เกิน 2MB)</label>
            <div style="display: flex; align-items: center; gap: 16px;">
                <?php if ($course && $course['image_url']): ?>
                    <img src="<?= htmlspecialchars($course['image_url']) ?>" style="width: 80px; height: 80px; border-radius: 10px; object-fit: cover; border: 1px solid var(--border);">
                <?php endif; ?>
                <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp,image/gif" style="padding: 8px; border: 1px solid var(--border); border-radius: 8px; background: #F8FAFC; font-size: 0.85rem; flex: 1;">
            </div>
        </div>

        <!-- Publish Toggle + Save -->
        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px solid #F1F5F9;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <style>
                    .switch { position: relative; display: inline-block; width: 44px; height: 24px; }
                    .switch input { opacity: 0; width: 0; height: 0; }
                    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #CBD5E1; transition: .3s; border-radius: 24px; }
                    .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background: white; transition: .3s; border-radius: 50%; }
                    input:checked + .slider { background: #3B82F6; }
                    input:checked + .slider:before { transform: translateX(20px); }
                </style>
                <span class="switch">
                    <input type="checkbox" name="is_published" <?= ($course['is_published'] ?? 1) ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </span>
                <span style="font-weight: 700; font-size: 0.9rem; color: #334155;">เผยแพร่คอร์สนี้</span>
            </label>
            <button type="submit" style="background: linear-gradient(135deg, #3B82F6, #2563EB); color: white; padding: 10px 28px; border-radius: 10px; font-weight: 700; font-size: 0.95rem; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(59,130,246,0.25); display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-floppy-disk"></i> บันทึกข้อมูลคอร์ส
            </button>
        </div>
    </form>
</div>

<?php if ($id): ?>
<!-- ═══════════════════════════════════════════════════ -->
<!-- SECTION 2: Episodes Manager                        -->
<!-- ═══════════════════════════════════════════════════ -->
<div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--border); margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-play-circle" style="color: #3B82F6; font-size: 1.1rem;"></i>
            <h2 style="font-size: 1.1rem; font-weight: 700; color: #1E293B; margin: 0;">วิดีโอบทเรียน (<?= count($episodes) ?> EP)</h2>
        </div>
        <button onclick="document.getElementById('modal_episode').style.display='flex';" style="background: linear-gradient(135deg, #3B82F6, #2563EB); color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(59,130,246,0.2);">
            <i class="fa-solid fa-plus"></i> เพิ่ม EP ใหม่
        </button>
    </div>

    <?php if (empty($episodes)): ?>
    <div style="text-align: center; color: #94A3B8; padding: 40px 0; font-size: 0.9rem;">
        <i class="fa-solid fa-video" style="font-size: 1.5rem; margin-bottom: 8px; display: block; color: #CBD5E1;"></i>
        ยังไม่มีวิดีโอบทเรียนในคอร์สนี้
    </div>
    <?php else: ?>
    <div style="display: flex; flex-direction: column; gap: 8px;">
        <?php foreach($episodes as $ep): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border: 1px solid #F1F5F9; border-radius: 10px; background: #FAFBFC; transition: background 0.15s;" onmouseover="this.style.background='#F1F5F9'" onmouseout="this.style.background='#FAFBFC'">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 36px; height: 36px; background: <?= $ep['is_locked'] ? '#F1F5F9' : 'linear-gradient(135deg, #3B82F6, #2563EB)' ?>; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: <?= $ep['is_locked'] ? '#94A3B8' : 'white' ?>; font-weight: 800; font-size: 0.85rem;">
                    <?= $ep['episode_number'] ?>
                </div>
                <div>
                    <div style="font-weight: 700; color: #1E293B; font-size: 0.9rem;"><?= htmlspecialchars($ep['title']) ?></div>
                    <div style="font-size: 0.75rem; color: #94A3B8; display: flex; gap: 12px; margin-top: 2px;">
                        <?php if ($ep['duration']): ?>
                            <span><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($ep['duration']) ?></span>
                        <?php endif; ?>
                        <?php if ($ep['video_url']): ?>
                            <span><i class="fa-brands fa-youtube"></i> <?= htmlspecialchars($ep['video_url']) ?></span>
                        <?php endif; ?>
                        <span><?= $ep['is_locked'] ? '🔒 ล็อค' : '🔓 ปลดล็อค' ?></span>
                    </div>
                </div>
            </div>
            <form method="POST" onsubmit="return confirm('ลบบทเรียนนี้?');" style="margin:0;">
                <input type="hidden" name="form_action" value="delete_episode">
                <input type="hidden" name="episode_id" value="<?= $ep['id'] ?>">
                <button type="submit" style="background: none; border: none; color: #EF4444; cursor: pointer; padding: 6px 10px; border-radius: 6px; font-size: 0.85rem;" title="ลบ">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════ -->
<!-- SECTION 3: Materials Manager                       -->
<!-- ═══════════════════════════════════════════════════ -->
<div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--border); margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-file-lines" style="color: #F59E0B; font-size: 1.1rem;"></i>
            <h2 style="font-size: 1.1rem; font-weight: 700; color: #1E293B; margin: 0;">เอกสารประกอบ (<?= count($materials) ?> ไฟล์)</h2>
        </div>
        <button onclick="document.getElementById('modal_material').style.display='flex';" style="background: linear-gradient(135deg, #F59E0B, #D97706); color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 8px rgba(245,158,11,0.2);">
            <i class="fa-solid fa-plus"></i> เพิ่มเอกสาร
        </button>
    </div>

    <?php if (empty($materials)): ?>
    <div style="text-align: center; color: #94A3B8; padding: 40px 0; font-size: 0.9rem;">
        <i class="fa-solid fa-file-pdf" style="font-size: 1.5rem; margin-bottom: 8px; display: block; color: #CBD5E1;"></i>
        ยังไม่มีเอกสารในคอร์สนี้
    </div>
    <?php else: ?>
    <div style="display: flex; flex-direction: column; gap: 8px;">
        <?php foreach($materials as $mat): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border: 1px solid #F1F5F9; border-radius: 10px; background: #FAFBFC;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 36px; height: 36px; background: #FEF3C7; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #D97706; font-size: 1rem;">
                    <i class="fa-solid fa-file-pdf"></i>
                </div>
                <div>
                    <div style="font-weight: 700; color: #1E293B; font-size: 0.9rem;"><?= htmlspecialchars($mat['title']) ?></div>
                    <div style="font-size: 0.75rem; color: #94A3B8;">EP <?= $mat['episode_number'] ?> • <?= $mat['size_mb'] ?> MB</div>
                </div>
            </div>
            <form method="POST" onsubmit="return confirm('ลบเอกสารนี้?');" style="margin:0;">
                <input type="hidden" name="form_action" value="delete_material">
                <input type="hidden" name="material_id" value="<?= $mat['id'] ?>">
                <button type="submit" style="background: none; border: none; color: #EF4444; cursor: pointer; padding: 6px 10px; border-radius: 6px; font-size: 0.85rem;" title="ลบ">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════ -->
<!-- MODAL: Add Episode                                 -->
<!-- ═══════════════════════════════════════════════════ -->
<div id="modal_episode" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(2px);" onclick="if(event.target===this)this.style.display='none';">
    <div style="background: white; border-radius: 16px; width: 100%; max-width: 480px; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); animation: slideUp 0.3s ease;">
        <h3 style="font-size: 1.15rem; font-weight: 800; color: #1E293B; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-play-circle" style="color: #3B82F6;"></i> เพิ่มวิดีโอบทเรียน
        </h3>
        <form method="POST" action="?page=course_edit&id=<?= $id ?>">
            <input type="hidden" name="form_action" value="add_episode">

            <div style="display: grid; grid-template-columns: 80px 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.8rem; color: #334155; margin-bottom: 6px;">EP No.</label>
                    <input type="number" name="episode_number" value="<?= count($episodes) + 1 ?>" required min="1" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; text-align: center; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.8rem; color: #334155; margin-bottom: 6px;">ชื่อบทเรียน <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="ep_title" required placeholder="เช่น Tenses พื้นฐาน" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; outline: none;">
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; font-size: 0.8rem; color: #334155; margin-bottom: 6px;">ความยาว</label>
                <input type="text" name="duration" placeholder="เช่น 15:30" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; outline: none;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-weight: 700; font-size: 0.8rem; color: #334155; margin-bottom: 6px;">
                    <i class="fa-brands fa-youtube" style="color: #FF0000;"></i> ลิงก์ YouTube
                </label>
                <input type="text" name="ep_youtube" placeholder="วางลิงก์ YouTube ที่นี่..." style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; outline: none;">
                <div style="font-size: 0.75rem; color: #94A3B8; margin-top: 4px;">รองรับทุกรูปแบบ: youtube.com/watch?v=..., youtu.be/..., youtube.com/embed/...</div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600; color: #334155; font-size: 0.9rem;">
                    <input type="checkbox" name="is_locked" checked style="width: 16px; height: 16px; accent-color: #3B82F6;">
                    🔒 ล็อคบทเรียนนี้ (ต้องซื้อก่อนถึงจะดูได้)
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="document.getElementById('modal_episode').style.display='none';" style="background: #F1F5F9; color: #64748B; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.9rem;">ยกเลิก</button>
                <button type="submit" style="background: linear-gradient(135deg, #3B82F6, #2563EB); color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.9rem; box-shadow: 0 2px 8px rgba(59,130,246,0.2);">
                    <i class="fa-solid fa-plus"></i> เพิ่มบทเรียน
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════ -->
<!-- MODAL: Add Material                                -->
<!-- ═══════════════════════════════════════════════════ -->
<div id="modal_material" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center; backdrop-filter: blur(2px);" onclick="if(event.target===this)this.style.display='none';">
    <div style="background: white; border-radius: 16px; width: 100%; max-width: 480px; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); animation: slideUp 0.3s ease;">
        <h3 style="font-size: 1.15rem; font-weight: 800; color: #1E293B; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-file-lines" style="color: #F59E0B;"></i> เพิ่มเอกสารประกอบ
        </h3>
        <form method="POST" action="?page=course_edit&id=<?= $id ?>" enctype="multipart/form-data">
            <input type="hidden" name="form_action" value="add_material">

            <div style="display: grid; grid-template-columns: 80px 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.8rem; color: #334155; margin-bottom: 6px;">EP No.</label>
                    <input type="number" name="mat_episode_number" value="1" required min="1" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; text-align: center; outline: none;">
                </div>
                <div>
                    <label style="display: block; font-weight: 700; font-size: 0.8rem; color: #334155; margin-bottom: 6px;">ชื่อเอกสาร <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="mat_title" required placeholder="เช่น สรุปเนื้อหาบทที่ 1" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.9rem; outline: none;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 700; font-size: 0.8rem; color: #334155; margin-bottom: 6px;">อัปโหลดเอกสาร (PDF, DOCX, ...)</label>
                <input type="file" name="mat_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx" style="width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 8px; background: #F8FAFC; font-size: 0.85rem;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="document.getElementById('modal_material').style.display='none';" style="background: #F1F5F9; color: #64748B; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.9rem;">ยกเลิก</button>
                <button type="submit" style="background: linear-gradient(135deg, #F59E0B, #D97706); color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.9rem; box-shadow: 0 2px 8px rgba(245,158,11,0.2);">
                    <i class="fa-solid fa-plus"></i> เพิ่มเอกสาร
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>

<!-- ═══════════════════════════════════════════════════ -->
<!-- SECTION 4: Student Management                      -->
<!-- ═══════════════════════════════════════════════════ -->
<div style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--border); margin-bottom: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-user-plus" style="color: #16A34A; font-size: 1.1rem;"></i>
            <h2 style="font-size: 1.1rem; font-weight: 700; color: #1E293B; margin: 0;">นักเรียนในคอร์ส (<?= count($enrollments) ?> คน)</h2>
        </div>
    </div>

    <!-- Add Student Form -->
    <?php
    $allStudents = $db->query("SELECT username, fname, lname FROM users WHERE role = 'student' ORDER BY fname ASC, username ASC")->fetchAll();
    $enrolledUsernames = array_column($enrollments, 'username');
    ?>
    <form method="POST" style="display: flex; gap: 10px; margin-bottom: 16px; padding: 14px; background: #F0FDF4; border-radius: 10px; border: 1px solid #BBF7D0;">
        <input type="hidden" name="form_action" value="add_student">
        <select name="student_username" required style="flex: 1; padding: 10px 14px; border: 1px solid #BBF7D0; border-radius: 8px; font-size: 0.9rem; outline: none; background: white;">
            <option value="">-- เลือกนักเรียนที่ต้องการเพิ่ม --</option>
            <?php foreach($allStudents as $stu): ?>
                <?php if (!in_array($stu['username'], $enrolledUsernames)): ?>
                    <option value="<?= htmlspecialchars($stu['username']) ?>">
                        <?= htmlspecialchars(($stu['fname'] ?? '') . ' ' . ($stu['lname'] ?? '')) ?> (@<?= htmlspecialchars($stu['username']) ?>)
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
        <button type="submit" style="background: #16A34A; color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.85rem; white-space: nowrap; display: flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-user-plus"></i> เพิ่มนักเรียน
        </button>
    </form>

    <?php if (empty($enrollments)): ?>
    <div style="text-align: center; color: #94A3B8; padding: 30px 0; font-size: 0.9rem;">
        <i class="fa-solid fa-users" style="font-size: 1.5rem; margin-bottom: 8px; display: block; color: #CBD5E1;"></i>
        ยังไม่มีนักเรียนในคอร์สนี้
    </div>
    <?php else: ?>
    <div style="display: flex; flex-direction: column; gap: 6px;">
        <?php foreach($enrollments as $enr): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border: 1px solid #F1F5F9; border-radius: 8px; background: #FAFBFC;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 32px; height: 32px; background: linear-gradient(135deg, #E0E7FF, #C7D2FE); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #6366F1; font-weight: 800; font-size: 0.75rem;">
                    <?= mb_substr($enr['fname'] ?: $enr['username'], 0, 1) ?>
                </div>
                <div>
                    <div style="font-weight: 700; color: #1E293B; font-size: 0.85rem;"><?= htmlspecialchars(($enr['fname'] ?? '') . ' ' . ($enr['lname'] ?? '')) ?></div>
                    <div style="font-size: 0.75rem; color: #94A3B8;">@<?= htmlspecialchars($enr['username']) ?></div>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <?php
                $statusColors = ['approved' => ['#DCFCE7','#16A34A'], 'pending' => ['#FEF3C7','#D97706'], 'rejected' => ['#FEE2E2','#DC2626']];
                $sc = $statusColors[$enr['status']] ?? ['#F1F5F9','#64748B'];
                ?>
                <span style="background: <?= $sc[0] ?>; color: <?= $sc[1] ?>; padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700;"><?= $enr['status'] ?></span>
                <form method="POST" onsubmit="return confirm('ลบนักเรียนนี้ออกจากคอร์ส?');" style="margin:0;">
                    <input type="hidden" name="form_action" value="remove_student">
                    <input type="hidden" name="enrollment_id" value="<?= $enr['id'] ?>">
                    <button type="submit" style="background: none; border: none; color: #EF4444; cursor: pointer; font-size: 0.8rem; padding: 4px;" title="ลบ">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Subcategory JS -->
<script>
const subcatsByCat = <?= json_encode($subcatsByCat) ?>;
const currentSubcat = '<?= addslashes($course['sub_category'] ?? '') ?>';

function updateSubcats() {
    const cat = document.getElementById('catSelect').value;
    const subSelect = document.getElementById('subcatSelect');
    subSelect.innerHTML = '<option value="">-- ไม่ระบุ --</option>';
    if (subcatsByCat[cat]) {
        subcatsByCat[cat].forEach(sub => {
            const opt = document.createElement('option');
            opt.value = sub;
            opt.textContent = sub;
            if (sub === currentSubcat) opt.selected = true;
            subSelect.appendChild(opt);
        });
    }
}
// Init on page load
updateSubcats();
</script>
