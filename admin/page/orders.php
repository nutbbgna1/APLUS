<?php
$action = $_GET['action'] ?? 'list';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $new_status = $_POST['status']; // 'approved' or 'rejected'
    
    try {
        $db->beginTransaction();
        
        // Get order info
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND status = 'pending' FOR UPDATE");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();
        
        if ($order) {
            // Update order status
            $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);
            
            if ($new_status === 'approved') {
                // 1. Give student access to course
                // Check if enrollment exists
                $stmt = $db->prepare("SELECT id FROM course_enrollments WHERE user_id = ? AND course_id = ?");
                $stmt->execute([$order['user_id'], $order['course_id']]);
                if (!$stmt->fetch()) {
                    $stmt = $db->prepare("INSERT INTO course_enrollments (user_id, course_id, status, payment_slip_url, approved_at) VALUES (?, ?, 'approved', ?, NOW())");
                    $stmt->execute([$order['user_id'], $order['course_id'], $order['slip_image']]);
                } else {
                    $stmt = $db->prepare("UPDATE course_enrollments SET status = 'approved', approved_at = NOW() WHERE user_id = ? AND course_id = ?");
                    $stmt->execute([$order['user_id'], $order['course_id']]);
                }
                
                // 2. Add to accounting logs
                $stmt = $db->prepare("SELECT title FROM courses WHERE id = ?");
                $stmt->execute([$order['course_id']]);
                $course_title = $stmt->fetchColumn() ?: 'Unknown Course';
                
                $log_title = "รายรับจากคอร์ส: " . $course_title . " (Order #" . $order_id . ")";
                $stmt = $db->prepare("INSERT INTO accounting_logs (title, type, amount, ref_order_id) VALUES (?, 'income', ?, ?)");
                $stmt->execute([$log_title, $order['price'], $order_id]);
            }
        }
        
        $db->commit();
        echo "<script>window.location.href='?page=orders';</script>";
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        $errorMsg = "Error processing order: " . $e->getMessage();
    }
}

// Fetch Orders
$stmt = $db->query("
    SELECT o.*, u.fname, u.lname, u.username, c.title as course_title 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN courses c ON o.course_id = c.id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Orders & Approvals</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Manage student course purchases and payment slips.</p>
    </div>
</div>

<?php if ($errorMsg): ?>
<div style="background: #FEE2E2; color: #DC2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
    <?= htmlspecialchars($errorMsg) ?>
</div>
<?php endif; ?>

<div class="card">
    <table style="width: 100%; text-align: left; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border);">
                <th style="padding: 12px; color: var(--text-muted);">Order ID</th>
                <th style="padding: 12px; color: var(--text-muted);">Student</th>
                <th style="padding: 12px; color: var(--text-muted);">Course</th>
                <th style="padding: 12px; color: var(--text-muted);">Amount</th>
                <th style="padding: 12px; color: var(--text-muted);">Date</th>
                <th style="padding: 12px; color: var(--text-muted);">Slip</th>
                <th style="padding: 12px; color: var(--text-muted);">Status</th>
                <th style="padding: 12px; color: var(--text-muted);">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($orders as $o): ?>
            <tr style="border-bottom: 1px solid var(--border);">
                <td style="padding: 12px; font-weight: 600;">#<?= $o['id'] ?></td>
                <td style="padding: 12px;">
                    <div><?= htmlspecialchars($o['fname'] . ' ' . $o['lname']) ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($o['username'] ?? '') ?></div>
                </td>
                <td style="padding: 12px;"><?= htmlspecialchars($o['course_title']) ?></td>
                <td style="padding: 12px; font-weight: 700; color: #10B981;">฿<?= number_format($o['price'], 2) ?></td>
                <td style="padding: 12px; font-size: 0.85rem; color: var(--text-muted);"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></td>
                <td style="padding: 12px;">
                    <a href="../<?= htmlspecialchars($o['slip_image']) ?>" target="_blank" style="color: var(--primary); text-decoration: none; font-size: 0.85rem; font-weight: 600;">
                        <i class="fa-solid fa-image"></i> View Slip
                    </a>
                </td>
                <td style="padding: 12px;">
                    <?php if($o['status'] === 'pending'): ?>
                        <span style="background:#FEF9C3;color:#CA8A04;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;">Pending</span>
                    <?php elseif($o['status'] === 'approved'): ?>
                        <span style="background:#DCFCE7;color:#16A34A;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;">Approved</span>
                    <?php else: ?>
                        <span style="background:#FEE2E2;color:#DC2626;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;">Rejected</span>
                    <?php endif; ?>
                </td>
                <td style="padding: 12px;">
                    <?php if($o['status'] === 'pending'): ?>
                    <form method="POST" style="display: flex; gap: 5px;">
                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                        <button type="submit" name="status" value="approved" class="btn btn-sm btn-success" style="padding: 6px 10px;" onclick="return confirm('Approve order #<?= $o['id'] ?>?');">Approve</button>
                        <button type="submit" name="status" value="rejected" class="btn btn-sm" style="background:#EF4444; color:white; padding: 6px 10px;" onclick="return confirm('Reject order #<?= $o['id'] ?>?');">Reject</button>
                    </form>
                    <?php else: ?>
                        <span style="color: var(--text-muted); font-size: 0.85rem;">Completed</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($orders)): ?>
            <tr><td colspan="8" style="text-align: center; padding: 20px;">No orders found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
