<?php
// ============================================================
// LinguaMax — Pretest Taking Page (Mockup)
// ============================================================
session_start();
require_once __DIR__ . '/../../includes/functions.php';

$subject = $_GET['subject'] ?? 'ทั่วไป';
$grade = $_GET['grade'] ?? 'ป.6';
$courseTitle = $_GET['title'] ?? "Pretest: $subject ($grade)";

// Mock questions database for all subjects
$mockQuestions = [
    'วิทย์' => [
        [
            'question_text' => 'เซลล์พืชและเซลล์สัตว์มีความแตกต่างกันอย่างไร?',
            'choice_a' => 'เซลล์พืชไม่มีนิวเคลียส',
            'choice_b' => 'เซลล์สัตว์มีคลอโรพลาสต์',
            'choice_c' => 'เซลล์พืชมีผนังเซลล์ (Cell Wall)',
            'choice_d' => 'เซลล์สัตว์สามารถสร้างอาหารเองได้',
            'correct_answer' => 2 // choice_c is index 2
        ],
        [
            'question_text' => 'ก๊าซชนิดใดที่มีมากที่สุดในบรรยากาศโลก?',
            'choice_a' => 'ออกซิเจน',
            'choice_b' => 'คาร์บอนไดออกไซด์',
            'choice_c' => 'ไนโตรเจน',
            'choice_d' => 'ไฮโดรเจน',
            'correct_answer' => 2
        ],
        [
            'question_text' => 'ดาวเคราะห์ดวงใดในระบบสุริยะที่มีขนาดใหญ่ที่สุด?',
            'choice_a' => 'ดาวพฤหัสบดี',
            'choice_b' => 'ดาวเสาร์',
            'choice_c' => 'โลก',
            'choice_d' => 'ดาวยูเรนัส',
            'correct_answer' => 0
        ]
    ],
    'คณิต' => [
        [
            'question_text' => 'ผลลัพธ์ของ 15 × (4 + 6) คือข้อใด?',
            'choice_a' => '150',
            'choice_b' => '66',
            'choice_c' => '90',
            'choice_d' => '100',
            'correct_answer' => 0
        ],
        [
            'question_text' => 'ถ้า 2x + 5 = 15 แล้ว x มีค่าเท่าใด?',
            'choice_a' => '3',
            'choice_b' => '5',
            'choice_c' => '10',
            'choice_d' => '20',
            'correct_answer' => 1
        ],
        [
            'question_text' => 'รูปสามเหลี่ยมด้านเท่ามีมุมภายในแต่ละมุมกี่องศา?',
            'choice_a' => '45 องศา',
            'choice_b' => '60 องศา',
            'choice_c' => '90 องศา',
            'choice_d' => '180 องศา',
            'correct_answer' => 1
        ]
    ],
    'อังกฤษ' => [
        [
            'question_text' => 'Choose the correct sentence:',
            'choice_a' => 'She don\'t like apples.',
            'choice_b' => 'She doesn\'t likes apples.',
            'choice_c' => 'She doesn\'t like apples.',
            'choice_d' => 'She isn\'t like apples.',
            'correct_answer' => 2
        ],
        [
            'question_text' => 'What is the past tense of "Go"?',
            'choice_a' => 'Goed',
            'choice_b' => 'Went',
            'choice_c' => 'Gone',
            'choice_d' => 'Going',
            'correct_answer' => 1
        ],
        [
            'question_text' => '____ you ever been to Japan?',
            'choice_a' => 'Has',
            'choice_b' => 'Have',
            'choice_c' => 'Are',
            'choice_d' => 'Did',
            'correct_answer' => 1
        ]
    ],
    'ไทย' => [
        [
            'question_text' => 'ข้อใดคือคำไวพจน์ของ "พระอาทิตย์"?',
            'choice_a' => 'บุหลัน',
            'choice_b' => 'ทินกร',
            'choice_c' => 'คงคา',
            'choice_d' => 'พนาลี',
            'correct_answer' => 1
        ],
        [
            'question_text' => 'สำนวน "ชี้โพรงให้กระรอก" มีความหมายว่าอย่างไร?',
            'choice_a' => 'แนะนำให้ทำในสิ่งที่ถูกต้อง',
            'choice_b' => 'ช่วยชี้ทางรอดให้ผู้ที่เดือดร้อน',
            'choice_c' => 'ชี้ช่องทางให้คนไม่ดีทำความผิด',
            'choice_d' => 'สอนให้คนมีความรู้มากขึ้น',
            'correct_answer' => 2
        ],
        [
            'question_text' => 'ข้อใดเขียนสะกดคำได้ถูกต้อง?',
            'choice_a' => 'สังเกตุ',
            'choice_b' => 'อนุญาติ',
            'choice_c' => 'อัญชัน',
            'choice_d' => 'รสชาด',
            'correct_answer' => 2
        ]
    ],
    'สังคม' => [
        [
            'question_text' => 'ทวีปใดมีพื้นที่ขนาดใหญ่ที่สุดในโลก?',
            'choice_a' => 'ทวีปแอฟริกา',
            'choice_b' => 'ทวีปเอเชีย',
            'choice_c' => 'ทวีปอเมริกาเหนือ',
            'choice_d' => 'ทวีปยุโรป',
            'correct_answer' => 1
        ],
        [
            'question_text' => 'วันมาฆบูชา ตรงกับวันใด?',
            'choice_a' => 'วันขึ้น 15 ค่ำ เดือน 3',
            'choice_b' => 'วันขึ้น 15 ค่ำ เดือน 6',
            'choice_c' => 'วันขึ้น 15 ค่ำ เดือน 8',
            'choice_d' => 'วันแรม 1 ค่ำ เดือน 11',
            'correct_answer' => 0
        ],
        [
            'question_text' => 'หน้าที่หลักของฝ่ายนิติบัญญัติคืออะไร?',
            'choice_a' => 'ออกกฎหมาย',
            'choice_b' => 'บริหารประเทศ',
            'choice_c' => 'ตัดสินคดีความ',
            'choice_d' => 'ปราบปรามอาชญากรรม',
            'correct_answer' => 0
        ]
    ]
];

// Fallback to general questions if subject is unknown
$generalQuestions = [
    [
        'question_text' => 'ข้อใดคือส่วนประกอบหลักของน้ำ?',
        'choice_a' => 'H2O',
        'choice_b' => 'CO2',
        'choice_c' => 'O2',
        'choice_d' => 'N2',
        'correct_answer' => 0
    ]
];

$questions = $mockQuestions[$subject] ?? $generalQuestions;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, maximum-scale=1.0">
    <title>Pretest — <?= htmlspecialchars($courseTitle) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #F8FAFC;
            --primary: #EF4444; 
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
        .back-btn { width: 44px; height: 44px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; border: 1px solid #F1F5F9; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.03); transition: transform 0.2s; font-size: 1.1rem; color: #1E293B; text-decoration: none;}
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
    <a href="javascript:history.back()" class="back-btn" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการออกจากแบบทดสอบ?');">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div class="progress-container">
        <div class="progress-bar" id="progressBar"></div>
    </div>
    <div class="progress-text" id="progressText">1/3</div>
</div>

<div class="content">
    <div class="hint-text" id="hintBox">
        <i class="fa-solid fa-file-signature"></i>
        <span id="hintText"><?= htmlspecialchars($courseTitle) ?> (แบบทดสอบก่อนเรียน)</span>
    </div>
    <div id="questionText" class="question-text">Loading...</div>
    
    <div class="options-list" id="optionsList">
        <!-- Options injected here -->
    </div>
</div>

<div class="footer">
    <div class="feedback-alert" id="feedbackAlert">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span id="feedbackText">Correct answer</span>
    </div>
    <button class="check-btn" id="checkBtn" onclick="checkAnswer()" disabled>ตรวจคำตอบ / Check</button>
    <button class="next-btn" id="nextBtn" onclick="nextQuestion()">ข้อถัดไป / Next <i class="fa-solid fa-arrow-right"></i></button>
</div>

<script>
const examData = {
    subject: "<?= htmlspecialchars($subject) ?>",
    title: "<?= htmlspecialchars($courseTitle) ?>",
    questions: <?= json_encode($questions, JSON_UNESCAPED_UNICODE) ?>
};

let currentQIndex = 0;
let selectedIndex = -1;
let isAnswerChecked = false;
let correctCount = 0;

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
    
    document.getElementById('progressText').textContent = `${currentQIndex + 1}/${examData.questions.length}`;
    document.getElementById('progressBar').style.width = `${((currentQIndex) / examData.questions.length) * 100}%`;
    
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
    
    document.querySelectorAll('.option-btn').forEach(opt => opt.classList.remove('unanswered', 'selected'));
    
    const selectedOpt = document.getElementById(`opt_${selectedIndex}`);
    const correctOpt = document.getElementById(`opt_${q.correct_answer}`);
    
    if (isCorrect) {
        selectedOpt.classList.add('correct');
        selectedOpt.querySelector('.fa-solid').classList.add('fa-check');
        document.getElementById('feedbackAlert').className = 'feedback-alert correct';
        document.getElementById('feedbackAlert').innerHTML = `<i class="fa-solid fa-circle-check"></i> <span id="feedbackText">ยอดเยี่ยม! ถูกต้องครับ</span>`;
    } else {
        selectedOpt.classList.add('incorrect');
        selectedOpt.querySelector('.fa-solid').classList.add('fa-xmark');
        correctOpt.classList.add('correct');
        correctOpt.querySelector('.fa-solid').classList.add('fa-check');
        
        document.getElementById('feedbackAlert').className = 'feedback-alert';
        document.getElementById('feedbackAlert').innerHTML = `<i class="fa-solid fa-circle-exclamation"></i> <span id="feedbackText">คำตอบที่ถูกคือ: "${q[choiceKeys[q.correct_answer]]}"</span>`;
    }
    
    document.getElementById('feedbackAlert').style.display = 'flex';
    document.getElementById('checkBtn').classList.add('hidden');
    document.getElementById('nextBtn').classList.remove('hidden');
    
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

function submitExam() {
    // Redirect to mock result page
    const total = examData.questions.length;
    const score = correctCount;
    window.location.href = `?page=pretest-result&subject=${encodeURIComponent(examData.subject)}&title=${encodeURIComponent(examData.title)}&score=${score}&total=${total}`;
}

// Initialize
renderQuestion();
</script>

</body>
</html>
