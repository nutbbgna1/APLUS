<?php
include __DIR__ . '/../../includes/header.php';
$db = getDB();
$userId = $_SESSION['user_id'] ?? 0;
$level = $_GET['level'] ?? 'beginner';

// Validate level
if (!in_array($level, ['beginner', 'intermediate', 'advanced'])) {
    $level = 'beginner';
}

// Stats Calculation for Progress Card
$totalStmt = $db->prepare("SELECT COUNT(*) FROM exams WHERE level = ?");
$totalStmt->execute([$level]);
$totalExams = $totalStmt->fetchColumn();

// Count completed exams (where user has a result for that exam)
$completedStmt = $db->prepare("
    SELECT COUNT(DISTINCT er.exam_id) 
    FROM exam_results er 
    JOIN exams e ON er.exam_id = e.id 
    WHERE er.user_id = ? AND e.level = ?
");
$completedStmt->execute([$userId, $level]);
$completedExams = $completedStmt->fetchColumn();

$progressPercent = $totalExams > 0 ? round(($completedExams / $totalExams) * 100) : 0;

// Fetch exams for the selected level
$stmt = $db->prepare("SELECT * FROM exams WHERE level = ? ORDER BY unit ASC, id ASC");
$stmt->execute([$level]);
$exams = $stmt->fetchAll();

// Fetch granted permissions for this user
$stmt = $db->prepare("SELECT exam_id FROM exam_permissions WHERE user_id = ?");
$stmt->execute([$userId]);
$grantedExams = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Group by Unit
$examsByUnit = [];
foreach ($exams as $e) {
    $unit = $e['unit'] ?: 'Unit 1';
    $examsByUnit[$unit][] = $e;
}

// Fetch user's completed exam IDs
$completedIdsStmt = $db->prepare("SELECT DISTINCT exam_id FROM exam_results WHERE user_id = ?");
$completedIdsStmt->execute([$userId]);
$completedExamIds = $completedIdsStmt->fetchAll(PDO::FETCH_COLUMN);

// Determine "Active" exam (first granted exam that is not completed)
$activeExamId = null;
foreach ($exams as $e) {
    if (in_array($e['id'], $grantedExams) && !in_array($e['id'], $completedExamIds)) {
        $activeExamId = $e['id'];
        break;
    }
}
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
            <div style="font-size: 1.25rem; font-weight: 800; letter-spacing: 0.5px;">Exams</div>
        </div>
        
        <!-- Right Side (Stats) -->
        <div style="display: flex; gap: 16px; align-items: center; border-left: 1px solid rgba(255,255,255,0.2); padding-left: 16px;">
            <div style="text-align: center;">
                <div style="font-size: 1.25rem; font-weight: 800; line-height: 1.2;"><?= $completedExams ?></div>
                <div style="font-size: 0.75rem; opacity: 0.9;">Done</div>
            </div>
            <div style="width: 1px; height: 30px; background: rgba(255,255,255,0.2);"></div>
            <div style="text-align: center;">
                <div style="font-size: 1.25rem; font-weight: 800; line-height: 1.2;"><?= $totalExams ?></div>
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
        <a href="?page=exams&level=beginner" style="flex: 1; text-align: center; text-decoration: none; padding: 10px 0; border-radius: 50px; font-weight: 700; font-size: 0.95rem; transition: 0.3s; <?= $level === 'beginner' ? 'background: #6C5CE7; color: white;' : 'color: #64748B;' ?>">
            Beginner
        </a>
        <a href="?page=exams&level=intermediate" style="flex: 1; text-align: center; text-decoration: none; padding: 10px 0; border-radius: 50px; font-weight: 700; font-size: 0.95rem; transition: 0.3s; <?= $level === 'intermediate' ? 'background: #6C5CE7; color: white;' : 'color: #64748B;' ?>">
            Intermediate
        </a>
        <a href="?page=exams&level=advanced" style="flex: 1; text-align: center; text-decoration: none; padding: 10px 0; border-radius: 50px; font-weight: 700; font-size: 0.95rem; transition: 0.3s; <?= $level === 'advanced' ? 'background: #6C5CE7; color: white;' : 'color: #64748B;' ?>">
            Advanced
        </a>
    </div>

    <!-- 3. Exam List -->
    <div class="flex-col gap-20" style="padding-bottom: 80px;">
        <?php foreach ($examsByUnit as $unit => $unitExams): ?>
        <div>
            <!-- Section Header -->
            <h2 style="font-size: 1.15rem; font-weight: 800; color: #1E293B; margin-bottom: 16px;">
                <?= sanitize($unit) ?>
            </h2>
            
            <div class="flex-col gap-12">
                <?php foreach ($unitExams as $e): 
                    $hasPermission = in_array($e['id'], $grantedExams);
                    $isCompleted = in_array($e['id'], $completedExamIds);
                    
                    $status = 'locked';
                    if ($isCompleted) {
                        $status = 'completed';
                    } elseif ($hasPermission && $e['id'] === $activeExamId) {
                        $status = 'active';
                    } elseif ($hasPermission) {
                        $status = 'active'; // Allow testing any granted exam if wanted
                    }
                    
                    // Status-based styling
                    $iconBg = '';
                    $iconHtml = '';
                    $titleColor = '';
                    $subTextColor = '';
                    $badgeBg = '';
                    $badgeColor = '';
                    $badgeText = '';
                    $cardLink = ($status !== 'locked') ? "?page=exam&id={$e['id']}" : "#";
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
                        $iconHtml = '<i class="fa-solid fa-lock" style="color: #94A3B8; font-size: 1.1rem;"></i>';
                        $titleColor = '#94A3B8';
                        $subTextColor = '#94A3B8';
                        $badgeBg = '#F1F5F9';
                        $badgeColor = '#94A3B8';
                        $badgeText = 'Locked';
                    }
                ?>
                <a href="<?= $cardLink ?>" style="<?= $cardStyle ?>">
                    <!-- Icon & Info -->
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: <?= $iconBg ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <?= $iconHtml ?>
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 1.05rem; color: <?= $titleColor ?>; margin-bottom: 2px;">
                                <?= sanitize($e['title']) ?>
                            </div>
                            <div style="font-size: 0.75rem; font-weight: 600; color: <?= $subTextColor ?>;">
                                <?= $e['total_questions'] ?> Qs &middot; <?= $e['time_minutes'] ?> mins
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Badge -->
                    <div style="background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; padding: 4px 10px; border-radius: 50px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.5px;">
                        <?= $badgeText ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($examsByUnit)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📝</div>
                <h3>No exams available</h3>
                <p>Complete some lessons first, then come back here to test your knowledge!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Hover Effect for Interactive Cards */
a[href]:not([href="#"]):hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.06) !important;
}
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
