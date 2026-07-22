<?php
// ============================================================
// LinguaMax — Student Classroom View (Dynamic)
// ============================================================
include __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../config/database.php';

$db = getDB();
$course_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 1; // Fallback to 1 for testing if no session

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

// Handle Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['slip'])) {
    if ($_FILES['slip']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION);
        $fileName = 'slip_' . time() . '_' . rand(100, 999) . '.' . $ext;
        
        if (move_uploaded_file($_FILES['slip']['tmp_name'], $uploadDir . $fileName)) {
            $slip_path = 'linguamax/uploads/slips/' . $fileName;
            
            try {
                $db->beginTransaction();
                // Insert Order
                $stmt = $db->prepare("INSERT INTO orders (user_id, course_id, price, slip_image, status) VALUES (?, ?, ?, ?, 'pending')");
                $stmt->execute([$user_id, $course_id, $course['price'], $slip_path]);
                
                // Insert Pending Enrollment
                $stmt = $db->prepare("SELECT id FROM course_enrollments WHERE user_id = ? AND course_id = ?");
                $stmt->execute([$user_id, $course_id]);
                if (!$stmt->fetch()) {
                    $stmt = $db->prepare("INSERT INTO course_enrollments (user_id, course_id, status, payment_slip_url) VALUES (?, ?, 'pending', ?)");
                    $stmt->execute([$user_id, $course_id, $slip_path]);
                } else {
                    $stmt = $db->prepare("UPDATE course_enrollments SET status = 'pending', payment_slip_url = ? WHERE user_id = ? AND course_id = ?");
                    $stmt->execute([$slip_path, $user_id, $course_id]);
                }
                $db->commit();
                
                echo "<script>window.location.href='?page=classroom-view&id=$course_id&success=1';</script>";
                exit;
            } catch (Exception $e) {
                $db->rollBack();
                $errorMsg = "Database error: " . $e->getMessage();
            }
        } else {
            $errorMsg = "Failed to upload slip to: " . $uploadDir;
        }
    } else {
        $errorMsg = "Please attach a valid payment slip. Upload Error Code: " . $_FILES['slip']['error'];
    }
}
?>

<div class="animate-fade-in" style="padding: 16px 4px 100px 4px; background: #FAFAFD; min-height: 100vh;">
    
    <!-- Top Bar -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <a href="?page=classroom" style="color: #1E293B; font-size: 1.5rem; text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div style="font-weight: 800; font-size: 1.15rem; color: #1E293B; font-family: var(--font-display);"><?= htmlspecialchars($title) ?></div>
        <i class="fa-regular fa-heart" style="font-size: 1.5rem; color: #1E293B; cursor: pointer;"></i>
    </div>

    <?php if ($errorMsg): ?>
    <div style="background: #FEE2E2; color: #DC2626; padding: 15px; border-radius: 12px; margin-bottom: 20px; font-weight: 600;">
        <?= htmlspecialchars($errorMsg) ?>
    </div>
    <?php endif; ?>

    <?php if (!$enrollment): ?>
        <!-- NOT PURCHASED: SHOW BUY UI -->
        <div style="background: white; border-radius: 20px; padding: 24px; box-shadow: var(--shadow-sm); text-align: center;" id="buy_card">
            <div style="background: var(--primary-light); width: 100px; height: 100px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 20px auto; color: white; font-size: 2.5rem;">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 12px; color: #1E293B;">เนื้อหานี้ถูกล็อค</h2>
            <p style="color: #64748B; margin-bottom: 24px;">คุณต้องซื้อคอร์สเรียนนี้ก่อนจึงจะสามารถเข้าดูวิดีโอและดาวน์โหลดเอกสารประกอบการเรียนได้</p>
            
            <div style="font-size: 2rem; font-weight: 900; color: var(--primary); margin-bottom: 30px;">฿<?= number_format($course['price'], 2) ?></div>
            
            <button onclick="document.getElementById('buy_card').style.display='none'; document.getElementById('payment_card').style.display='block';" style="display: block; width: 100%; background: var(--primary); color: white; padding: 16px; border-radius: 16px; font-weight: 800; font-size: 1.1rem; border: none; cursor: pointer; box-shadow: 0 4px 12px var(--primary-glow); transition: 0.2s;">
                สั่งซื้อคอร์สนี้
            </button>
        </div>

        <!-- PAYMENT UI (Hidden initially) -->
        <div style="display: none; background: white; border-radius: 20px; padding: 24px; box-shadow: var(--shadow-sm); text-align: left;" id="payment_card">
            <h2 style="font-size: 1.4rem; font-weight: 800; margin-bottom: 15px; color: #1E293B; text-align: center;">ชำระเงินและแนบสลิป</h2>
            
            <div style="font-size: 1.8rem; font-weight: 900; color: var(--primary); margin-bottom: 20px; text-align: center;">ยอดที่ต้องชำระ: ฿<?= number_format($course['price'], 2) ?></div>
            
            <div style="margin-bottom: 25px;">
                <?php foreach($payment_methods as $pm): ?>
                <div style="border: 1px solid var(--border); border-radius: 12px; padding: 15px; margin-bottom: 15px; background: #F8FAFC;">
                    <div style="font-weight: 800; color: #1E293B; margin-bottom: 5px; font-size: 1.1rem;"><?= htmlspecialchars($pm['bank_name']) ?></div>
                    <div style="font-weight: 600; font-size: 1.2rem; color: var(--primary); margin-bottom: 5px;"><?= htmlspecialchars($pm['account_number']) ?></div>
                    <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 10px;">ชื่อบัญชี: <?= htmlspecialchars($pm['account_name']) ?></div>
                    
                    <?php if($pm['qr_code_image']): ?>
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--border); text-align: center;">
                            <img src="../<?= $pm['qr_code_image'] ?>" alt="QR" style="width: 150px; height: 150px; border-radius: 8px; border: 1px solid var(--border); background: white; padding: 5px;">
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if(empty($payment_methods)): ?>
                    <p style="text-align: center; color: #EF4444;">*ไม่มีช่องทางการชำระเงิน กรุณาติดต่อแอดมิน*</p>
                <?php endif; ?>
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 700; margin-bottom: 10px; color: #1E293B;">แนบสลิปโอนเงิน</label>
                    <div style="border: 2px dashed var(--border); border-radius: 12px; padding: 30px 20px; text-align: center; background: #FAFAFD; cursor: pointer; position: relative; overflow: hidden;">
                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 2rem; color: var(--text-muted); margin-bottom: 10px;" id="upload_icon"></i>
                        <div style="font-weight: 600; color: var(--text-muted);" id="upload_text">คลิกเพื่อเลือกไฟล์ หรือลากสลิปมาที่นี่</div>
                        <input type="file" name="slip" accept="image/*" required style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 10;" onchange="document.getElementById('upload_text').innerText = this.files[0] ? this.files[0].name : 'คลิกเพื่อเลือกไฟล์ หรือลากสลิปมาที่นี่'; document.getElementById('upload_icon').style.color = 'var(--primary)';">
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
        <!-- PURCHASED BUT PENDING APPROVAL -->
        <div style="background: white; border-radius: 20px; padding: 24px; box-shadow: var(--shadow-sm); text-align: center;">
            <div style="background: #FEF3C7; width: 100px; height: 100px; border-radius: 50%; display: flex; justify-content: center; align-items: center; margin: 0 auto 20px auto; color: #D97706; font-size: 2.5rem;">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <h2 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 12px; color: #1E293B;">ส่งข้อมูลสำเร็จแล้ว!</h2>
            <p style="color: #64748B; margin-bottom: 15px;">ระบบได้รับสลิปของคุณเรียบร้อยแล้ว แอดมินกำลังตรวจสอบข้อมูลการชำระเงิน</p>
            
            <div style="background: #F1F5F9; padding: 15px; border-radius: 12px; font-size: 0.9rem; color: #1E293B; font-weight: 600; margin-bottom: 24px; border: 1px dashed var(--border);">
                💡 คุณสามารถตรวจสอบสถานะการ Approve ของคอร์สเรียนนี้ได้ที่ <a href="?page=profile" style="color: var(--primary); text-decoration: none;">หน้า Profile</a>
            </div>
            <a href="?page=classroom" class="btn btn-outline" style="text-decoration: none;">กลับหน้าหลัก</a>
        </div>

    <?php elseif ($enrollment['status'] === 'approved'): ?>
        <!-- PURCHASE APPROVED: SHOW COURSE CONTENT -->
        <?php
        // Fetch Episodes
        $stmt = $db->prepare("SELECT * FROM course_episodes WHERE course_id = ? ORDER BY episode_number ASC");
        $stmt->execute([$course_id]);
        $episodes = $stmt->fetchAll();

        // Fetch Materials
        $stmt = $db->prepare("SELECT * FROM course_materials WHERE course_id = ? ORDER BY episode_number ASC");
        $stmt->execute([$course_id]);
        $materials = $stmt->fetchAll();
        ?>

        <!-- Video Player -->
        <div style="position: relative; width: 100%; aspect-ratio: 16/9; background: #000; border-radius: 20px; margin-bottom: 24px; overflow: hidden; box-shadow: var(--shadow-sm);">
            <?php 
            $first_video = $episodes[0]['video_url'] ?? 'njX2bu-_Vw4';
            if (empty($first_video)) $first_video = 'njX2bu-_Vw4'; // default
            
            $is_mp4 = strpos(strtolower($first_video), '.mp4') !== false;
            ?>
            <?php if ($is_mp4): ?>
                <video width="100%" height="100%" controls style="position: absolute; top: 0; left: 0;">
                    <source src="<?= SITE_URL ?>/../<?= htmlspecialchars($first_video) ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            <?php else: ?>
                <iframe width="100%" height="100%" src="https://www.youtube.com/embed/<?= htmlspecialchars($first_video) ?>?rel=0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen style="position: absolute; top: 0; left: 0;"></iframe>
            <?php endif; ?>
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
            <div id="tab-btn-playlist" onclick="switchTab('playlist')" style="flex: 1; text-align: center; padding: 12px; background: white; border-radius: 12px; font-weight: 700; color: #FF7675; font-size: 0.9rem; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.02); transition: all 0.3s ease;">
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
                <div style="background: white; border-radius: 16px; padding: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-sm); <?= $ep['is_locked'] ? 'opacity: 0.7;' : '' ?>">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 44px; height: 44px; background: #F8FAFC; border-radius: 12px; display: flex; justify-content: center; align-items: center; font-weight: 800; color: #1E293B; font-family: var(--font-display);"><?= $ep['episode_number'] ?></div>
                        <div>
                            <div style="font-weight: 800; font-size: 0.95rem; color: #1E293B; font-family: var(--font-display); margin-bottom: 4px;"><?= htmlspecialchars($ep['title']) ?></div>
                            <div style="font-size: 0.75rem; color: #94A3B8; font-weight: 600;"><?= htmlspecialchars($ep['duration']) ?></div>
                        </div>
                    </div>
                    <?php if (!$ep['is_locked']): ?>
                        <div style="width: 36px; height: 36px; background: #FF7675; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; box-shadow: 0 4px 10px rgba(255,118,117,0.3);">
                            <i class="fa-solid fa-play" style="color: white; font-size: 0.8rem; margin-left: 2px;"></i>
                        </div>
                    <?php else: ?>
                        <div style="width: 36px; height: 36px; background: #F1F5F9; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                            <i class="fa-solid fa-lock" style="color: #94A3B8; font-size: 0.9rem;"></i>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <?php if (empty($episodes)): ?>
                <p style="text-align: center; color: #94A3B8; margin-top: 20px;">ยังไม่มี EP ในคอร์สนี้</p>
            <?php endif; ?>

            <!-- EP Final Exam -->
            <div style="background: linear-gradient(135deg, #FFF0F0, #FFFFFF); border-radius: 16px; padding: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-sm); border: 1px solid #FFE4E6; margin-top: 8px;">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 44px; height: 44px; background: #FF7675; border-radius: 12px; display: flex; justify-content: center; align-items: center; font-weight: 800; color: white; font-family: var(--font-display);">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 1rem; color: #1E293B; font-family: var(--font-display); margin-bottom: 4px;">Final Exam (สอบวัดผล)</div>
                        <div style="font-size: 0.8rem; color: #FF7675; font-weight: 700;">ต้องเรียนให้จบก่อนถึงจะสอบได้</div>
                    </div>
                </div>
                <div style="width: 36px; height: 36px; background: #F1F5F9; border-radius: 50%; display: flex; justify-content: center; align-items: center;">
                    <i class="fa-solid fa-lock" style="color: #94A3B8; font-size: 0.9rem;"></i>
                </div>
            </div>
        </div>

        <!-- Content: Sheet เรียน (Sheet Tab) -->
        <div id="content-sheet" style="display: none; flex-direction: column; gap: 12px; margin-bottom: 32px;">
            <h3 style="font-size: 1.1rem; font-family: var(--font-display); color: #1E293B; margin-bottom: 8px; padding-left: 4px;">เอกสารประกอบการเรียน</h3>
            
            <?php if (!empty($course['document_url'])): ?>
            <div style="background: white; border-radius: 16px; padding: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 44px; height: 44px; background: #E0F2FE; border-radius: 12px; display: flex; justify-content: center; align-items: center; font-size: 1.2rem; color: #0284C7;">
                        <i class="fa-solid fa-file-arrow-down"></i>
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 0.95rem; color: #1E293B; font-family: var(--font-display); margin-bottom: 4px;">เอกสารประกอบคอร์สเรียน</div>
                        <div style="font-size: 0.75rem; color: #94A3B8; font-weight: 600;">Main Document File</div>
                    </div>
                </div>
                <a href="<?= SITE_URL ?>/../<?= htmlspecialchars($course['document_url']) ?>" target="_blank" style="background: #F1F5F9; color: #1E293B; padding: 8px 12px; border-radius: 8px; font-weight: 700; font-size: 0.8rem; text-decoration: none;">Download</a>
            </div>
            <?php endif; ?>

            <?php foreach ($materials as $mat): ?>
            <div style="background: white; border-radius: 16px; padding: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="width: 44px; height: 44px; background: #FFE4E6; border-radius: 12px; display: flex; justify-content: center; align-items: center; font-size: 1.2rem; color: #FF7675;">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>
                    <div>
                        <div style="font-weight: 800; font-size: 0.95rem; color: #1E293B; font-family: var(--font-display); margin-bottom: 4px;"><?= htmlspecialchars($mat['title']) ?></div>
                        <div style="font-size: 0.75rem; color: #94A3B8; font-weight: 600;">PDF Document • <?= $mat['size_mb'] ?> MB</div>
                    </div>
                </div>
                <a href="<?= htmlspecialchars($mat['file_url']) ?>" style="background: #F1F5F9; color: #1E293B; padding: 8px 12px; border-radius: 8px; font-weight: 700; font-size: 0.8rem; text-decoration: none;">Download</a>
            </div>
            <?php endforeach; ?>

            <?php if (empty($materials) && empty($course['document_url'])): ?>
                <p style="text-align: center; color: #94A3B8; margin-top: 20px;">ยังไม่มีเอกสารในคอร์สนี้</p>
            <?php endif; ?>
        </div>

        <!-- Content: Details Tab -->
        <div id="content-details" style="display: none; flex-direction: column; gap: 12px; margin-bottom: 32px; padding: 16px; background: white; border-radius: 16px; box-shadow: var(--shadow-sm); border: 1px solid var(--border);">
            <h3 style="font-size: 1.1rem; font-family: var(--font-display); color: #1E293B; margin-bottom: 8px;">รายละเอียดคอร์สเรียน</h3>
            <p style="font-size: 0.9rem; color: #64748B; line-height: 1.6; white-space: pre-wrap;"><?= !empty($course['description']) ? htmlspecialchars($course['description']) : 'คอร์สเรียนนี้ถูกออกแบบมาเพื่อช่วยให้นักเรียนสามารถทำความเข้าใจเนื้อหาได้อย่างเป็นระบบ เริ่มตั้งแต่พื้นฐานไปจนถึงการทำข้อสอบวัดผล เมื่อเรียนจบครบทุก EP แล้ว ระบบจะปลดล็อคข้อสอบปลายภาคให้โดยอัตโนมัติ' ?></p>
        </div>

        <!-- Bottom Action Button (Pretest - Optional) -->
        <?php 
        $hasPretest = true; 
        ?>
        <?php if($hasPretest): ?>
        <div style="position: fixed; bottom: 80px; left: 16px; right: 16px; z-index: 10;">
            <a href="?page=pretest&subject=<?= urlencode($subject) ?>&title=<?= urlencode($title) ?>" style="text-decoration: none;">
                <button style="width: 100%; background: #FF7675; color: white; padding: 18px; border-radius: 24px; font-weight: 800; font-size: 1rem; border: none; cursor: pointer; font-family: var(--font-display); box-shadow: 0 8px 20px rgba(255,118,117,0.4); text-transform: uppercase; letter-spacing: 1px; transition: transform 0.2s;">
                    ทำแบบทดสอบก่อนเรียน (Pretest)
                </button>
            </a>
        </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<!-- Script to handle tab switching -->
<script>
function switchTab(tabName) {
    // Reset all buttons
    document.getElementById('tab-btn-playlist').style.background = 'transparent';
    document.getElementById('tab-btn-playlist').style.color = '#94A3B8';
    document.getElementById('tab-btn-playlist').style.boxShadow = 'none';

    document.getElementById('tab-btn-sheet').style.background = 'transparent';
    document.getElementById('tab-btn-sheet').style.color = '#94A3B8';
    document.getElementById('tab-btn-sheet').style.boxShadow = 'none';

    document.getElementById('tab-btn-details').style.background = 'transparent';
    document.getElementById('tab-btn-details').style.color = '#94A3B8';
    document.getElementById('tab-btn-details').style.boxShadow = 'none';

    // Hide all contents
    document.getElementById('content-playlist').style.display = 'none';
    document.getElementById('content-sheet').style.display = 'none';
    document.getElementById('content-details').style.display = 'none';

    // Activate selected button & show content
    const activeBtn = document.getElementById('tab-btn-' + tabName);
    activeBtn.style.background = 'white';
    activeBtn.style.color = '#FF7675';
    activeBtn.style.boxShadow = '0 2px 4px rgba(0,0,0,0.02)';

    const activeContent = document.getElementById('content-' + tabName);
    activeContent.style.display = 'flex';
    activeContent.classList.add('animate-fade-in');
}
</script>

<style>
.bottom-nav { display: none !important; } /* Hide bottom nav for immersive course view */
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
