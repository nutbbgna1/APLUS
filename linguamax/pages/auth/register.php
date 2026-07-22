<?php
// ============================================================
// LinguaMax — Register Page
// ============================================================
$error = '';
$success = '';
$newStudentCode = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    
    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $nickname = trim($_POST['nickname'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($fname) || empty($lname) || empty($nickname) || empty($password)) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    } else {
        // Generate a unique student code
        do {
            $code = 'ST' . rand(100000, 999999);
            $stmt = $db->prepare("SELECT id FROM users WHERE code = ?");
            $stmt->execute([$code]);
            $exists = $stmt->fetch();
        } while ($exists);

        // Insert new student
        $colors = ['#4A8CFF', '#38BDF8', '#F59E0B', '#10B981', '#EF4444', '#8CB3FF'];
        $avatar_color = $colors[array_rand($colors)];
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $db->prepare("INSERT INTO users (code, password, fname, lname, nickname, role, avatar_color) VALUES (?, ?, ?, ?, ?, 'student', ?)");
            $stmt->execute([$code, $hashed, $fname, $lname, $nickname, $avatar_color]);
            
            $success = 'สมัครสมาชิกสำเร็จ!';
            $newStudentCode = $code;
        } catch (Exception $e) {
            $error = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>LinguaMax — สมัครสมาชิก</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="login-page" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #8CB3FF 0%, #4A8CFF 100%); padding: 20px;">
    
    <div class="login-card" style="background: white; border-radius: var(--radius-xl); text-align: center; box-shadow: 0 12px 40px rgba(0,0,0,0.2); width: 100%; max-width: 400px; position: relative;">
        
        <a href="?page=login" style="position: absolute; top: 20px; left: 20px; color: #94A3B8; text-decoration: none; font-size: 1.2rem;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>

        <!-- Logo -->
        <div style="margin-bottom: 24px; margin-top: 10px;">
            <div class="logo-icon" style="margin: 0 auto 12px; width: 64px; height: 64px; font-size: 2rem; background: linear-gradient(135deg, #8CB3FF, #4A8CFF); box-shadow: 0 8px 24px rgba(74, 140, 255, 0.4);">E</div>
            <h1 style="font-size: 1.8rem; font-family: var(--font-display); font-weight: 900; color: #4A8CFF;">สมัครสมาชิก</h1>
            <div class="subtitle" style="font-weight: 600;">สร้างบัญชีใหม่เพื่อเริ่มเรียนรู้!</div>
        </div>

        <?php if ($error): ?>
            <div style="background:var(--danger-light);color:var(--danger);padding:12px;border-radius:var(--radius-sm);margin-bottom:20px;font-weight:600;text-align:center; animation: shake 0.4s;">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background:var(--success-light);color:var(--success);padding:24px 16px;border-radius:var(--radius-lg);margin-bottom:20px;text-align:center;box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);">
                <div style="font-size: 3rem; margin-bottom: 8px;">🎉</div>
                <h2 style="font-weight: 900; margin-bottom: 8px; font-size: 1.2rem;">สมัครสมาชิกสำเร็จ!</h2>
                <p style="font-size: 0.95rem; font-weight: 600; margin-bottom: 16px; color: #1E293B;">
                    รหัสนักเรียนของคุณคือ
                </p>
                <div style="background: white; border: 2px dashed var(--success); padding: 12px; border-radius: var(--radius-sm); font-size: 1.8rem; font-family: var(--font-display); font-weight: 900; color: var(--success); letter-spacing: 2px; margin-bottom: 20px;">
                    <?= htmlspecialchars($newStudentCode) ?>
                </div>
                <p style="font-size: 0.85rem; font-weight: 700; color: var(--danger); margin-bottom: 20px;">
                    ⚠️ โปรดจดจำหรือแคปหน้าจอรหัสนี้ไว้ เพื่อใช้เข้าสู่ระบบในครั้งต่อไป
                </p>
                <div>
                    <a href="?page=login" class="btn btn-block" style="background: var(--success); color: white; padding: 14px; border-radius: var(--radius-lg); font-size: 1rem; font-weight: 800; text-decoration: none; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">ไปที่หน้าเข้าสู่ระบบ</a>
                </div>
            </div>
        <?php else: ?>
            <form method="POST" action="?page=register" class="flex-col gap-16 animate-fade-in">
                <input class="input-field" type="text" name="fname" placeholder="ชื่อจริง" value="<?= htmlspecialchars($_POST['fname'] ?? '') ?>" required maxlength="50" autocomplete="off" style="border-radius: var(--radius-lg); padding: 14px 16px; font-weight: 700; font-size: 1rem; border: 2px solid var(--border); transition: var(--transition);">
                
                <input class="input-field" type="text" name="lname" placeholder="นามสกุล" value="<?= htmlspecialchars($_POST['lname'] ?? '') ?>" required maxlength="50" autocomplete="off" style="border-radius: var(--radius-lg); padding: 14px 16px; font-weight: 700; font-size: 1rem; border: 2px solid var(--border); transition: var(--transition);">
                
                <input class="input-field" type="text" name="nickname" placeholder="ชื่อเล่น (ใช้แสดงในแอป)" value="<?= htmlspecialchars($_POST['nickname'] ?? '') ?>" required maxlength="50" autocomplete="off" style="border-radius: var(--radius-lg); padding: 14px 16px; font-weight: 700; font-size: 1rem; border: 2px solid var(--border); transition: var(--transition);">
                
                <input class="input-field" type="password" name="password" placeholder="รหัสผ่าน" required maxlength="100" autocomplete="off" style="border-radius: var(--radius-lg); padding: 14px 16px; font-weight: 700; font-size: 1rem; border: 2px solid var(--border); transition: var(--transition);">
                
                <button type="submit" class="btn btn-block btn-lg" style="background: linear-gradient(135deg, #8CB3FF, #4A8CFF); color: white; border: none; border-radius: var(--radius-lg); font-size: 1.1rem; padding: 14px; box-shadow: 0 4px 12px rgba(74, 140, 255, 0.4); font-weight: 800; cursor: pointer; margin-top: 8px;">
                    สร้างบัญชี 🚀
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>
<style>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}
.input-field:focus { border-color: #8CB3FF !important; outline: none; box-shadow: 0 0 0 3px rgba(74, 140, 255, 0.3) !important; }
</style>
<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
</body>
</html>
