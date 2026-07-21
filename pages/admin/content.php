<?php
include __DIR__ . '/../../includes/header.php';
?>
<div class="animate-fade-in">
    <h1 style="margin-bottom:20px;"><i class="fa-solid fa-folder-tree"></i> จัดการเนื้อหา (CMS)</h1>
    
    <div class="game-hub-grid">
        <a href="?page=admin&sub=lessons" class="card game-hub-card text-center" style="text-decoration:none;">
            <span style="font-size:clamp(2rem, 8vw, 3rem);margin-bottom:12px;display:block;color:var(--primary);"><i class="fa-solid fa-book"></i></span>
            <h3 style="margin-bottom:4px;color:var(--text);">บทเรียน</h3>
            <p style="font-size:0.8rem;color:var(--text-secondary);">เพิ่ม แก้ไข ลบ เนื้อหาบทเรียน</p>
        </a>
        <a href="?page=admin&sub=exams" class="card game-hub-card text-center" style="text-decoration:none;">
            <span style="font-size:clamp(2rem, 8vw, 3rem);margin-bottom:12px;display:block;color:var(--danger);"><i class="fa-solid fa-file-signature"></i></span>
            <h3 style="margin-bottom:4px;color:var(--text);">ข้อสอบ</h3>
            <p style="font-size:0.8rem;color:var(--text-secondary);">จัดการข้อสอบและตัวเลือก</p>
        </a>
        <a href="?page=admin&sub=vocab" class="card game-hub-card text-center" style="text-decoration:none;">
            <span style="font-size:clamp(2rem, 8vw, 3rem);margin-bottom:12px;display:block;color:var(--accent);"><i class="fa-solid fa-layer-group"></i></span>
            <h3 style="margin-bottom:4px;color:var(--text);">คำศัพท์</h3>
            <p style="font-size:0.8rem;color:var(--text-secondary);">จัดการคำศัพท์ Flashcard</p>
        </a>
        <a href="?page=admin&sub=reading" class="card game-hub-card text-center" style="text-decoration:none;">
            <span style="font-size:clamp(2rem, 8vw, 3rem);margin-bottom:12px;display:block;color:var(--info);"><i class="fa-solid fa-book-open"></i></span>
            <h3 style="margin-bottom:4px;color:var(--text);">เรื่องสั้น</h3>
            <p style="font-size:0.8rem;color:var(--text-secondary);">จัดการบทความ Leveled Reading</p>
        </a>
        <a href="?page=admin&sub=games" class="card game-hub-card text-center" style="text-decoration:none;">
            <span style="font-size:clamp(2rem, 8vw, 3rem);margin-bottom:12px;display:block;color:var(--secondary);"><i class="fa-solid fa-gamepad"></i></span>
            <h3 style="margin-bottom:4px;color:var(--text);">มินิเกมส์</h3>
            <p style="font-size:0.8rem;color:var(--text-secondary);">จัดการโจทย์ Mini Games</p>
        </a>
    </div>
</div>
<?php include __DIR__ . '/../../includes/footer.php'; ?>
