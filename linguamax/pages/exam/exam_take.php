<?php
// ============================================================
// LinguaMax — Exam Taking Page (Redesigned)
// ============================================================
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
require_once __DIR__ . '/../../includes/functions.php';

$db = getDB();
$examId = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT * FROM exams WHERE id = ?");
$stmt->execute([$examId]);
$exam = $stmt->fetch();
if (!$exam) { header('Location: index.php?page=exams'); exit; }

// Get questions for this exam
$stmt = $db->prepare("SELECT * FROM questions WHERE exam_id = ? ORDER BY RAND() LIMIT ?");
$stmt->execute([$examId, $exam['total_questions']]);
$questions = $stmt->fetchAll();

// If not enough questions from this exam, supplement with level-matched questions
if (count($questions) < $exam['total_questions']) {
    $needed = $exam['total_questions'] - count($questions);
    $existingIds = array_column($questions, 'id');
    $placeholders = !empty($existingIds) ? implode(',', array_fill(0, count($existingIds), '?')) : '0';
    $stmt = $db->prepare("SELECT * FROM questions WHERE level = ? AND id NOT IN ($placeholders) ORDER BY RAND() LIMIT ?");
    $params = array_merge([$exam['level']], $existingIds, [$needed]);
    $stmt->execute($params);
    $questions = array_merge($questions, $stmt->fetchAll());
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0">
    <title>LinguaMax — <?= sanitize($exam['title']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #F8FAFC;
            --primary: #EF4444; /* Match mockup red */
            --primary-hover: #DC2626;
            --text-main: #0F172A;
            --text-secondary: #64748B;
            --success-bg: #DCFCE7;
            --success-border: #22C55E;
            --error-bg: #FEE2E2;
            --error-border: #EF4444;
            --card-bg: #FFFFFF;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Prompt', 'Nunito', sans-serif; }
        body { background-color: var(--bg-color); color: var(--text-main); min-height: 100vh; display: flex; flex-direction: column; }
        
        .header { display: flex; align-items: center; padding: 20px 24px; gap: 16px; max-width: 600px; margin: 0 auto; width: 100%; }
        .back-btn { width: 44px; height: 44px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; border: 1px solid #F1F5F9; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: transform 0.2s; font-size: 1.1rem; }
        .back-btn:active { transform: scale(0.95); }
        .progress-container { flex: 1; height: 6px; background: #E2E8F0; border-radius: 6px; overflow: hidden; position: relative; }
        .progress-bar { height: 100%; background: var(--primary); width: 10%; transition: width 0.3s ease; border-radius: 6px; }
        .progress-text { font-size: 1rem; font-weight: 700; color: var(--text-secondary); min-width: 40px; text-align: right; }

        .content { padding: 10px 24px 120px 24px; flex: 1; max-width: 600px; margin: 0 auto; width: 100%; }
        
        .hint-text { font-size: 0.9rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .hint-text i { color: var(--primary); font-size: 1rem; }
        
        .question-text { font-size: 1.4rem; font-weight: 700; color: var(--text-main); margin-bottom: 32px; line-height: 1.4; }
        
        .options-list { display: flex; flex-direction: column; gap: 12px; }
        .option-btn { background: var(--card-bg); border: 2px solid transparent; border-radius: 16px; padding: 16px; display: flex; align-items: center; gap: 16px; cursor: pointer; transition: all 0.2s; text-align: left; position: relative; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
        .option-btn.unanswered:active { transform: translateY(2px); }
        
        .letter-circle { width: 42px; height: 42px; border-radius: 50%; border: 1px solid #CBD5E1; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 1.1rem; color: var(--text-main); flex-shrink: 0; background: white; transition: all 0.2s; }
        .option-text { font-size: 1.1rem; font-weight: 600; color: var(--text-main); flex: 1; }
        
        /* States */
        .option-btn.selected { border-color: var(--primary); background: #FEF2F2; }
        .option-btn.selected .letter-circle { border-color: var(--primary); color: var(--primary); }
        
        .option-btn.correct { background: var(--success-bg); border-color: var(--success-border); }
        .option-btn.correct .letter-circle { border-color: var(--success-border); color: var(--success-border); }
        .option-btn.correct .option-text { color: #166534; }
        
        .option-btn.incorrect { background: var(--error-bg); border-color: var(--error-border); }
        .option-btn.incorrect .letter-circle { border-color: var(--error-border); color: var(--error-border); }
        .option-btn.incorrect .option-text { color: #991B1B; }
        
        .status-icon { position: absolute; right: 20px; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.9rem; display: none; }
        .option-btn.correct .status-icon { display: flex; background: var(--success-border); }
        .option-btn.incorrect .status-icon { display: flex; background: var(--error-border); }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 20px 24px; background: var(--bg-color); display: flex; flex-direction: column; gap: 12px; max-width: 600px; margin: 0 auto; z-index: 10; border-top: 1px solid #F1F5F9; }
        
        .feedback-alert { background: #FEE2E2; border-radius: 12px; padding: 14px 16px; display: flex; align-items: center; gap: 12px; color: #DC2626; font-weight: 600; font-size: 1rem; display: none; }
        .feedback-alert.correct { background: #DCFCE7; color: #16A34A; }
        
        .next-btn { background: var(--primary); color: white; border: none; border-radius: 30px; padding: 18px; font-size: 1.1rem; font-weight: 700; width: 100%; cursor: pointer; display: none; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3); transition: transform 0.2s; }
        .next-btn:active { transform: scale(0.98); }
        
        .check-btn { background: #E2E8F0; color: #94A3B8; border: none; border-radius: 30px; padding: 18px; font-size: 1.1rem; font-weight: 700; width: 100%; cursor: pointer; display: block; transition: all 0.2s; }
        .check-btn.active { background: var(--primary); color: white; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3); }
        
        .hidden { display: none !important; }
    </style>
</head>
<body>

<div class="header">
    <button class="back-btn" onclick="confirmExit()">
        <i class="fa-solid fa-arrow-left"></i>
    </button>
    <div class="progress-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>
    <div class="progress-text" id="progressText">1/10</div>
</div>

<div class="content">
    <div class="hint-text" id="hintBox">
        <i class="fa-regular fa-lightbulb"></i>
        <span id="hintText">Question context</span>
    </div>
    <div id="questionText" class="question-text">Loading...</div>
    
    <div class="options-list" id="optionsList">
        <!-- Options injected here -->
    </div>
</div>

<div class="footer">
    <div class="feedback-alert" id="feedbackAlert">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span id="feedbackText">Correct answer: "Buenos días"</span>
    </div>
    <button class="check-btn" id="checkBtn" onclick="checkAnswer()" disabled>Check</button>
    <button class="next-btn" id="nextBtn" onclick="nextQuestion()">Next Question <i class="fa-solid fa-arrow-right"></i></button>
</div>

<script>
const examData = {
    id: <?= $exam['id'] ?>,
    questions: <?= json_encode($questions, JSON_UNESCAPED_UNICODE) ?>
};

let currentQIndex = 0;
let selectedIndex = -1;
let isAnswerChecked = false;
let correctCount = 0;
let timeSpent = 0;

const letters = ['A', 'B', 'C', 'D'];
const choiceKeys = ['choice_a', 'choice_b', 'choice_c', 'choice_d'];

function renderQuestion() {
    isAnswerChecked = false;
    selectedIndex = -1;
    document.getElementById('checkBtn').classList.remove('active');
    document.getElementById('checkBtn').disabled = true;
    document.getElementById('checkBtn').classList.remove('hidden');
    document.getElementById('nextBtn').classList.add('hidden');
    document.getElementById('feedbackAlert').style.display = 'none';
    
    const q = examData.questions[currentQIndex];
    
    // Update progress
    document.getElementById('progressText').textContent = `${currentQIndex + 1}/${examData.questions.length}`;
    document.getElementById('progressBar').style.width = `${((currentQIndex) / examData.questions.length) * 100}%`;
    
    // Optional Hint from exam title or question logic
    document.getElementById('hintText').textContent = `<?= sanitize($exam['title']) ?> - ${q.level.charAt(0).toUpperCase() + q.level.slice(1)}`;
    
    document.getElementById('questionText').textContent = q.question_text;
    
    const optionsHtml = choiceKeys.map((key, index) => {
        if(!q[key]) return '';
        return `
            <div class="option-btn unanswered" id="opt_${index}" onclick="selectOption(${index})">
                <div class="letter-circle">${letters[index]}</div>
                <div class="option-text">${q[key]}</div>
                <div class="status-icon"><i class="fa-solid"></i></div>
            </div>
        `;
    }).join('');
    
    document.getElementById('optionsList').innerHTML = optionsHtml;
}

function selectOption(index) {
    if (isAnswerChecked) return;
    
    selectedIndex = index;
    const options = document.querySelectorAll('.option-btn');
    options.forEach(opt => opt.classList.remove('selected'));
    document.getElementById(`opt_${index}`).classList.add('selected');
    
    const checkBtn = document.getElementById('checkBtn');
    checkBtn.classList.add('active');
    checkBtn.disabled = false;
}

function checkAnswer() {
    if (selectedIndex === -1 || isAnswerChecked) return;
    isAnswerChecked = true;
    
    const q = examData.questions[currentQIndex];
    const isCorrect = selectedIndex === q.correct_answer;
    
    if (isCorrect) correctCount++;
    
    // Style options
    document.querySelectorAll('.option-btn').forEach(opt => opt.classList.remove('unanswered', 'selected'));
    
    const selectedOpt = document.getElementById(`opt_${selectedIndex}`);
    const correctOpt = document.getElementById(`opt_${q.correct_answer}`);
    
    if (isCorrect) {
        selectedOpt.classList.add('correct');
        selectedOpt.querySelector('.fa-solid').classList.add('fa-check');
        document.getElementById('feedbackAlert').className = 'feedback-alert correct';
        document.getElementById('feedbackAlert').innerHTML = `<i class="fa-solid fa-circle-check"></i> <span id="feedbackText">Excellent!</span>`;
    } else {
        selectedOpt.classList.add('incorrect');
        selectedOpt.querySelector('.fa-solid').classList.add('fa-xmark');
        correctOpt.classList.add('correct');
        correctOpt.querySelector('.fa-solid').classList.add('fa-check');
        
        document.getElementById('feedbackAlert').className = 'feedback-alert';
        document.getElementById('feedbackAlert').innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> <span id="feedbackText">Correct answer: "${q[choiceKeys[q.correct_answer]]}"</span>`;
    }
    
    document.getElementById('feedbackAlert').style.display = 'flex';
    document.getElementById('checkBtn').classList.add('hidden');
    document.getElementById('nextBtn').classList.remove('hidden');
    
    // Update progress bar
    document.getElementById('progressBar').style.width = `${((currentQIndex + 1) / examData.questions.length) * 100}%`;
}

function nextQuestion() {
    if (currentQIndex < examData.questions.length - 1) {
        currentQIndex++;
        renderQuestion();
    } else {
        submitExam();
    }
}

async function submitExam() {
    const pct = Math.round((correctCount / examData.questions.length) * 100);
    
    try {
        const response = await fetch('<?= SITE_URL ?>/api/exams.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'submit',
                exam_id: examData.id,
                score: correctCount,
                total: examData.questions.length,
                percentage: pct,
                time_spent: timeSpent,
                answers: [] // Simplified for now since we just check immediately
            })
        });
        const result = await response.json();
        if (result.success) {
            window.location.href = `?page=exam-result&id=${result.result_id}&score=${correctCount}&total=${examData.questions.length}&pct=${pct}&coins=${result.coins_earned}`;
        } else {
            alert('Error submitting exam.');
        }
    } catch(e) {
        alert('Connection error.');
    }
}

function confirmExit() {
    if (confirm('คุณแน่ใจหรือไม่ว่าต้องการออกจากแบบทดสอบ? (ความคืบหน้าจะไม่ถูกบันทึก)')) {
        window.location.href = '?page=exams';
    }
}

// Start timer
setInterval(() => {
    if (!isAnswerChecked) timeSpent++;
}, 1000);

// Initialize
renderQuestion();
</script>

</body>
</html>
