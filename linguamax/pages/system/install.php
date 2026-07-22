<?php
// ============================================================
// LinguaMax — Database Installer
// ============================================================
session_start();

$message = '';
$error = '';
$isInstalled = false;

// Check if database exists
try {
    $pdo = new PDO("mysql:host=localhost", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->query("SHOW DATABASES LIKE 'u865886212_english'");
    if ($stmt->fetch()) {
        $isInstalled = true;
    }
} catch (PDOException $e) {
    $error = "ไม่สามารถเชื่อมต่อ MySQL ได้: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['install'])) {
    if (!$error) {
        try {
            $sqlFile = __DIR__ . '/../../setup.sql';
            if (!file_exists($sqlFile)) {
                throw new Exception("ไม่พบไฟล์ setup.sql");
            }
            $sql = file_get_contents($sqlFile);
            
            // Execute the SQL file
            $pdo->exec($sql);
            $isInstalled = true;
            $message = "ติดตั้งฐานข้อมูลและข้อมูลเริ่มต้นสำเร็จ!";
        } catch (Exception $e) {
            $error = "การติดตั้งล้มเหลว: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinguaMax — ติดตั้งระบบ</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body style="background:var(--bg-gradient);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px;">

<div class="card" style="max-width:500px;width:100%;">
    <div class="text-center" style="margin-bottom:24px;">
        <div class="logo-icon" style="margin:0 auto 16px;width:60px;height:60px;font-size:2rem;">⚙️</div>
        <h1>ติดตั้งฐานข้อมูล LinguaMax</h1>
    </div>

    <?php if ($error): ?>
        <div style="background:var(--danger-light);color:var(--danger);padding:16px;border-radius:var(--radius-sm);margin-bottom:20px;text-align:center;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($message): ?>
        <div style="background:var(--success-light);color:var(--success);padding:16px;border-radius:var(--radius-sm);margin-bottom:20px;text-align:center;">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div style="margin-bottom:24px;line-height:1.8;">
        <p><strong>สถานะ:</strong> 
            <?php if ($isInstalled): ?>
                <span style="color:var(--success);font-weight:700;">✅ ติดตั้งแล้ว</span>
            <?php else: ?>
                <span style="color:var(--danger);font-weight:700;">❌ ยังไม่ได้ติดตั้ง</span>
            <?php endif; ?>
        </p>
        <p style="color:var(--text-secondary);font-size:0.9rem;">
            ระบบจะสร้างฐานข้อมูล <code>u865886212_english</code> และนำเข้าข้อมูลเริ่มต้น เช่น บทเรียน, คำศัพท์, ข้อสอบ, และบัญชีผู้ใช้
        </p>
    </div>

    <form method="POST" style="display:flex;flex-direction:column;gap:12px;">
        <button type="submit" name="install" class="btn btn-primary btn-lg btn-block" <?= $isInstalled && !$error ? 'onclick="return confirm(\'ฐานข้อมูลมีอยู่แล้ว คุณต้องการล้างข้อมูลเก่าและติดตั้งใหม่หรือไม่?\')"' : '' ?>>
            <?= $isInstalled ? '🔄 รีเซ็ตและติดตั้งใหม่' : '🚀 เริ่มการติดตั้ง' ?>
        </button>
        <a href="<?= SITE_URL ?>/index.php" class="btn btn-outline btn-block text-center">
            🏠 กลับหน้าเข้าสู่ระบบ
        </a>
    </form>
</div>

</body>
</html>
