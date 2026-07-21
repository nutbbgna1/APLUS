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

// Recent lesson for "Continue Learning"
$stmt = $db->prepare("SELECT * FROM lessons ORDER BY sort_order ASC LIMIT 1");
$stmt->execute();
$currentLesson = $stmt->fetch();
?>

<!-- Page Sky-Blue Header Background Wrapper -->
<div class="home-wrapper" style="margin: -20px -20px 0 -20px; padding: 20px; background: linear-gradient(180deg, #CEF3FF 0%, #EBF9FF 50%, #FFFFFF 100%); min-height: 100vh;">

    <!-- Top Navigation Header -->
    <div class="home-top-header flex justify-between items-center animate-fade-in" style="margin-bottom: 24px; padding-top: 8px;">
        <div class="flex items-center gap-12">
            <div class="avatar" style="background: <?= $user['avatar_color'] ?? '#BDC3C7' ?>; width: 48px; height: 48px; font-size: 1.4rem; font-weight: 900; box-shadow: 0 4px 10px rgba(0,0,0,0.08); display: flex; align-items: center; justify-content: center; border-radius: 50%; color: white;">
                <?= mb_substr($user['fname'], 0, 1) ?>
            </div>
            <div>
                <div style="font-weight: 800; font-size: 1.15rem; color: #2D3436; font-family: var(--font-display);">Hello, <?= sanitize($user['nickname']) ?></div>
                <div style="color: #636E72; font-size: 0.85rem; font-weight: 600;">Ready to learn?</div>
            </div>
        </div>
        
        <div class="flex items-center gap-8">
            <!-- Streak Pill -->
            <div style="background: white; padding: 8px 14px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); font-weight: 800; display: flex; align-items: center; gap: 6px; font-size: 0.95rem; color: #2D3436;">
                <span style="font-size: 1.1rem;">🔥</span>
                <span><?= $streak['current_streak'] ?></span>
            </div>
            <!-- Gems/Coins Pill -->
            <div style="background: white; padding: 8px 14px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); font-weight: 800; display: flex; align-items: center; gap: 6px; font-size: 0.95rem; color: #2D3436;">
                <span style="font-size: 1.1rem;">💎</span>
                <span><?= number_format($currentUser['coins'] ?? 250) ?></span>
            </div>
        </div>
    </div>

    <!-- Today's XP Progress Card -->
    <div class="xp-card animate-fade-in" style="background: white; border-radius: 24px; padding: 20px 24px; box-shadow: 0 10px 30px rgba(108, 92, 231, 0.08); margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
        <div style="flex: 1; padding-right: 16px;">
            <div style="font-size: 0.9rem; font-weight: 800; color: #2D3436; margin-bottom: 6px;">Today's XP</div>
            <div style="margin-bottom: 10px;">
                <span style="color: #6C5CE7; font-weight: 900; font-size: 1.45rem;"><?= $stats['xp'] ?? 120 ?></span>
                <span style="color: #B2BEC3; font-weight: 800; font-size: 1.05rem;">/ 250 XP</span>
            </div>
            <!-- Progress Bar -->
            <div style="background: #F1F2F6; height: 10px; border-radius: 10px; width: 100%; overflow: hidden;">
                <div style="background: #6C5CE7; width: <?= min(100, max(10, (($stats['xp'] ?? 120) / 250) * 100)) ?>%; height: 100%; border-radius: 10px; transition: width 0.5s ease;"></div>
            </div>
        </div>
        
        <!-- Daily Reward -->
        <div style="text-align: center; flex-shrink: 0;">
            <div style="background: #FFF4E6; border-radius: 18px; width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; margin: 0 auto 6px; font-size: 1.8rem; box-shadow: 0 4px 12px rgba(255, 168, 38, 0.15);">
                🎁
            </div>
            <div style="font-size: 0.75rem; font-weight: 800; color: #6C5CE7;">Daily Reward</div>
        </div>
    </div>

    <!-- Motivational Quote Card (Floating Right) -->
    <div class="quote-card animate-fade-in" style="background: #E8FDF5; border-radius: 20px; padding: 16px 20px; margin-bottom: 24px; width: 75%; margin-left: auto; position: relative; border: 1px solid rgba(46, 213, 115, 0.2); box-shadow: 0 6px 16px rgba(46, 213, 115, 0.08);">
        <div style="color: #2ED573; font-size: 2.2rem; font-family: Georgia, serif; line-height: 0.8; margin-bottom: 6px; font-weight: bold;">“</div>
        <div style="font-weight: 700; font-size: 0.88rem; color: #2D3436; line-height: 1.45;">
            Small steps every day lead to big progress. You've got this!
        </div>
        <div style="width: 32px; height: 4px; background: #2ED573; border-radius: 2px; margin-top: 10px;"></div>
    </div>

    <!-- Continue Learning Section -->
    <div class="continue-learning-section animate-fade-in" style="margin-bottom: 24px;">
        <div class="flex justify-between items-center" style="margin-bottom: 12px;">
            <h2 style="font-size: 1.25rem; font-weight: 800; color: #2D3436; margin: 0;">Continue Learning</h2>
            <a href="?page=lessons" style="color: #6C5CE7; font-weight: 800; font-size: 0.9rem; text-decoration: none;">View All</a>
        </div>

        <div style="background: white; border-radius: 20px; padding: 16px 20px; box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04); display: flex; align-items: center; justify-content: space-between;">
            <div class="flex items-center gap-14" style="flex: 1;">
                <div style="background: #FF5252; color: white; border-radius: 16px; width: 52px; height: 52px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.2rem; flex-shrink: 0; box-shadow: 0 4px 12px rgba(255, 52, 52, 0.3);">
                    AB
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 900; font-size: 1.05rem; color: #2D3436; margin-bottom: 2px;">
                        <?= sanitize($currentLesson['title'] ?? 'Greetings 1') ?>
                    </div>
                    <div style="font-size: 0.78rem; color: #888; font-weight: 600; margin-bottom: 8px;">
                        <?= sanitize($currentLesson['description'] ?? 'Say Hello and Get to Know People') ?>
                    </div>
                    <!-- Mini Progress Bar -->
                    <div class="flex items-center gap-8">
                        <div style="background: #F1F2F6; height: 6px; border-radius: 6px; flex: 1; max-width: 140px; overflow: hidden;">
                            <div style="background: #6C5CE7; width: 73%; height: 100%; border-radius: 6px;"></div>
                        </div>
                        <span style="font-size: 0.75rem; font-weight: 800; color: #6C5CE7;">73%</span>
                    </div>
                </div>
            </div>
            
            <a href="?page=lesson&id=<?= $currentLesson['id'] ?? 1 ?>" style="width: 44px; height: 44px; background: #6C5CE7; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 15px rgba(108, 92, 231, 0.4); margin-left: 12px; flex-shrink: 0;">
                <i class="fa-solid fa-play" style="margin-left: 3px; font-size: 1.1rem;"></i>
            </a>
        </div>
    </div>

    <!-- Study Topics Section -->
    <div class="study-topics-section animate-fade-in" style="margin-bottom: 30px;">
        <div class="flex justify-between items-center" style="margin-bottom: 16px;">
            <h2 style="font-size: 1.25rem; font-weight: 800; color: #2D3436; margin: 0;">Study Topics</h2>
            <a href="?page=lessons" style="color: #6C5CE7; font-weight: 800; font-size: 0.9rem; text-decoration: none;">View All</a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; text-align: center;">
            <!-- Topic 1: Conversation -->
            <a href="?page=lessons" style="text-decoration: none;">
                <div style="background: #E6F9F0; border-radius: 20px; width: 64px; height: 64px; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #2ED573; box-shadow: 0 4px 12px rgba(46, 213, 115, 0.15); transition: transform 0.2s;">
                    <i class="fa-solid fa-comment-dots"></i>
                </div>
                <div style="font-size: 0.78rem; font-weight: 800; color: #2D3436;">Conversation</div>
            </a>

            <!-- Topic 2: Vocabulary -->
            <a href="?page=flashcards" style="text-decoration: none;">
                <div style="background: #E8F4FE; border-radius: 20px; width: 64px; height: 64px; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #38BDF8; box-shadow: 0 4px 12px rgba(56, 189, 248, 0.15); transition: transform 0.2s;">
                    <i class="fa-solid fa-book"></i>
                </div>
                <div style="font-size: 0.78rem; font-weight: 800; color: #2D3436;">Vocabulary</div>
            </a>

            <!-- Topic 3: Listening -->
            <a href="?page=reading" style="text-decoration: none;">
                <div style="background: #FFF8E7; border-radius: 20px; width: 64px; height: 64px; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #FFB347; box-shadow: 0 4px 12px rgba(255, 179, 71, 0.15); transition: transform 0.2s;">
                    <i class="fa-solid fa-headphones"></i>
                </div>
                <div style="font-size: 0.78rem; font-weight: 800; color: #2D3436;">Listening</div>
            </a>

            <!-- Topic 4: Grammar -->
            <a href="?page=exams" style="text-decoration: none;">
                <div style="background: #FDEDF0; border-radius: 20px; width: 64px; height: 64px; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #FF6B81; box-shadow: 0 4px 12px rgba(255, 107, 129, 0.15); transition: transform 0.2s;">
                    <i class="fa-solid fa-pen-nib"></i>
                </div>
                <div style="font-size: 0.78rem; font-weight: 800; color: #2D3436;">Grammar</div>
            </a>
        </div>
    </div>

</div><!-- /.home-wrapper -->

<?php
// Show new badge notifications if any
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
