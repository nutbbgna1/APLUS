<div class="page-header">
    <h1 class="page-title">Search Results</h1>
    <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Showing results for: <strong><?= htmlspecialchars($_GET['q'] ?? '') ?></strong></p>
</div>

<?php
$q = $_GET['q'] ?? '';
$hasResults = false;

if (!empty($q)):
    $searchTerm = "%$q%";
    
    // Search Courses
    $stmt = $db->prepare("SELECT * FROM courses WHERE title LIKE ? OR course_code LIKE ?");
    $stmt->execute([$searchTerm, $searchTerm]);
    $courses = $stmt->fetchAll();
    
    // Search Students
    $stmt = $db->prepare("SELECT * FROM users WHERE role='student' AND (fname LIKE ? OR lname LIKE ? OR nickname LIKE ? OR code LIKE ?)");
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $students = $stmt->fetchAll();
    
    if (count($courses) > 0 || count($students) > 0) $hasResults = true;
?>

    <?php if (count($courses) > 0): ?>
    <div class="card" style="margin-bottom: 24px;">
        <h3 style="margin-bottom: 15px; font-weight: 700;">Courses Found (<?= count($courses) ?>)</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">
            <?php foreach($courses as $c): ?>
            <a href="?page=course_edit&id=<?= $c['id'] ?>" style="display: block; padding: 15px; border: 1px solid var(--border); border-radius: 8px; text-decoration: none; color: inherit;">
                <div style="font-weight: 700; color: #3B82F6;"><?= htmlspecialchars($c['title']) ?></div>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 5px;">Code: <?= htmlspecialchars($c['course_code'] ?? 'N/A') ?></div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (count($students) > 0): ?>
    <div class="card" style="margin-bottom: 24px;">
        <h3 style="margin-bottom: 15px; font-weight: 700;">Students Found (<?= count($students) ?>)</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">
            <?php foreach($students as $s): ?>
            <div style="display: flex; align-items: center; gap: 15px; padding: 15px; border: 1px solid var(--border); border-radius: 8px;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: <?= htmlspecialchars($s['avatar_color']) ?>; color: white; display: flex; justify-content: center; align-items: center; font-weight: 700;">
                    <?= mb_substr($s['fname'], 0, 1) ?>
                </div>
                <div>
                    <div style="font-weight: 700;"><?= htmlspecialchars($s['fname'] . ' ' . $s['lname']) ?></div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">Code: <?= htmlspecialchars($s['code']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

<?php endif; ?>

<?php if (!$hasResults): ?>
<div style="text-align: center; padding: 50px; background: white; border-radius: 12px; border: 1px solid var(--border);">
    <i class="fa-solid fa-magnifying-glass" style="font-size: 3rem; color: #CBD5E1; margin-bottom: 15px;"></i>
    <h3 style="color: var(--text-muted);">No results found</h3>
    <p style="font-size: 0.9rem; color: #94A3B8;">Try searching with different keywords.</p>
</div>
<?php endif; ?>
