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

// Check Access Mode Permissions
$userId = $_SESSION['user_id'];
$accessMode = $exam['access_mode'] ?? 'restricted';
if ($accessMode === 'locked') {
    die("This exam is currently locked and unavailable.");
} elseif ($accessMode === 'restricted') {
    $permStmt = $db->prepare("SELECT 1 FROM exam_permissions WHERE user_id = ? AND exam_id = ?");
    $permStmt->execute([$userId, $examId]);
    if (!$permStmt->fetch()) {
        header('Location: index.php?page=exams&error=access_denied');
        exit;
    }
}

// Get questions for this exam
$stmt = $db->prepare("SELECT * FROM exam_questions WHERE exam_id = ? ORDER BY sort_order ASC, RAND() LIMIT ?");
$stmt->execute([$examId, $exam['total_questions']]);
$questions = $stmt->fetchAll();

// If not enough questions from this exam, supplement with random fallback questions from exam_questions
if (count($questions) < $exam['total_questions']) {
    $needed = $exam['total_questions'] - count($questions);
    $existingIds = array_column($questions, 'id');
    $placeholders = !empty($existingIds) ? implode(',', array_fill(0, count($existingIds), '?')) : '0';
    $stmt = $db->prepare("SELECT * FROM exam_questions WHERE id NOT IN ($placeholders) ORDER BY RAND() LIMIT ?");
    $params = array_merge($existingIds, [$needed]);
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
        
        .question-text { font-size: 1.15rem; font-weight: 500; color: var(--text-main); margin-bottom: 24px; line-height: 1.5; }
        .question-image { max-width: 100%; border-radius: 12px; margin-bottom: 24px; display: none; object-fit: contain; }
        
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
        
        .next-btn { background: var(--primary); color: white; border: none; border-radius: 30px; padding: 18px; font-size: 1.1rem; font-weight: 700; width: 100%; cursor: pointer; display: block; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3); transition: transform 0.2s; }
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
    <div style="display: flex; flex-direction: column; align-items: flex-end;">
        <div class="progress-text" id="progressText">1/10</div>
        <?php if ($exam['time_minutes'] > 0): ?>
            <div id="timerText" style="font-size: 0.9rem; font-weight: 600; color: #EF4444; margin-top: 4px;">
                <i class="fa-regular fa-clock"></i> <span id="timeRemaining">--:--</span>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="content">
    <div class="hint-text" id="hintBox">
        <i class="fa-regular fa-lightbulb"></i>
        <span id="hintText">Question context</span>
    </div>
    
    <div id="passageBox" style="display: none; background: #F8FAFC; border-left: 4px solid var(--primary); padding: 16px; margin-bottom: 20px; border-radius: 0 8px 8px 0; color: #334155; font-size: 0.95rem; line-height: 1.6;">
        <!-- Passage or Conversation injected here -->
    </div>
    
    <img id="questionImage" class="question-image" src="" alt="Question Image" onerror="this.style.display='none'">
    <div id="questionText" class="question-text">Loading...</div>
    
    <div class="options-list" id="optionsList">
        <!-- Options injected here -->
    </div>
</div>

<div class="footer">
    <div class="feedback-alert" id="feedbackAlert" style="flex-direction: column; align-items: flex-start;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <i class="fa-solid fa-circle-exclamation" id="feedbackIcon"></i>
            <span id="feedbackText">Correct answer: "Buenos días"</span>
        </div>
        <div id="feedbackExplanation" style="font-size: 0.95rem; margin-top: 8px; color: #475569; font-weight: 500; display: none; line-height: 1.5;">
            <!-- Explanation injected here -->
        </div>
    </div>
    <button class="check-btn" id="checkBtn" onclick="checkAnswer()" disabled>Check</button>
    <button class="next-btn hidden" id="nextBtn" onclick="nextQuestion()">Next Question <i class="fa-solid fa-arrow-right"></i></button>
</div>

<script>
const examData = {
    id: <?= $exam['id'] ?>,
    questions: <?= json_encode($questions, JSON_UNESCAPED_UNICODE) ?>
};

const uploadBaseUrl = "<?= str_replace('/linguamax', '', SITE_URL) ?>/";

let currentQIndex = 0;
let selectedIndex = -1;
let isAnswerChecked = false;
let correctCount = 0;
let timeSpent = 0;
const timeLimitSeconds = <?= ($exam['time_minutes'] > 0) ? ($exam['time_minutes'] * 60) : 0 ?>;
let userAnswers = [];

const letters = ['A', 'B', 'C', 'D'];
const choiceKeys = ['choice_1', 'choice_2', 'choice_3', 'choice_4'];

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
    document.getElementById('hintText').textContent = `<?= sanitize($exam['title']) ?>`;
    
    const questionImage = document.getElementById('questionImage');
    const questionText = document.getElementById('questionText');
    const passageBox = document.getElementById('passageBox');
    
    if (q.passage_text && q.passage_text.trim() !== '') {
        // Handle both actual newlines and literal \n strings that might have been saved in DB
        let formattedPassage = q.passage_text.replace(/\\n/g, '<br>').replace(/\n/g, '<br>');
        passageBox.innerHTML = formattedPassage;
        passageBox.style.display = 'block';
    } else {
        passageBox.style.display = 'none';
        passageBox.innerHTML = '';
    }

    if (q.image_path) {
        questionImage.src = uploadBaseUrl + q.image_path;
        questionImage.style.display = 'block';
    } else {
        questionImage.src = '';
        questionImage.style.display = 'none';
    }

    if (q.question_text) {
        let formattedQuestion = q.question_text.replace(/\\n/g, '<br>').replace(/\n/g, '<br>');
        questionText.innerHTML = formattedQuestion;
        questionText.style.display = 'block';
    } else {
        questionText.style.display = 'none';
    }
    
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
    const isCorrect = selectedIndex == q.correct_answer;
    
    if (isCorrect) correctCount++;
    
    // Store answer tracking
    userAnswers.push({
        question_id: q.id,
        question_text: q.question_text,
        selected_choice: selectedIndex,
        selected_text: q[choiceKeys[selectedIndex]],
        correct_choice: q.correct_answer,
        correct_text: q[choiceKeys[q.correct_answer]],
        is_correct: isCorrect
    });
    
    // Style options
    document.querySelectorAll('.option-btn').forEach(opt => opt.classList.remove('unanswered', 'selected'));
    
    const selectedOpt = document.getElementById(`opt_${selectedIndex}`);
    const correctOpt = document.getElementById(`opt_${q.correct_answer}`);
    
    if (isCorrect) {
        selectedOpt.classList.add('correct');
        selectedOpt.querySelector('.fa-solid').classList.add('fa-check');
        document.getElementById('feedbackAlert').className = 'feedback-alert correct';
        document.getElementById('feedbackIcon').className = 'fa-solid fa-circle-check';
        document.getElementById('feedbackText').innerHTML = `Excellent!`;
    } else {
        selectedOpt.classList.add('incorrect');
        selectedOpt.querySelector('.fa-solid').classList.add('fa-xmark');
        correctOpt.classList.add('correct');
        correctOpt.querySelector('.fa-solid').classList.add('fa-check');
        
        document.getElementById('feedbackAlert').className = 'feedback-alert';
        document.getElementById('feedbackIcon').className = 'fa-solid fa-circle-exclamation';
        document.getElementById('feedbackText').innerHTML = `Correct answer: "${q[choiceKeys[q.correct_answer]]}"`;
    }
    
    // Show explanation if available
    const explanationEl = document.getElementById('feedbackExplanation');
    if (q.explanation && q.explanation.trim() !== '') {
        explanationEl.innerHTML = `<strong>คำอธิบาย:</strong> ${q.explanation}`;
        explanationEl.style.display = 'block';
    } else {
        explanationEl.style.display = 'none';
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
    
    let apiUrl = '<?= SITE_URL ?>/api/exams.php';
    if (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
        apiUrl = '<?= SITE_URL ?>/api/exams';
    }
    
    try {
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'submit',
                exam_id: examData.id,
                score: correctCount,
                total: examData.questions.length,
                percentage: pct,
                time_spent: timeSpent,
                answers: userAnswers
            })
        });
        const result = await response.json();
        if (result.success) {
            window.location.href = `?page=exam-result&id=${result.result_id}&exam_id=${examData.id}&score=${correctCount}&total=${examData.questions.length}&pct=${pct}&coins=${result.coins_earned}`;
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
const timerInterval = setInterval(() => {
    if (!isAnswerChecked) {
        timeSpent++;
    }
    
    if (timeLimitSeconds > 0) {
        let remaining = timeLimitSeconds - timeSpent;
        if (remaining <= 0) {
            remaining = 0;
            clearInterval(timerInterval);
            document.getElementById('timeRemaining').textContent = "00:00";
            alert("หมดเวลาทำข้อสอบแล้ว! ระบบจะส่งคำตอบของคุณอัตโนมัติ");
            submitExam();
        } else {
            const m = Math.floor(remaining / 60).toString().padStart(2, '0');
            const s = (remaining % 60).toString().padStart(2, '0');
            document.getElementById('timeRemaining').textContent = `${m}:${s}`;
        }
    }
}, 1000);

// Initialize
renderQuestion();
</script>

</body>
</html>
