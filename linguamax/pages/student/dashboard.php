<?php
// ============================================================
// LinguaMax — Student Dashboard (Mockup Redesign)
// ============================================================
include __DIR__ . '/../../includes/header.php';

$user = $currentUser;
$stats = getUserStats($user['id']);
$streak = getStreak($user['id']);
$db = getDB();

// Fetch active / recent lessons for "Explore Classes"
$stmt = $db->prepare("
    SELECT l.*, IFNULL(lp.completed, 0) as is_completed,
           (SELECT COUNT(*) FROM vocabulary WHERE lesson_id = l.id) as vocab_count
    FROM lessons l 
    LEFT JOIN lesson_progress lp ON l.id = lp.lesson_id AND lp.user_id = ? 
    ORDER BY l.sort_order ASC 
    LIMIT 4
");
$stmt->execute([$user['id']]);
$lessons = $stmt->fetchAll();

// Hero lesson (first incomplete lesson or first lesson)
$heroLesson = $lessons[0] ?? ['id' => 1, 'title' => 'English Class', 'description' => 'Starting Soon'];
foreach ($lessons as $l) {
    if (!$l['is_completed']) {
        $heroLesson = $l;
        break;
    }
}

// Preset theme styles for explore cards
$cardThemes = [
    ['bg' => '#E8F4FE', 'icon' => '🧪', 'bar' => '#4A8CFF', 'default_title' => 'Cool Experiments', 'default_progress' => '7/10', 'percent' => 70],
    ['bg' => '#FFF0E6', 'icon' => '📖', 'bar' => '#FF9F43', 'default_title' => 'Story Time', 'default_progress' => '9/10', 'percent' => 90],
    ['bg' => '#F3E8FF', 'icon' => '🎨', 'bar' => '#A855F7', 'default_title' => 'Creative Arts', 'default_progress' => '5/10', 'percent' => 50],
    ['bg' => '#E6F9F0', 'icon' => '🎵', 'bar' => '#10B981', 'default_title' => 'Phonics & Songs', 'default_progress' => '8/10', 'percent' => 80],
];
?>

<div class="dashboard-container animate-fade-in" style="background: #FAFAFD; min-height: 100vh; padding: 16px 16px 100px 16px; margin: -16px -16px 0 -16px;">
    
    <!-- 1. Top Header Row -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <!-- Left: Profile Capsule -->
        <div style="background: white; border-radius: 50px; padding: 6px 18px 6px 6px; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #F1F5F9;">
            <div style="width: 38px; height: 38px; border-radius: 50%; background: <?= $user['avatar_color'] ?? '#FFB74D' ?>; display: flex; align-items: center; justify-content: center; font-weight: 900; color: white; font-size: 1.1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                <?= mb_substr($user['nickname'] ?: $user['fname'], 0, 1) ?>
            </div>
            <div style="font-weight: 800; font-size: 0.95rem; color: #1E293B; font-family: var(--font-display);">
                <?= sanitize($user['nickname'] ?: ($user['fname'] . ' ' . mb_substr($user['lname'], 0, 1) . '.')) ?>
            </div>
        </div>

        <!-- Right: Coins Pill & Notification Bell -->
        <div style="display: flex; align-items: center; gap: 10px;">
            <!-- Coins Pill -->
            <div style="background: #EEF2FF; border-radius: 50px; padding: 6px 12px 6px 14px; display: flex; align-items: center; gap: 6px; font-weight: 900; font-size: 0.95rem; color: #6C5CE7; box-shadow: 0 2px 10px rgba(108, 92, 231, 0.08);">
                <span style="font-size: 1.2rem;">⭐</span>
                <span><?= number_format($currentUser['coins'] ?? 3500) ?></span>
                <div style="background: #6C5CE7; color: white; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 900; margin-left: 2px;">
                    +
                </div>
            </div>

            <!-- Bell Icon -->
            <a href="?page=notifications" style="background: white; width: 42px; height: 42px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.04); color: #64748B; font-size: 1.1rem; text-decoration: none; border: 1px solid #F1F5F9;">
                <i class="fa-regular fa-bell"></i>
            </a>
        </div>
    </div>

    <!-- 2. Hero Banner Card -->
    <div style="background: linear-gradient(135deg, #E0F2FE 0%, #BAE6FD 100%); border-radius: 28px; padding: 24px 22px; position: relative; overflow: hidden; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 10px 30px rgba(186, 230, 253, 0.4);">
        <!-- Left Content -->
        <div style="position: relative; z-index: 2; flex: 1; padding-right: 10px;">
            <div style="font-size: 0.9rem; font-weight: 700; color: #64748B; margin-bottom: 4px; font-family: var(--font-display);">
                English Class
            </div>
            <div style="font-size: 1.65rem; font-weight: 900; color: #0F172A; margin-bottom: 16px; font-family: var(--font-display); line-height: 1.2;">
                Starting Soon
            </div>

            <!-- Overlapping Avatars Stack -->
            <div style="display: flex; align-items: center; margin-bottom: 18px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #FFB74D; border: 2px solid white; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800; color: white;">👩</div>
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #4DD0E1; border: 2px solid white; margin-left: -10px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800; color: white;">👨</div>
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #81C784; border: 2px solid white; margin-left: -10px; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800; color: white;">👦</div>
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #0F172A; color: white; border: 2px solid white; margin-left: -10px; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 800;">
                    31+
                </div>
            </div>

            <!-- Join Now Button -->
            <a href="?page=lesson&id=<?= $heroLesson['id'] ?? 1 ?>" style="background: #6C5CE7; color: white; border-radius: 50px; padding: 11px 26px; font-weight: 800; font-size: 0.95rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 6px 18px rgba(108, 92, 231, 0.35); transition: transform 0.2s;">
                Join Now!
            </a>
        </div>

        <!-- Right 3D Illustration -->
        <div style="position: relative; z-index: 2; width: 135px; flex-shrink: 0; display: flex; justify-content: center; align-items: center;">
            <img src="<?= SITE_URL ?>/assets/img/graduation_hero.png" style="width: 100%; max-width: 135px; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.12)); animation: float 3s ease-in-out infinite;" alt="Graduation Cap">
        </div>
    </div>

    <!-- 3. Study Topics Section -->
    <div class="study-topics-section" style="margin-bottom: 28px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding: 0 4px;">
            <h2 style="font-size: 1.3rem; font-weight: 900; color: #1E293B; margin: 0; font-family: var(--font-display);">Study Topics</h2>
            <a href="?page=lessons" style="color: #6C5CE7; font-weight: 800; font-size: 0.9rem; text-decoration: none;">View All</a>
        </div>

        <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; text-align: center;">
            <!-- Topic 1: Lessons -->
            <a href="?page=lessons" style="flex: 1 1 95px; max-width: 120px; text-decoration: none;">
                <div style="background: #E6F9F0; border-radius: 20px; width: 100%; aspect-ratio: 1; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(46, 213, 115, 0.15); transition: transform 0.2s;">
                    <img src="<?= SITE_URL ?>/assets/SVG/Open book.svg" alt="Lessons" style="width: 60%; height: auto; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                </div>
                <div style="font-size: 0.8rem; font-weight: 800; color: #1E293B; font-family: var(--font-display);">Lessons</div>
            </a>

            <!-- Topic 2: Pronunciation -->
            <a href="?page=pronunciation" style="flex: 1 1 95px; max-width: 120px; text-decoration: none;">
                <div style="background: #E8F4FE; border-radius: 20px; width: 100%; aspect-ratio: 1; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(56, 189, 248, 0.15); transition: transform 0.2s;">
                    <img src="<?= SITE_URL ?>/assets/SVG/Brain.svg" alt="Pronunciation" style="width: 60%; height: auto; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                </div>
                <div style="font-size: 0.8rem; font-weight: 800; color: #1E293B; font-family: var(--font-display);">Pronunciation</div>
            </a>

            <!-- Topic 3: Reading -->
            <a href="?page=reading" style="flex: 1 1 95px; max-width: 120px; text-decoration: none;">
                <div style="background: #FFF8E7; border-radius: 20px; width: 100%; aspect-ratio: 1; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(255, 179, 71, 0.15); transition: transform 0.2s;">
                    <img src="<?= SITE_URL ?>/assets/SVG/Blackboard Reading.svg" alt="Reading" style="width: 60%; height: auto; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                </div>
                <div style="font-size: 0.8rem; font-weight: 800; color: #1E293B; font-family: var(--font-display);">Reading</div>
            </a>

            <!-- Topic 4: Mini Games -->
            <a href="?page=games" style="flex: 1 1 95px; max-width: 120px; text-decoration: none;">
                <div style="background: #F3E8FF; border-radius: 20px; width: 100%; aspect-ratio: 1; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(168, 85, 247, 0.15); transition: transform 0.2s;">
                    <img src="<?= SITE_URL ?>/assets/SVG/Rocket.svg" alt="Mini Games" style="width: 60%; height: auto; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                </div>
                <div style="font-size: 0.8rem; font-weight: 800; color: #1E293B; font-family: var(--font-display);">Mini Games</div>
            </a>

            <!-- Topic 5: Exams -->
            <a href="?page=exams" style="flex: 1 1 95px; max-width: 120px; text-decoration: none;">
                <div style="background: #FDEDF0; border-radius: 20px; width: 100%; aspect-ratio: 1; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(255, 107, 129, 0.15); transition: transform 0.2s;">
                    <img src="<?= SITE_URL ?>/assets/SVG/Test A+.svg" alt="Exams" style="width: 60%; height: auto; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));">
                </div>
                <div style="font-size: 0.8rem; font-weight: 800; color: #1E293B; font-family: var(--font-display);">Exams</div>
            </a>
        </div>
    </div>

    <!-- 4. Section: Explore Classes -->
    <div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding: 0 4px;">
            <h2 style="font-size: 1.25rem; font-weight: 900; color: #1E293B; font-family: var(--font-display); margin: 0;">
                Explore Classes
            </h2>
            <a href="?page=lessons" style="color: #6C5CE7; font-weight: 800; font-size: 0.85rem; text-decoration: none;">
                View All
            </a>
        </div>

        <div style="display: flex; flex-direction: column; gap: 12px;">
            <?php 
            $index = 0;
            foreach ($lessons as $lesson): 
                $theme = $cardThemes[$index % count($cardThemes)];
                $title = sanitize($lesson['title']);
                $completed = $lesson['is_completed'];
                $totalParts = max((int)$lesson['vocab_count'], 1); // Avoid division by zero, at least 1 part
                $completedParts = $completed ? $totalParts : 0;
                $progressText = "{$completedParts}/{$totalParts}";
                $percent = $completed ? 100 : 0;
                $index++;
            ?>
            <a href="?page=lesson&id=<?= $lesson['id'] ?>" style="background: white; border-radius: 22px; padding: 16px 20px; box-shadow: 0 4px 18px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between; text-decoration: none; border: 1px solid #F1F5F9; transition: transform 0.2s;">
                <!-- Left: Icon & Title & Progress bar -->
                <div style="display: flex; align-items: center; gap: 16px; flex: 1;">
                    <div style="width: 52px; height: 52px; border-radius: 18px; background: <?= $theme['bg'] ?>; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; flex-shrink: 0;">
                        <?= $theme['icon'] ?>
                    </div>
                    <div style="flex: 1; padding-right: 12px;">
                        <div style="font-weight: 800; font-size: 1rem; color: #1E293B; margin-bottom: 8px; font-family: var(--font-display);">
                            <?= $title ?>
                        </div>
                        <!-- Mini Progress Bar -->
                        <div style="background: #F1F5F9; height: 8px; border-radius: 10px; width: 100%; max-width: 180px; overflow: hidden;">
                            <div style="background: <?= $theme['bar'] ?>; width: <?= $percent ?>%; height: 100%; border-radius: 10px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Right: Progress Count -->
                <div style="font-weight: 900; font-size: 1.15rem; color: #1E293B; font-family: var(--font-display);">
                    <?= $progressText ?>
                </div>
            </a>
            <?php endforeach; ?>

            <?php if (empty($lessons)): ?>
                <!-- Fallback Mock Cards if DB is empty -->
                <div style="background: white; border-radius: 22px; padding: 16px 20px; box-shadow: 0 4px 18px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between; border: 1px solid #F1F5F9;">
                    <div style="display: flex; align-items: center; gap: 16px; flex: 1;">
                        <div style="width: 52px; height: 52px; border-radius: 18px; background: #E8F4FE; display: flex; align-items: center; justify-content: center; font-size: 1.6rem;">🧪</div>
                        <div style="flex: 1;">
                            <div style="font-weight: 800; font-size: 1rem; color: #1E293B; margin-bottom: 8px;">Cool Experiments</div>
                            <div style="background: #F1F5F9; height: 8px; border-radius: 10px; width: 140px; overflow: hidden;">
                                <div style="background: #4A8CFF; width: 70%; height: 100%; border-radius: 10px;"></div>
                            </div>
                        </div>
                    </div>
                    <div style="font-weight: 900; font-size: 1.15rem; color: #1E293B;">7/10</div>
                </div>
                <div style="background: white; border-radius: 22px; padding: 16px 20px; box-shadow: 0 4px 18px rgba(0,0,0,0.03); display: flex; align-items: center; justify-content: space-between; border: 1px solid #F1F5F9;">
                    <div style="display: flex; align-items: center; gap: 16px; flex: 1;">
                        <div style="width: 52px; height: 52px; border-radius: 18px; background: #FFF0E6; display: flex; align-items: center; justify-content: center; font-size: 1.6rem;">📖</div>
                        <div style="flex: 1;">
                            <div style="font-weight: 800; font-size: 1rem; color: #1E293B; margin-bottom: 8px;">Story Time</div>
                            <div style="background: #F1F5F9; height: 8px; border-radius: 10px; width: 140px; overflow: hidden;">
                                <div style="background: #FF9F43; width: 90%; height: 100%; border-radius: 10px;"></div>
                            </div>
                        </div>
                    </div>
                    <div style="font-weight: 900; font-size: 1.15rem; color: #1E293B;">9/10</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 5. Section: Class Schedule -->
    <div style="margin-top: 32px; margin-bottom: 32px;">
        <h2 style="font-size: 1.3rem; font-weight: 900; color: #1E293B; margin-bottom: 16px; font-family: var(--font-display); padding: 0 4px;">My Class Schedule</h2>
        
        <!-- Controls -->
        <div style="display: flex; justify-content: center; gap: 12px; margin-bottom: 24px;">
            <button style="background: white; border: 1px solid #F1F5F9; border-radius: 20px; padding: 10px 20px; font-weight: 700; color: #1E293B; box-shadow: 0 2px 4px rgba(0,0,0,0.02); cursor: pointer; font-size: 0.95rem;">This Day</button>
            <button style="background: #4A8CFF; border: none; border-radius: 20px; padding: 10px 20px; font-weight: 700; color: white; box-shadow: 0 4px 12px rgba(74,140,255,0.3); cursor: pointer; font-size: 0.95rem;">Next Week</button>
            <button style="background: white; border: 1px solid #F1F5F9; border-radius: 20px; padding: 10px 20px; font-weight: 700; color: #1E293B; box-shadow: 0 2px 4px rgba(0,0,0,0.02); cursor: pointer; font-size: 0.95rem;">This Week</button>
        </div>

        <!-- Timetable Scroll Container -->
        <div style="overflow-x: auto; margin: 0 -16px; padding: 0 16px 20px 16px;">
            <div style="min-width: 600px; background: white; border-radius: 24px; padding: 16px; box-shadow: 0 4px 18px rgba(0,0,0,0.03); border: 1px solid #F1F5F9;">
                
                <div class="schedule-grid">
                    <!-- Headers -->
                    <div class="day-header" style="grid-column: 2;">Mon</div>
                    <div class="day-header" style="grid-column: 3;">Tue</div>
                    <div class="day-header" style="grid-column: 4;">Wed</div>
                    <div class="day-header" style="grid-column: 5;">Thu</div>
                    <div class="day-header" style="grid-column: 6;">Fri</div>

                    <!-- Vertical Grid Lines (Background) -->
                    <div class="col-line" style="grid-column: 2;"></div>
                    <div class="col-line" style="grid-column: 3;"></div>
                    <div class="col-line" style="grid-column: 4;"></div>
                    <div class="col-line" style="grid-column: 5;"></div>
                    <div class="col-line" style="grid-column: 6;"></div>

                    <!-- Time Labels -->
                    <div class="time-label" style="grid-row: 2;">09:00</div>
                    <div class="time-label" style="grid-row: 4;">10:00</div>
                    <div class="time-label" style="grid-row: 6;">11:00</div>
                    <div class="time-label" style="grid-row: 8;">12:00</div>
                    <div class="time-label" style="grid-row: 10;">13:00</div>
                    <div class="time-label" style="grid-row: 12;">14:00</div>
                    <div class="time-label" style="grid-row: 14;">15:00</div>
                    <div class="time-label" style="grid-row: 16;">16:00</div>
                    <div class="time-label" style="grid-row: 18;">17:00</div>
                    <div class="time-label" style="grid-row: 20;">18:00</div>
                    <div class="time-label" style="grid-row: 22;">19:00</div>

                    <!-- Blocks: Scheduled lessons from Classroom will be dynamically loaded here -->
                </div>

            </div>
        </div>
    </div>

    <!-- 6. Section: Activity Calendar -->
    <div style="margin-top: 24px; margin-bottom: 32px;">
        <h2 style="font-size: 1.3rem; font-weight: 900; color: #1E293B; margin-bottom: 16px; font-family: var(--font-display); padding: 0 4px;">My Activity Log</h2>
        
        <div style="background: white; border-radius: 24px; padding: 24px; box-shadow: 0 4px 18px rgba(0,0,0,0.03); border: 1px solid #F1F5F9;">
            <!-- Calendar Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <button id="prevMonth" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748B;"><i class="fa-solid fa-chevron-left"></i></button>
                <div id="calendarMonthLabel" style="font-size: 1.1rem; font-weight: 800; color: #1E293B;">Loading...</div>
                <button id="nextMonth" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #64748B;"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
            
            <!-- Calendar Grid -->
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; text-align: center; margin-bottom: 8px;">
                <div style="font-size: 0.8rem; font-weight: 700; color: #94A3B8;">Sun</div>
                <div style="font-size: 0.8rem; font-weight: 700; color: #94A3B8;">Mon</div>
                <div style="font-size: 0.8rem; font-weight: 700; color: #94A3B8;">Tue</div>
                <div style="font-size: 0.8rem; font-weight: 700; color: #94A3B8;">Wed</div>
                <div style="font-size: 0.8rem; font-weight: 700; color: #94A3B8;">Thu</div>
                <div style="font-size: 0.8rem; font-weight: 700; color: #94A3B8;">Fri</div>
                <div style="font-size: 0.8rem; font-weight: 700; color: #94A3B8;">Sat</div>
            </div>
            
            <div id="calendarDays" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 4px; text-align: center;">
                <!-- Days will be generated by JS -->
            </div>
            
            <!-- Activity Feed for selected day -->
            <div id="activityFeedContainer" style="margin-top: 24px; border-top: 1px solid #F1F5F9; padding-top: 24px; display: none;">
                <div id="selectedDateLabel" style="font-size: 1rem; font-weight: 800; color: #1E293B; margin-bottom: 12px;">Activities</div>
                <div id="activityList" style="display: flex; flex-direction: column; gap: 12px;">
                    <!-- Activities -->
                </div>
            </div>
        </div>
    </div>

</div>

<style>
/* Schedule Grid Styles */
.schedule-grid {
    display: grid;
    grid-template-columns: 50px repeat(5, minmax(100px, 1fr));
    grid-template-rows: 40px repeat(22, 40px); /* 1 row = 30 mins */
}
.col-line {
    border-right: 1px solid #F1F5F9;
    grid-row: 1 / -1;
}
.day-header {
    font-weight: 700;
    color: #1E293B;
    font-size: 0.85rem;
    text-align: center;
    padding-bottom: 16px;
    grid-row: 1;
}
.time-label {
    font-size: 0.75rem;
    color: #94A3B8;
    text-align: right;
    padding-right: 12px;
    transform: translateY(-8px);
}
.schedule-card {
    border-radius: 12px;
    padding: 12px;
    margin: 4px;
    font-family: var(--font-display);
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    z-index: 10;
}
.schedule-card-title {
    font-weight: 800;
    font-size: 0.85rem;
    color: #1E293B;
    margin-bottom: 4px;
}
.schedule-card-subtitle {
    font-weight: 600;
    font-size: 0.75rem;
    color: #94A3B8;
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}
a:hover {
    transform: translateY(-2px);
}
.cal-day {
    padding: 8px 4px;
    border-radius: 12px;
    font-size: 0.95rem;
    font-weight: 600;
    color: #1E293B;
    cursor: pointer;
    transition: 0.2s;
    height: 44px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
}
.cal-day:hover {
    background: #F1F5F9;
}
.cal-day.today {
    color: #3B82F6;
    font-weight: 800;
}
.cal-day.selected {
    background: #3B82F6;
    color: white;
}
.cal-day.selected.today {
    background: #3B82F6;
    color: white;
}
.cal-day .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #10B981;
    margin-top: 2px;
}
.cal-day.selected .dot {
    background: white;
}
</style>

<script>
let currentMonth = new Date().getMonth() + 1;
let currentYear = new Date().getFullYear();
let activityData = {};
let selectedDateStr = "";

async function loadCalendar(month, year) {
    document.getElementById('calendarMonthLabel').textContent = new Date(year, month - 1).toLocaleString('default', { month: 'long', year: 'numeric' });
    
    try {
        const res = await fetch(`<?= SITE_URL ?>/api/activity_log.php?month=${month}&year=${year}`);
        const result = await res.json();
        if(result.success) {
            activityData = result.data;
            renderCalendarDays(month, year);
            if(selectedDateStr.startsWith(`${year}-${String(month).padStart(2, '0')}`)) {
                selectDate(selectedDateStr);
            }
        }
    } catch(e) {
        console.error('Failed to load activity log');
    }
}

function renderCalendarDays(month, year) {
    const daysContainer = document.getElementById('calendarDays');
    daysContainer.innerHTML = '';
    
    const firstDay = new Date(year, month - 1, 1).getDay();
    const daysInMonth = new Date(year, month, 0).getDate();
    
    for(let i = 0; i < firstDay; i++) {
        daysContainer.innerHTML += `<div></div>`;
    }
    
    const todayStr = new Date().toLocaleDateString('en-CA'); // YYYY-MM-DD local format
    
    for(let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const hasActivity = activityData[dateStr] && activityData[dateStr].length > 0;
        
        let extraClass = '';
        if(dateStr === todayStr) extraClass = 'today';
        if(dateStr === selectedDateStr) extraClass += ' selected';
        
        let indicator = hasActivity ? `<div class="dot"></div>` : '';
        
        daysContainer.innerHTML += `
            <div class="cal-day ${extraClass}" onclick="selectDate('${dateStr}')">
                ${day}
                ${indicator}
            </div>
        `;
    }
}

function selectDate(dateStr) {
    selectedDateStr = dateStr;
    renderCalendarDays(currentMonth, currentYear);
    
    const feedContainer = document.getElementById('activityFeedContainer');
    const listContainer = document.getElementById('activityList');
    
    const dateObj = new Date(dateStr);
    // adjust for timezone issues by appending T00:00:00
    const displayDate = new Date(dateStr + "T00:00:00").toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
    
    if(!activityData[dateStr] || activityData[dateStr].length === 0) {
        feedContainer.style.display = 'block';
        document.getElementById('selectedDateLabel').textContent = `Activities on ${displayDate}`;
        listContainer.innerHTML = `<div style="color: #94A3B8; font-size: 0.9rem; text-align: center; padding: 12px 0;">No activities on this day</div>`;
        return;
    }
    
    feedContainer.style.display = 'block';
    document.getElementById('selectedDateLabel').textContent = `Activities on ${displayDate}`;
    
    let html = '';
    const icons = {
        'lesson': '<div style="background:#E6F9F0;color:#10B981;width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-book-open"></i></div>',
        'exam': '<div style="background:#FDEDF0;color:#F43F5E;width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-file-signature"></i></div>',
        'pronunciation': '<div style="background:#E8F4FE;color:#0EA5E9;width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-microphone"></i></div>'
    };
    
    activityData[dateStr].forEach(act => {
        const icon = icons[act.type] || '<div style="background:#F1F5F9;color:#64748B;width:32px;height:32px;border-radius:10px;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-star"></i></div>';
        
        html += `
            <div style="display: flex; align-items: center; gap: 12px; background: #F8FAFC; padding: 12px; border-radius: 16px;">
                ${icon}
                <div style="flex: 1;">
                    <div style="font-size: 0.95rem; font-weight: 700; color: #1E293B;">${act.details}</div>
                    <div style="font-size: 0.8rem; color: #64748B;"><i class="fa-regular fa-clock"></i> ${act.time}</div>
                </div>
            </div>
        `;
    });
    
    listContainer.innerHTML = html;
}

document.getElementById('prevMonth').addEventListener('click', () => {
    currentMonth--;
    if(currentMonth < 1) { currentMonth = 12; currentYear--; }
    loadCalendar(currentMonth, currentYear);
});

document.getElementById('nextMonth').addEventListener('click', () => {
    currentMonth++;
    if(currentMonth > 12) { currentMonth = 1; currentYear++; }
    loadCalendar(currentMonth, currentYear);
});

document.addEventListener('DOMContentLoaded', () => {
    selectedDateStr = new Date().toLocaleDateString('en-CA');
    loadCalendar(currentMonth, currentYear).then(() => {
        selectDate(selectedDateStr);
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
