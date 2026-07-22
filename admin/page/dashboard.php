<?php
// Fetch actual stats from the database
try {
    $stmt = $db->query("SELECT COUNT(*) FROM courses");
    $total_courses = $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM lessons");
    $total_lessons = $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM vocabulary");
    $total_vocabulary = $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM game_sentences");
    $total_sentences = $stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(*) FROM game_fill_blanks");
    $total_blanks = $stmt->fetchColumn();
    $total_games = $total_sentences + $total_blanks;

    $stmt = $db->query("SELECT COUNT(*) FROM exams");
    $total_exams = $stmt->fetchColumn();
    
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE role = 'student'");
    $total_students = $stmt->fetchColumn();
    
} catch (Exception $e) {
    // Fallback if tables don't exist yet
    $total_courses = 0;
    $total_lessons = 0;
    $total_vocabulary = 0;
    $total_games = 0;
    $total_exams = 0;
    $total_students = 0;
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Admin Dashboard</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Overview of your LinguaMax content and statistics.</p>
    </div>
</div>

<div class="dashboard-grid">
    <!-- Left Column: KPI & Recent Activity -->
    <div>
        <!-- KPIs -->
        <div class="kpi-row">
            <div class="card" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 5px;">Total Courses</div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-main);"><?= number_format((float)$total_courses) ?></div>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #EEF2FF; color: #4F46E5; font-size: 1.2rem;">
                        <i class="fa-solid fa-book"></i>
                    </div>
                </div>
            </div>
            
            <div class="card" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 5px;">Total Students</div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-main);"><?= number_format((float)$total_students) ?></div>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #FEF2F2; color: #EF4444; font-size: 1.2rem;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>
            
            <div class="card" style="margin-bottom: 0;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 5px;">Total Lessons</div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-main);"><?= number_format((float)$total_lessons) ?></div>
                    </div>
                    <div style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #F0FDF4; color: #22C55E; font-size: 1.2rem;">
                        <i class="fa-solid fa-file-alt"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Content Statistics Chart -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Content Overview</div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-top: 10px;">
                <div style="background: #F8FAFC; padding: 20px; border-radius: 12px; text-align: center; border: 1px solid var(--border);">
                    <i class="fa-solid fa-spell-check" style="font-size: 2rem; color: #6366F1; margin-bottom: 10px;"></i>
                    <div style="font-size: 1.2rem; font-weight: 800;"><?= number_format((float)$total_vocabulary) ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Vocabulary</div>
                </div>
                
                <div style="background: #F8FAFC; padding: 20px; border-radius: 12px; text-align: center; border: 1px solid var(--border);">
                    <i class="fa-solid fa-gamepad" style="font-size: 2rem; color: #EC4899; margin-bottom: 10px;"></i>
                    <div style="font-size: 1.2rem; font-weight: 800;"><?= number_format((float)$total_games) ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Mini Games</div>
                </div>
                
                <div style="background: #F8FAFC; padding: 20px; border-radius: 12px; text-align: center; border: 1px solid var(--border);">
                    <i class="fa-solid fa-file-pen" style="font-size: 2rem; color: #EAB308; margin-bottom: 10px;"></i>
                    <div style="font-size: 1.2rem; font-weight: 800;"><?= number_format((float)$total_exams) ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Exams</div>
                </div>
                
                <div style="background: #F8FAFC; padding: 20px; border-radius: 12px; text-align: center; border: 1px solid var(--border);">
                    <?php 
                        $stmt = $db->query("SELECT COUNT(*) FROM reading_passages");
                        $total_reading = $stmt->fetchColumn();
                    ?>
                    <i class="fa-solid fa-book-open-reader" style="font-size: 2rem; color: #14B8A6; margin-bottom: 10px;"></i>
                    <div style="font-size: 1.2rem; font-weight: 800;"><?= number_format((float)$total_reading) ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">Reading</div>
                </div>
            </div>
        </div>

        <!-- Latest Courses -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Latest Courses Added</div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Grade Level</th>
                        <th>Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    try {
                        $stmt = $db->query("SELECT * FROM courses ORDER BY id DESC LIMIT 5");
                        $latest_courses = $stmt->fetchAll();
                        
                        foreach($latest_courses as $c): 
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['title']) ?></strong></td>
                        <td><?= htmlspecialchars($c['category']) ?></td>
                        <td><?= htmlspecialchars($c['grade_level'] ?: 'ทั้งหมด') ?></td>
                        <td><strong>฿<?= number_format($c['price']) ?></strong></td>
                        <td>
                            <?php if($c['is_published']): ?>
                                <span style="background:#DCFCE7;color:#16A34A;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;">Published</span>
                            <?php else: ?>
                                <span style="background:#F1F5F9;color:#475569;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;">Draft</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php 
                        endforeach; 
                        if(empty($latest_courses)):
                    ?>
                        <tr><td colspan="5" style="text-align:center; padding: 20px;">No courses found.</td></tr>
                    <?php
                        endif;
                    } catch (Exception $e) {} 
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Right Column: System Status & Shortcuts -->
    <div>
        <!-- Welcome Card -->
        <div class="card" style="background: linear-gradient(135deg, var(--primary) 0%, #312E81 100%); color: white; border: none;">
            <div style="font-size: 1.5rem; font-weight: 800; margin-bottom: 10px;">Welcome Admin! 👋</div>
            <div style="font-size: 0.9rem; opacity: 0.9; line-height: 1.5; margin-bottom: 20px;">
                You have full control over LinguaMax content. Check the quick links below to manage your school's data.
            </div>
            
            <a href="?page=courses&action=add" class="btn" style="background: white; color: var(--primary); width: 100%; text-align: center; border: none; font-weight: 700;">
                + Add New Course
            </a>
        </div>

        <!-- Quick Links -->
        <div class="card">
            <div class="card-header">
                <div class="card-title">Quick Actions</div>
            </div>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <a href="?page=lessons" style="display: flex; align-items: center; gap: 15px; padding: 15px; background: #F8FAFC; border-radius: 12px; text-decoration: none; color: var(--text-main); font-weight: 600; border: 1px solid var(--border); transition: all 0.2s;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #E0E7FF; color: #4338CA; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fa-solid fa-file-alt"></i></div>
                    <div style="flex: 1;">Manage Lessons</div>
                    <i class="fa-solid fa-chevron-right" style="color: var(--text-muted); font-size: 0.8rem;"></i>
                </a>
                
                <a href="?page=vocabulary" style="display: flex; align-items: center; gap: 15px; padding: 15px; background: #F8FAFC; border-radius: 12px; text-decoration: none; color: var(--text-main); font-weight: 600; border: 1px solid var(--border); transition: all 0.2s;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #FEF9C3; color: #CA8A04; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fa-solid fa-spell-check"></i></div>
                    <div style="flex: 1;">Manage Vocabulary</div>
                    <i class="fa-solid fa-chevron-right" style="color: var(--text-muted); font-size: 0.8rem;"></i>
                </a>
                
                <a href="?page=minigames" style="display: flex; align-items: center; gap: 15px; padding: 15px; background: #F8FAFC; border-radius: 12px; text-decoration: none; color: var(--text-main); font-weight: 600; border: 1px solid var(--border); transition: all 0.2s;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #FCE7F3; color: #BE185D; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fa-solid fa-gamepad"></i></div>
                    <div style="flex: 1;">Manage Mini Games</div>
                    <i class="fa-solid fa-chevron-right" style="color: var(--text-muted); font-size: 0.8rem;"></i>
                </a>
                
                <a href="?page=categories" style="display: flex; align-items: center; gap: 15px; padding: 15px; background: #F8FAFC; border-radius: 12px; text-decoration: none; color: var(--text-main); font-weight: 600; border: 1px solid var(--border); transition: all 0.2s;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;"><i class="fa-solid fa-list"></i></div>
                    <div style="flex: 1;">Manage Categories</div>
                    <i class="fa-solid fa-chevron-right" style="color: var(--text-muted); font-size: 0.8rem;"></i>
                </a>
            </div>
        </div>

    </div>
</div>

<style>
.quick-action-link:hover {
    border-color: var(--primary) !important;
    background: white !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
</style>
