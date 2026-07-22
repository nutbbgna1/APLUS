<?php
// ============================================================
// LinguaMax — Login Page
// ============================================================
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = getDB();
    $role = 'student'; // Force student role on linguamax portal

    $code = strtoupper(trim($_POST['code'] ?? ''));
    $stmt = $db->prepare("SELECT * FROM users WHERE code = ? AND role = 'student'");
    $stmt->execute([$code]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = 'student';
        updateStreak($user['id']);
        header('Location: ' . SITE_URL . '/index.php?page=dashboard');
        exit;
    } else {
        $error = 'ไม่พบรหัสนักเรียนนี้ กรุณาลองใหม่';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>LinguaMax — เข้าสู่ระบบ</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="login-page" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #4A8CFF 0%, #326CE5 100%); padding: 20px;">
    
    <div class="login-card" style="background: white; border-radius: var(--radius-xl); text-align: center; box-shadow: 0 12px 40px rgba(0,0,0,0.2);">
        <!-- Logo -->
        <div style="margin-bottom: 24px;">
            <div class="logo-icon" style="margin: 0 auto 12px; width: 64px; height: 64px; font-size: 2rem; background: linear-gradient(135deg, #4A8CFF, #326CE5); box-shadow: 0 8px 24px rgba(74, 140, 255, 0.4);">E</div>
            <h1 style="font-size: 2rem; font-family: var(--font-display); font-weight: 900; color: #4A8CFF;">LinguaMax</h1>
            <div class="subtitle" style="font-weight: 600;">Welcome back! Let's learn together.</div>
        </div>

        <?php if ($error): ?>
            <div style="background:var(--danger-light);color:var(--danger);padding:12px;border-radius:var(--radius-sm);margin-bottom:20px;font-weight:600;text-align:center; animation: shake 0.4s;">
                ⚠️ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Student Login -->
        <form id="studentForm" method="POST" class="flex-col gap-16 animate-fade-in">
            <input class="input-field" name="code" placeholder="รหัสนักเรียน (เช่น STD001)" value="" autocomplete="off" style="border-radius: var(--radius-lg); padding: 16px; text-align: center; font-weight: 700; font-size: 1.1rem; border: 2px solid var(--border); transition: var(--transition);">
            <button type="submit" class="btn btn-block btn-lg" style="background: linear-gradient(135deg, #4A8CFF, #326CE5); color: white; border: none; border-radius: var(--radius-lg); font-size: 1.2rem; padding: 14px; box-shadow: 0 4px 12px rgba(74,140,255,0.4); font-weight: 800; cursor: pointer;">
                เข้าสู่ระบบ 🚀
            </button>
            <p style="margin-top: 8px; font-size: 0.95rem; font-weight: 600; color: #64748B;">
                ยังไม่มีรหัสนักเรียน? <a href="?page=register" style="color: #4A8CFF; text-decoration: none; font-weight: 800;">สมัครสมาชิก</a>
            </p>
        </form>
    </div>
</div>
<style>
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}
.input-field:focus { border-color: #4A8CFF !important; outline: none; box-shadow: 0 0 0 3px rgba(74,140,255,0.3) !important; }
.role-btn.active { background: linear-gradient(135deg, #4A8CFF, #326CE5) !important; color: white !important; box-shadow: 0 2px 8px rgba(74,140,255,0.4) !important; }
</style>

</body>
</html>
