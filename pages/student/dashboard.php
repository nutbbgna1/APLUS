<?php
// ============================================================
// LinguaMax — Student Dashboard
// ============================================================
include __DIR__ . '/../../includes/header.php';

$user = $currentUser;
$stats = getUserStats($user['id']);
$streak = getStreak($user['id']);
$dailyChallenge = getTodayChallenge();
$dailyDone = isDailyChallengeCompleted($user['id']);
$db = getDB();

// Recent lessons
$stmt = $db->prepare("SELECT l.* FROM lessons l ORDER BY l.sort_order LIMIT 3");
$stmt->execute();
$recentLessons = $stmt->fetchAll();

// Recent exams
$stmt = $db->prepare("SELECT * FROM exams ORDER BY id LIMIT 3");
$stmt->execute();
$recentExams = $stmt->fetchAll();

// New badges check
$newBadges = checkAndAwardBadges($user['id']);

// Cards to review today (flashcard)
$stmt = $db->prepare("SELECT COUNT(*) as cnt FROM flashcard_progress WHERE user_id = ? AND next_review <= CURDATE()");
$stmt->execute([$user['id']]);
$cardsToReview = $stmt->fetch()['cnt'];

// Total vocab
$stmt = $db->prepare("SELECT COUNT(*) as cnt FROM vocabulary");
$stmt->execute();
$totalVocab = $stmt->fetch()['cnt'];
?>

<!-- Home Header -->
<div class="home-header flex justify-between items-center animate-fade-in" style="margin-bottom: 24px; padding-top: 12px;">
    <div class="flex items-center gap-12">
        <div class="avatar" style="background:var(--secondary);width:50px;height:50px;font-size:1.5rem;box-shadow:var(--shadow-sm);"><?= mb_substr($user['fname'], 0, 1) ?></div>
        <div>
            <div style="font-weight: 800; font-size: 1.1rem; font-family: var(--font-display);">Evening, <?= sanitize($user['nickname']) ?></div>
            <div style="color:var(--text-secondary); font-size: 0.85rem;">Let's learn more</div>
        </div>
    </div>
    <div class="streak-badge flex items-center" style="background: var(--surface); padding: 6px 12px; border-radius: 20px; box-shadow: var(--shadow-sm); font-weight: 800; gap: 6px;">
        <span style="color:#FF5722; font-size: 1.2rem;">🔥</span>
        <span style="font-size: 1.1rem;"><?= $streak['current_streak'] ?></span>
    </div>
</div>

<!-- Hero Banner -->
<div class="hero-banner animate-fade-in" style="background: linear-gradient(135deg, #4A8CFF 0%, #3B7AE5 100%); border-radius: var(--radius-xl); display: flex; align-items: center; justify-content: space-between; position: relative; overflow: hidden; margin-bottom: 24px; box-shadow: var(--shadow-lg); min-height: 170px;">
    
    <!-- Decorative background blobs -->
    <div style="position: absolute; top: -30px; right: -20px; width: 150px; height: 150px; background-color: rgba(255, 255, 255, 0.1); border-radius: 50%; z-index: 1;"></div>
    <div style="position: absolute; bottom: -40px; right: 25%; width: 100px; height: 100px; background-color: rgba(255, 255, 255, 0.15); border-radius: 50%; z-index: 1;"></div>

    <div style="position: relative; z-index: 2; width: 55%; padding: 24px;">
        <h2 style="font-size: 1.5rem; margin-bottom: 8px; font-weight: 900; line-height: 1.2; color: white;">Howdy partner!</h2>
        <p style="font-size: 0.95rem; opacity: 0.95; margin-bottom: 16px; font-weight: 600; line-height: 1.4; color: white;">It's time for our periodic test. Ya' ready?</p>
        <a href="?page=exams" class="btn" style="background: white; color: #4A8CFF; border-radius: var(--radius-lg); padding: 8px 24px; font-weight: 900; font-size: 0.95rem; display: inline-block; box-shadow: 0 4px 10px rgba(0,0,0,0.1); text-decoration: none; transition: transform 0.2s;">Start Now</a>
    </div>
    
    <div style="width: 45%; height: 100%; position: absolute; right: 0; bottom: 0; display: flex; align-items: flex-end; justify-content: center; z-index: 2;">
        <img src="<?= SITE_URL ?>/assets/img/hero_laptop_transparent.png" style="height: 150px; max-width: 100%; object-fit: contain; margin-bottom: -5px;" alt="Hero">
    </div>
</div>

<!-- Horizontal Calendar -->
<div class="calendar-strip flex gap-12 animate-fade-in" style="overflow-x: auto; padding-bottom: 12px; margin-bottom: 16px; scrollbar-width: none; -webkit-overflow-scrolling: touch;">
    <?php
    $days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
    $todayIndex = date('w');
    for($i=0; $i<7; $i++) {
        $isActive = $i == $todayIndex;
        $date = date('d', strtotime("-".($todayIndex - $i)." days"));
        echo '
        <div class="cal-day '.($isActive ? 'active' : '').'" style="min-width: 65px; padding: 14px 10px; border-radius: var(--radius-lg); text-align: center; border: 1px solid var(--border); '.($isActive ? 'background:#4A8CFF; color:white; border:none; box-shadow: 0 8px 16px rgba(74,140,255,0.3);' : 'background:var(--surface);').'">
            <div style="font-size:0.75rem; font-weight:700; margin-bottom:4px; '.($isActive?'':'color:var(--text-secondary);').'">'.$days[$i].'</div>
            <div style="font-size:1.3rem; font-weight:900;">'.$date.'</div>
        </div>';
    }
    ?>
</div>
<style>
.calendar-strip::-webkit-scrollbar { display: none; }
</style>

<!-- Skills Section -->
<h2 class="animate-fade-in" style="color: #2B4D8A; font-size: 1.5rem; margin-bottom: 16px; font-weight: 900;">Let's Hone Our Skills</h2>

<div class="skills-grid animate-fade-in" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 30px;">
    
    <!-- Speaking Practice (tall) -->
    <a href="?page=lessons" class="skill-card-tall" style="background-color: #FFCD90; border-radius: var(--radius-xl); display: flex; flex-direction: column; justify-content: space-between; text-decoration: none; grid-row: span 2; position: relative; overflow: hidden; box-shadow: 0 6px 12px rgba(255,154,66,0.15); transition: transform 0.2s;">
        <!-- Orange Curve Background -->
        <div style="position: absolute; bottom: -5%; left: -10%; width: 120%; height: 80%; background-color: #F58D38; border-radius: 50% 50% 0 0; z-index: 1;"></div>
        
        <div style="position: relative; z-index: 2; color: #1A2942; padding: 20px; padding-bottom: 0;">
            <h3 style="font-size: 1.3rem; font-weight: 900; line-height: 1.1; margin-bottom: 6px;">Speaking<br>Practice</h3>
            <p style="font-size: 0.85rem; font-weight: 700; opacity: 0.8;">With Elise</p>
        </div>
        <img src="<?= SITE_URL ?>/assets/img/icon_speaking.png" style="width: 130%; margin-left: -15%; margin-bottom: -25px; position: relative; z-index: 2; margin-top: 10px;" alt="Speaking">
    </a>

    <!-- Vocab Sprint (wide) -->
    <a href="?page=flashcards" class="skill-card-wide" style="background-color: #D1E3FF; border-radius: var(--radius-xl); padding: 20px; display: flex; justify-content: space-between; text-decoration: none; position: relative; overflow: hidden; box-shadow: 0 6px 12px rgba(140,179,255,0.15); transition: transform 0.2s;">
        <!-- Blue Curve Background -->
        <div style="position: absolute; bottom: -20%; right: -10%; width: 120%; height: 140%; background-color: #95BEFF; border-radius: 100% 0 0 0; z-index: 1;"></div>
        
        <div style="position: relative; z-index: 2; width: 70%; color: #1A2942;">
            <h3 style="font-size: 1.2rem; font-weight: 900; line-height: 1.1; margin-bottom: 6px;">Vocab<br>Sprint</h3>
            <p style="font-size: 0.8rem; font-weight: 700; opacity: 0.8; line-height: 1.2;">Challenge Your Skill</p>
        </div>
        <img src="<?= SITE_URL ?>/assets/img/icon_vocab.png" style="width: 80px; height: 80px; position: absolute; right: -5px; bottom: -5px; z-index: 2;" alt="Vocab">
    </a>

    <!-- Practice (wide) -->
    <a href="?page=practice" class="skill-card-wide" style="background-color: #FFE6F0; border-radius: var(--radius-xl); padding: 20px; display: flex; align-items: center; justify-content: space-between; text-decoration: none; position: relative; overflow: hidden; box-shadow: 0 6px 12px rgba(255,168,197,0.15); transition: transform 0.2s;">
        <!-- Pink Circle Background -->
        <div style="position: absolute; top: -20%; right: -10%; width: 80%; height: 140%; background-color: #FFB8CF; border-radius: 50%; z-index: 1;"></div>
        
        <h3 style="font-size: 1.2rem; font-weight: 900; position: relative; z-index: 2; color: #1A2942;">Practice</h3>
        <img src="<?= SITE_URL ?>/assets/img/icon_practice.png" style="width: 70px; height: 70px; position: absolute; right: 5px; top: 50%; transform: translateY(-50%); z-index: 2;" alt="Practice">
    </a>
</div>

<style>
.skill-card-tall:active, .skill-card-wide:active { transform: scale(0.97); }
</style>

<?php
// Show new badge notifications
if (!empty($newBadges)):
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($newBadges as $badge): ?>
    showBadgeToast('<?= $badge['icon'] ?>', '<?= sanitize($badge['name_th']) ?>');
    <?php endforeach; ?>
});
</script>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
