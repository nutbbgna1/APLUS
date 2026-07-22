<?php
// ============================================================
// LinguaMax — Pretest Result Page (Mockup)
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/functions.php';

$subject = $_GET['subject'] ?? 'ทั่วไป';
$title = $_GET['title'] ?? "Pretest: $subject";
$score = intval($_GET['score'] ?? 0);
$total = intval($_GET['total'] ?? 0);

$percentage = $total > 0 ? round(($score / $total) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0">
    <title>Pretest Result — <?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #F8FAFC;
            --primary: #EF4444; 
            --text-main: #0F172A;
            --text-secondary: #64748B;
            --card-bg: #FFFFFF;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Prompt', 'Nunito', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        
        .result-card { background: var(--card-bg); width: 90%; max-width: 400px; border-radius: 24px; padding: 40px 24px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        
        .icon-box { width: 80px; height: 80px; background: #FEF2F2; color: var(--primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 24px auto; }
        
        .title { font-size: 1.5rem; font-weight: 800; color: var(--text-main); margin-bottom: 8px; font-family: var(--font-display); }
        .subtitle { font-size: 1rem; color: var(--text-secondary); margin-bottom: 32px; }
        
        .score-circle { width: 120px; height: 120px; border-radius: 50%; background: white; border: 8px solid var(--primary); display: flex; flex-direction: column; align-items: center; justify-content: center; margin: 0 auto 32px auto; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2); }
        .score-text { font-size: 2.5rem; font-weight: 900; color: var(--text-main); line-height: 1; }
        .score-total { font-size: 1rem; font-weight: 700; color: var(--text-secondary); }
        
        .stats-box { background: #F1F5F9; border-radius: 16px; padding: 16px; display: flex; justify-content: space-around; margin-bottom: 32px; }
        .stat-item { text-align: center; }
        .stat-val { font-size: 1.2rem; font-weight: 800; color: var(--text-main); }
        .stat-label { font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; }

        .back-btn { background: var(--primary); color: white; border: none; border-radius: 30px; padding: 16px; font-size: 1.1rem; font-weight: 700; width: 100%; cursor: pointer; text-decoration: none; display: inline-block; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3); transition: transform 0.2s; }
        .back-btn:active { transform: scale(0.98); }
    </style>
</head>
<body>

<div class="result-card animate-fade-in">
    <div class="icon-box">
        <i class="fa-solid fa-ranking-star"></i>
    </div>
    
    <div class="title">ประเมินความรู้ก่อนเรียน</div>
    <div class="subtitle"><?= htmlspecialchars($title) ?></div>
    
    <div class="score-circle">
        <div class="score-text"><?= $score ?></div>
        <div class="score-total">/ <?= $total ?></div>
    </div>
    
    <div class="stats-box">
        <div class="stat-item">
            <div class="stat-val"><?= $percentage ?>%</div>
            <div class="stat-label">Accuracy</div>
        </div>
        <div class="stat-item">
            <div class="stat-val"><?= htmlspecialchars($subject) ?></div>
            <div class="stat-label">Subject</div>
        </div>
    </div>
    
    <!-- Assuming we want to go back to the classroom view of this course -->
    <a href="?page=classroom-view&title=<?= urlencode($title) ?>" class="back-btn">กลับสู่บทเรียน (Back to Course)</a>
</div>

</body>
</html>
