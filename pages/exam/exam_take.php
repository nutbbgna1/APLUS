<?php
// ============================================================
// LinguaMax — Exam Taking Page
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

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>LinguaMax — <?= sanitize($exam['title']) ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>


<div class="exam-header">
    <div style="max-width:800px;margin:0 auto;">
        <div class="flex justify-between items-center">
            <button class="btn-ghost" onclick="confirmExit()">✕ ออก</button>
            <div class="text-center">
                <div style="font-size:0.75rem;color:var(--text-secondary);"><?= sanitize($exam['title']) ?></div>
                <div class="exam-timer" id="timer"><?= sprintf('%02d:%02d', $exam['time_minutes'], 0) ?></div>
            </div>
            <button class="btn btn-sm btn-outline" onclick="toggleSheet()" id="sheetBtn">
                📋 <span id="answeredCount">0</span>/<?= count($questions) ?>
            </button>
        </div>
        <div class="exam-progress-bar">
            <div class="exam-progress-fill" id="timeBar" style="width:100%;"></div>
        </div>
    </div>
</div>

<div class="page" style="max-width:800px;padding-top:20px;">
    <!-- Question Display -->
    <div id="questionView">
        <div class="question-card" id="questionCard"></div>
        <div class="flex justify-between" style="margin-top:20px;" id="navButtons"></div>
    </div>

    <!-- Answer Sheet -->
    <div class="hidden" id="answerSheet">
        <div style="margin-bottom:16px;">
            <h2 style="margin-bottom:4px;">กระดาษคำตอบ</h2>
            <p style="font-size:0.85rem;color:var(--text-secondary);">แตะข้อที่ต้องการเพื่อข้ามไป</p>
        </div>
        <div class="answer-sheet" id="answerGrid"></div>
        <div class="flex justify-between items-center" style="margin-top:20px;">
            <div style="font-size:0.85rem;color:var(--text-secondary);" id="sheetInfo"></div>
            <button class="btn btn-accent" onclick="submitExam()">ส่งข้อสอบ ✓</button>
        </div>
    </div>
</div>

<script src="<?= SITE_URL ?>/assets/js/app.js"></script>
<script>
const examData = {
    id: <?= $exam['id'] ?>,
    title: <?= json_encode($exam['title']) ?>,
    timeMinutes: <?= $exam['time_minutes'] ?>,
    questions: <?= json_encode($questions, JSON_UNESCAPED_UNICODE) ?>
};

let state = {
    currentQ: 0,
    answers: new Array(examData.questions.length).fill(-1),
    timeLeft: examData.timeMinutes * 60,
    showSheet: false,
    submitted: false
};

const letters = ['A','B','C','D','E'];
const choiceKeys = ['choice_a','choice_b','choice_c','choice_d','choice_e'];

// Timer
let timerInterval = setInterval(() => {
    if (state.timeLeft > 0 && !state.submitted) {
        state.timeLeft--;
        document.getElementById('timer').textContent = formatTime(state.timeLeft);
        document.getElementById('timeBar').style.width =
            (state.timeLeft / (examData.timeMinutes * 60) * 100) + '%';
    } else if (state.timeLeft <= 0 && !state.submitted) {
        submitExam();
    }
}, 1000);

function renderQuestion() {
    const q = examData.questions[state.currentQ];
    const choices = choiceKeys.filter(k => q[k]).map((k, i) => ({
        text: q[k], index: i
    }));

    document.getElementById('questionCard').innerHTML = `
        <div class="question-num">ข้อที่ ${state.currentQ + 1} / ${examData.questions.length}</div>
        <div class="question-text">${q.question_text}</div>
        <div class="choices">
            ${choices.map(c => `
                <button class="choice-btn ${state.answers[state.currentQ] === c.index ? 'selected' : ''}"
                        onclick="selectAnswer(${c.index})">
                    <div class="choice-letter">${letters[c.index]}</div>
                    <div>${c.text}</div>
                </button>
            `).join('')}
        </div>
    `;

    document.getElementById('navButtons').innerHTML = `
        <button class="btn btn-outline" ${state.currentQ === 0 ? 'disabled style="opacity:0.3"' : ''} onclick="prevQ()">← ก่อนหน้า</button>
        ${state.currentQ === examData.questions.length - 1
            ? '<button class="btn btn-accent" onclick="submitExam()">ส่งข้อสอบ ✓</button>'
            : '<button class="btn btn-primary" onclick="nextQ()">ข้อถัดไป →</button>'}
    `;

    updateAnswered();
}

function selectAnswer(i) {
    state.answers[state.currentQ] = i;
    renderQuestion();
}

function nextQ() { if (state.currentQ < examData.questions.length - 1) { state.currentQ++; renderQuestion(); window.scrollTo(0, 0); } }
function prevQ() { if (state.currentQ > 0) { state.currentQ--; renderQuestion(); window.scrollTo(0, 0); } }

function toggleSheet() {
    state.showSheet = !state.showSheet;
    document.getElementById('questionView').classList.toggle('hidden', state.showSheet);
    document.getElementById('answerSheet').classList.toggle('hidden', !state.showSheet);
    document.getElementById('sheetBtn').innerHTML = state.showSheet ? '✕ ปิด' : `📋 <span id="answeredCount">${state.answers.filter(a => a >= 0).length}</span>/${examData.questions.length}`;

    if (state.showSheet) renderAnswerSheet();
}

function renderAnswerSheet() {
    document.getElementById('answerGrid').innerHTML = examData.questions.map((q, i) => `
        <button class="answer-dot ${state.answers[i] >= 0 ? 'answered' : ''} ${state.currentQ === i ? 'current' : ''}"
                onclick="jumpToQ(${i})">${i + 1}</button>
    `).join('');
    document.getElementById('sheetInfo').textContent = `ตอบแล้ว ${state.answers.filter(a => a >= 0).length} / ${examData.questions.length} ข้อ`;
}

function jumpToQ(i) { state.currentQ = i; state.showSheet = false; document.getElementById('questionView').classList.remove('hidden'); document.getElementById('answerSheet').classList.add('hidden'); renderQuestion(); window.scrollTo(0, 0); }

function updateAnswered() {
    const el = document.getElementById('answeredCount');
    if (el) el.textContent = state.answers.filter(a => a >= 0).length;
}

async function submitExam() {
    if (state.submitted) return;
    if (!confirm('ต้องการส่งข้อสอบหรือไม่?')) return;

    state.submitted = true;
    clearInterval(timerInterval);

    let correct = 0;
    examData.questions.forEach((q, i) => {
        if (state.answers[i] === q.correct_answer) correct++;
    });
    const pct = Math.round((correct / examData.questions.length) * 100);
    const timeSpent = examData.timeMinutes * 60 - state.timeLeft;

    // Save result
    const result = await apiCall('exams.php', {
        action: 'submit',
        exam_id: examData.id,
        score: correct,
        total: examData.questions.length,
        percentage: pct,
        time_spent: timeSpent,
        answers: state.answers
    });

    // Redirect to result
    window.location.href = `?page=exam-result&exam_id=${examData.id}&score=${correct}&total=${examData.questions.length}&pct=${pct}`;
}

function confirmExit() {
    if (confirm('ต้องการออกจากข้อสอบหรือไม่? คำตอบจะไม่ถูกบันทึก')) {
        clearInterval(timerInterval);
        window.location.href = '?page=exams';
    }
}

// Init
document.addEventListener('DOMContentLoaded', renderQuestion);
</script>
</body>
</html>
