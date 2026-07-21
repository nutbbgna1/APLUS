<?php
// ============================================================
// LinguaMax — Mini Games Hub
// ============================================================
include __DIR__ . '/../../includes/header.php';

$db = getDB();
$userId = $_SESSION['user_id'];
$gameType = $_GET['type'] ?? '';

// Get vocab for games
$stmt = $db->prepare("SELECT * FROM vocabulary ORDER BY RAND() LIMIT 20");
$stmt->execute();
$vocabList = $stmt->fetchAll();

// Sentence data for sentence builder
$stmt = $db->query("SELECT * FROM game_sentences ORDER BY RAND() LIMIT 10");
$sentenceRows = $stmt->fetchAll();
$sentences = [];
foreach ($sentenceRows as $row) {
    $words = explode(' ', trim($row['sentence_en']));
    $words = array_values(array_filter($words));
    $sentences[] = ['words' => $words, 'thai' => $row['sentence_th']];
}

// Fill-blank data
$stmt = $db->query("SELECT * FROM game_fill_blanks ORDER BY RAND() LIMIT 10");
$fillRows = $stmt->fetchAll();
$fillBlanks = [];
foreach ($fillRows as $row) {
    $fillBlanks[] = [
        'text' => $row['question_text'],
        'answer' => $row['correct_answer'],
        'options' => [$row['choice_1'], $row['choice_2'], $row['choice_3'], $row['choice_4']]
    ];
}
?>

<?php if ($gameType === ''): ?>
<!-- Game Hub -->
<div class="animate-fade-in">
    <h1 style="margin-bottom:20px;">🎮 Mini Games</h1>
    <p style="color:var(--text-secondary);margin-bottom:24px;">เล่นเกมสนุกๆ พร้อมเรียนรู้ภาษาอังกฤษ!</p>

    <div class="game-hub-grid">
        <a href="?page=games&type=match" class="card game-hub-card" style="background:linear-gradient(135deg,#EDE9FF,#F0F4FF);">
            <span class="game-emoji animate-bounce">🃏</span>
            <div class="game-name">จับคู่คำศัพท์</div>
            <div class="game-desc">Match Pairs</div>
        </a>
        <a href="?page=games&type=sentence" class="card game-hub-card" style="background:linear-gradient(135deg,#FFE5EE,#FFF0F5);">
            <span class="game-emoji animate-bounce" style="animation-delay:0.2s;">📝</span>
            <div class="game-name">เรียงประโยค</div>
            <div class="game-desc">Sentence Builder</div>
        </a>
        <a href="?page=games&type=fill" class="card game-hub-card" style="background:linear-gradient(135deg,#E8F8EF,#F0FFF5);">
            <span class="game-emoji animate-bounce" style="animation-delay:0.4s;">✏️</span>
            <div class="game-name">เติมคำ</div>
            <div class="game-desc">Fill in the Blank</div>
        </a>
    </div>
</div>

<?php elseif ($gameType === 'match'): ?>
<!-- Match Pairs Game -->
<div class="animate-fade-in">
    <div class="flex justify-between items-center" style="margin-bottom:16px;">
        <a href="?page=games" class="btn-ghost">← กลับ</a>
        <h2>🃏 จับคู่คำศัพท์</h2>
        <div></div>
    </div>

    <div class="game-status" id="matchStatus">
        <div class="game-status-item">
            <div class="game-status-value" id="matchMoves">0</div>
            <div class="game-status-label">ครั้ง</div>
        </div>
        <div class="game-status-item">
            <div class="game-status-value" id="matchPairs">0/6</div>
            <div class="game-status-label">คู่</div>
        </div>
        <div class="game-status-item">
            <div class="game-status-value" id="matchTime">00:00</div>
            <div class="game-status-label">เวลา</div>
        </div>
    </div>

    <div class="game-grid" id="matchGrid"></div>

    <div class="hidden text-center" id="matchComplete" style="margin-top:24px;">
        <div class="card" style="padding:30px;">
            <div style="font-size:3rem;margin-bottom:12px;">🎉</div>
            <h2>เยี่ยมมาก!</h2>
            <p style="color:var(--text-secondary);margin:8px 0;">จับคู่ครบ! ใช้ <span id="matchFinalMoves">0</span> ครั้ง ใน <span id="matchFinalTime">00:00</span></p>
            <div class="flex gap-8 justify-center" style="margin-top:16px;">
                <button class="btn btn-primary" onclick="initMatchGame()">เล่นอีกครั้ง</button>
                <a href="?page=games" class="btn btn-outline">เกมอื่น</a>
            </div>
        </div>
    </div>
</div>

<script>
const matchVocab = <?= json_encode(array_slice($vocabList, 0, 6), JSON_UNESCAPED_UNICODE) ?>;
let matchState = { cards: [], flipped: [], matched: [], moves: 0, timer: null, seconds: 0 };

function initMatchGame() {
    // Create pairs (EN + TH)
    let pairs = [];
    matchVocab.forEach((v, i) => {
        pairs.push({ id: i, type: 'en', text: v.word_en, pairId: i });
        pairs.push({ id: i, type: 'th', text: v.word_th, pairId: i });
    });
    matchState = { cards: shuffleArray(pairs), flipped: [], matched: [], moves: 0, timer: null, seconds: 0 };

    document.getElementById('matchComplete').classList.add('hidden');
    document.getElementById('matchMoves').textContent = '0';
    document.getElementById('matchPairs').textContent = '0/6';
    document.getElementById('matchTime').textContent = '00:00';

    renderMatchGrid();
    clearInterval(matchState.timer);
    matchState.timer = setInterval(() => {
        matchState.seconds++;
        document.getElementById('matchTime').textContent = formatTime(matchState.seconds);
    }, 1000);
}

function renderMatchGrid() {
    const grid = document.getElementById('matchGrid');
    grid.innerHTML = matchState.cards.map((card, i) => `
        <div class="game-card ${matchState.matched.includes(card.pairId) ? 'matched' : ''}"
             id="mcard${i}" onclick="flipMatchCard(${i})">
            ${matchState.flipped.includes(i) || matchState.matched.includes(card.pairId) ? card.text : '?'}
        </div>
    `).join('');
}

function flipMatchCard(index) {
    if (matchState.flipped.length >= 2) return;
    if (matchState.flipped.includes(index)) return;
    if (matchState.matched.includes(matchState.cards[index].pairId)) return;

    matchState.flipped.push(index);
    const el = document.getElementById('mcard' + index);
    el.classList.add('flipped');
    el.textContent = matchState.cards[index].text;

    // Speak if English
    if (matchState.cards[index].type === 'en') {
        TTS.speak(matchState.cards[index].text);
    }

    if (matchState.flipped.length === 2) {
        matchState.moves++;
        document.getElementById('matchMoves').textContent = matchState.moves;

        const [a, b] = matchState.flipped;
        const cardA = matchState.cards[a], cardB = matchState.cards[b];

        if (cardA.pairId === cardB.pairId && cardA.type !== cardB.type) {
            // Match!
            matchState.matched.push(cardA.pairId);
            document.getElementById('matchPairs').textContent = matchState.matched.length + '/6';
            setTimeout(() => {
                document.getElementById('mcard' + a).classList.add('matched');
                document.getElementById('mcard' + b).classList.add('matched');
                matchState.flipped = [];

                if (matchState.matched.length === 6) {
                    clearInterval(matchState.timer);
                    document.getElementById('matchComplete').classList.remove('hidden');
                    document.getElementById('matchFinalMoves').textContent = matchState.moves;
                    document.getElementById('matchFinalTime').textContent = formatTime(matchState.seconds);
                    launchConfetti();

                    // Save score
                    const score = Math.max(0, 100 - (matchState.moves - 6) * 5);
                    apiCall('games.php', { action: 'save_score', game_type: 'match_pairs', score, time_spent: matchState.seconds });
                }
            }, 300);
        } else {
            // No match
            setTimeout(() => {
                document.getElementById('mcard' + a).classList.remove('flipped');
                document.getElementById('mcard' + a).textContent = '?';
                document.getElementById('mcard' + b).classList.remove('flipped');
                document.getElementById('mcard' + b).textContent = '?';
                matchState.flipped = [];
            }, 800);
        }
    }
}

document.addEventListener('DOMContentLoaded', initMatchGame);
</script>

<?php elseif ($gameType === 'sentence'): ?>
<!-- Sentence Builder Game -->
<div class="animate-fade-in">
    <div class="flex justify-between items-center" style="margin-bottom:16px;">
        <a href="?page=games" class="btn-ghost">← กลับ</a>
        <h2>📝 เรียงประโยค</h2>
        <div></div>
    </div>

    <div class="game-status">
        <div class="game-status-item">
            <div class="game-status-value" id="senScore">0</div>
            <div class="game-status-label">คะแนน</div>
        </div>
        <div class="game-status-item">
            <div class="game-status-value" id="senCurrent">1/5</div>
            <div class="game-status-label">ข้อที่</div>
        </div>
    </div>

    <div id="sentenceGame">
        <div class="card" style="margin-bottom:16px;">
            <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:4px;">📝 เรียงคำให้เป็นประโยค:</p>
            <h3 id="senThai" style="color:var(--primary);"></h3>
        </div>

        <div class="sentence-area" id="sentenceArea">
            <span style="color:var(--text-light);font-size:0.85rem;">แตะคำด้านล่างเพื่อเรียง...</span>
        </div>

        <div id="wordBank" class="flex flex-wrap gap-8" style="margin-bottom:16px;justify-content:center;"></div>

        <div class="flex gap-8">
            <button class="btn btn-outline w-full" onclick="clearSentence()">🔄 ล้าง</button>
            <button class="btn btn-primary w-full" onclick="checkSentence()">✓ ตรวจ</button>
        </div>
    </div>

    <div class="hidden" id="sentenceComplete">
        <div class="card text-center" style="padding:30px;">
            <div style="font-size:3rem;margin-bottom:12px;">🎉</div>
            <h2>จบแล้ว!</h2>
            <p style="color:var(--text-secondary);margin:8px 0;">ได้ <span id="senFinalScore">0</span> / 5 คะแนน</p>
            <div class="flex gap-8 justify-center" style="margin-top:16px;">
                <button class="btn btn-primary" onclick="location.reload()">เล่นอีก</button>
                <a href="?page=games" class="btn btn-outline">เกมอื่น</a>
            </div>
        </div>
    </div>
</div>

<script>
const allSentences = <?= json_encode($sentences, JSON_UNESCAPED_UNICODE) ?>;
let gameSentences = [];
let senState = { current: 0, score: 0, placed: [] };

function initSentenceGame() {
    if (allSentences.length === 0) return;
    gameSentences = shuffleArray(allSentences).slice(0, 5);
    loadSentence();
}

function loadSentence() {
    if (!gameSentences[senState.current]) return;
    const s = gameSentences[senState.current];
    document.getElementById('senThai').textContent = s.thai;
    document.getElementById('senCurrent').textContent = `${senState.current + 1}/5`;

    const shuffled = shuffleArray(s.words.map((w, i) => ({word: w, origIndex: i})));
    document.getElementById('wordBank').innerHTML = shuffled.map((item, i) =>
        `<button class="word-chip" id="wc${i}" onclick="placeWord(${i}, '${item.word.replace(/'/g, "\\'")}')">${item.word}</button>`
    ).join('');

    senState.placed = []; // will store objects: { index, word }
    document.getElementById('sentenceArea').innerHTML = '<span style="color:var(--text-light);font-size:0.85rem;">แตะคำด้านล่างเพื่อเรียง...</span>';
}

function placeWord(index, word) {
    const chip = document.getElementById('wc' + index);
    if (chip.classList.contains('placed')) return;
    chip.classList.add('placed');
    senState.placed.push({ index, word });
    renderSentenceArea();
}

function renderSentenceArea() {
    const area = document.getElementById('sentenceArea');
    if (senState.placed.length === 0) {
        area.innerHTML = '<span style="color:var(--text-light);font-size:0.85rem;">แตะคำด้านล่างเพื่อเรียง...</span>';
        return;
    }
    area.innerHTML = senState.placed.map((item, i) =>
        `<span class="word-chip placed" onclick="removeWord(${i})">${item.word}</span>`
    ).join('');
}

function removeWord(placedIndex) {
    const removedItem = senState.placed.splice(placedIndex, 1)[0];
    document.getElementById('wc' + removedItem.index).classList.remove('placed');
    renderSentenceArea();
}

function clearSentence() {
    senState.placed = [];
    document.querySelectorAll('#wordBank .word-chip').forEach(c => c.classList.remove('placed'));
    renderSentenceArea();
}

function checkSentence() {
    const correct = gameSentences[senState.current].words;
    const currentWords = senState.placed.map(p => p.word);
    const isCorrect = JSON.stringify(currentWords) === JSON.stringify(correct);

    if (isCorrect) {
        senState.score++;
        document.getElementById('senScore').textContent = senState.score;
        showToast('ถูกต้อง! 🎉', '✅');
        TTS.speak(correct.join(' '));
    } else {
        showToast('ลองอีกครั้ง! คำตอบ: ' + correct.join(' '), '❌');
    }

    senState.current++;
    if (senState.current >= 5) {
        setTimeout(() => {
            document.getElementById('sentenceGame').classList.add('hidden');
            document.getElementById('sentenceComplete').classList.remove('hidden');
            document.getElementById('senFinalScore').textContent = senState.score;
            if (senState.score >= 3) launchConfetti();
            apiCall('games.php', { action: 'save_score', game_type: 'sentence_order', score: senState.score * 20, time_spent: 0 });
        }, 1500);
    } else {
        setTimeout(loadSentence, 1500);
    }
}

document.addEventListener('DOMContentLoaded', initSentenceGame);
</script>

<?php elseif ($gameType === 'fill'): ?>
<!-- Fill in the Blank Game -->
<div class="animate-fade-in">
    <div class="flex justify-between items-center" style="margin-bottom:16px;">
        <a href="?page=games" class="btn-ghost">← กลับ</a>
        <h2>✏️ เติมคำ</h2>
        <div></div>
    </div>

    <div class="game-status">
        <div class="game-status-item">
            <div class="game-status-value" id="fillScore">0</div>
            <div class="game-status-label">คะแนน</div>
        </div>
        <div class="game-status-item">
            <div class="game-status-value" id="fillCurrent">1/5</div>
            <div class="game-status-label">ข้อที่</div>
        </div>
    </div>

    <div id="fillGame">
        <div class="card" style="margin-bottom:16px;">
            <div class="fill-text" id="fillText"></div>
        </div>
        <div class="fill-options" id="fillOptions"></div>
    </div>

    <div class="hidden" id="fillComplete">
        <div class="card text-center" style="padding:30px;">
            <div style="font-size:3rem;margin-bottom:12px;">🎉</div>
            <h2>จบแล้ว!</h2>
            <p style="color:var(--text-secondary);margin:8px 0;">ได้ <span id="fillFinalScore">0</span> / 5 คะแนน</p>
            <div class="flex gap-8 justify-center" style="margin-top:16px;">
                <button class="btn btn-primary" onclick="location.reload()">เล่นอีก</button>
                <a href="?page=games" class="btn btn-outline">เกมอื่น</a>
            </div>
        </div>
    </div>
</div>

<script>
const allFills = <?= json_encode($fillBlanks, JSON_UNESCAPED_UNICODE) ?>;
let gameFills = [];
let fillState = { current: 0, score: 0 };

function initFillGame() {
    if (allFills.length === 0) return;
    gameFills = shuffleArray(allFills).slice(0, 5);
    loadFill();
}

function loadFill() {
    if (!gameFills[fillState.current]) return;
    const q = gameFills[fillState.current];
    document.getElementById('fillCurrent').textContent = `${fillState.current + 1}/5`;

    const parts = q.text.split('___');
    document.getElementById('fillText').innerHTML =
        parts[0] + '<span class="fill-blank" id="fillBlank">___</span>' + (parts[1] || '');

    const shuffled = shuffleArray(q.options);
    document.getElementById('fillOptions').innerHTML = shuffled.map(opt =>
        `<button class="word-chip" onclick="selectFill('${opt.replace(/'/g, "\\'")}')">${opt}</button>`
    ).join('');
}

function selectFill(answer) {
    const correct = gameFills[fillState.current].answer;
    const blank = document.getElementById('fillBlank');
    blank.textContent = answer;
    blank.classList.add('filled');

    // Disable all options
    document.querySelectorAll('#fillOptions .word-chip').forEach(b => b.style.pointerEvents = 'none');

    if (answer === correct) {
        blank.classList.add('correct');
        fillState.score++;
        document.getElementById('fillScore').textContent = fillState.score;
        showToast('ถูกต้อง! 🎉', '✅');
        TTS.speak(q.text.replace('___', correct));
    } else {
        blank.classList.add('wrong');
        showToast('คำตอบที่ถูก: ' + correct, '❌');
    }

    fillState.current++;
    if (fillState.current >= 5) {
        setTimeout(() => {
            document.getElementById('fillGame').classList.add('hidden');
            document.getElementById('fillComplete').classList.remove('hidden');
            document.getElementById('fillFinalScore').textContent = fillState.score;
            if (fillState.score >= 3) launchConfetti();
            apiCall('games.php', { action: 'save_score', game_type: 'fill_blank', score: fillState.score * 20, time_spent: 0 });
        }, 1500);
    } else {
        setTimeout(loadFill, 1500);
    }
}

document.addEventListener('DOMContentLoaded', initFillGame);
</script>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
