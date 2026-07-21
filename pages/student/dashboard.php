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
<div class="home-wrapper" style="margin: -20px -20px 0 -20px; padding: 20px; background: linear-gradient(180deg, #ABE9FF 0%, #D4F4FF 40%, #F5FCFF 100%); min-height: 100vh;">

    <!-- Top Navigation Header -->
    <div class="home-top-header flex justify-between items-center animate-fade-in" style="margin-bottom: 20px; padding-top: 4px;">
        <div class="flex items-center gap-12">
            <div class="avatar" style="background: <?= $user['avatar_color'] ?? '#A855F7' ?>; width: 48px; height: 48px; font-size: 1.4rem; font-weight: 900; box-shadow: 0 4px 12px rgba(168,85,247,0.3); display: flex; align-items: center; justify-content: center; border-radius: 50%; color: white;">
                <?= mb_substr($user['fname'], 0, 1) ?>
            </div>
            <div>
                <div style="font-weight: 900; font-size: 1.2rem; color: #1E293B; font-family: var(--font-display);">Hello, <?= sanitize($user['nickname']) ?></div>
                <div style="color: #64748B; font-size: 0.85rem; font-weight: 600;">Ready to learn?</div>
            </div>
        </div>
        
        <div class="flex items-center gap-8">
            <!-- Streak Pill -->
            <div style="background: white; padding: 8px 14px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); font-weight: 800; display: flex; align-items: center; gap: 6px; font-size: 0.95rem; color: #1E293B;">
                <span style="font-size: 1.15rem;">🔥</span>
                <span><?= $streak['current_streak'] ?></span>
            </div>
            <!-- Gems/Coins Pill -->
            <div style="background: white; padding: 8px 14px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); font-weight: 800; display: flex; align-items: center; gap: 6px; font-size: 0.95rem; color: #1E293B;">
                <span style="font-size: 1.15rem;">💎</span>
                <span><?= number_format($currentUser['coins'] ?? 250) ?></span>
            </div>
        </div>
    </div>

    <!-- Today's XP Progress Card -->
    <div class="xp-card animate-fade-in" style="background: white; border-radius: 24px; padding: 20px 24px; box-shadow: 0 10px 30px rgba(108, 92, 231, 0.08); margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between;">
        <div style="flex: 1; padding-right: 16px;">
            <div style="font-size: 0.9rem; font-weight: 800; color: #1E293B; margin-bottom: 6px;">Today's XP</div>
            <div style="margin-bottom: 10px;">
                <span style="color: #6C5CE7; font-weight: 900; font-size: 1.45rem;"><?= $stats['xp'] ?? 120 ?></span>
                <span style="color: #94A3B8; font-weight: 800; font-size: 1.05rem;">/ 250 XP</span>
            </div>
            <!-- Progress Bar -->
            <div style="background: #F1F5F9; height: 10px; border-radius: 10px; width: 100%; overflow: hidden;">
                <div style="background: linear-gradient(90deg, #6C5CE7, #8E44AD); width: <?= min(100, max(10, (($stats['xp'] ?? 120) / 250) * 100)) ?>%; height: 100%; border-radius: 10px; transition: width 0.5s ease;"></div>
            </div>
        </div>
        
        <!-- Daily Reward -->
        <div style="text-align: center; flex-shrink: 0;">
            <div style="background: #FFF4E6; border-radius: 18px; width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; margin: 0 auto 6px; font-size: 1.8rem; box-shadow: 0 4px 12px rgba(255, 168, 38, 0.18);">
                🎁
            </div>
            <div style="font-size: 0.75rem; font-weight: 800; color: #6C5CE7;">Daily Reward</div>
        </div>
    </div>

    <!-- Mascot & Quote Park Scene (Middle Section) -->
    <div class="park-scene animate-fade-in" style="background: url('<?= SITE_URL ?>/assets/img/park_bg.png') center/cover no-repeat; border-radius: 24px; padding: 16px 18px; margin-bottom: 20px; position: relative; display: flex; align-items: center; justify-content: space-between; overflow: hidden; min-height: 190px; box-shadow: 0 10px 25px rgba(0,0,0,0.08);">
        
        <!-- Subtle overlay gradient for readability -->
        <div style="position: absolute; inset: 0; background: linear-gradient(90deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.2) 100%); z-index: 1;"></div>

        <!-- Left: Mascot Dog Character -->
        <div style="position: relative; z-index: 2; width: 45%; display: flex; justify-content: center; align-items: flex-end; margin-left: -10px;">
            <img src="<?= SITE_URL ?>/assets/img/mascot_dog.png" style="width: 175px; max-width: 130%; filter: drop-shadow(0 8px 16px rgba(0,0,0,0.15)); margin-bottom: -22px;" alt="Mascot Dog">
        </div>

        <!-- Right: Quote Card Floating -->
        <div class="quote-card" style="position: relative; z-index: 2; width: 53%; background: rgba(255, 255, 255, 0.94); backdrop-filter: blur(10px); border-radius: 20px; padding: 14px 16px; border: 1px solid rgba(46, 213, 115, 0.3); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);">
            <div style="color: #2ED573; font-size: 2rem; font-family: Georgia, serif; line-height: 0.8; margin-bottom: 6px; font-weight: bold;">“</div>
            <div style="font-weight: 700; font-size: 0.84rem; color: #1E293B; line-height: 1.45;">
                Small steps every day lead to big progress. You've got this!
            </div>
            <div style="width: 32px; height: 4px; background: #2ED573; border-radius: 2px; margin-top: 8px;"></div>
        </div>
    </div>

    <!-- Bottom Rounded White Container (Continue Learning + Study Topics) -->
    <div class="bottom-sheet-card animate-fade-in" style="background: white; border-radius: 32px 32px 0 0; padding: 24px 20px 30px 20px; margin: 0 -20px -20px -20px; box-shadow: 0 -12px 40px rgba(108, 92, 231, 0.1); position: relative; z-index: 3;">

        <!-- Continue Learning Section -->
        <div class="continue-learning-section" style="margin-bottom: 28px;">
            <div class="flex justify-between items-center" style="margin-bottom: 14px;">
                <h2 style="font-size: 1.25rem; font-weight: 900; color: #1E293B; margin: 0;">Continue Learning</h2>
                <a href="?page=lessons" style="color: #6C5CE7; font-weight: 800; font-size: 0.9rem; text-decoration: none;">View All</a>
            </div>

            <div style="background: #F9FAFB; border: 1px solid #F1F5F9; border-radius: 22px; padding: 16px 18px; box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03); display: flex; align-items: center; justify-content: space-between;">
                <div class="flex items-center gap-14" style="flex: 1;">
                    <!-- AB Badge Icon -->
                    <div style="background: #FF5252; color: white; border-radius: 16px; width: 52px; height: 52px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.2rem; flex-shrink: 0; box-shadow: 0 4px 14px rgba(255, 82, 82, 0.35);">
                        AB
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 900; font-size: 1.05rem; color: #1E293B; margin-bottom: 2px;">
                            <?= sanitize($currentLesson['title'] ?? 'Greetings 1') ?>
                        </div>
                        <div style="font-size: 0.78rem; color: #64748B; font-weight: 600; margin-bottom: 8px;">
                            <?= sanitize($currentLesson['description'] ?? 'Say Hello and Get to Know People') ?>
                        </div>
                        <!-- Mini Progress Bar -->
                        <div class="flex items-center gap-8">
                            <div style="background: #E2E8F0; height: 6px; border-radius: 6px; flex: 1; max-width: 140px; overflow: hidden;">
                                <div style="background: #6C5CE7; width: 73%; height: 100%; border-radius: 6px;"></div>
                            </div>
                            <span style="font-size: 0.75rem; font-weight: 800; color: #6C5CE7;">73%</span>
                        </div>
                    </div>
                </div>
                
                <!-- Play Button -->
                <a href="?page=lesson&id=<?= $currentLesson['id'] ?? 1 ?>" style="width: 46px; height: 46px; background: #6C5CE7; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 6px 18px rgba(108, 92, 231, 0.4); margin-left: 12px; flex-shrink: 0; transition: transform 0.2s;">
                    <i class="fa-solid fa-play" style="margin-left: 3px; font-size: 1.15rem;"></i>
                </a>
            </div>
        </div>

        <!-- Study Topics Section -->
        <div class="study-topics-section">
            <div class="flex justify-between items-center" style="margin-bottom: 16px;">
                <h2 style="font-size: 1.25rem; font-weight: 900; color: #1E293B; margin: 0;">Study Topics</h2>
                <a href="?page=lessons" style="color: #6C5CE7; font-weight: 800; font-size: 0.9rem; text-decoration: none;">View All</a>
            </div>

            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; text-align: center;">
                <!-- Topic 1: Conversation -->
                <a href="?page=lessons" style="text-decoration: none;">
                    <div style="background: #E6F9F0; border-radius: 20px; width: 64px; height: 64px; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #2ED573; box-shadow: 0 4px 12px rgba(46, 213, 115, 0.15); transition: transform 0.2s;">
                        <i class="fa-solid fa-comment-dots"></i>
                    </div>
                    <div style="font-size: 0.78rem; font-weight: 800; color: #1E293B;">Conversation</div>
                </a>

                <!-- Topic 2: Vocabulary -->
                <a href="?page=flashcards" style="text-decoration: none;">
                    <div style="background: #E8F4FE; border-radius: 20px; width: 64px; height: 64px; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #38BDF8; box-shadow: 0 4px 12px rgba(56, 189, 248, 0.15); transition: transform 0.2s;">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <div style="font-size: 0.78rem; font-weight: 800; color: #1E293B;">Vocabulary</div>
                </a>

                <!-- Topic 3: Listening -->
                <a href="?page=reading" style="text-decoration: none;">
                    <div style="background: #FFF8E7; border-radius: 20px; width: 64px; height: 64px; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #FFB347; box-shadow: 0 4px 12px rgba(255, 179, 71, 0.15); transition: transform 0.2s;">
                        <i class="fa-solid fa-headphones"></i>
                    </div>
                    <div style="font-size: 0.78rem; font-weight: 800; color: #1E293B;">Vocabulary</div>
                </a>

                <!-- Topic 4: Grammar -->
                <a href="?page=exams" style="text-decoration: none;">
                    <div style="background: #FDEDF0; border-radius: 20px; width: 64px; height: 64px; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #FF6B81; box-shadow: 0 4px 12px rgba(255, 107, 129, 0.15); transition: transform 0.2s;">
                        <i class="fa-solid fa-pen-nib"></i>
                    </div>
                    <div style="font-size: 0.78rem; font-weight: 800; color: #1E293B;">Grammar</div>
                </a>
            </div>
        </div>

    </div><!-- /.bottom-sheet-card -->

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
