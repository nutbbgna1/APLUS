<?php
// ============================================================
// LinguaMax — Reading View + Comprehension Questions
// ============================================================
include __DIR__ . '/../../includes/header.php';
$db = getDB();
$userId = $_SESSION['user_id'];
$passageId = intval($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM reading_passages WHERE id = ?");
$stmt->execute([$passageId]);
$passage = $stmt->fetch();
if (!$passage) { header('Location: ?page=reading'); exit; }

$stmt = $db->prepare("SELECT * FROM reading_questions WHERE passage_id = ?");
$stmt->execute([$passageId]);
$questions = $stmt->fetchAll();
?>

<div class="animate-fade-in">
    <div class="flex justify-between items-center" style="margin-bottom:16px;">
        <a href="?page=reading" class="btn-ghost">← กลับ</a>
        <span class="badge <?= getLevelBadgeClass($passage['level']) ?>"><?= $passage['level'] ?> · <?= $passage['word_count'] ?> คำ</span>
    </div>

    <h1 style="margin-bottom:4px;"><?= sanitize($passage['title']) ?></h1>
    <?php if ($passage['title_th']): ?>
        <p style="color:var(--text-secondary);margin-bottom:16px;"><?= sanitize($passage['title_th']) ?></p>
    <?php endif; ?>

    <!-- Reading Content -->
    <div class="card reading-content" style="margin-bottom:24px;">
        <button class="btn btn-sm btn-outline" onclick="TTS.speak(document.getElementById('readingText').textContent)" style="margin-bottom:12px;">🔊 ฟังเสียงอ่าน</button>
        <div id="readingText"><?= $passage['content'] ?></div>
    </div>

    <!-- Comprehension Questions -->
    <?php if (!empty($questions)): ?>
    <h2 style="margin-bottom:16px;">📝 ตอบคำถาม</h2>
    <div class="flex-col gap-16" id="readingQuestions">
        <?php foreach ($questions as $qi => $q): ?>
        <div class="card" id="rq<?= $qi ?>">
            <div class="question-num">ข้อที่ <?= $qi + 1 ?></div>
            <div class="question-text"><?= sanitize($q['question_text']) ?></div>
            <div class="choices">
                <?php
                $choices = [$q['choice_a'], $q['choice_b'], $q['choice_c'], $q['choice_d']];
                $letters = ['A','B','C','D'];
                foreach ($choices as $ci => $choice):
                ?>
                <button class="choice-btn" onclick="selectReadingAnswer(<?= $qi ?>, <?= $ci ?>, <?= $q['correct_answer'] ?>)">
                    <div class="choice-letter"><?= $letters[$ci] ?></div>
                    <div><?= sanitize($choice) ?></div>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <button class="btn btn-primary btn-block btn-lg" style="margin-top:20px;" onclick="submitReading()" id="submitReadingBtn">
        ✓ ส่งคำตอบ
    </button>

    <div class="hidden" id="readingResult">
        <div class="card text-center" style="padding:30px;margin-top:20px;">
            <div style="font-size:3rem;margin-bottom:12px;" id="readingEmoji">🎉</div>
            <h2 id="readingResultTitle">ยอดเยี่ยม!</h2>
            <p style="color:var(--text-secondary);margin:8px 0;">ได้ <span id="readingScore">0</span> / <?= count($questions) ?> คะแนน</p>
            <div class="flex gap-8 justify-center" style="margin-top:16px;">
                <a href="?page=reading" class="btn btn-primary">อ่านเรื่องอื่น</a>
                <a href="?page=dashboard" class="btn btn-outline">หน้าแรก</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
let readingAnswers = {};
const totalQuestions = <?= count($questions) ?>;

function selectReadingAnswer(qIndex, choiceIndex, correctIndex) {
    readingAnswers[qIndex] = choiceIndex;
    const card = document.getElementById('rq' + qIndex);
    card.querySelectorAll('.choice-btn').forEach((btn, i) => {
        btn.classList.toggle('selected', i === choiceIndex);
    });
}

async function submitReading() {
    let correct = 0;
    for (let i = 0; i < totalQuestions; i++) {
        const card = document.getElementById('rq' + i);
        const btns = card.querySelectorAll('.choice-btn');
        const correctIdx = parseInt(btns[0]?.closest('.card')?.querySelector('.choice-btn')?.getAttribute('onclick')?.match(/\d+,\s*(\d+)\)$/)?.[1] || 0);

        btns.forEach((btn, ci) => {
            btn.style.pointerEvents = 'none';
        });
    }

    // Re-check with data
    <?php foreach ($questions as $qi => $q): ?>
    (function(){
        const qi = <?= $qi ?>;
        const correctAns = <?= $q['correct_answer'] ?>;
        const userAns = readingAnswers[qi] ?? -1;
        const card = document.getElementById('rq' + qi);
        const btns = card.querySelectorAll('.choice-btn');
        btns.forEach((btn, ci) => {
            btn.style.pointerEvents = 'none';
            if (ci === correctAns) btn.classList.add('correct');
            if (ci === userAns && ci !== correctAns) btn.classList.add('wrong');
        });
        if (userAns === correctAns) correct++;
    })();
    <?php endforeach; ?>

    document.getElementById('submitReadingBtn').classList.add('hidden');
    document.getElementById('readingResult').classList.remove('hidden');
    document.getElementById('readingScore').textContent = correct;

    if (correct >= totalQuestions * 0.8) {
        document.getElementById('readingEmoji').textContent = '🎉';
        document.getElementById('readingResultTitle').textContent = 'ยอดเยี่ยม!';
        launchConfetti();
    } else if (correct >= totalQuestions * 0.6) {
        document.getElementById('readingEmoji').textContent = '👍';
        document.getElementById('readingResultTitle').textContent = 'ดีมาก!';
    } else {
        document.getElementById('readingEmoji').textContent = '💪';
        document.getElementById('readingResultTitle').textContent = 'สู้ต่อไป!';
    }

    // Save to API
    await apiCall('reading.php', {
        action: 'save_progress',
        passage_id: <?= $passageId ?>,
        score: correct,
        total: totalQuestions
    });
}

let correct = 0; // Global for the IIFE scope
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
