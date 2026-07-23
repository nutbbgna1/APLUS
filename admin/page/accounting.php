<?php
if (!isset($_SESSION['admin_logged_in'])) {
    die("Unauthorized Access");
}

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_log') {
    $log_id = intval($_POST['log_id']);
    if ($log_id > 0) {
        $stmt = $db->prepare("DELETE FROM accounting_logs WHERE id = ?");
        $stmt->execute([$log_id]);
        echo "<script>window.location.href='?page=accounting';</script>";
        exit;
    }
}

// Calculate Totals
$stmt = $db->query("SELECT SUM(amount) as total_income FROM accounting_logs WHERE type = 'income'");
$total_income = $stmt->fetchColumn() ?: 0;

$stmt = $db->query("SELECT SUM(amount) as total_expense FROM accounting_logs WHERE type = 'expense'");
$total_expense = $stmt->fetchColumn() ?: 0;

$net_balance = $total_income - $total_expense;

// Fetch Logs
$stmt = $db->query("SELECT * FROM accounting_logs ORDER BY created_at DESC LIMIT 100");
$logs = $stmt->fetchAll();
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Central Accounting</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Overview of revenue from course sales and expenses.</p>
    </div>
</div>

<div class="kpi-row">
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 5px;">Total Revenue (Income)</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #10B981;">฿<?= number_format($total_income, 2) ?></div>
            </div>
            <div style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #DCFCE7; color: #16A34A; font-size: 1.2rem;">
                <i class="fa-solid fa-arrow-down"></i>
            </div>
        </div>
    </div>
    
    <div class="card" style="margin-bottom: 0;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 5px;">Total Expenses</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #EF4444;">฿<?= number_format($total_expense, 2) ?></div>
            </div>
            <div style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #FEE2E2; color: #DC2626; font-size: 1.2rem;">
                <i class="fa-solid fa-arrow-up"></i>
            </div>
        </div>
    </div>
    
    <div class="card" style="margin-bottom: 0; background: linear-gradient(135deg, var(--primary) 0%, #312E81 100%); color: white;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <div style="font-size: 0.85rem; margin-bottom: 5px; opacity: 0.8;">Net Balance</div>
                <div style="font-size: 1.5rem; font-weight: 800; color: #D9F99D;">฿<?= number_format($net_balance, 2) ?></div>
            </div>
            <div style="width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.2); font-size: 1.2rem;">
                <i class="fa-solid fa-wallet"></i>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Transaction History</h3>
        <!-- You could add expense addition button here in the future -->
    </div>
    
    <table style="width: 100%; text-align: left; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid var(--border);">
                <th style="padding: 12px; color: var(--text-muted);">Date</th>
                <th style="padding: 12px; color: var(--text-muted);">Description</th>
                <th style="padding: 12px; color: var(--text-muted);">Ref Order</th>
                <th style="padding: 12px; color: var(--text-muted); text-align: right;">Amount</th>
                <th style="padding: 12px; color: var(--text-muted);">Type</th>
                <th style="padding: 12px; color: var(--text-muted); text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($logs as $log): ?>
            <tr style="border-bottom: 1px solid var(--border);">
                <td style="padding: 12px; font-size: 0.85rem; color: var(--text-muted);"><?= date('d M Y, H:i', strtotime($log['created_at'])) ?></td>
                <td style="padding: 12px; font-weight: 500;"><?= htmlspecialchars($log['title']) ?></td>
                <td style="padding: 12px; color: var(--text-muted);">
                    <?= $log['ref_order_id'] ? '#' . $log['ref_order_id'] : '-' ?>
                </td>
                <td style="padding: 12px; text-align: right; font-weight: 700; color: <?= $log['type'] === 'income' ? '#10B981' : '#EF4444' ?>;">
                    <?= $log['type'] === 'income' ? '+' : '-' ?>฿<?= number_format($log['amount'], 2) ?>
                </td>
                <td style="padding: 12px;">
                    <?php if($log['type'] === 'income'): ?>
                        <span style="background:#DCFCE7;color:#16A34A;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;">Income</span>
                    <?php else: ?>
                        <span style="background:#FEE2E2;color:#DC2626;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;">Expense</span>
                    <?php endif; ?>
                </td>
                <td style="padding: 12px; text-align: right;">
                    <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this transaction? It cannot be undone.');">
                        <input type="hidden" name="action" value="delete_log">
                        <input type="hidden" name="log_id" value="<?= $log['id'] ?>">
                        <button type="submit" class="btn btn-sm" style="background: #FEE2E2; color: #DC2626; border: none; padding: 6px 10px; border-radius: 6px; cursor: pointer; transition: 0.2s;">
                            <i class="fa-solid fa-trash-can"></i> ลบ
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($logs)): ?>
            <tr><td colspan="6" style="text-align: center; padding: 20px;">No transactions recorded yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
