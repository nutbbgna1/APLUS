<?php
// ============================================================
// LinguaMax — Student Classroom (Course Storefront)
// ============================================================
include __DIR__ . '/../../includes/header.php';

$db = getDB();
$user_id = $_SESSION['user_id'] ?? 1;

// Fetch published courses
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

// Fetch categories
$catDb = $db->query("SELECT * FROM course_categories ORDER BY id ASC")->fetchAll();
$categories = array_column($catDb, 'name');

// Fetch subcategories grouped by category
$subcatDb = $db->query("SELECT s.*, c.name as cat_name FROM course_subcategories s JOIN course_categories c ON s.category_id = c.id")->fetchAll();
$subcatsByCat = [];
foreach ($subcatDb as $s) {
    $subcatsByCat[$s['cat_name']][] = $s['name'];
}

$grades = ['ทั้งหมด', 'ป.4', 'ป.5', 'ป.6', 'ม.1', 'ม.2', 'ม.3', 'ม.4', 'ม.5', 'ม.6'];
?>

<div class="animate-fade-in" style="padding: 16px 4px 40px 4px; min-height: 100vh;">
    
    <!-- Title -->
    <h1 style="font-size: 1.8rem; font-weight: 900; color: #1E293B; line-height: 1.25; margin-bottom: 24px; font-family: var(--font-display);">
        Find a resource you<br>want to learn!
    </h1>

    <!-- Search Bar -->
    <div style="position: relative; margin-bottom: 24px;">
        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 1.2rem;"></i>
        <input type="text" id="searchInput" placeholder="Search Courses" onkeyup="filterCourses()" style="width: 100%; padding: 14px 16px 14px 48px; border-radius: 12px; border: 1px solid var(--border); font-size: 1rem; box-shadow: var(--shadow-sm); outline: none; font-family: inherit; color: var(--text); background: var(--surface);">
    </div>
    
    <!-- Filters -->
    <div style="margin-bottom: 16px; padding: 12px; background: white; border-radius: 12px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); display: flex; gap: 16px;">
        <div style="flex: 1;">
            <label style="font-size: 0.9rem; font-weight: 700; color: var(--text); margin-bottom: 8px; display: block;">เลือกระดับชั้นเรียน:</label>
            <select id="gradeFilter" onchange="filterCourses()" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); outline: none; font-size: 0.95rem; font-weight: 600; color: var(--primary);">
                <?php foreach($grades as $g): ?>
                    <option value="<?= $g ?>"><?= $g ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="flex: 1;">
            <label style="font-size: 0.9rem; font-weight: 700; color: var(--text); margin-bottom: 8px; display: block;">หมวดย่อย:</label>
            <select id="subcatFilter" onchange="filterCourses()" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); outline: none; font-size: 0.95rem; font-weight: 600; color: var(--primary);">
                <option value="ทั้งหมด">ทั้งหมด</option>
            </select>
        </div>
    </div>

    <!-- My Courses (Approved) -->
    <?php if (!empty($myApprovedCourses)): ?>
    <div style="margin-bottom: 36px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 style="font-size: 1.15rem; font-weight: 800; color: #1E293B; margin: 0; font-family: var(--font-display);">คอร์สเรียนของฉัน</h2>
        </div>
        <div style="display: flex; gap: 16px; overflow-x: auto; padding-bottom: 12px; margin: 0 -4px; padding-left: 4px; padding-right: 4px; -webkit-overflow-scrolling: touch;" class="custom-scrollbar">
            <?php foreach($myApprovedCourses as $mc): ?>
            <a href="?page=classroom-view&id=<?= $mc['course_id'] ?>" style="flex: 0 0 240px; background: white; border-radius: 20px; border: 1px solid var(--border); overflow: hidden; text-decoration: none; box-shadow: var(--shadow-sm); transition: 0.3s; display: block;">
                <div style="width: 100%; height: 140px; background: #F1F5F9; position: relative;">
                    <?php if ($mc['image_url']): ?>
                        <img src="<?= SITE_URL ?>/<?= htmlspecialchars($mc['image_url']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; background: linear-gradient(135deg, #E0E7FF, #C7D2FE); color: #6366F1; font-size: 2.5rem; font-weight: 900;"><?= mb_substr($mc['category'], 0, 1) ?></div>
                    <?php endif; ?>
                    <div style="position: absolute; bottom: 10px; right: 10px; background: rgba(255,255,255,0.95); padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; color: #16A34A; display: flex; align-items: center; gap: 4px;">
                        <i class="fa-solid fa-circle-check"></i> เข้าเรียนได้
                    </div>
                </div>
                <div style="padding: 16px;">
                    <div style="font-size: 0.75rem; font-weight: 800; color: var(--primary); margin-bottom: 4px;"><?= htmlspecialchars($mc['category']) ?></div>
                    <div style="font-weight: 800; color: #1E293B; font-size: 1.05rem; margin-bottom: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars($mc['title']) ?></div>
                    <div style="font-size: 0.75rem; color: #94A3B8; font-weight: 600;"><?= $mc['ep_count'] ?> บทเรียน</div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Categories -->
    <div style="margin-bottom: 36px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 style="font-size: 1.15rem; font-weight: 800; color: #1E293B; margin: 0; font-family: var(--font-display);">Categories</h2>
        </div>
        <div style="display: flex; gap: 12px; overflow-x: auto; padding-bottom: 8px; margin: 0 -4px; padding-left: 4px; padding-right: 4px; -webkit-overflow-scrolling: touch;" id="category-tabs">
            <?php foreach($categories as $index => $cat): ?>
                <div class="category-tab <?= $index === 0 ? 'active' : '' ?>" data-category="<?= $cat ?>" onclick="selectCategory('<?= $cat ?>')">
                    <?= $cat ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Course List -->
    <div style="margin-bottom: 40px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 id="course-list-title" style="font-size: 1.15rem; font-weight: 800; color: #1E293B; margin: 0; font-family: var(--font-display);">คอร์ส<?= !empty($categories) ? $categories[0] : '' ?></h2>
        </div>
        
        <div id="course-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px;">
            <?php foreach($coursesDb as $c): ?>
                <a href="?page=classroom-view&id=<?= $c['id'] ?>" 
                   class="course-card" 
                   data-category="<?= htmlspecialchars($c['category']) ?>" 
                   data-subcategory="<?= htmlspecialchars($c['sub_category']) ?>" 
                   data-grade="<?= htmlspecialchars($c['grade_level'] ?: 'ทั้งหมด') ?>"
                   data-title="<?= htmlspecialchars(strtolower($c['title'])) ?>"
                   style="text-decoration: none; color: inherit; background: var(--surface); border-radius: 20px; box-shadow: var(--shadow); border: 1px solid var(--border); transition: var(--transition); cursor: pointer; display: none; overflow: hidden;">
                   
                    <!-- Course Image -->
                    <div style="width: 100%; height: 150px; background: linear-gradient(135deg, #E0E7FF, #C7D2FE); position: relative;">
                        <?php if ($c['image_url']): ?>
                            <img src="<?= SITE_URL ?>/<?= htmlspecialchars($c['image_url']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color: #6366F1; font-size: 3rem; font-weight: 900; font-family: var(--font-display);"><?= mb_substr($c['category'], 0, 1) ?></div>
                        <?php endif; ?>
                        <div style="position: absolute; bottom: 8px; left: 8px; background: rgba(0,0,0,0.65); color: white; padding: 3px 10px; border-radius: 12px; font-size: 0.7rem; font-weight: 700;">
                            <?= htmlspecialchars($c['grade_level'] ?: 'ทั้งหมด') ?>
                        </div>
                        <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.65); color: white; padding: 3px 10px; border-radius: 12px; font-size: 0.7rem; font-weight: 700;">
                            <?= $c['ep_count'] ?> EP
                        </div>
                    </div>
                    
                    <div style="padding: 16px;">
                        <div style="font-weight: 800; font-size: 1.05rem; color: var(--text); font-family: var(--font-display); margin-bottom: 6px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars($c['title']) ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; margin-bottom: 12px;">By <?= htmlspecialchars($c['instructor']) ?></div>
                        <div style="display: flex; justify-content: flex-end; align-items: center;">
                            <div style="font-weight: 800; font-size: 1.15rem; color: var(--primary); font-family: var(--font-display);">฿<?= number_format($c['price']) ?></div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
            
            <div id="no-results" style="display: none; grid-column: 1 / -1; text-align: center; padding: 40px 20px; color: var(--text-muted); font-weight: 600;">
                ไม่พบคอร์สเรียนที่ตรงกับเงื่อนไข
            </div>
        </div>
    </div>

</div>

<style>
/* Hide default scrollbars */
div[style*="overflow-x: auto"]::-webkit-scrollbar { display: none; }
div[style*="overflow-x: auto"] { -ms-overflow-style: none; scrollbar-width: none; }

.category-tab {
    background: var(--surface); border: 1px solid var(--border); color: var(--text-secondary); 
    padding: 10px 20px; border-radius: 12px; font-weight: 700; font-size: 0.9rem; 
    flex-shrink: 0; box-shadow: var(--shadow-sm); cursor: pointer;
    transition: all 0.3s ease;
}
.category-tab:hover {
    border-color: var(--primary);
    color: var(--primary);
}
.category-tab.active {
    background: var(--primary); color: white; border-color: var(--primary);
    box-shadow: 0 4px 12px var(--primary-glow);
}
.course-card { position: relative; }
.course-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg) !important;
}
</style>

<script>
const subcatsByCat = <?= json_encode($subcatsByCat) ?>;
let currentCategory = '<?= !empty($categories) ? addslashes($categories[0]) : '' ?>';

function updateSubcatDropdown() {
    const subSelect = document.getElementById('subcatFilter');
    subSelect.innerHTML = '<option value="ทั้งหมด">ทั้งหมด</option>';
    if (subcatsByCat[currentCategory]) {
        subcatsByCat[currentCategory].forEach(sub => {
            const opt = document.createElement('option');
            opt.value = sub;
            opt.textContent = sub;
            subSelect.appendChild(opt);
        });
    }
}

function selectCategory(categoryName) {
    currentCategory = categoryName;
    
    const tabs = document.querySelectorAll('.category-tab');
    tabs.forEach(tab => {
        tab.classList.toggle('active', tab.getAttribute('data-category') === categoryName);
    });

    document.getElementById('course-list-title').innerText = 'คอร์ส' + categoryName;
    
    updateSubcatDropdown();
    filterCourses();
}

function filterCourses() {
    const selectedGrade = document.getElementById('gradeFilter').value;
    const selectedSubcat = document.getElementById('subcatFilter').value;
    const searchQuery = document.getElementById('searchInput').value.toLowerCase();
    const courses = document.querySelectorAll('.course-card');
    let visibleCount = 0;

    courses.forEach(course => {
        const cat = course.getAttribute('data-category');
        const subcat = course.getAttribute('data-subcategory');
        const grade = course.getAttribute('data-grade');
        const title = course.getAttribute('data-title');
        
        const matchCat = (cat === currentCategory);
        const matchSubcat = (selectedSubcat === 'ทั้งหมด' || subcat === selectedSubcat);
        const matchGrade = (selectedGrade === 'ทั้งหมด' || grade === 'ทั้งหมด' || grade === selectedGrade);
        const matchSearch = title.includes(searchQuery);
        
        if (matchCat && matchSubcat && matchGrade && matchSearch) {
            course.style.display = 'block';
            course.classList.add('animate-fade-in');
            visibleCount++;
        } else {
            course.style.display = 'none';
        }
    });
    
    document.getElementById('no-results').style.display = visibleCount === 0 ? 'block' : 'none';
}

window.onload = function() {
    updateSubcatDropdown();
    filterCourses();
};
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
