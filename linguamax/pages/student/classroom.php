<?php
// ============================================================
// LinguaMax — Student Classroom
// ============================================================
include __DIR__ . '/../../includes/header.php';

// Fetch courses from database
$db = getDB();
$stmt = $db->query("SELECT * FROM courses WHERE is_published = 1");
$coursesDb = $stmt->fetchAll();

$user_id = $_SESSION['user_id'] ?? 1;
$stmt = $db->prepare("SELECT e.*, c.title, c.instructor, c.image_url, c.category, c.grade_level 
                      FROM course_enrollments e 
                      JOIN courses c ON e.course_id = c.id 
                      WHERE e.user_id = ? AND e.status = 'approved' 
                      ORDER BY e.approved_at DESC");
$stmt->execute([$user_id]);
$myApprovedCourses = $stmt->fetchAll();

$stmt = $db->query("SELECT * FROM course_categories ORDER BY id ASC");
$catDb = $stmt->fetchAll();
$categories = [];
foreach ($catDb as $c) {
    $categories[] = $c['name'];
}

$stmt = $db->query("SELECT * FROM course_subcategories");
$subcatDb = $stmt->fetchAll();
$subcatsByCat = [];
foreach ($subcatDb as $s) {
    // We need category name to map to subcats easily on frontend
    $cName = '';
    foreach ($catDb as $c) {
        if ($c['id'] == $s['category_id']) { $cName = $c['name']; break; }
    }
    if ($cName) {
        $subcatsByCat[$cName][] = $s['name'];
    }
}

$grades = ['ทั้งหมด', 'ป.4', 'ป.5', 'ป.6', 'ม.1', 'ม.2', 'ม.3', 'ม.4', 'ม.5', 'ม.6'];

$courses = [];
foreach ($coursesDb as $c) {
    $courses[] = [
        'id' => $c['id'],
        'category' => $c['category'],
        'sub_category' => $c['sub_category'],
        'grade_level' => $c['grade_level'] ?: 'ทั้งหมด',
        'title' => $c['title'],
        'instructor' => $c['instructor'],
        'rating' => '4.9',
        'students' => '12k',
        'price' => $c['price']
    ];
}
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
                        <img src="<?= SITE_URL ?>/../<?= htmlspecialchars($mc['image_url']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#94A3B8;"><i class="fa-solid fa-book fa-2x"></i></div>
                    <?php endif; ?>
                    <div style="position: absolute; bottom: 10px; right: 10px; background: rgba(255,255,255,0.9); padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; color: #16A34A; display: flex; align-items: center; gap: 4px;">
                        <i class="fa-solid fa-circle-check"></i> เข้าเรียนได้
                    </div>
                </div>
                <div style="padding: 16px;">
                    <div style="font-size: 0.75rem; font-weight: 800; color: var(--primary); margin-bottom: 4px;"><?= htmlspecialchars($mc['category']) ?></div>
                    <div style="font-weight: 800; color: #1E293B; font-size: 1.05rem; margin-bottom: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?= htmlspecialchars($mc['title']) ?></div>
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
            <h2 id="course-list-title" style="font-size: 1.15rem; font-weight: 800; color: #1E293B; margin: 0; font-family: var(--font-display);">คอร์สวิทย์</h2>
        </div>
        
        <div id="course-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px;">
            <?php foreach($courses as $course): ?>
                <a href="?page=classroom-view&id=<?= $course['id'] ?>&title=<?= urlencode($course['title']) ?>&instructor=<?= urlencode($course['instructor']) ?>&subject=<?= urlencode($course['category']) ?>" 
                   class="course-card" 
                   data-category="<?= htmlspecialchars($course['category']) ?>" 
                   data-subcategory="<?= htmlspecialchars($course['sub_category']) ?>" 
                   data-grade="<?= htmlspecialchars($course['grade_level']) ?>"
                   data-title="<?= htmlspecialchars(strtolower($course['title'])) ?>"
                   style="text-decoration: none; color: inherit; background: var(--surface); border-radius: 20px; padding: 16px; box-shadow: var(--shadow); border: 1px solid var(--border); transition: var(--transition); cursor: pointer; display: none;">
                   
                    <div style="background: var(--primary-light); border-radius: 16px; width: 100%; height: 140px; margin-bottom: 16px; display:flex; align-items:center; justify-content:center; color:white; font-size:3.5rem; font-family: var(--font-display); font-weight:900;">
                        <?= mb_substr($course['category'], 0, 1) ?>
                    </div>
                    
                    <div style="position: absolute; margin-top: -55px; margin-left: 10px; background: rgba(0,0,0,0.6); color: white; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 700;">
                        <?= $course['grade_level'] ?>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <div style="font-weight: 800; font-size: 1.05rem; color: var(--text); font-family: var(--font-display);"><?= htmlspecialchars($course['title']) ?></div>
                        <div style="display: flex; align-items: center; gap: 4px; font-size: 0.85rem; font-weight: 700; color: var(--text-secondary);">
                            <i class="fa-solid fa-star" style="color: var(--warning);"></i> <?= $course['rating'] ?>
                        </div>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; margin-bottom: 16px;">By <?= htmlspecialchars($course['instructor']) ?></div>
                    <div style="display: flex; justify-content: flex-end; align-items: center;">
                        <div style="font-weight: 800; font-size: 1.15rem; color: var(--primary); font-family: var(--font-display);">฿<?= number_format($course['price']) ?></div>
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
let currentCategory = '<?= !empty($categories) ? $categories[0] : '' ?>';

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
    
    // Update active tab styling
    const tabs = document.querySelectorAll('.category-tab');
    tabs.forEach(tab => {
        if(tab.getAttribute('data-category') === categoryName) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    // Update section title
    document.getElementById('course-list-title').innerText = 'คอร์ส' + categoryName;
    
    updateSubcatDropdown();
    filterCourses();
}

function filterCourses() {
    const selectedGrade = document.getElementById('gradeFilter').value;
    const searchQuery = document.getElementById('searchInput').value.toLowerCase();
    const courses = document.querySelectorAll('.course-card');
    let visibleCount = 0;

    courses.forEach(course => {
        const cat = course.getAttribute('data-category');
        const subcat = course.getAttribute('data-subcategory');
        const grade = course.getAttribute('data-grade');
        const title = course.getAttribute('data-title');
        
        const selectedSubcat = document.getElementById('subcatFilter').value;
        
        // Check category match
        const matchCat = (cat === currentCategory);
        
        const matchSubcat = (selectedSubcat === 'ทั้งหมด' || subcat === selectedSubcat);
        
        // Check grade match ('ทั้งหมด' in filter means show all grades for that cat. 
        // If course has 'ทั้งหมด', it should appear in all grade filters)
        const matchGrade = (selectedGrade === 'ทั้งหมด' || grade === 'ทั้งหมด' || grade === selectedGrade);
        
        // Check search match
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

// Initial filter call on page load
window.onload = function() {
    updateSubcatDropdown();
    filterCourses();
};
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
