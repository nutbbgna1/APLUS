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
$stmt->execute([$userId, $lessonId]);
addXP($userId, 5);
updateStreak($userId);

// Related exam
$stmt = $db->prepare("SELECT * FROM exams WHERE lesson_id = ?");
$stmt->execute([$lessonId]);
$relatedExam = $stmt->fetch();
?>

<div class="lesson-detail-page animate-fade-in" style="background: white; min-height: 100vh; padding: 20px; padding-bottom: 100px;">
    
    <!-- Top Navigation -->
    <div style="margin-bottom: 20px;">
        <a href="?page=lessons" style="color: var(--primary); font-size: 1.5rem; text-decoration: none; font-weight: 800;">
            <i class="fa-solid fa-chevron-left"></i>
        </a>
    </div>

    <!-- Illustration -->
    <div style="text-align: center; margin-bottom: 24px; position: relative;">
        <!-- Abstract background blobs could go here -->
        <div style="background: #EBF4FF; width: 250px; height: 250px; border-radius: 40% 60% 70% 30% / 40% 50% 60% 50%; position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); z-index: 1; opacity: 0.5;"></div>
        <img src="<?= SITE_URL ?>/assets/img/hero_sticky.png" style="width: 280px; max-width: 100%; position: relative; z-index: 2;" alt="Lesson Image">
    </div>

    <!-- Title -->
    <h1 style="text-align: center; color: var(--primary-dark); font-size: 2rem; font-weight: 900; margin-bottom: 24px;">
        <?= sanitize($lesson['title']) ?>
    </h1>

    <!-- Stats Row -->
    <div style="display: flex; gap: 12px; margin-bottom: 24px;">
        <div style="flex: 1; background: var(--primary-light); padding: 16px 8px; border-radius: var(--radius-lg); text-align: center; color: white;">
            <div style="font-size: 0.8rem; font-weight: 700; margin-bottom: 4px;">Level</div>
            <div style="font-size: 1.1rem; font-weight: 900;"><?= ucfirst($lesson['level']) ?></div>
        </div>
        <div style="flex: 1; background: var(--primary-light); padding: 16px 8px; border-radius: var(--radius-lg); text-align: center; color: white;">
            <div style="font-size: 0.8rem; font-weight: 700; margin-bottom: 4px;">Chapter</div>
            <div style="font-size: 1.1rem; font-weight: 900;"><?= $lesson['sort_order'] ?></div>
        </div>
        <div style="flex: 1; background: var(--primary-light); padding: 16px 8px; border-radius: var(--radius-lg); text-align: center; color: white;">
            <div style="font-size: 0.8rem; font-weight: 700; margin-bottom: 4px;">Words</div>
            <div style="font-size: 1.1rem; font-weight: 900;"><?= count($vocab) ?></div>
        </div>
    </div>

    <!-- Description Box -->
    <div style="background: var(--primary-light); padding: 24px; border-radius: var(--radius-xl); color: white; margin-bottom: 24px;">
        <h3 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 12px; opacity: 0.9;">Description & Content</h3>
        <div style="font-size: 0.95rem; line-height: 1.6; opacity: 0.95; font-weight: 600;">
            <p style="margin-bottom: 16px;"><?= sanitize($lesson['description']) ?></p>
            <div class="lesson-content-box" style="background: rgba(255,255,255,0.2); padding: 16px; border-radius: var(--radius-lg);">
                <?= $lesson['content'] ?: 'เนื้อหาจะเพิ่มโดยครูผู้สอน' ?>
            </div>
        </div>
    </div>

    <!-- Vocabulary Cards -->
    <?php if (!empty($vocab)): ?>
    <h3 style="margin-bottom:16px; font-weight: 900; color: var(--primary-dark);">📖 Vocabulary</h3>
    <div class="vocab-grid" style="margin-bottom:30px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
        <?php foreach ($vocab as $v): ?>
        <div class="vocab-card" onclick="TTS.speak('<?= addslashes($v['word_en']) ?>')" style="background: white; border: 2px solid var(--border); border-radius: var(--radius-lg); padding: 16px; text-align: center; cursor: pointer; box-shadow: var(--shadow-sm); transition: transform 0.2s;">
            <div class="vocab-en" style="font-weight: 900; font-size: 1.1rem; color: var(--primary); margin-bottom: 4px;"><?= sanitize($v['word_en']) ?></div>
            <div class="vocab-th" style="font-size: 0.9rem; color: var(--text-secondary);"><?= sanitize($v['word_th']) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Action Button -->
    <?php if ($relatedExam): ?>
    <a href="?page=exam&id=<?= $relatedExam['id'] ?>" class="btn btn-primary" style="display: block; width: 100%; text-align: center; padding: 18px; border-radius: var(--radius-xl); font-size: 1.2rem; font-weight: 800; box-shadow: var(--shadow); position: sticky; bottom: 20px; z-index: 100;">
        🚀 Start Exam
    </a>
    <?php else: ?>
    <a href="?page=lessons" class="btn btn-primary" style="display: block; width: 100%; text-align: center; padding: 18px; border-radius: var(--radius-xl); font-size: 1.2rem; font-weight: 800; box-shadow: var(--shadow); position: sticky; bottom: 20px; z-index: 100;">
        🚀 Finish Lesson
    </a>
    <?php endif; ?>
</div>

<style>
.lesson-content-box img { max-width: 100%; height: auto; border-radius: 8px; margin-top: 10px; }
.vocab-card:active { transform: scale(0.95); }
.bottom-nav { display: none !important; } /* Hide bottom nav on detail page for app feel */
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
