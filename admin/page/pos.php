<div class="page-header">
    <div>
        <h1 class="page-title">Point of Sales (POS)</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Manually enroll a student into a course</p>
    </div>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'sell') {
    $user_id = (int)$_POST['user_id'];
    $course_id = (int)$_POST['course_id'];
    
    // Check if already enrolled
    $stmt = $db->prepare("SELECT id FROM course_enrollments WHERE user_id = ? AND course_id = ?");
    $stmt->execute([$user_id, $course_id]);
    if ($stmt->fetch()) {
        $error = "This student is already enrolled in this course.";
    } else {
        // Enroll immediately
        $stmt = $db->prepare("INSERT INTO course_enrollments (user_id, course_id, status) VALUES (?, ?, 'approved')");
        $stmt->execute([$user_id, $course_id]);
        $success = "Successfully enrolled student into the course!";
    }
}

// Fetch all active users
$users_stmt = $db->query("SELECT id, username, email FROM users ORDER BY username ASC");
$users = $users_stmt->fetchAll();

// Fetch all courses
$courses_stmt = $db->query("SELECT id, title, price FROM courses ORDER BY title ASC");
$courses = $courses_stmt->fetchAll();

// Fetch recent manual enrollments for activity
$history_stmt = $db->query("
    SELECT ce.id, ce.enrolled_at, u.username, c.title, c.price 
    FROM course_enrollments ce
    JOIN users u ON ce.user_id = u.id
    JOIN courses c ON ce.course_id = c.id
    WHERE ce.status = 'approved'
    ORDER BY ce.enrolled_at DESC LIMIT 10
");
$history = $history_stmt->fetchAll();
?>

<?php if(isset($error)): ?>
<div style="background: #FEE2E2; color: #DC2626; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #FCA5A5;">
    <i class="fa-solid fa-circle-exclamation"></i> <?= $error ?>
</div>
<?php endif; ?>

<?php if(isset($success)): ?>
<div style="background: #DCFCE7; color: #16A34A; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #86EFAC;">
    <i class="fa-solid fa-circle-check"></i> <?= $success ?>
</div>
<?php endif; ?>

<div class="dashboard-grid">
    <!-- POS Form -->
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fa-solid fa-cart-arrow-down"></i> New Transaction</div>
        </div>
        
        <form method="POST">
            <input type="hidden" name="action" value="sell">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 8px;">Select Student</label>
                <select name="user_id" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem;">
                    <option value="">-- Choose Student --</option>
                    <?php foreach($users as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['username']) ?> (<?= htmlspecialchars($u['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.9rem; font-weight: 600; margin-bottom: 8px;">Select Course</label>
                <select name="course_id" id="courseSelect" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 0.95rem;">
                    <option value="" data-price="0">-- Choose Course --</option>
                    <?php foreach($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" data-price="<?= $c['price'] ?>"><?= htmlspecialchars($c['title']) ?> (฿<?= number_format($c['price']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="background: #F8FAFC; padding: 20px; border-radius: 8px; border: 1px dashed var(--border); margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem;">
                    <span style="color: var(--text-muted);">Subtotal</span>
                    <strong id="subtotalLabel">฿0</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem;">
                    <span style="color: var(--text-muted);">Tax / Fee</span>
                    <strong>฿0</strong>
                </div>
                <hr style="border: none; border-top: 1px solid var(--border); margin: 15px 0;">
                <div style="display: flex; justify-content: space-between; font-size: 1.2rem; font-weight: 800; color: var(--primary);">
                    <span>Total Amount</span>
                    <span id="totalLabel">฿0</span>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1.1rem; justify-content: center;"><i class="fa-solid fa-check-double"></i> Complete Transaction</button>
        </form>
    </div>
    
    <!-- Recent POS Activity -->
    <div>
        <div class="card">
            <div class="card-header">
                <div class="card-title">Recent Approved Enrollments</div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <?php foreach($history as $h): ?>
                <div style="display: flex; align-items: center; gap: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border);">
                    <div style="width: 40px; height: 40px; background: #DCFCE7; color: #16A34A; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                        <i class="fa-solid fa-basket-shopping"></i>
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 700; font-size: 0.9rem;"><?= htmlspecialchars($h['username']) ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($h['title']) ?></div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 700; color: var(--primary);">฿<?= number_format($h['price']) ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= date('d M, H:i', strtotime($h['enrolled_at'])) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($history)): ?>
                <div style="text-align: center; color: var(--text-muted); font-size: 0.9rem; padding: 20px 0;">No recent activity.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('courseSelect').addEventListener('change', function() {
    var price = this.options[this.selectedIndex].getAttribute('data-price');
    if(price) {
        var formatted = '฿' + parseInt(price).toLocaleString();
        document.getElementById('subtotalLabel').innerText = formatted;
        document.getElementById('totalLabel').innerText = formatted;
    } else {
        document.getElementById('subtotalLabel').innerText = '฿0';
        document.getElementById('totalLabel').innerText = '฿0';
    }
});
</script>
