<?php
// ============================================================
// LinguaMax — Flashcard Page (Spaced Repetition)
// ============================================================
include __DIR__ . '/../../includes/header.php';

$db = getDB();
$userId = $_SESSION['user_id'];

// Get vocab that needs review (next_review <= today or never reviewed)
$stmt = $db->prepare("
    SELECT v.*, fp.ease_factor, fp.interval_days, fp.repetitions, fp.next_review, fp.times_correct, fp.times_wrong
    FROM vocabulary v
    LEFT JOIN flashcard_progress fp ON v.id = fp.vocabulary_id AND fp.user_id = ?
    WHERE fp.next_review <= CURDATE() OR fp.id IS NULL
    ORDER BY fp.next_review ASC, RAND()
    LIMIT 20
");
$stmt->execute([$userId]);
$cardsToReview = $stmt->fetchAll();

// Get total vocab stats
$stmt = $db->prepare("SELECT COUNT(*) as total FROM vocabulary");
$stmt->execute();
$totalVocab = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as learned FROM flashcard_progress WHERE user_id = ? AND repetitions >= 1");
$stmt->execute([$userId]);
$learnedVocab = $stmt->fetch()['learned'];

$stmt = $db->prepare("
    SELECT COUNT(*) as due FROM flashcard_progress
    WHERE user_id = ? AND next_review <= CURDATE()
");
$stmt->execute([$userId]);
$dueCount = $stmt->fetch()['due'];

// Get new cards count
$stmt = $db->prepare("
    SELECT COUNT(*) as new_count FROM vocabulary v
    LEFT JOIN flashcard_progress fp ON v.id = fp.vocabulary_id AND fp.user_id = ?
    WHERE fp.id IS NULL
");
$stmt->execute([$userId]);
$newCount = $stmt->fetch()['new_count'];
?>

<div class="animate-fade-in">
    <div class="flex justify-between items-center" style="margin-bottom:20px;">
        <h1>🃏 Flashcard</h1>
        <span class="badge badge-primary"><?= $learnedVocab ?>/<?= $totalVocab ?> คำ</span>
    </div>

    <!-- Stats -->
    <div class="flex gap-12" style="margin-bottom:24px;">
        <div class="card" style="flex:1;text-align:center;padding:14px;">
            <div style="font-size:1.5rem;">📚</div>
            <div style="font-family:var(--font-display);font-weight:900;font-size:1.3rem;color:var(--primary);"><?= count($cardsToReview) ?></div>
            <div style="font-size:0.75rem;color:var(--text-secondary);">รอทบทวน</div>
        </div>
        <div class="card" style="flex:1;text-align:center;padding:14px;">
            <div style="font-size:1.5rem;">✅</div>
            <div style="font-family:var(--font-display);font-weight:900;font-size:1.3rem;color:var(--success);"><?= $learnedVocab ?></div>
            <div style="font-size:0.75rem;color:var(--text-secondary);">จำได้แล้ว</div>
        </div>
        <div class="card" style="flex:1;text-align:center;padding:14px;">
            <div style="font-size:1.5rem;">🆕</div>
            <div style="font-family:var(--font-display);font-weight:900;font-size:1.3rem;color:var(--accent);"><?= $newCount ?></div>
            <div style="font-size:0.75rem;color:var(--text-secondary);">คำใหม่</div>
        </div>
    </div>

    <?php if (empty($cardsToReview)): ?>
        <div class="card text-center" style="padding:40px;">
            <div style="font-size:4rem;margin-bottom:16px;">🎉</div>
            <h2>ทบทวนครบแล้ว!</h2>
            <p style="color:var(--text-secondary);margin-top:8px;">ไม่มีคำศัพท์ที่ต้องทบทวนวันนี้ กลับมาใหม่พรุ่งนี้นะ!</p>
            <a href="?page=dashboard" class="btn btn-primary" style="margin-top:16px;">กลับหน้าแรก</a>
        </div>
    <?php else: ?>
        <!-- Flashcard Area -->
        <div id="flashcard-app">
            <!-- Counter -->
            <div class="text-center" style="margin-bottom:12px;">
                <span class="flashcard-counter" id="cardCounter">1 / <?= count($cardsToReview) ?></span>
            </div>

            <!-- Flashcard -->
            <div class="flashcard-container">
                <div class="flashcard" id="flashcard" onclick="flipCard()">
                    <div class="flashcard-face flashcard-front">
                        <button class="speak-btn" onclick="event.stopPropagation(); TTS.speak(currentCard().word_en)" style="margin-bottom:16px;">🔊</button>
                        <div class="flashcard-word" id="cardWord"></div>
                        <div class="flashcard-pronunciation" id="cardPron"></div>
                        <div style="font-size:0.8rem;color:var(--text-light);margin-top:auto;">แตะเพื่อดูคำตอบ</div>
                    </div>
                    <div class="flashcard-face flashcard-back">
                        <div class="flashcard-meaning" id="cardMeaning"></div>
                        <div class="flashcard-example" id="cardExample"></div>
                        <button class="speak-btn" onclick="event.stopPropagation(); TTS.speak(currentCard().word_en)" style="margin-top:16px;">🔊</button>
                    </div>
                </div>
            </div>

            <!-- SR Rating Buttons (shown after flip) -->
            <div class="sr-buttons hidden" id="srButtons">
                <button class="sr-btn sr-btn-hard" onclick="rateCard(1)">
                    😣<br>ยาก
                </button>
                <button class="sr-btn sr-btn-good" onclick="rateCard(3)">
                    🤔<br>พอได้
                </button>
                <button class="sr-btn sr-btn-easy" onclick="rateCard(5)">
                    😄<br>ง่าย
                </button>
            </div>
        </div>

        <!-- Completion Screen -->
        <div class="hidden" id="flashcardComplete">
            <div class="card text-center" style="padding:40px;">
                <div style="font-size:4rem;margin-bottom:16px;">🎉</div>
                <h2>เยี่ยมมาก!</h2>
                <p style="color:var(--text-secondary);margin:12px 0;">ทบทวนครบ <strong id="completedCount">0</strong> คำแล้ว!</p>
                <div class="flex gap-8 justify-center" style="margin-top:16px;">
                    <a href="?page=flashcards" class="btn btn-primary">ทบทวนอีกครั้ง</a>
                    <a href="?page=dashboard" class="btn btn-outline">กลับหน้าแรก</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($cardsToReview)): ?>
<script>
const cards = <?= json_encode($cardsToReview, JSON_UNESCAPED_UNICODE) ?>;
let currentIndex = 0;
let isFlipped = false;
let totalReviewed = 0;

function currentCard() { return cards[currentIndex]; }

function showCard() {
    const card = currentCard();
    document.getElementById('cardWord').textContent = card.word_en;
    document.getElementById('cardPron').textContent = card.pronunciation || '';
    document.getElementById('cardMeaning').textContent = card.word_th;
    document.getElementById('cardExample').textContent = card.example_sentence || '';
    document.getElementById('cardCounter').textContent = `${currentIndex + 1} / ${cards.length}`;

    // Reset flip
    isFlipped = false;
    document.getElementById('flashcard').classList.remove('flipped');
    document.getElementById('srButtons').classList.add('hidden');

    // Auto-speak
    setTimeout(() => TTS.speak(card.word_en), 300);
}

function flipCard() {
    isFlipped = !isFlipped;
    document.getElementById('flashcard').classList.toggle('flipped', isFlipped);
    if (isFlipped) {
        document.getElementById('srButtons').classList.remove('hidden');
    } else {
        document.getElementById('srButtons').classList.add('hidden');
    }
}

async function rateCard(quality) {
    const card = currentCard();
    totalReviewed++;

    // Send to API
    await apiCall('flashcards.php', {
        action: 'review',
        vocabulary_id: card.id,
        quality: quality
    });

    // Next card
    currentIndex++;
    if (currentIndex >= cards.length) {
        // All done!
        document.getElementById('flashcard-app').classList.add('hidden');
        document.getElementById('flashcardComplete').classList.remove('hidden');
        document.getElementById('completedCount').textContent = totalReviewed;
        launchConfetti();
    } else {
        showCard();
    }
}

// Init
document.addEventListener('DOMContentLoaded', showCard);
</script>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
