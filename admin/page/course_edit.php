<?php
$id = $_GET['id'] ?? null;
$course = null;
$errorMsg = '';
$successMsg = '';

if ($id) {
    $stmt = $db->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$id]);
    $course = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    try {
        $course_code = $_POST['course_code'] ?? '';
        $title = $_POST['title'] ?? '';
        $price = (float)($_POST['price'] ?? 0);
        $category = $_POST['category'] ?? '';
        $sub_category = $_POST['sub_category'] ?? '';
        $course_month = $_POST['course_month'] ?? '';
        $description = $_POST['description'] ?? '';
        $is_published = isset($_POST['is_published']) ? 1 : 0;
        
        $instructor = $_POST['instructor'] ?? ($course ? $course['instructor'] : 'Admin');
        $grade_level = $_POST['grade_level'] ?? ($course ? $course['grade_level'] : 'ทั้งหมด');

        // Handle Image Upload
        $image_url = $course['image_url'] ?? null;
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['name'] !== '') {
            if ($_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
                $imgName = 'course_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $imgPath = __DIR__ . '/../../uploads/courses/' . $imgName;
                if (!is_dir(__DIR__ . '/../../uploads/courses/')) {
                    mkdir(__DIR__ . '/../../uploads/courses/', 0777, true);
                }
                if (move_uploaded_file($_FILES['cover_image']['tmp_name'], $imgPath)) {
                    $image_url = 'uploads/courses/' . $imgName;
                } else {
                    throw new Exception("อัปโหลดรูปภาพล้มเหลว: ไม่สามารถย้ายไฟล์ไปยัง " . $imgPath);
                }
            } else {
                throw new Exception("เกิดข้อผิดพลาดในการอัปโหลดไฟล์ (Error Code: " . $_FILES['cover_image']['error'] . ") - ไฟล์อาจมีขนาดใหญ่เกินไป หรือระบบขัดข้อง");
            }
        }

        if ($course) {
            $stmt = $db->prepare("UPDATE courses SET course_code=?, title=?, price=?, category=?, sub_category=?, course_month=?, description=?, is_published=?, image_url=? WHERE id=?");
            $stmt->execute([$course_code, $title, $price, $category, $sub_category, $course_month, $description, $is_published, $image_url, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO courses (course_code, title, price, category, sub_category, course_month, description, is_published, image_url, instructor, grade_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$course_code, $title, $price, $category, $sub_category, $course_month, $description, $is_published, $image_url, $instructor, $grade_level]);
            $id = $db->lastInsertId(); // Get ID for redirection
        }
        
        echo "<script>window.location.href='?page=course_edit&id=$id&success=1';</script>";
        exit;
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

if (isset($_GET['success'])) {
    $successMsg = "บันทึกข้อมูลเรียบร้อยแล้ว";
}

// Handle Add Episode
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_episode') {
    try {
        $ep_num = (int)$_POST['episode_number'];
        $ep_title = $_POST['ep_title'];
        $duration = $_POST['duration'];
        $is_locked = isset($_POST['is_locked']) ? 1 : 0;
        
        set_time_limit(0); // Prevent PHP timeout for large video uploads
        $ep_video_url = '';
        if (isset($_FILES['ep_video']) && $_FILES['ep_video']['name'] !== '') {
            if ($_FILES['ep_video']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['ep_video']['name'], PATHINFO_EXTENSION);
                $vidName = 'ep_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $vidPath = __DIR__ . '/../../uploads/courses/episodes/' . $vidName;
                if (!is_dir(__DIR__ . '/../../uploads/courses/episodes/')) {
                    mkdir(__DIR__ . '/../../uploads/courses/episodes/', 0777, true);
                }
                if (move_uploaded_file($_FILES['ep_video']['tmp_name'], $vidPath)) {
                    $ep_video_url = 'uploads/courses/episodes/' . $vidName;
                } else {
                    throw new Exception("อัปโหลดวิดีโอล้มเหลว: ไม่สามารถย้ายไฟล์ได้");
                }
            } else {
                throw new Exception("เกิดข้อผิดพลาดในการอัปโหลดวิดีโอ (Error Code: " . $_FILES['ep_video']['error'] . ") - ไฟล์อาจมีขนาดใหญ่เกินไป หรือเซิร์ฟเวอร์ขัดข้อง");
            }
        } else {
            $ep_youtube = trim($_POST['ep_youtube'] ?? '');
            if (!empty($ep_youtube)) {
                preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $ep_youtube, $match);
                $ep_video_url = $match[1] ?? $ep_youtube;
            }
        }
        
        $stmt = $db->prepare("INSERT INTO course_episodes (course_id, episode_number, title, duration, video_url, is_locked) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $ep_num, $ep_title, $duration, $ep_video_url, $is_locked]);
        
        echo "<script>window.location.href='?page=course_edit&id=$id&success=1';</script>";
        exit;
    } catch (Exception $e) {
        $errorMsg = "Episode error: " . $e->getMessage();
    }
}

// Handle Add Material
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_material') {
    try {
        $mat_ep_num = (int)$_POST['mat_episode_number'];
        $mat_title = $_POST['mat_title'];
        
        set_time_limit(0); // Prevent PHP timeout for large file uploads
        $mat_file_url = '';
        $mat_size_mb = 0;
        if (isset($_FILES['mat_file']) && $_FILES['mat_file']['name'] !== '') {
            if ($_FILES['mat_file']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['mat_file']['name'], PATHINFO_EXTENSION);
                $docName = 'mat_' . time() . '_' . rand(100, 999) . '.' . $ext;
                $docPath = __DIR__ . '/../../uploads/courses/materials/' . $docName;
                if (!is_dir(__DIR__ . '/../../uploads/courses/materials/')) {
                    mkdir(__DIR__ . '/../../uploads/courses/materials/', 0777, true);
                }
                if (move_uploaded_file($_FILES['mat_file']['tmp_name'], $docPath)) {
                    $mat_file_url = 'uploads/courses/materials/' . $docName;
                    $mat_size_mb = round(filesize($docPath) / 1048576, 2);
                } else {
                    throw new Exception("อัปโหลดเอกสารล้มเหลว: ไม่สามารถย้ายไฟล์ได้");
                }
            } else {
                throw new Exception("เกิดข้อผิดพลาดในการอัปโหลดเอกสาร (Error Code: " . $_FILES['mat_file']['error'] . ") - ไฟล์อาจมีขนาดใหญ่เกินไป หรือเซิร์ฟเวอร์ขัดข้อง");
            }
        }
        
        $stmt = $db->prepare("INSERT INTO course_materials (course_id, episode_number, title, file_url, size_mb) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id, $mat_ep_num, $mat_title, $mat_file_url, $mat_size_mb]);
        
        echo "<script>window.location.href='?page=course_edit&id=$id&success=1';</script>";
        exit;
    } catch (Exception $e) {
        $errorMsg = "Material error: " . $e->getMessage();
    }
}
?>

<div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1 class="page-title" style="font-size: 1.5rem; font-weight: 700; color: #1E293B;"><?= $course ? 'แก้ไขคอร์ส' : 'เพิ่มคอร์สใหม่' ?></h1>
    <a href="?page=courses" class="btn btn-outline" style="background: white; border: 1px solid var(--border); padding: 8px 16px; border-radius: 8px; color: #64748B; text-decoration: none; font-size: 0.9rem; font-weight: 600;"><i class="fa-solid fa-arrow-left"></i> ย้อนกลับ</a>
</div>

<?php if ($errorMsg): ?>
<div style="background: #FEE2E2; color: #DC2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
    <?= htmlspecialchars($errorMsg) ?>
</div>
<?php endif; ?>
<?php if ($successMsg): ?>
<div style="background: #DCFCE7; color: #16A34A; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
    <i class="fa-solid fa-check-circle"></i> <?= htmlspecialchars($successMsg) ?>
</div>
<?php endif; ?>

<div class="card" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--border); margin-bottom: 24px;">
    <form method="POST" action="?page=course_edit<?= $id ? '&id='.$id : '' ?>" enctype="multipart/form-data">
        
        <div style="display: grid; grid-template-columns: 1fr 2fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">รหัสคอร์ส</label>
                <input type="text" name="course_code" placeholder="เว้นว่างรันอัตโนมัติ" value="<?= htmlspecialchars($course['course_code'] ?? '') ?>" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem;">
            </div>
            <div>
                <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">ชื่อคอร์สเรียน <span style="color: #EF4444;">*</span></label>
                <input type="text" name="title" required value="<?= htmlspecialchars($course['title'] ?? '') ?>" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem;">
            </div>
            <div>
                <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">ราคา (บาท)</label>
                <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($course['price'] ?? 0) ?>" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem;">
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">หมวดหมู่หลัก <span style="color: #EF4444;">*</span></label>
                <?php 
                $stmt = $db->query("SELECT * FROM course_categories ORDER BY id ASC");
                $all_cats = $stmt->fetchAll();
                $stmt = $db->query("SELECT * FROM course_subcategories ORDER BY id ASC");
                $all_subs = $stmt->fetchAll();
                ?>
                <select name="category" id="catSelect" required style="width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem; background: white;" onchange="updateSubs()">
                    <option value="">-- เลือกหมวดหมู่หลัก --</option>
                    <?php foreach($all_cats as $c): ?>
                    <option value="<?= htmlspecialchars($c['name']) ?>" data-id="<?= $c['id'] ?>" <?= ($course['category'] ?? '') === $c['name'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">หมวดหมู่ย่อย (เช่น ชื่อโรงเรียน)</label>
                <select name="sub_category" id="subSelect" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem; background: white;">
                    <option value="">-- ไม่ระบุ --</option>
                </select>
            </div>
            
            <div>
                <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">เดือนประจำคอร์ส</label>
                <select name="course_month" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem; background: white;">
                    <option value="">-- ไม่ระบุ --</option>
                    <?php 
                    $months = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
                    foreach($months as $m): 
                    ?>
                    <option value="<?= $m ?>" <?= ($course['course_month'] ?? '') === $m ? 'selected' : '' ?>><?= $m ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">ชื่อผู้สอน (Instructor)</label>
                <input type="text" name="instructor" value="<?= htmlspecialchars($course['instructor'] ?? '') ?>" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem;">
            </div>
            <div>
                <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">ชั้นเรียน (Grade Level)</label>
                <select name="grade_level" style="width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem; background: white;">
                    <?php 
                    $grades = ['ป.4', 'ป.5', 'ป.6', 'ม.1', 'ม.2', 'ม.3', 'ม.4', 'ม.5', 'ม.6', 'ทั้งหมด'];
                    foreach($grades as $g): 
                    ?>
                    <option value="<?= $g ?>" <?= ($course['grade_level'] ?? '') === $g ? 'selected' : '' ?>><?= $g ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">รายละเอียด</label>
            <textarea name="description" rows="4" placeholder="คำอธิบายคอร์สเรียน" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; font-size: 0.95rem; resize: vertical;"><?= htmlspecialchars($course['description'] ?? '') ?></textarea>
        </div>

        <div style="margin-bottom: 24px;">
            <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">อัปโหลดรูปภาพหน้าปกคอร์ส</label>
            <div style="display: flex; gap: 20px; align-items: flex-start;">
                <div style="width: 200px; height: 130px; border: 1px solid var(--border); border-radius: 8px; display: flex; justify-content: center; align-items: center; background: #F8FAFC; overflow: hidden;">
                    <?php if(!empty($course['image_url'])): ?>
                        <img src="../<?= htmlspecialchars($course['image_url']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div style="color: #94A3B8; font-size: 0.85rem; text-align: center;">ไม่มีรูปภาพ</div>
                    <?php endif; ?>
                </div>
                <div style="flex: 1;">
                    <input type="file" name="cover_image" accept=".jpg, .jpeg, .png, .gif, .webp" style="display: block; margin-bottom: 8px; font-size: 0.9rem;">
                    <div style="font-size: 0.8rem; color: #64748B;">รองรับไฟล์ภาพ .jpg, .png, .gif, .webp (หากไม่อัปโหลดใหม่ จะใช้รูปเดิม)</div>
                </div>
            </div>
        </div>
        
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 24px; padding-top: 20px; border-top: 1px solid var(--border);">
            <!-- Toggle Switch CSS -->
            <style>
                .switch { position: relative; display: inline-block; width: 44px; height: 24px; }
                .switch input { opacity: 0; width: 0; height: 0; }
                .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #CBD5E1; transition: .4s; border-radius: 24px; }
                .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
                input:checked + .slider { background-color: #3B82F6; }
                input:checked + .slider:before { transform: translateX(20px); }
            </style>
            <label class="switch">
                <input type="checkbox" name="is_published" <?= ($course['is_published'] ?? 1) ? 'checked' : '' ?>>
                <span class="slider"></span>
            </label>
            <label style="font-weight: 700; font-size: 0.95rem; color: #334155;">เผยแพร่คอร์สนี้ให้นักเรียนเห็น</label>
        </div>
        
        <div style="text-align: right;">
            <button type="submit" style="background: #3B82F6; color: white; padding: 10px 24px; border-radius: 8px; font-weight: 700; font-size: 0.95rem; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);">บันทึกข้อมูลคอร์ส</button>
        </div>
    </form>
</div>

<?php if ($id): ?>
<!-- Episodes & Materials Manager for Existing Courses -->
<div class="card" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--border); margin-bottom: 24px;">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-play-circle" style="color: #3B82F6; font-size: 1.2rem;"></i>
            <h2 style="font-size: 1.1rem; font-weight: 700; color: #1E293B; margin: 0;">จัดการวิดีโอบทเรียน</h2>
        </div>
        <button onclick="document.getElementById('modal_episode').style.display='flex';" style="background: #007BFF; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-plus"></i> เพิ่มบทเรียนใหม่
        </button>
    </div>

    <?php
    $stmt = $db->prepare("SELECT * FROM course_episodes WHERE course_id = ? ORDER BY episode_number ASC");
    $stmt->execute([$id]);
    $episodes = $stmt->fetchAll();
    ?>
    
    <?php if (empty($episodes)): ?>
    <div style="text-align: center; color: #64748B; padding: 40px 0; font-size: 0.95rem;">
        ยังไม่มีวิดีโอบทเรียนในคอร์สนี้
    </div>
    <?php else: ?>
    <div style="display: flex; flex-direction: column; gap: 10px;">
        <?php foreach($episodes as $ep): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border: 1px solid var(--border); border-radius: 8px; background: #F8FAFC;">
            <div>
                <span style="font-weight: 700; color: #334155; margin-right: 10px;">EP. <?= $ep['episode_number'] ?></span>
                <span style="font-weight: 600; color: #1E293B;"><?= htmlspecialchars($ep['title']) ?></span>
                <?php if ($ep['duration']): ?>
                    <span style="font-size: 0.8rem; color: #64748B; margin-left: 10px;"><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($ep['duration']) ?></span>
                <?php endif; ?>
            </div>
            <div style="display: flex; gap: 10px;">
                <button class="btn btn-sm btn-outline" style="padding: 4px 10px; font-size: 0.8rem;"><i class="fa-solid fa-edit"></i> Edit</button>
                <button class="btn btn-sm btn-outline" style="padding: 4px 10px; font-size: 0.8rem; color: #EF4444; border-color: #EF4444;"><i class="fa-solid fa-trash"></i> Delete</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<div class="card" style="background: white; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid var(--border);">
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-file-pdf" style="color: #EF4444; font-size: 1.2rem;"></i>
            <h2 style="font-size: 1.1rem; font-weight: 700; color: #1E293B; margin: 0;">จัดการเอกสารประกอบการเรียน</h2>
        </div>
        <button onclick="document.getElementById('modal_material').style.display='flex';" style="background: #10B981; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-plus"></i> เพิ่มเอกสาร
        </button>
    </div>

    <?php
    $stmt = $db->prepare("SELECT * FROM course_materials WHERE course_id = ? ORDER BY episode_number ASC");
    $stmt->execute([$id]);
    $materials = $stmt->fetchAll();
    ?>
    
    <?php if (empty($materials)): ?>
    <div style="text-align: center; color: #64748B; padding: 40px 0; font-size: 0.95rem;">
        ยังไม่มีเอกสารในคอร์สนี้
    </div>
    <?php else: ?>
    <div style="display: flex; flex-direction: column; gap: 10px;">
        <?php foreach($materials as $mat): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border: 1px solid var(--border); border-radius: 8px; background: #F8FAFC;">
            <div>
                <span style="font-weight: 600; color: #1E293B;"><?= htmlspecialchars($mat['title']) ?></span>
                <span style="font-size: 0.8rem; color: #64748B; margin-left: 10px;"><?= $mat['size_mb'] ?> MB</span>
            </div>
            <div style="display: flex; gap: 10px;">
                <button class="btn btn-sm btn-outline" style="padding: 4px 10px; font-size: 0.8rem; color: #EF4444; border-color: #EF4444;"><i class="fa-solid fa-trash"></i> Delete</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

</div>

<!-- Modals -->
<div id="modal_episode" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 12px; width: 100%; max-width: 500px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="font-size: 1.2rem; font-weight: 700; color: #1E293B; margin-bottom: 20px;">เพิ่มวิดีโอบทเรียน</h3>
        <form method="POST" action="?page=course_edit&id=<?= $id ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_episode">
            <div style="display: grid; grid-template-columns: 1fr 3fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">EP No.</label>
                    <input type="number" name="episode_number" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div>
                    <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">ชื่อบทเรียน</label>
                    <input type="text" name="ep_title" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">ความยาว (เช่น 15:30)</label>
                <input type="text" name="duration" style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
            </div>
            <div style="margin-bottom: 15px;">
                <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">ลิงก์ YouTube (ถ้ารูปแบบเป็นวิดีโอ YouTube)</label>
                <input type="text" name="ep_youtube" placeholder="เช่น https://www.youtube.com/watch?v=..." style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
            </div>
            <div style="text-align: center; color: #94A3B8; font-weight: 700; margin-bottom: 15px; font-size: 0.9rem;">หรือ</div>
            <div style="margin-bottom: 15px;">
                <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">อัปโหลดไฟล์วิดีโอ (MP4)</label>
                <input type="file" name="ep_video" accept="video/mp4" style="width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 8px; background: #F8FAFC;">
            </div>
            <div style="margin-bottom: 24px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600; color: #334155;">
                    <input type="checkbox" name="is_locked" checked style="width: 16px; height: 16px;">
                    ล็อคบทเรียนนี้ (นักเรียนต้องซื้อก่อนถึงจะดูได้)
                </label>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="document.getElementById('modal_episode').style.display='none';" class="btn btn-outline" style="padding: 8px 16px;">ยกเลิก</button>
                <button type="submit" style="background: #3B82F6; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600;">บันทึกบทเรียน</button>
            </div>
        </form>
    </div>
</div>

<div id="modal_material" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; border-radius: 12px; width: 100%; max-width: 500px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="font-size: 1.2rem; font-weight: 700; color: #1E293B; margin-bottom: 20px;">เพิ่มเอกสารประกอบการเรียน</h3>
        <form method="POST" action="?page=course_edit&id=<?= $id ?>" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add_material">
            <div style="display: grid; grid-template-columns: 1fr 3fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">EP No.</label>
                    <input type="number" name="mat_episode_number" value="1" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
                <div>
                    <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">ชื่อเอกสาร</label>
                    <input type="text" name="mat_title" required style="width: 100%; padding: 10px; border: 1px solid var(--border); border-radius: 8px;">
                </div>
            </div>
            <div style="margin-bottom: 24px;">
                <label style="display:block; font-weight: 700; font-size: 0.9rem; color: #334155; margin-bottom: 8px;">อัปโหลดเอกสาร (PDF, DOCX)</label>
                <input type="file" name="mat_file" accept=".pdf,.doc,.docx,.ppt,.pptx" required style="width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 8px; background: #F8FAFC;">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="document.getElementById('modal_material').style.display='none';" class="btn btn-outline" style="padding: 8px 16px;">ยกเลิก</button>
                <button type="submit" style="background: #10B981; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600;">บันทึกเอกสาร</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
const allSubs = <?= json_encode($all_subs ?? []) ?>;
const currentSub = <?= json_encode($course['sub_category'] ?? '') ?>;

function updateSubs() {
    const catSelect = document.getElementById('catSelect');
    const subSelect = document.getElementById('subSelect');
    
    // Clear sub select
    subSelect.innerHTML = '<option value="">-- ไม่ระบุ --</option>';
    
    // Get selected category ID
    const selectedOpt = catSelect.options[catSelect.selectedIndex];
    if (!selectedOpt || !selectedOpt.value) return;
    
    const catId = selectedOpt.getAttribute('data-id');
    
    // Filter subs
    const filteredSubs = allSubs.filter(s => s.category_id == catId);
    
    filteredSubs.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.name;
        opt.textContent = s.name;
        if (s.name === currentSub) opt.selected = true;
        subSelect.appendChild(opt);
    });
}

// Initial call
updateSubs();
</script>
