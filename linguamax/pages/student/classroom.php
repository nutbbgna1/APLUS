<?php
// ============================================================
// LinguaMax — Student Classroom (Storefront & My Courses)
// ============================================================
include __DIR__ . '/../../includes/header.php';

$db = getDB();
$user_id = $_SESSION['user_id'] ?? 1;

// Fetch all published courses
$coursesDb = $db->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM course_episodes WHERE course_id = c.id) as ep_count
    FROM courses c 
    WHERE c.is_published = 1 
    ORDER BY c.id DESC
")->fetchAll();

// Fetch my approved courses
$stmt = $db->prepare("
    SELECT e.course_id, c.title, c.instructor, c.image_url, c.category, c.grade_level,
           (SELECT COUNT(*) FROM course_episodes WHERE course_id = c.id) as ep_count
    FROM course_enrollments e 
    JOIN courses c ON e.course_id = c.id 
    WHERE e.user_id = ? AND e.status = 'approved' 
    ORDER BY e.approved_at DESC
");
$stmt->execute([$user_id]);
$myApprovedCourses = $stmt->fetchAll();

// Get unique categories and grades for filter
$categories = array_unique(array_column($coursesDb, 'category'));
$grades = ['ทั้งหมด', 'ป.4', 'ป.5', 'ป.6', 'ม.1', 'ม.2', 'ม.3', 'ม.4', 'ม.5', 'ม.6'];
?>

<div class="animate-fade-in" style="padding: 24px 16px 80px 16px; min-height: 100vh; max-width: 1200px; margin: 0 auto;">
    
    <!-- Header -->
    <div style="margin-bottom: 32px;">
        <h1 style="font-size: 2.2rem; font-weight: 900; color: #1E293B; line-height: 1.2; font-family: var(--font-display); margin-bottom: 8px;">
            Let's start learning! 🚀
        </h1>
        <p style="color: #64748B; font-size: 1.05rem;">Choose a course below and level up your skills.</p>
    </div>

    <!-- My Courses Section -->
    <?php if (count($myApprovedCourses) > 0): ?>
    <div style="margin-bottom: 40px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h2 style="font-size: 1.4rem; font-weight: 800; color: #1E293B; font-family: var(--font-display);">คอร์สเรียนของฉัน</h2>
            <div style="background: #E0E7FF; color: #4F46E5; padding: 4px 12px; border-radius: 20px; font-weight: 800; font-size: 0.85rem;">
                <?= count($myApprovedCourses) ?> Courses
            </div>
        </div>
        
        <div style="display: flex; gap: 16px; overflow-x: auto; padding-bottom: 16px; scroll-snap-type: x mandatory; scrollbar-width: none;" class="hide-scrollbar">
            <?php foreach ($myApprovedCourses as $mc): ?>
            <a href="?page=classroom-view&id=<?= $mc['course_id'] ?>" style="scroll-snap-align: start; flex: 0 0 280px; background: white; border-radius: 24px; border: 1px solid rgba(226, 232, 240, 0.8); overflow: hidden; text-decoration: none; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: block;" class="course-card-hover">
                <div style="width: 100%; height: 150px; background: linear-gradient(135deg, #F1F5F9, #E2E8F0); position: relative; overflow: hidden;">
                    <?php if ($mc['image_url']): ?>
                        <img src="<?= SITE_URL ?>/<?= htmlspecialchars($mc['image_url']) ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;" class="course-img">
                    <?php else: ?>
                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background: linear-gradient(135deg, #4F46E5, #6366F1); color: white; font-size: 3rem; font-weight: 900;"><?= mb_substr($mc['category'], 0, 1) ?></div>
                    <?php endif; ?>
                    
                    <!-- Continue Learning Badge -->
                    <div style="position: absolute; bottom: 12px; right: 12px; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(4px); padding: 6px 12px; border-radius: 12px; font-size: 0.75rem; font-weight: 800; color: #4F46E5; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <i class="fa-solid fa-play" style="margin-right: 4px;"></i> เข้าเรียน
                    </div>
                </div>
                <div style="padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #6366F1; background: #E0E7FF; padding: 4px 10px; border-radius: 8px; text-transform: uppercase; letter-spacing: 0.5px;"><?= htmlspecialchars($mc['category']) ?></span>
                        <span style="font-size: 0.75rem; color: #94A3B8; font-weight: 600;"><i class="fa-solid fa-video"></i> <?= $mc['ep_count'] ?> EP</span>
                    </div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: #1E293B; margin-bottom: 6px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars($mc['title']) ?></div>
                    <div style="font-size: 0.8rem; color: #64748B; font-weight: 600;"><i class="fa-solid fa-chalkboard-user"></i> By <?= htmlspecialchars($mc['instructor']) ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Explore Courses -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h2 style="font-size: 1.4rem; font-weight: 800; color: #1E293B; font-family: var(--font-display);">คอร์สทั้งหมด</h2>
    </div>

    <!-- Search & Filters -->
    <div style="background: white; border-radius: 20px; padding: 20px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid var(--border); margin-bottom: 32px;">
        <div style="position: relative; margin-bottom: 16px;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94A3B8; font-size: 1.1rem;"></i>
            <input type="text" id="searchInput" placeholder="ค้นหาคอร์สเรียน..." onkeyup="filterCourses()" style="width: 100%; padding: 14px 16px 14px 44px; border-radius: 12px; border: 1px solid #E2E8F0; background: #F8FAFC; font-size: 0.95rem; outline: none; transition: all 0.2s; font-family: inherit;">
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748B; margin-bottom: 6px; text-transform: uppercase;">หมวดหมู่</label>
                <select id="catFilter" onchange="filterCourses()" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #E2E8F0; background: #F8FAFC; font-size: 0.9rem; outline: none; font-family: inherit;">
                    <option value="">ทั้งหมด</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748B; margin-bottom: 6px; text-transform: uppercase;">ระดับชั้น</label>
                <select id="gradeFilter" onchange="filterCourses()" style="width: 100%; padding: 12px; border-radius: 10px; border: 1px solid #E2E8F0; background: #F8FAFC; font-size: 0.9rem; outline: none; font-family: inherit;">
                    <?php foreach ($grades as $g): ?>
                        <option value="<?= htmlspecialchars($g === 'ทั้งหมด' ? '' : $g) ?>"><?= htmlspecialchars($g) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <!-- Course Grid -->
    <div id="courseList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">
        <?php foreach ($coursesDb as $c): ?>
            <div class="course-item" data-title="<?= strtolower(htmlspecialchars($c['title'])) ?>" data-cat="<?= htmlspecialchars($c['category']) ?>" data-grade="<?= htmlspecialchars($c['grade_level']) ?>" style="background: white; border-radius: 24px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid rgba(226, 232, 240, 0.8); cursor: pointer; transition: all 0.3s ease; display: flex; flex-direction: column; height: 100%;" onclick="window.location.href='?page=classroom-view&id=<?= $c['id'] ?>'" class="store-card-hover">
                
                <!-- Course Image -->
                <div style="width: 100%; height: 160px; background: #F1F5F9; position: relative; overflow: hidden;">
                    <?php if ($c['image_url']): ?>
                        <img src="<?= SITE_URL ?>/<?= htmlspecialchars($c['image_url']) ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;" class="course-img">
                    <?php else: ?>
                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color: white; background: linear-gradient(135deg, #0EA5E9, #38BDF8); font-size: 3rem; font-weight: 900; font-family: var(--font-display);"><?= mb_substr($c['category'], 0, 1) ?></div>
                    <?php endif; ?>
                    
                    <!-- Grade Badge -->
                    <div style="position: absolute; top: 12px; right: 12px; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px); color: white; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 700;">
                        <?= htmlspecialchars($c['grade_level']) ?>
                    </div>
                </div>

                <!-- Course Content -->
                <div style="padding: 20px; display: flex; flex-direction: column; flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #0EA5E9; background: #E0F2FE; padding: 4px 10px; border-radius: 8px; text-transform: uppercase;"><?= htmlspecialchars($c['category']) ?></span>
                        <span style="font-size: 0.75rem; color: #94A3B8; font-weight: 600;"><i class="fa-solid fa-list"></i> <?= $c['ep_count'] ?> EP</span>
                    </div>
                    
                    <div style="font-size: 1.15rem; font-weight: 800; color: #1E293B; margin-bottom: 8px; line-height: 1.4; flex: 1;"><?= htmlspecialchars($c['title']) ?></div>
                    <div style="font-size: 0.85rem; color: #64748B; font-weight: 600; margin-bottom: 16px;"><i class="fa-solid fa-user-tie"></i> <?= htmlspecialchars($c['instructor']) ?></div>
                    
                    <div style="border-top: 1px solid #F1F5F9; padding-top: 16px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="font-size: 1.3rem; font-weight: 900; color: #4F46E5;">
                            <?= $c['price'] > 0 ? '฿' . number_format($c['price'], 2) : 'ฟรี' ?>
                        </div>
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: #F8FAFC; display: flex; justify-content: center; align-items: center; color: #94A3B8; border: 1px solid #E2E8F0; transition: all 0.2s;" class="arrow-btn">
                            <i class="fa-solid fa-arrow-right"></i>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($coursesDb)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: white; border-radius: 20px; border: 1px dashed #CBD5E1;">
                <i class="fa-solid fa-box-open" style="font-size: 3rem; color: #CBD5E1; margin-bottom: 16px;"></i>
                <h3 style="font-size: 1.2rem; color: #475569; margin-bottom: 8px;">ยังไม่มีคอร์สเรียนในระบบ</h3>
                <p style="color: #94A3B8;">แอดมินกำลังเพิ่มคอร์สเรียนใหม่ เร็วๆ นี้!</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<style>
/* Hide Scrollbar but keep functionality */
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Hover effects */
.course-card-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
}
.course-card-hover:hover .course-img {
    transform: scale(1.05);
}

.store-card-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
    border-color: #CBD5E1 !important;
}
.store-card-hover:hover .course-img {
    transform: scale(1.05);
}
.store-card-hover:hover .arrow-btn {
    background: #4F46E5 !important;
    color: white !important;
    border-color: #4F46E5 !important;
}

#searchInput:focus {
    border-color: #4F46E5 !important;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1) !important;
}
select:focus {
    border-color: #4F46E5 !important;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1) !important;
}
</style>

<script>
function filterCourses() {
    let q = document.getElementById('searchInput').value.toLowerCase();
    let c = document.getElementById('catFilter').value;
    let g = document.getElementById('gradeFilter').value;
    
    document.querySelectorAll('.course-item').forEach(item => {
        let title = item.getAttribute('data-title');
        let cat = item.getAttribute('data-cat');
        let grade = item.getAttribute('data-grade');
        
        let matchQ = title.includes(q);
        let matchC = c === '' || cat === c;
        let matchG = g === '' || grade === g;
        
        if (matchQ && matchC && matchG) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
