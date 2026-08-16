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
    echo "<div style='padding:60px 20px; text-align:center;'><h2 style='color:#64748B;'>Course not found</h2></div>";
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
$uploadDir = __DIR__ . '/../../uploads/slips/'; // Remains unchanged for processing

// Handle Coupon Apply
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
            $slip_path = 'linguamax/uploads/slips/' . $fileName; // Path to save in DB
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
                    $stmt->execute([$user_id, $course_id, 'pending', $slip_path]);
                } else {
                    $stmt = $db->prepare("UPDATE course_enrollments SET status = 'pending', payment_slip_url = ? WHERE user_id = ? AND course_id = ?");
                    $stmt->execute([$slip_path, $user_id, $course_id]);
                }
                
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
        $errorMsg = "กรุณาแนบสลิปโอนเงิน";
    }
}

if (isset($_GET['success'])) {
    $successMsg = "ส่งข้อมูลการชำระเงินเรียบร้อยแล้ว กำลังรอแอดมินตรวจสอบครับ";
}
?>

<div class="animate-fade-in" style="padding: 24px 16px 100px 16px; background: #FAFAFD; min-height: 100vh; max-width: 1200px; margin: 0 auto;">
    
    <!-- Header Navigation -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <a href="?page=classroom" style="display: flex; justify-content: center; align-items: center; width: 44px; height: 44px; border-radius: 50%; background: white; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); color: #1E293B; text-decoration: none; transition: all 0.2s;" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div style="font-weight: 900; font-size: 1.25rem; color: #1E293B; font-family: var(--font-display); text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 70%;"><?= htmlspecialchars($title) ?></div>
        <div style="width: 44px;"></div>
    </div>

    <!-- Alert Messages -->
    <?php if ($errorMsg): ?>
    <div style="background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; padding: 16px; border-radius: 16px; margin-bottom: 24px; font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.1);">
        <i class="fa-solid fa-circle-exclamation" style="font-size: 1.2rem;"></i> <?= htmlspecialchars($errorMsg) ?>
    </div>
    <?php endif; ?>

    <?php if ($successMsg && (!$enrollment || $enrollment['status'] !== 'approved')): ?>
    <div style="background: #F0FDF4; color: #16A34A; border: 1px solid #BBF7D0; padding: 16px; border-radius: 16px; margin-bottom: 24px; font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 6px -1px rgba(22, 163, 74, 0.1);">
        <i class="fa-solid fa-circle-check" style="font-size: 1.2rem;"></i> <?= htmlspecialchars($successMsg) ?>
    </div>
    <?php endif; ?>

    <?php if (!$enrollment): ?>
        <!-- ========================================== -->
        <!-- NOT ENROLLED: Course Preview & Buy Option  -->
        <!-- ========================================== -->
        <div style="display: grid; grid-template-columns: 1fr; gap: 24px; align-items: start;" class="course-layout">
            
            <!-- Left: Course Banner & Info -->
            <div style="background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); border: 1px solid rgba(226, 232, 240, 0.8);">
                <div style="width: 100%; height: 260px; background: linear-gradient(135deg, #E0E7FF, #C7D2FE); position: relative;">
                    <?php if ($course['image_url']): ?>
                        <img src="<?= SITE_URL ?>/<?= htmlspecialchars($course['image_url']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color: #6366F1; font-size: 4rem; font-weight: 900; font-family: var(--font-display);"><?= mb_substr($subject, 0, 1) ?></div>
                    <?php endif; ?>
                </div>
                <div style="padding: 32px 24px;">
                    <div style="display: inline-block; font-size: 0.8rem; font-weight: 800; color: #4F46E5; background: #E0E7FF; padding: 6px 12px; border-radius: 8px; text-transform: uppercase; margin-bottom: 12px; letter-spacing: 0.5px;"><?= htmlspecialchars($subject) ?></div>
                    <h2 style="font-size: 1.8rem; font-weight: 900; color: #1E293B; margin-bottom: 12px; line-height: 1.3; font-family: var(--font-display);"><?= htmlspecialchars($title) ?></h2>
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.95rem; color: #64748B; font-weight: 600; margin-bottom: 24px;">
                        <i class="fa-solid fa-user-tie"></i> สอนโดย <?= htmlspecialchars($instructor) ?>
                    </div>
                    <?php if ($course['description']): ?>
                        <div style="padding-top: 20px; border-top: 1px solid #F1F5F9;">
                            <h3 style="font-size: 1.1rem; font-weight: 800; color: #1E293B; margin-bottom: 12px;">รายละเอียดคอร์สเรียน</h3>
                            <p style="font-size: 1rem; color: #475569; line-height: 1.7;"><?= nl2br(htmlspecialchars($course['description'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: Buy Card & Payment -->
            <div style="position: sticky; top: 24px;">
                <!-- Buy Card -->
                <div id="buy_card" style="background: white; border-radius: 24px; padding: 32px 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border: 1px solid rgba(226, 232, 240, 0.8); text-align: center;">
                    <div style="background: linear-gradient(135deg, #EEF2FF, #E0E7FF); width: 88px; height: 88px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 20px auto; color: #4F46E5; font-size: 2.2rem; box-shadow: inset 0 2px 4px rgba(255,255,255,0.5);">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <h2 style="font-size: 1.5rem; font-weight: 900; margin-bottom: 12px; color: #1E293B; font-family: var(--font-display);">คอร์สเรียนถูกล็อค</h2>
                    <p style="color: #64748B; margin-bottom: 24px; font-size: 1rem; line-height: 1.5;">ซื้อคอร์สเรียนเพื่อปลดล็อคการเข้าถึงวิดีโอและดาวน์โหลดเอกสารประกอบการเรียนทั้งหมด</p>
                    
                    <div style="font-size: 2.5rem; font-weight: 900; color: #4F46E5; margin-bottom: 32px; font-family: var(--font-display);">
                        <?= $course['price'] > 0 ? '฿' . number_format($course['price'], 2) : 'เรียนฟรี!' ?>
                    </div>
                    
                    <button onclick="document.getElementById('buy_card').style.display='none'; document.getElementById('payment_card').style.display='block';" style="display: block; width: 100%; background: linear-gradient(135deg, #4F46E5, #6366F1); color: white; padding: 18px; border-radius: 16px; font-weight: 800; font-size: 1.15rem; border: none; cursor: pointer; box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3); transition: all 0.3s;" class="hover-scale">
                        สมัครเรียนคอร์สนี้
                    </button>
                </div>

                <!-- Payment UI (Hidden initially) -->
                <div id="payment_card" style="display: none; background: white; border-radius: 24px; padding: 32px 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); border: 1px solid rgba(226, 232, 240, 0.8);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3 style="font-size: 1.3rem; font-weight: 900; color: #1E293B; font-family: var(--font-display);">ช่องทางการชำระเงิน</h3>
                        <button type="button" onclick="document.getElementById('payment_card').style.display='none'; document.getElementById('buy_card').style.display='block';" style="background: none; border: none; color: #94A3B8; cursor: pointer; font-size: 1.2rem;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <?php if (empty($payment_methods)): ?>
                        <div style="background: #F8FAFC; padding: 20px; border-radius: 16px; text-align: center; color: #64748B;">
                            ยังไม่มีช่องทางการชำระเงิน กรุณาติดต่อแอดมิน
                        </div>
                    <?php else: ?>
                        <!-- Coupon Form -->
                        <div style="margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px dashed #E2E8F0;">
                            <form method="POST" style="display: flex; gap: 8px;">
                                <input type="hidden" name="apply_coupon" value="1">
                                <input type="text" name="coupon_code" placeholder="โค้ดส่วนลด (ถ้ามี)" value="<?= htmlspecialchars($couponCode) ?>" style="flex: 1; padding: 12px 16px; border: 1px solid var(--border); border-radius: 12px; outline: none; font-size: 0.95rem; background: #F8FAFC;">
                                <button type="submit" style="background: #1E293B; color: white; border: none; padding: 0 20px; border-radius: 12px; font-weight: 700; cursor: pointer;">ใช้โค้ด</button>
                            </form>
                        </div>

                        <!-- Price Summary -->
                        <div style="background: #F8FAFC; padding: 20px; border-radius: 16px; margin-bottom: 24px; border: 1px solid #E2E8F0;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: #64748B; font-size: 0.95rem;">
                                <span>ราคาคอร์ส</span>
                                <span>฿<?= number_format($course['price'], 2) ?></span>
                            </div>
                            <?php if ($couponDiscount > 0): ?>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: #10B981; font-weight: 700; font-size: 0.95rem;">
                                <span>ส่วนลด</span>
                                <span>-฿<?= number_format($couponDiscount, 2) ?></span>
                            </div>
                            <?php endif; ?>
                            <div style="display: flex; justify-content: space-between; margin-top: 12px; padding-top: 12px; border-top: 1px dashed #CBD5E1; color: #1E293B; font-weight: 900; font-size: 1.25rem;">
                                <span>ยอดชำระสุทธิ</span>
                                <span style="color: #4F46E5;">฿<?= number_format($finalPrice, 2) ?></span>
                            </div>
                        </div>

                        <!-- Bank Details -->
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="used_coupon_code" value="<?= htmlspecialchars($couponCode) ?>">
                            <input type="hidden" name="paid_price" value="<?= $finalPrice ?>">
                            
                            <h4 style="font-size: 1rem; font-weight: 800; color: #1E293B; margin-bottom: 12px;">โอนเงินเข้าบัญชีด้านล่างนี้:</h4>
                            <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
                                <?php foreach ($payment_methods as $pm): ?>
                                    <div style="border: 2px solid #E2E8F0; border-radius: 16px; padding: 16px; background: white;">
                                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: <?php echo $pm['qr_code_image'] ? '16px' : '0'; ?>;">
                                            <div style="width: 48px; height: 48px; background: #EEF2FF; border-radius: 12px; display: flex; justify-content: center; align-items: center; color: #4F46E5; font-size: 1.5rem;">
                                                <i class="fa-solid fa-building-columns"></i>
                                            </div>
                                            <div>
                                                <div style="font-weight: 800; color: #1E293B; font-size: 1.05rem;"><?= htmlspecialchars($pm['bank_name']) ?></div>
                                                <div style="color: #64748B; font-size: 0.95rem; font-family: monospace; letter-spacing: 0.5px;"><?= htmlspecialchars($pm['account_number']) ?></div>
                                                <div style="color: #94A3B8; font-size: 0.85rem;"><?= htmlspecialchars($pm['account_name']) ?></div>
                                            </div>
                                        </div>
                                        <?php if ($pm['qr_code_image']): ?>
                                            <div style="text-align: center; border-top: 1px dashed #E2E8F0; padding-top: 16px;">
                                                <img src="<?= SITE_URL ?>/<?= htmlspecialchars($pm['qr_code_image']) ?>" style="width: 140px; border-radius: 12px; border: 1px solid #E2E8F0; padding: 4px; background: white;">
                                                <div style="font-size: 0.8rem; color: #94A3B8; margin-top: 8px; font-weight: 600;">สแกนเพื่อจ่ายเงิน</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Upload Slip -->
                            <div style="margin-bottom: 24px;">
                                <label style="display: block; font-weight: 800; color: #1E293B; margin-bottom: 12px;">แนบสลิปโอนเงิน <span style="color: #EF4444;">*</span></label>
                                <input type="file" name="slip" required accept="image/*" style="width: 100%; padding: 12px; border: 2px dashed #CBD5E1; border-radius: 16px; background: #F8FAFC; cursor: pointer; font-size: 0.95rem;">
                            </div>

                            <button type="submit" style="width: 100%; background: linear-gradient(135deg, #10B981, #059669); color: white; padding: 16px; border-radius: 16px; font-weight: 800; font-size: 1.1rem; border: none; cursor: pointer; box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3); transition: all 0.3s;" class="hover-scale">
                                แจ้งชำระเงิน
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php elseif ($enrollment['status'] === 'pending'): ?>
        <!-- ========================================== -->
        <!-- PENDING APPROVAL                           -->
        <!-- ========================================== -->
        <div style="background: white; border-radius: 24px; padding: 60px 24px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); text-align: center; max-width: 600px; margin: 40px auto; border: 1px solid rgba(226, 232, 240, 0.8);">
            <div style="background: linear-gradient(135deg, #FEF3C7, #FDE68A); width: 100px; height: 100px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 24px auto; color: #D97706; font-size: 3rem; box-shadow: inset 0 2px 4px rgba(255,255,255,0.5);">
                <i class="fa-solid fa-hourglass-half fa-spin-pulse"></i>
            </div>
            <h2 style="font-size: 1.8rem; font-weight: 900; margin-bottom: 12px; color: #1E293B; font-family: var(--font-display);">กำลังรอตรวจสอบ</h2>
            <p style="color: #64748B; margin-bottom: 32px; font-size: 1.05rem; line-height: 1.6;">ระบบได้รับสลิปการโอนเงินเรียบร้อยแล้ว<br>กรุณารอแอดมินตรวจสอบข้อมูลและอนุมัติเข้าเรียนภายใน 24 ชั่วโมงครับ</p>
            <button onclick="window.location.href='?page=classroom'" style="background: #F1F5F9; color: #475569; padding: 14px 28px; border-radius: 14px; font-weight: 800; border: none; cursor: pointer; transition: all 0.2s;" class="hover-bg-gray">
                กลับไปหน้าคอร์สเรียน
            </button>
        </div>

    <?php elseif ($enrollment['status'] === 'rejected'): ?>
        <!-- ========================================== -->
        <!-- REJECTED                                   -->
        <!-- ========================================== -->
        <div style="background: white; border-radius: 24px; padding: 60px 24px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); text-align: center; max-width: 600px; margin: 40px auto; border: 1px solid rgba(226, 232, 240, 0.8);">
            <div style="background: linear-gradient(135deg, #FEE2E2, #FECACA); width: 100px; height: 100px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 24px auto; color: #DC2626; font-size: 3rem; box-shadow: inset 0 2px 4px rgba(255,255,255,0.5);">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <h2 style="font-size: 1.8rem; font-weight: 900; margin-bottom: 12px; color: #1E293B; font-family: var(--font-display);">การชำระเงินถูกปฏิเสธ</h2>
            <p style="color: #64748B; margin-bottom: 32px; font-size: 1.05rem; line-height: 1.6;">สลิปที่ส่งมาไม่ผ่านการตรวจสอบจากระบบ<br>กรุณาตรวจสอบความถูกต้องและแจ้งชำระเงินใหม่อีกครั้ง</p>
            
            <form method="POST">
                <input type="hidden" name="delete_enrollment" value="1">
                <button type="submit" style="background: linear-gradient(135deg, #EF4444, #DC2626); color: white; padding: 14px 28px; border-radius: 14px; font-weight: 800; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3); transition: all 0.2s;" class="hover-scale">
                    ลองชำระเงินใหม่อีกครั้ง
                </button>
            </form>
            <?php
            // Simple logic to delete rejected enrollment so user can try again
            if (isset($_POST['delete_enrollment'])) {
                $db->prepare("DELETE FROM course_enrollments WHERE user_id = ? AND course_id = ?")->execute([$user_id, $course_id]);
                echo "<script>window.location.href='?page=classroom-view&id=$course_id';</script>";
                exit;
            }
            ?>
        </div>

    <?php else: ?>
        <!-- ========================================== -->
        <!-- ENROLLED: Show Video & Materials           -->
        <!-- ========================================== -->
        <?php
        $episodes = $db->prepare("SELECT * FROM course_episodes WHERE course_id = ? ORDER BY episode_number ASC");
        $episodes->execute([$course_id]);
        $episodes = $episodes->fetchAll();

        $materials = $db->prepare("SELECT * FROM course_materials WHERE course_id = ? ORDER BY episode_number ASC");
        $materials->execute([$course_id]);
        $materials = $materials->fetchAll();
        
        $firstVideo = count($episodes) > 0 ? $episodes[0]['video_url'] : '';
        ?>

        <!-- Video Player -->
        <div id="video-container" style="background: black; border-radius: 24px; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); margin-bottom: 32px; aspect-ratio: 16/9; position: relative;">
            <?php if ($firstVideo): ?>
                <iframe id="videoPlayer" src="https://www.youtube.com/embed/<?= htmlspecialchars($firstVideo) ?>?rel=0&modestbranding=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="width: 100%; height: 100%; position: absolute; top: 0; left: 0;"></iframe>
            <?php else: ?>
                <div style="width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #475569;">
                    <i class="fa-solid fa-video-slash" style="font-size: 4rem; margin-bottom: 16px;"></i>
                    <h3 style="font-size: 1.2rem; font-family: var(--font-display);">ยังไม่มีวิดีโอในคอร์สนี้</h3>
                </div>
            <?php endif; ?>
        </div>

        <!-- Custom Tabs -->
        <div style="display: flex; gap: 12px; margin-bottom: 24px; overflow-x: auto; padding-bottom: 8px;" class="hide-scrollbar">
            <button id="tab-btn-playlist" onclick="switchTab('playlist')" style="background: white; color: #4F46E5; padding: 12px 24px; border-radius: 14px; font-weight: 800; font-size: 0.95rem; border: none; cursor: pointer; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transition: all 0.2s; flex-shrink: 0;">
                <i class="fa-solid fa-list" style="margin-right: 6px;"></i> บทเรียน (<?= count($episodes) ?>)
            </button>
            <button id="tab-btn-sheet" onclick="switchTab('sheet')" style="background: transparent; color: #64748B; padding: 12px 24px; border-radius: 14px; font-weight: 800; font-size: 0.95rem; border: none; cursor: pointer; transition: all 0.2s; flex-shrink: 0;">
                <i class="fa-solid fa-file-pdf" style="margin-right: 6px;"></i> เอกสาร Sheet (<?= count($materials) ?>)
            </button>
            <button id="tab-btn-details" onclick="switchTab('details')" style="background: transparent; color: #64748B; padding: 12px 24px; border-radius: 14px; font-weight: 800; font-size: 0.95rem; border: none; cursor: pointer; transition: all 0.2s; flex-shrink: 0;">
                <i class="fa-solid fa-circle-info" style="margin-right: 6px;"></i> รายละเอียด
            </button>
        </div>

        <!-- Content: Playlist Tab -->
        <div id="content-playlist" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 32px;" class="animate-fade-in">
            <?php foreach ($episodes as $index => $ep): ?>
            <div class="ep-item" data-video="<?= htmlspecialchars($ep['video_url']) ?>" onclick="playVideo('<?= $ep['video_url'] ?>', this)" style="background: white; border-radius: 16px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; border: 1px solid rgba(226, 232, 240, 0.8); transition: all 0.2s; <?= $index === 0 ? 'border-left: 4px solid #4F46E5;' : '' ?>">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 48px; height: 48px; background: #EEF2FF; border-radius: 14px; display: flex; justify-content: center; align-items: center; font-weight: 900; color: #4F46E5; font-size: 1.1rem; font-family: var(--font-display);">
                        <?= $ep['episode_number'] ?>
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 1.05rem; color: #1E293B; margin-bottom: 4px;"><?= htmlspecialchars($ep['title']) ?></div>
                        <div style="font-size: 0.8rem; color: #64748B; font-weight: 600;"><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($ep['duration']) ?> นาที</div>
                    </div>
                </div>
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #F8FAFC; display: flex; justify-content: center; align-items: center; color: #4F46E5; border: 1px solid #E2E8F0; transition: all 0.2s;" class="play-btn">
                    <i class="fa-solid fa-play" style="margin-left: 2px;"></i>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($episodes)): ?>
                <div style="text-align: center; padding: 40px 20px; background: white; border-radius: 20px; border: 1px dashed #CBD5E1;">
                    <i class="fa-solid fa-video-slash" style="font-size: 3rem; color: #CBD5E1; margin-bottom: 16px;"></i>
                    <p style="color: #94A3B8; font-size: 1.05rem;">ยังไม่มีบทเรียนในคอร์สนี้</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Content: Sheet Tab -->
        <div id="content-sheet" style="display: none; flex-direction: column; gap: 12px; margin-bottom: 32px;" class="animate-fade-in">
            <?php foreach ($materials as $mat): ?>
            <div style="background: white; border-radius: 16px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(226, 232, 240, 0.8);">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 48px; height: 48px; background: #FEF2F2; border-radius: 14px; display: flex; justify-content: center; align-items: center; font-size: 1.5rem; color: #EF4444;">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 1.05rem; color: #1E293B; margin-bottom: 4px;"><?= htmlspecialchars($mat['title']) ?></div>
                        <div style="font-size: 0.8rem; color: #64748B; font-weight: 600;"><i class="fa-solid fa-hashtag"></i> EP <?= $mat['episode_number'] ?> • <?= $mat['size_mb'] ?> MB</div>
                    </div>
                </div>
                <a href="?page=download&id=<?= $mat['id'] ?>" target="_blank" style="background: #EEF2FF; color: #4F46E5; padding: 10px 20px; border-radius: 12px; font-weight: 800; font-size: 0.9rem; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: all 0.2s;" class="hover-bg-indigo">
                    <i class="fa-solid fa-download"></i> Download
                </a>
            </div>
            <?php endforeach; ?>

            <?php if (empty($materials)): ?>
                <div style="text-align: center; padding: 40px 20px; background: white; border-radius: 20px; border: 1px dashed #CBD5E1;">
                    <i class="fa-solid fa-file-pdf" style="font-size: 3rem; color: #CBD5E1; margin-bottom: 16px;"></i>
                    <p style="color: #94A3B8; font-size: 1.05rem;">ยังไม่มีเอกสารประกอบการเรียน</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Content: Details Tab -->
        <div id="content-details" style="display: none; background: white; border-radius: 24px; padding: 32px 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid rgba(226, 232, 240, 0.8); margin-bottom: 32px;" class="animate-fade-in">
            <h3 style="font-size: 1.3rem; font-weight: 900; color: #1E293B; font-family: var(--font-display); margin-bottom: 16px;">รายละเอียดคอร์สเรียน</h3>
            <div style="display: flex; gap: 20px; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid #F1F5F9;">
                <div>
                    <div style="font-size: 0.8rem; color: #64748B; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">ผู้สอน</div>
                    <div style="font-weight: 800; color: #1E293B;"><i class="fa-solid fa-user-tie" style="color: #4F46E5; margin-right: 6px;"></i> <?= htmlspecialchars($instructor) ?></div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: #64748B; font-weight: 700; text-transform: uppercase; margin-bottom: 4px;">หมวดหมู่</div>
                    <div style="font-weight: 800; color: #1E293B;"><i class="fa-solid fa-tag" style="color: #4F46E5; margin-right: 6px;"></i> <?= htmlspecialchars($subject) ?></div>
                </div>
            </div>
            <p style="font-size: 1rem; color: #475569; line-height: 1.8; white-space: pre-wrap;"><?= !empty($course['description']) ? htmlspecialchars($course['description']) : 'ไม่มีรายละเอียดเพิ่มเติม' ?></p>
        </div>

    <?php endif; ?>

</div>

<style>
/* Hide Scrollbar */
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

/* Desktop layout adjustment for course preview */
@media (min-width: 768px) {
    .course-layout {
        grid-template-columns: 2fr 1fr;
    }
}

.back-btn:hover {
    background: #F1F5F9 !important;
    transform: scale(1.05);
}

.hover-scale:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 20px -5px rgba(0,0,0,0.15) !important;
}

.hover-bg-gray:hover {
    background: #E2E8F0 !important;
}

.hover-bg-indigo:hover {
    background: #4F46E5 !important;
    color: white !important;
}

.ep-item:hover {
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.ep-item:hover .play-btn {
    background: #4F46E5;
    color: white;
}
</style>

<!-- Script for tab switching and video playing -->
<script>
function switchTab(tabName) {
    ['playlist', 'sheet', 'details'].forEach(name => {
        const btn = document.getElementById('tab-btn-' + name);
        const content = document.getElementById('content-' + name);
        
        if (name === tabName) {
            btn.style.background = 'white';
            btn.style.color = '#4F46E5';
            btn.style.boxShadow = '0 4px 6px -1px rgba(0,0,0,0.05)';
            content.style.display = 'flex';
        } else {
            btn.style.background = 'transparent';
            btn.style.color = '#64748B';
            btn.style.boxShadow = 'none';
            content.style.display = 'none';
        }
    });
}

function playVideo(videoId, el) {
    if (!videoId) return;
    const player = document.getElementById('videoPlayer');
    player.src = 'https://www.youtube.com/embed/' + videoId + '?rel=0&autoplay=1&modestbranding=1';
    
    // Highlight active episode
    document.querySelectorAll('.ep-item').forEach(item => {
        item.style.borderLeft = '1px solid rgba(226, 232, 240, 0.8)';
    });
    el.style.borderLeft = '4px solid #4F46E5';
    
    // Scroll to video
    document.getElementById('video-container').scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
