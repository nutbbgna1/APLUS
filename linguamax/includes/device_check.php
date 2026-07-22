<?php
// ============================================================
// LinguaMax — Device Checker
// ============================================================
// Blocks students from using desktop devices if enabled in global settings.

$settingsFile = __DIR__ . '/global_settings.json';
$globalSettings = ['block_desktop' => false];

if (file_exists($settingsFile)) {
    $data = file_get_contents($settingsFile);
    $decoded = json_decode($data, true);
    if (is_array($decoded)) {
        $globalSettings = array_merge($globalSettings, $decoded);
    }
}

$currentUser = getCurrentUser();

if ($globalSettings['block_desktop'] && $currentUser && $currentUser['role'] === 'student') {
    // Inject CSS to block desktop views
    ?>
    <style>
    @media (min-width: 1025px) {
        body > *:not(#desktop-block-msg) {
            display: none !important;
        }
        body {
            background: #F8FAFC !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        #desktop-block-msg {
            display: flex !important;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            width: 100vw;
            height: 100vh;
            position: fixed;
            top: 0; left: 0;
            z-index: 9999999;
            background: #F8FAFC;
            font-family: 'Nunito', 'Sarabun', sans-serif;
            color: #1E293B;
            padding: 20px;
        }
        #desktop-block-msg .icon {
            font-size: 5rem;
            margin-bottom: 20px;
        }
        #desktop-block-msg h1 {
            font-size: 2rem;
            font-weight: 900;
            margin-bottom: 10px;
            color: #4A8CFF;
        }
        #desktop-block-msg p {
            font-size: 1.2rem;
            font-weight: 600;
            color: #64748B;
            max-width: 500px;
            line-height: 1.6;
        }
    }
    #desktop-block-msg {
        display: none;
    }
    </style>
    <div id="desktop-block-msg">
        <div class="icon">💻🚫</div>
        <h1>ระบบยังไม่รองรับการใช้งานบนคอมพิวเตอร์</h1>
        <p>เพื่อประสบการณ์การเรียนรู้ที่ดีที่สุด กรุณาเข้าใช้งานผ่าน Tablet หรือ Mobile เท่านั้นครับ</p>
    </div>
    <?php
}
?>
