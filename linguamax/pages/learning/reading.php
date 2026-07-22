<?php
// ============================================================
// LinguaMax — Reading List (UI matches Lessons)
// ============================================================
include __DIR__ . '/../../includes/header.php';
$db = getDB();
$userId = $_SESSION['user_id'];
$level = $_GET['level'] ?? 'beginner';

$allowedLevels = ['beginner', 'intermediate', 'advanced'];
if (!in_array($level, $allowedLevels)) {
    $level = 'beginner';
}

// Fetch all passages for progress calculation
$stmtAll = $db->prepare("
    SELECT rp.id, 
           (SELECT COUNT(*) FROM reading_progress WHERE user_id = ? AND passage_id = rp.id) as completed 
    FROM reading_passages rp
");
$stmtAll->execute([$userId]);
$allPassages = $stmtAll->fetchAll();

$totalPassages = count($allPassages);
$completedPassages = 0;
foreach ($allPassages as $p) {
    if ($p['completed'] > 0) $completedPassages++;
}
$progressPercent = $totalPassages > 0 ? round(($completedPassages / $totalPassages) * 100) : 0;

// Fetch passages for the current selected level
$query = "
    SELECT rp.*, 
           (SELECT COUNT(*) FROM reading_progress WHERE user_id = ? AND passage_id = rp.id) as completed 
    FROM reading_passages rp 
    WHERE rp.level = ? 
    ORDER BY rp.id ASC
";
$stmt = $db->prepare($query);
$stmt->execute([$userId, $level]);
$passages = $stmt->fetchAll();

// Determine Active Passage
$activePassageId = null;
foreach ($passages as $p) {
    if ($p['completed'] == 0) {
        $activePassageId = $p['id'];
        break; // First uncompleted is the active one
    }
}
?>

<div class="animate-fade-in" style="padding: 16px; background: #F8FAFC; min-height: 100vh;">
    
    <a href="?page=dashboard" style="display: inline-flex; align-items: center; gap: 8px; color: #64748B; text-decoration: none; font-weight: 700; font-size: 0.9rem; margin-bottom: 16px; background: white; padding: 8px 16px; border-radius: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
        <i class="fa-solid fa-arrow-left"></i> Back
    </a>

    <!-- 1. Progress Card (Green theme for Reading) -->
    <div style="background: #10B981; border-radius: 16px; padding: 20px; color: white; display: flex; align-items: stretch; justify-content: space-between; margin-bottom: 24px; box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);">
        <!-- Left Side -->
        <div style="display: flex; flex-direction: column; justify-content: center; flex: 1;">
            <div style="font-size: 0.85rem; font-weight: 600; opacity: 0.9; margin-bottom: 2px;">Your progress in</div>
            <div style="font-size: 1.25rem; font-weight: 800; letter-spacing: 0.5px;">Reading</div>
        </div>
        
        <!-- Right Side (Stats) -->
        <div style="display: flex; gap: 16px; align-items: center; border-left: 1px solid rgba(255,255,255,0.2); padding-left: 16px;">
            <div style="text-align: center;">
                <div style="font-size: 1.25rem; font-weight: 800; line-height: 1.2;"><?= $completedPassages ?></div>
                <div style="font-size: 0.75rem; opacity: 0.9;">Done</div>
            </div>
            <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.2);"></div>
            <div style="text-align: center;">
                <div style="font-size: 1.25rem; font-weight: 800; line-height: 1.2;"><?= $totalPassages ?></div>
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
        <a href="?page=reading&level=beginner" style="flex: 1; text-align: center; text-decoration: none; padding: 10px 0; border-radius: 50px; font-weight: 700; font-size: 0.95rem; transition: 0.3s; <?= $level === 'beginner' ? 'background: #10B981; color: white;' : 'color: #64748B;' ?>">
            Beginner
        </a>
        <a href="?page=reading&level=intermediate" style="flex: 1; text-align: center; text-decoration: none; padding: 10px 0; border-radius: 50px; font-weight: 700; font-size: 0.95rem; transition: 0.3s; <?= $level === 'intermediate' ? 'background: #10B981; color: white;' : 'color: #64748B;' ?>">
            Intermediate
        </a>
        <a href="?page=reading&level=advanced" style="flex: 1; text-align: center; text-decoration: none; padding: 10px 0; border-radius: 50px; font-weight: 700; font-size: 0.95rem; transition: 0.3s; <?= $level === 'advanced' ? 'background: #10B981; color: white;' : 'color: #64748B;' ?>">
            Advanced
        </a>
    </div>

    <!-- 3. Section Header -->
    <h2 style="font-size: 1.15rem; font-weight: 800; color: #1E293B; margin-bottom: 16px;">
        <?= ucfirst($level) ?> Articles
    </h2>

    <!-- 4. Reading List -->
    <div class="flex-col gap-12" style="padding-bottom: 80px;">
        <?php foreach ($passages as $index => $p): 
            $status = 'active';
            if ($p['completed'] > 0) {
                $status = 'completed';
            }

            // Static UI Mock Data
            $timeMin = (3 + ($p['id'] % 5)) . ' min';
            $xpReward = '+' . ($p['id'] * 5 + 30) . ' XP';
            
            // Status-based styling
            $iconBg = '';
            $iconHtml = '';
            $titleColor = '';
            $subTextColor = '';
            $badgeBg = '';
            $badgeColor = '';
            $badgeText = '';
            $cardLink = "?page=reading-view&id={$p['id']}";
            $cardStyle = "display: flex; align-items: center; justify-content: space-between; background: white; border-radius: 16px; padding: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); text-decoration: none; transition: transform 0.2s;";

            if ($status === 'completed') {
                $iconBg = '#D1FAE5'; // Light green
                $iconHtml = '<i class="fa-solid fa-check" style="color: #10B981; font-size: 1.1rem;"></i>';
                $titleColor = '#1E293B';
                $subTextColor = '#64748B';
                $badgeBg = '#D1FAE5';
                $badgeColor = '#10B981';
                $badgeText = 'Completed';
            } else {
                $iconBg = '#D1FAE5'; // Light green (using green theme for reading)
                $iconHtml = '<i class="fa-solid fa-play" style="color: #10B981; font-size: 1rem; margin-left: 2px;"></i>';
                $titleColor = '#1E293B';
                $subTextColor = '#64748B';
                $badgeBg = '#D1FAE5';
                $badgeColor = '#10B981';
                $badgeText = 'Read Now';
            }
        ?>
        <a href="<?= $cardLink ?>" style="<?= $cardStyle ?>" class="card-interactive">
            <div style="display: flex; align-items: center; gap: 16px;">
                <!-- Left Icon -->
                <div style="width: 48px; height: 48px; border-radius: 50%; background: <?= $iconBg ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <?= $iconHtml ?>
                </div>
                
                <!-- Middle Text -->
                <div>
                    <div style="font-weight: 800; font-size: 1rem; color: <?= $titleColor ?>; margin-bottom: 4px; max-width: 180px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?= sanitize($p['title']) ?>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px; font-size: 0.8rem; color: <?= $subTextColor ?>; font-weight: 600;">
                        <span style="display: flex; align-items: center; gap: 4px;">
                            <i class="fa-regular fa-clock"></i> <?= $timeMin ?>
                        </span>
                        <span style="display: flex; align-items: center; gap: 4px;">
                            <i class="fa-solid fa-star" style="color: #F59E0B;"></i> <?= $xpReward ?>
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
        
        <?php if (empty($passages)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📖</div>
                <h3>No articles available for this level yet</h3>
                <p>Stay tuned for new articles coming soon!</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
