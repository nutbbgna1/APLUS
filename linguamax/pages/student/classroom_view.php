<?php
// ============================================================
// LinguaMax — Student Classroom View (Course Detail & Video)
// ============================================================
include __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

$db = getDB();
$course_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 1;

// Fetch Course
$stmt = $db->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    echo "<div style='padding:40px; text-align:center;'><h2>Course not found</h2></div>";
    include __DIR__ . '/../../includes/footer.php';
    exit;
}

$title = $course['title'];
$instructor = $course['instructor'];
$subject = $course['category'];

// Check Enrollment
$stmt = $db->prepare("SELECT * FROM course_enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$user_id, $course_id]);
$enrollment = $stmt->fetch();

// Fetch Active Payment Methods
$stmt = $db->query("SELECT * FROM payment_methods WHERE is_active = 1");
$payment_methods = $stmt->fetchAll();

$errorMsg = '';
$successMsg = '';
$uploadDir = __DIR__ . '/../../uploads/slips/';

// Handle Coupon Apply (AJAX-like via POST)
$couponDiscount = 0;
$couponCode = '';
$finalPrice = $course['price'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $couponCode = trim($_POST['coupon_code'] ?? '');
    if (!empty($couponCode)) {
        $stmt = $db->prepare("SELECT * FROM course_coupons WHERE code = ? AND is_active = 1 AND (max_uses IS NULL OR used_count < max_uses) AND (expires_at IS NULL OR expires_at > NOW())");
        $stmt->execute([$couponCode]);
        $coupon = $stmt->fetch();
        if ($coupon) {
            // Check if coupon is course-specific
            if ($coupon['course_id'] && $coupon['course_id'] != $course_id) {
                $errorMsg = "คูปองนี้ไม่สามารถใช้กับคอร์สนี้ได้";
            } else {
                if ($coupon['discount_type'] === 'percent') {
                    $couponDiscount = $course['price'] * ($coupon['discount_value'] / 100);
                } else {
                    $couponDiscount = $coupon['discount_value'];
                }
                $finalPrice = max(0, $course['price'] - $couponDiscount);
                $successMsg = "ใช้คูปอง \"$couponCode\" สำเร็จ! ลด ฿" . number_format($couponDiscount, 2);
            }
        } else {
            $errorMsg = "คูปองไม่ถูกต้อง หมดอายุ หรือถูกใช้งานครบแล้ว";
        }
    }
}

// Handle Slip Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['slip'])) {
    if ($_FILES['slip']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION);
        $fileName = 'slip_' . time() . '_' . rand(100, 999) . '.' . $ext;
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        if (move_uploaded_file($_FILES['slip']['tmp_name'], $uploadDir . $fileName)) {
            $slip_path = 'linguamax/uploads/slips/' . $fileName;
            $usedCoupon = trim($_POST['used_coupon_code'] ?? '');
            $paidPrice = (float)($_POST['paid_price'] ?? $course['price']);
            
            try {
                $db->beginTransaction();
                
                // Insert Order
                $stmt = $db->prepare("INSERT INTO orders (user_id, course_id, price, slip_image, status) VALUES (?, ?, ?, ?, 'pending')");
                $stmt->execute([$user_id, $course_id, $paidPrice, $slip_path]);
                
                // Insert/Update Enrollment
                $stmt = $db->prepare("SELECT id FROM course_enrollments WHERE user_id = ? AND course_id = ?");
                $stmt->execute([$user_id, $course_id]);
                if (!$stmt->fetch()) {
                    $stmt = $db->prepare("INSERT INTO course_enrollments (user_id, course_id, status, payment_slip_url) VALUES (?, ?, 'pending', ?)");
                    $stmt->execute([$user_id, $course_id, $slip_path]);
                } else {
                    $stmt = $db->prepare("UPDATE course_enrollments SET status = 'pending', payment_slip_url = ? WHERE user_id = ? AND course_id = ?");
                    $stmt->execute([$slip_path, $user_id, $course_id]);
                }
                
                // Update coupon used_count
                if (!empty($usedCoupon)) {
                    $db->prepare("UPDATE course_coupons SET used_count = used_count + 1 WHERE code = ?")->execute([$usedCoupon]);
                }
                
                $db->commit();
                echo "<script>window.location.href='?page=classroom-view&id=$course_id&success=1';</script>";
                exit;
            } catch (Exception $e) {
                $db->rollBack();
                $errorMsg = "Database error: " . $e->getMessage();
            }
        } else {
            $errorMsg = "อัปโหลดสลิปล้มเหลว กรุณาลองใหม่";
        }
    } else {
        $errorMsg = "กรุณาแนบสลิปโอนเงิน (Error: " . $_FILES['slip']['error'] . ")";
    }
}

if (isset($_GET['success'])) {
    $successMsg = "ส่งข้อมูลสำเร็จแล้ว!";
}
?>

<div class="animate-fade-in" style="padding: 16px 4px 100px 4px; background: #FAFAFD; min-height: 100vh;">
    
    <!-- Top Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <a href="?page=classroom" style="color: #1E293B; font-size: 1.5rem; text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div style="font-weight: 800; font-size: 1.15rem; color: #1E293B; font-family: var(--font-display); max-width: 70%; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($title) ?></div>
        <div style="width: 24px;"></div>
    </div>

    <?php if ($errorMsg): ?>
    <div style="background: #FEE2E2; color: #DC2626; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; font-size: 0.9rem;">
        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($errorMsg) ?>
    </div>
    <?php endif; ?>

    <?php if ($successMsg && !$enrollment): ?>
    <div style="background: #DCFCE7; color: #16A34A; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: 600; font-size: 0.9rem;">
        <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($successMsg) ?>
    </div>
    <?php endif; ?>

    <?php if (!$enrollment): ?>
        <!-- ══════════════════════════════════════════ -->
        <!-- NOT PURCHASED: Show Buy UI                -->
        <!-- ══════════════════════════════════════════ -->
        
        <!-- Course Preview -->
        <div style="background: white; border-radius: 20px; overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 20px;">
            <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #E0E7FF, #C7D2FE); position: relative;">
                <?php if ($course['image_url']): ?>
                    <img src="<?= SITE_URL ?>/<?= htmlspecialchars($course['image_url']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color: #6366F1; font-size: 3.5rem; font-weight: 900;"><?= mb_substr($subject, 0, 1) ?></div>
                <?php endif; ?>
            </div>
            <div style="padding: 20px;">
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--primary); margin-bottom: 6px;"><?= htmlspecialchars($subject) ?></div>
                <h2 style="font-size: 1.3rem; font-weight: 800; color: #1E293B; margin-bottom: 8px;"><?= htmlspecialchars($title) ?></h2>
                <div style="font-size: 0.85rem; color: #94A3B8; font-weight: 600; margin-bottom: 16px;">By <?= htmlspecialchars($instructor) ?></div>
                <?php if ($course['description']): ?>
                    <p style="font-size: 0.9rem; color: #64748B; line-height: 1.6; margin-bottom: 16px;"><?= nl2br(htmlspecialchars($course['description'])) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Buy Card -->
        <div style="background: white; border-radius: 20px; padding: 24px; box-shadow: var(--shadow-sm); text-align: center;" id="buy_card">
            <div style="background: linear-gradient(135deg, #EEF2FF, #E0E7FF); width: 80px; height: 80px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 16px auto; color: #6366F1; font-size: 2rem;">
                <i class="fa-solid fa-lock"></i>
            </div>
            <h2 style="font-size: 1.3rem; font-weight: 800; margin-bottom: 8px; color: #1E293B;">เนื้อหานี้ถูกล็อค</h2>
            <p style="color: #64748B; margin-bottom: 20px; font-size: 0.9rem;">ซื้อคอร์สเพื่อเข้าดูวิดีโอและดาวน์โหลดเอกสาร</p>
            
            <div style="font-size: 2rem; font-weight: 900; color: var(--primary); margin-bottom: 24px;">฿<?= number_format($course['price'], 2) ?></div>
            
            <button onclick="document.getElementById('buy_card').style.display='none'; document.getElementById('payment_card').style.display='block';" style="display: block; width: 100%; background: var(--primary); color: white; padding: 16px; border-radius: 16px; font-weight: 800; font-size: 1.1rem; border: none; cursor: pointer; box-shadow: 0 4px 12px var(--primary-glow); transition: 0.2s;">
                สั่งซื้อคอร์สนี้
            </button>
        </div>

        <!-- Payment UI (Hidden initially) -->
        <div style="display: none; background: white; border-radius: 20px; padding: 24px; box-shadow: var(--shadow-sm); text-align: left;" id="payment_card">
            <h2 style="font-size: 1.3rem; font-weight: 800; margin-bottom: 16px; color: #1E293B; text-align: center;">ชำระเงินและแนบสลิป</h2>
            
            <!-- Coupon Section -->
            <div style="margin-bottom: 20px; padding: 16px; background: #FEFCE8; border-radius: 12px; border: 1px solid #FDE68A;">
                <label style="display: block; font-weight: 700; margin-bottom: 8px; color: #92400E; font-size: 0.9rem;"><i class="fa-solid fa-ticket"></i> มีคูปองส่วนลด?</label>
                <form method="POST" style="display: flex; gap: 8px;">
                    <input type="text" name="coupon_code" value="<?= htmlspecialchars($couponCode) ?>" placeholder="กรอกรหัสคูปอง" style="flex: 1; padding: 10px; border: 1px solid #FDE68A; border-radius: 8px; outline: none; font-size: 0.9rem;">
                    <button type="submit" name="apply_coupon" value="1" style="background: #F59E0B; color: white; border: none; padding: 10px 16px; border-radius: 8px; font-weight: 700; cursor: pointer; white-space: nowrap;">ใช้คูปอง</button>
                </form>
            </div>

            <!-- Price Display -->
            <div style="text-align: center; margin-bottom: 20px;">
                <?php if ($couponDiscount > 0): ?>
                    <div style="font-size: 1rem; color: #94A3B8; text-decoration: line-through; margin-bottom: 4px;">฿<?= number_format($course['price'], 2) ?></div>
                    <div style="font-size: 1.8rem; font-weight: 900; color: #16A34A;">฿<?= number_format($finalPrice, 2) ?></div>
                    <div style="font-size: 0.8rem; color: #16A34A; font-weight: 700;">ประหยัด ฿<?= number_format($couponDiscount, 2) ?></div>
                <?php else: ?>
                    <div style="font-size: 1.8rem; font-weight: 900; color: var(--primary);">ยอดชำระ: ฿<?= number_format($finalPrice, 2) ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Payment Methods -->
            <div style="margin-bottom: 20px;">
                <?php foreach($payment_methods as $pm): ?>
                <div style="border: 1px solid var(--border); border-radius: 12px; padding: 15px; margin-bottom: 12px; background: #F8FAFC;">
                    <div style="font-weight: 800; color: #1E293B; margin-bottom: 4px; font-size: 1rem;"><?= htmlspecialchars($pm['bank_name']) ?></div>
                    <div style="font-weight: 600; font-size: 1.1rem; color: var(--primary); margin-bottom: 4px;"><?= htmlspecialchars($pm['account_number']) ?></div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">ชื่อบัญชี: <?= htmlspecialchars($pm['account_name']) ?></div>
                    <?php if($pm['qr_code_image']): ?>
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px dashed var(--border); text-align: center;">
                            <img src="../<?= $pm['qr_code_image'] ?>" alt="QR" style="width: 140px; height: 140px; border-radius: 8px; border: 1px solid var(--border); background: white; padding: 5px;">
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if(empty($payment_methods)): ?>
                    <p style="text-align: center; color: #EF4444; font-weight: 600;">*ไม่มีช่องทางการชำระเงิน กรุณาติดต่อแอดมิน*</p>
                <?php endif; ?>
            </div>
            
            <!-- Slip Upload Form -->
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="used_coupon_code" value="<?= htmlspecialchars($couponCode) ?>">
                <input type="hidden" name="paid_price" value="<?= $finalPrice ?>">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 10px; color: #1E293B;">แนบสลิปโอนเงิน</label>
                    <div style="border: 2px dashed var(--border); border-radius: 12px; padding: 30px 20px; text-align: center; background: #FAFAFD; cursor: pointer; position: relative; overflow: hidden;">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2rem; color: var(--text-muted); margin-bottom: 10px;" id="upload_icon"></i>
                        <div style="font-weight: 600; color: var(--text-muted);" id="upload_text">คลิกเพื่อเลือกไฟล์ หรือลากสลิปมาที่นี่</div>
                        <input type="file" name="slip" accept="image/*" required style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;" onchange="document.getElementById('upload_text').innerText = this.files[0] ? this.files[0].name : 'คลิกเพื่อเลือกไฟล์'; document.getElementById('upload_icon').style.color = 'var(--primary)';">
                    </div>
                </div>
                
                <button type="submit" style="width: 100%; background: var(--primary); color: white; padding: 16px; border-radius: 16px; font-weight: 800; font-size: 1.1rem; border: none; cursor: pointer; box-shadow: 0 4px 12px var(--primary-glow); transition: 0.2s;">
                    ยืนยันการชำระเงิน
                </button>
            </form>
            <button onclick="document.getElementById('payment_card').style.display='none'; document.getElementById('buy_card').style.display='block';" style="width: 100%; background: transparent; color: var(--text-muted); padding: 12px; border-radius: 16px; font-weight: 700; font-size: 1rem; border: none; cursor: pointer; margin-top: 10px;">
                ยกเลิก
            </button>
        </div>

    <?php elseif ($enrollment['status'] === 'pending'): ?>
        <!-- ══════════════════════════════════════════ -->
        <!-- PENDING APPROVAL                          -->
        <!-- ══════════════════════════════════════════ -->
        <div style="background: white; border-radius: 20px; padding: 24px; box-shadow: var(--shadow-sm); text-align: center;">
            <div style="background: #FEF3C7; width: 100px; height: 100px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 20px auto; color: #D97706; font-size: 2.5rem;">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 12px; color: #1E293B;">ส่งข้อมูลสำเร็จแล้ว!</h2>
            <p style="color: #64748B; margin-bottom: 15px;">ระบบได้รับสลิปของคุณเรียบร้อยแล้ว แอดมินกำลังตรวจสอบข้อมูลการชำระเงิน</p>
            <div style="background: #F1F5F9; padding: 15px; border-radius: 12px; font-size: 0.9rem; color: #1E293B; font-weight: 600; margin-bottom: 24px; border: 1px dashed var(--border);">
                💡 คุณสามารถตรวจสอบสถานะได้ที่ <a href="?page=profile" style="color: var(--primary); text-decoration: none;">หน้า Profile</a>
            </div>
            <a href="?page=classroom" style="text-decoration: none; background: #F1F5F9; color: #64748B; padding: 12px 24px; border-radius: 12px; font-weight: 700; display: inline-block;">กลับหน้าหลัก</a>
        </div>

    <?php elseif ($enrollment['status'] === 'approved'): ?>
        <!-- ══════════════════════════════════════════ -->
        <!-- APPROVED: Show Course Content             -->
        <!-- ══════════════════════════════════════════ -->
        <?php
        $episodes = $db->prepare("SELECT * FROM course_episodes WHERE course_id = ? ORDER BY episode_number ASC");
        $episodes->execute([$course_id]);
        $episodes = $episodes->fetchAll();

        $materials = $db->prepare("SELECT * FROM course_materials WHERE course_id = ? ORDER BY episode_number ASC");
        $materials->execute([$course_id]);
        $materials = $materials->fetchAll();

        $first_video = $episodes[0]['video_url'] ?? '';
        if (empty($first_video)) $first_video = 'dQw4w9WgXcQ';
        ?>

        <!-- Video Player -->
        <div style="position: relative; width: 100%; aspect-ratio: 16/9; background: #000; border-radius: 20px; margin-bottom: 24px; overflow: hidden; box-shadow: var(--shadow-sm);" id="video-container">
            <iframe id="videoPlayer" width="100%" height="100%" src="https://www.youtube.com/embed/<?= htmlspecialchars($first_video) ?>?rel=0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen style="position: absolute; top: 0; left: 0;"></iframe>
        </div>

        <!-- Course Info -->
        <div style="margin-bottom: 24px; padding: 0 4px;">
            <h1 style="font-size: 1.4rem; font-weight: 800; color: #1E293B; margin-bottom: 8px; font-family: var(--font-display);"><?= htmlspecialchars($title) ?></h1>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div style="font-size: 0.9rem; color: #94A3B8; font-weight: 600;">By <?= htmlspecialchars($instructor) ?></div>
                <div style="font-size: 0.85rem; color: #94A3B8; font-weight: 600;"><?= count($episodes) ?> EP</div>
            </div>
        </div>

        <!-- Tabs -->
        <div style="display: flex; background: #F1F5F9; border-radius: 16px; padding: 4px; margin-bottom: 24px;">
            <div id="tab-btn-playlist" onclick="switchTab('playlist')" style="flex: 1; text-align: center; padding: 12px; background: white; border-radius: 12px; font-weight: 700; color: var(--primary); font-size: 0.9rem; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: all 0.3s ease;">
                Playlist (<?= count($episodes) ?>)
            </div>
            <div id="tab-btn-sheet" onclick="switchTab('sheet')" style="flex: 1; text-align: center; padding: 12px; border-radius: 12px; font-weight: 700; color: #94A3B8; font-size: 0.9rem; cursor: pointer; transition: all 0.3s ease;">
                Sheet เรียน
            </div>
            <div id="tab-btn-details" onclick="switchTab('details')" style="flex: 1; text-align: center; padding: 12px; border-radius: 12px; font-weight: 700; color: #94A3B8; font-size: 0.9rem; cursor: pointer; transition: all 0.3s ease;">
                Details
            </div>
        </div>

        <!-- Content: Playlist Tab -->
        <div id="content-playlist" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 32px;">
            <?php foreach ($episodes as $ep): ?>
                <div class="ep-item" data-video="<?= htmlspecialchars($ep['video_url']) ?>" onclick="<?= $ep['video_url'] ? "playVideo('" . htmlspecialchars($ep['video_url']) . "', this)" : '' ?>" style="background: white; border-radius: 16px; padding: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-sm); cursor: pointer; transition: all 0.2s;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 44px; height: 44px; background: #F8FAFC; border-radius: 12px; display: flex; justify-content: center; align-items: center; font-weight: 800; color: #1E293B; font-family: var(--font-display);"><?= $ep['episode_number'] ?></div>
                        <div>
                            <div style="font-weight: 800; font-size: 0.95rem; color: #1E293B; font-family: var(--font-display); margin-bottom: 4px;"><?= htmlspecialchars($ep['title']) ?></div>
                            <div style="font-size: 0.75rem; color: #94A3B8; font-weight: 600;"><?= htmlspecialchars($ep['duration']) ?></div>
                        </div>
                    </div>
                    <div style="width: 36px; height: 36px; background: var(--primary); border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; box-shadow: 0 4px 10px var(--primary-glow);">
                        <i class="fa-solid fa-play" style="color: white; font-size: 0.8rem; margin-left: 2px;"></i>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($episodes)): ?>
                <p style="text-align: center; color: #94A3B8; margin-top: 20px;">ยังไม่มี EP ในคอร์สนี้</p>
            <?php endif; ?>
        </div>

        <!-- Content: Sheet Tab -->
        <div id="content-sheet" style="display: none; flex-direction: column; gap: 12px; margin-bottom: 32px;">
            <h3 style="font-size: 1.1rem; font-family: var(--font-display); color: #1E293B; margin-bottom: 8px; padding-left: 4px;">เอกสารประกอบการเรียน</h3>
            
            <?php foreach ($materials as $mat): ?>
            <div style="background: white; border-radius: 16px; padding: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 44px; height: 44px; background: #FFE4E6; border-radius: 12px; display: flex; justify-content: center; align-items: center; font-size: 1.2rem; color: #EF4444;">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 0.95rem; color: #1E293B; font-family: var(--font-display); margin-bottom: 4px;"><?= htmlspecialchars($mat['title']) ?></div>
                        <div style="font-size: 0.75rem; color: #94A3B8; font-weight: 600;">EP <?= $mat['episode_number'] ?> • <?= $mat['size_mb'] ?> MB</div>
                    </div>
                </div>
                <a href="<?= SITE_URL ?>/<?= htmlspecialchars($mat['file_url']) ?>" download="<?= htmlspecialchars($mat['title']) ?>.pdf" target="_blank" style="background: #F1F5F9; color: #1E293B; padding: 8px 12px; border-radius: 8px; font-weight: 700; font-size: 0.8rem; text-decoration: none;">Download</a>
            </div>
            <?php endforeach; ?>

            <?php if (empty($materials)): ?>
                <p style="text-align: center; color: #94A3B8; margin-top: 20px;">ยังไม่มีเอกสารในคอร์สนี้</p>
            <?php endif; ?>
        </div>

        <!-- Content: Details Tab -->
        <div id="content-details" style="display: none; flex-direction: column; gap: 12px; margin-bottom: 32px; padding: 16px; background: white; border-radius: 16px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-size: 1.1rem; font-family: var(--font-display); color: #1E293B; margin-bottom: 8px;">รายละเอียดคอร์สเรียน</h3>
            <p style="font-size: 0.9rem; color: #64748B; line-height: 1.6; white-space: pre-wrap;"><?= !empty($course['description']) ? htmlspecialchars($course['description']) : 'คอร์สเรียนนี้ถูกออกแบบมาเพื่อช่วยให้นักเรียนสามารถทำความเข้าใจเนื้อหาได้อย่างเป็นระบบ' ?></p>
        </div>

    <?php elseif ($enrollment['status'] === 'rejected'): ?>
        <!-- ══════════════════════════════════════════ -->
        <!-- REJECTED                                  -->
        <!-- ══════════════════════════════════════════ -->
        <div style="background: white; border-radius: 20px; padding: 24px; box-shadow: var(--shadow-sm); text-align: center;">
            <div style="background: #FEE2E2; width: 100px; height: 100px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 20px auto; color: #DC2626; font-size: 2.5rem;">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 12px; color: #1E293B;">การชำระเงินถูกปฏิเสธ</h2>
            <p style="color: #64748B; margin-bottom: 24px;">สลิปที่ส่งมาไม่ผ่านการตรวจสอบ กรุณาลองใหม่อีกครั้ง</p>
            <button onclick="window.location.href='?page=classroom-view&id=<?= $course_id ?>'" style="background: var(--primary); color: white; padding: 12px 24px; border-radius: 12px; font-weight: 700; border: none; cursor: pointer;">ลองใหม่อีกครั้ง</button>
        </div>
    <?php endif; ?>

</div>

<!-- Script for tab switching and video playing -->
<script>
function switchTab(tabName) {
    ['playlist', 'sheet', 'details'].forEach(name => {
        const btn = document.getElementById('tab-btn-' + name);
        const content = document.getElementById('content-' + name);
        if (name === tabName) {
            btn.style.background = 'white';
            btn.style.color = 'var(--primary)';
            btn.style.boxShadow = '0 2px 4px rgba(0,0,0,0.02)';
            content.style.display = 'flex';
            content.classList.add('animate-fade-in');
        } else {
            btn.style.background = 'transparent';
            btn.style.color = '#94A3B8';
            btn.style.boxShadow = 'none';
            content.style.display = 'none';
        }
    });
}

function playVideo(videoId, el) {
    if (!videoId) return;
    const player = document.getElementById('videoPlayer');
    player.src = 'https://www.youtube.com/embed/' + videoId + '?rel=0&autoplay=1';
    
    // Highlight active episode
    document.querySelectorAll('.ep-item').forEach(item => {
        item.style.borderLeft = 'none';
    });
    el.style.borderLeft = '4px solid var(--primary)';
    
    // Scroll to video
    document.getElementById('video-container').scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<style>
.ep-item:not([style*="opacity: 0.6"]):hover {
    transform: translateX(4px);
    box-shadow: var(--shadow) !important;
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
