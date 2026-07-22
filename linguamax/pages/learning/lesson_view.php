<?php
include __DIR__ . '/../../includes/header.php';
$db = getDB();
$userId = $_SESSION['user_id'];
$lessonId = intval($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM lessons WHERE id = ?");
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch();
if (!$lesson) { header('Location: ?page=lessons'); exit; }

// Get vocab for this lesson
$stmt = $db->prepare("SELECT * FROM vocabulary WHERE lesson_id = ?");
$stmt->execute([$lessonId]);
$vocab = $stmt->fetchAll();

// Mark as completed
$stmt = $db->prepare("INSERT IGNORE INTO lesson_progress (user_id, lesson_id, completed, completed_at) VALUES (?, ?, TRUE, NOW())");
$inserted = $stmt->execute([$userId, $lessonId]);
if ($stmt->rowCount() > 0) {
    addXP($userId, 5);
    updateStreak($userId);
    
    // Log activity
    $logStmt = $db->prepare("INSERT INTO user_activity_log (user_id, activity_type, activity_details) VALUES (?, 'lesson', ?)");
    $logStmt->execute([$userId, "Completed Lesson: " . $lesson['title']]);
}

// Related exam
$stmt = $db->prepare("SELECT * FROM exams WHERE lesson_id = ?");
$stmt->execute([$lessonId]);
$relatedExam = $stmt->fetch();
?>

<div class="lesson-detail-page animate-fade-in" style="background: #F8FAFC; min-height: 100vh; padding: 16px 16px 100px 16px; margin: -16px; margin-top: 0;">
    
    <!-- Top Navigation -->
    <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px; padding-top: 8px;">
        <a href="?page=lessons" style="width: 44px; height: 44px; background: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #1E293B; text-decoration: none; box-shadow: 0 4px 15px rgba(0,0,0,0.03); font-size: 1.2rem; flex-shrink: 0;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1 style="font-size: 1.25rem; font-weight: 900; color: #1E293B; margin: 0; flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            Lesson <?= $lesson['sort_order'] ?>
        </h1>
    </div>

    <!-- Title & Intro Card -->
    <div style="background: white; border-radius: 24px; padding: 24px; margin-bottom: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
        <h2 style="font-size: 1.6rem; font-weight: 900; color: #1E293B; margin-bottom: 8px; margin-top: 0; line-height: 1.3;">
            <?= sanitize($lesson['title']) ?>
        </h2>
        <p style="font-size: 0.95rem; color: #64748B; font-weight: 600; line-height: 1.5; margin: 0;">
            <?= sanitize($lesson['description']) ?>
        </p>
    </div>

    <!-- Stats Row -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 24px;">
        <div style="background: white; border-radius: 20px; padding: 16px 8px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="font-size: 1.5rem; margin-bottom: 8px;">📊</div>
            <div style="font-size: 1.05rem; font-weight: 900; color: #1E293B; margin-bottom: 2px; text-transform: capitalize;"><?= sanitize($lesson['level']) ?></div>
            <div style="font-size: 0.75rem; font-weight: 700; color: #94A3B8;">Level</div>
        </div>
        <div style="background: white; border-radius: 20px; padding: 16px 8px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="font-size: 1.5rem; margin-bottom: 8px;">🔖</div>
            <div style="font-size: 1.05rem; font-weight: 900; color: #1E293B; margin-bottom: 2px;"><?= $lesson['sort_order'] ?></div>
            <div style="font-size: 0.75rem; font-weight: 700; color: #94A3B8;">Chapter</div>
        </div>
        <div style="background: white; border-radius: 20px; padding: 16px 8px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="font-size: 1.5rem; margin-bottom: 8px;">📝</div>
            <div style="font-size: 1.05rem; font-weight: 900; color: #1E293B; margin-bottom: 2px;"><?= count($vocab) ?></div>
            <div style="font-size: 0.75rem; font-weight: 700; color: #94A3B8;">Words</div>
        </div>
    </div>

    <!-- Content Box -->
    <?php if (!empty(trim(strip_tags($lesson['content'])))): ?>
    <div style="background: white; border-radius: 24px; padding: 24px; margin-bottom: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
        <h3 style="font-size: 1.15rem; font-weight: 900; color: #1E293B; margin-bottom: 16px; margin-top: 0;">Lesson Content</h3>
        <div class="lesson-content-box" style="font-size: 0.95rem; color: #475569; line-height: 1.7; font-weight: 500;">
            <?= $lesson['content'] ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Vocabulary -->
    <?php if (!empty($vocab)): ?>
    <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: flex-end;">
        <h3 style="font-size: 1.25rem; font-weight: 900; color: #1E293B; margin: 0;">Vocabulary</h3>
        <div style="font-size: 0.75rem; font-weight: 700; color: #94A3B8;">Tap to listen 🔊</div>
    </div>
    
    <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 32px;">
        <?php foreach ($vocab as $v): ?>
        <div class="vocab-card" onclick="TTS.speak('<?= addslashes($v['word_en']) ?>')" style="background: white; border-radius: 20px; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 15px rgba(0,0,0,0.02); cursor: pointer; transition: transform 0.1s;">
            <div>
                <div style="font-weight: 900; font-size: 1.15rem; color: #1E293B; margin-bottom: 4px;"><?= sanitize($v['word_en']) ?></div>
                <div style="font-size: 0.9rem; font-weight: 600; color: #64748B;"><?= sanitize($v['word_th']) ?></div>
            </div>
            <div style="width: 44px; height: 44px; border-radius: 50%; background: #F3E8FF; color: #8CB3FF; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                <i class="fa-solid fa-volume-high"></i>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Action Button -->
    <div style="position: sticky; bottom: 24px; z-index: 100; margin-top: 32px;">
        <?php if ($relatedExam): ?>
        <a href="?page=exam&id=<?= $relatedExam['id'] ?>" style="display: flex; align-items: center; justify-content: center; gap: 12px; width: 100%; background: #4A8CFF; color: white; padding: 18px; border-radius: 24px; font-size: 1.15rem; font-weight: 800; text-decoration: none; box-shadow: 0 8px 25px rgba(74, 140, 255, 0.4);">
            Start Exam <i class="fa-solid fa-arrow-right"></i>
        </a>
        <?php else: ?>
        <a href="?page=lessons" style="display: flex; align-items: center; justify-content: center; gap: 12px; width: 100%; background: #4A8CFF; color: white; padding: 18px; border-radius: 24px; font-size: 1.15rem; font-weight: 800; text-decoration: none; box-shadow: 0 8px 25px rgba(74, 140, 255, 0.4);">
            Finish Lesson <i class="fa-solid fa-check"></i>
        </a>
        <?php endif; ?>
    </div>
</div>

<style>
.lesson-content-box img { max-width: 100%; height: auto; border-radius: 16px; margin-top: 12px; }
.vocab-card:active { transform: scale(0.96); }
.bottom-nav { display: none !important; }
body { background: #F8FAFC !important; }
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
