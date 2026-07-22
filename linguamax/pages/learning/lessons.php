<?php
include __DIR__ . '/../../includes/header.php';
$db = getDB();
$userId = $_SESSION['user_id'];
$level = $_GET['level'] ?? 'beginner';

// Validate level to ensure it matches one of the tabs
$allowedLevels = ['beginner', 'intermediate', 'advanced'];
if (!in_array($level, $allowedLevels)) {
    $level = 'beginner';
}

// Fetch all lessons for progress calculation
$stmtAll = $db->prepare("
    SELECT l.id, 
           (SELECT completed FROM lesson_progress WHERE user_id = ? AND lesson_id = l.id) as completed 
    FROM lessons l
");
$stmtAll->execute([$userId]);
$allLessons = $stmtAll->fetchAll();

$totalLessons = count($allLessons);
$completedLessons = 0;
foreach ($allLessons as $l) {
    if ($l['completed']) $completedLessons++;
}
$progressPercent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

// Fetch lessons for the current selected level
$query = "
    SELECT l.*, 
           (SELECT completed FROM lesson_progress WHERE user_id = ? AND lesson_id = l.id) as completed 
    FROM lessons l 
    WHERE l.level = ? 
    ORDER BY l.sort_order ASC
";
$stmt = $db->prepare($query);
$stmt->execute([$userId, $level]);
$lessons = $stmt->fetchAll();

// Determine Active Lesson
$activeLessonId = null;
foreach ($lessons as $l) {
    if (!$l['completed']) {
        $activeLessonId = $l['id'];
        break; // First uncompleted is the active one
    }
}
// If all are completed in this level, active is none (or maybe the last one, but none is fine)
?>

<div class="animate-fade-in" style="padding: 16px; background: #F8FAFC; min-height: 100vh;">
    
    <a href="?page=dashboard" style="display: inline-flex; align-items: center; gap: 8px; color: #64748B; text-decoration: none; font-weight: 700; font-size: 0.9rem; margin-bottom: 16px; background: white; padding: 8px 16px; border-radius: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>

    <!-- 1. Progress Card (Purple) -->
    <div style="background: #6C5CE7; border-radius: 16px; padding: 20px; color: white; display: flex; align-items: stretch; justify-content: space-between; margin-bottom: 24px; box-shadow: 0 8px 24px rgba(108, 92, 231, 0.3);">
        <!-- Left Side -->
        <div style="display: flex; flex-direction: column; justify-content: center; flex: 1;">
            <div style="font-size: 0.85rem; font-weight: 600; opacity: 0.9; margin-bottom: 2px;">Your progress in</div>
            <div style="font-size: 1.25rem; font-weight: 800; letter-spacing: 0.5px;">English</div>
        </div>
        
        <!-- Right Side (Stats) -->
        <div style="display: flex; gap: 16px; align-items: center; border-left: 1px solid rgba(255,255,255,0.2); padding-left: 16px;">
            <div style="text-align: center;">
                <div style="font-size: 1.25rem; font-weight: 800; line-height: 1.2;"><?= $completedLessons ?></div>
                <div style="font-size: 0.75rem; opacity: 0.9;">Done</div>
            </div>
            <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.2);"></div>
            <div style="text-align: center;">
                <div style="font-size: 1.25rem; font-weight: 800; line-height: 1.2;"><?= $totalLessons ?></div>
                <div style="font-size: 0.75rem; opacity: 0.9;">Total</div>
            </div>
            <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.2);"></div>
            <div style="text-align: center;">
                <div style="font-size: 1.25rem; font-weight: 800; line-height: 1.2;"><?= $progressPercent ?>%</div>
                <div style="font-size: 0.75rem; opacity: 0.9;">Done</div>
            </div>
        </div>
    </div>

    <!-- 2. Pill Tabs -->
    <div style="background: white; border-radius: 50px; padding: 4px; display: flex; box-shadow: 0 2px 8px rgba(0,0,0,0.05); margin-bottom: 32px;">
        <a href="?page=lessons&level=beginner" style="flex: 1; text-align: center; text-decoration: none; padding: 10px 0; border-radius: 50px; font-weight: 700; font-size: 0.95rem; transition: 0.3s; <?= $level === 'beginner' ? 'background: #6C5CE7; color: white;' : 'color: #64748B;' ?>">
            Beginner
        </a>
        <a href="?page=lessons&level=intermediate" style="flex: 1; text-align: center; text-decoration: none; padding: 10px 0; border-radius: 50px; font-weight: 700; font-size: 0.95rem; transition: 0.3s; <?= $level === 'intermediate' ? 'background: #6C5CE7; color: white;' : 'color: #64748B;' ?>">
            Intermediate
        </a>
        <a href="?page=lessons&level=advanced" style="flex: 1; text-align: center; text-decoration: none; padding: 10px 0; border-radius: 50px; font-weight: 700; font-size: 0.95rem; transition: 0.3s; <?= $level === 'advanced' ? 'background: #6C5CE7; color: white;' : 'color: #64748B;' ?>">
            Advanced
        </a>
    </div>

    <!-- 3. Section Header -->
    <h2 style="font-size: 1.15rem; font-weight: 800; color: #1E293B; margin-bottom: 16px;">
        Unit 1 · Foundations
    </h2>

    <!-- 4. Lesson List -->
    <div class="flex-col gap-12" style="padding-bottom: 80px;">
        <?php foreach ($lessons as $index => $l): 
            $status = 'locked';
            if ($l['completed']) {
                $status = 'completed';
            } elseif ($l['id'] === $activeLessonId) {
                $status = 'active';
            }

            // Static UI Mock Data
            $timeMin = (5 + ($l['id'] % 10)) . ' min';
            $xpReward = '+' . ($l['id'] * 10 + 20) . ' XP';
            
            // Status-based styling
            $iconBg = '';
            $iconHtml = '';
            $titleColor = '';
            $subTextColor = '';
            $badgeBg = '';
            $badgeColor = '';
            $badgeText = '';
            $cardLink = ($status !== 'locked') ? "?page=lesson&id={$l['id']}" : "#";
            $cardStyle = "display: flex; align-items: center; justify-content: space-between; background: white; border-radius: 16px; padding: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); text-decoration: none; transition: transform 0.2s;";

            if ($status === 'completed') {
                $iconBg = '#D1FAE5'; // Light green
                $iconHtml = '<i class="fa-solid fa-check" style="color: #10B981; font-size: 1.1rem;"></i>';
                $titleColor = '#1E293B';
                $subTextColor = '#64748B';
                $badgeBg = '#D1FAE5';
                $badgeColor = '#10B981';
                $badgeText = 'Completed';
            } elseif ($status === 'active') {
                $iconBg = '#F3E8FF'; // Light purple
                $iconHtml = '<i class="fa-solid fa-play" style="color: #6C5CE7; font-size: 1rem; margin-left: 2px;"></i>';
                $titleColor = '#1E293B';
                $subTextColor = '#64748B';
                $badgeBg = '#F3E8FF';
                $badgeColor = '#6C5CE7';
                $badgeText = 'Continue';
            } else {
                $iconBg = '#F1F5F9'; // Light gray
                $iconHtml = '<i class="fa-solid fa-lock" style="color: #94A3B8; font-size: 1rem;"></i>';
                $titleColor = '#94A3B8';
                $subTextColor = '#CBD5E1';
                $badgeBg = '#F1F5F9';
                $badgeColor = '#94A3B8';
                $badgeText = 'Locked';
                $cardStyle .= " opacity: 0.8; cursor: not-allowed;";
            }
        ?>
        <a href="<?= $cardLink ?>" style="<?= $cardStyle ?>" <?= $status !== 'locked' ? 'class="card-interactive"' : '' ?>>
            <div style="display: flex; align-items: center; gap: 16px;">
                <!-- Left Icon -->
                <div style="width: 48px; height: 48px; border-radius: 50%; background: <?= $iconBg ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <?= $iconHtml ?>
                </div>
                
                <!-- Middle Text -->
                <div>
                    <div style="font-weight: 800; font-size: 1rem; color: <?= $titleColor ?>; margin-bottom: 4px;">
                        <?= sanitize($l['title']) ?>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px; font-size: 0.8rem; color: <?= $subTextColor ?>; font-weight: 600;">
                        <span style="display: flex; align-items: center; gap: 4px;">
                            <i class="fa-regular fa-clock"></i> <?= $timeMin ?>
                        </span>
                        <span style="display: flex; align-items: center; gap: 4px;">
                            <i class="fa-solid fa-star" style="color: <?= $status !== 'locked' ? '#F59E0B' : '#CBD5E1' ?>;"></i> <?= $xpReward ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Right Badge -->
            <div style="background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; font-size: 0.7rem; font-weight: 800; padding: 4px 10px; border-radius: 12px; flex-shrink: 0;">
                <?= $badgeText ?>
            </div>
        </a>
        <?php endforeach; ?>
        
        <?php if (empty($lessons)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📚</div>
                <h3>No lessons available for this level yet</h3>
                <p>Stay tuned for new lessons coming soon!</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
