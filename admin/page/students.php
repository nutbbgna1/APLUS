<div class="page-header">
    <div>
        <h1 class="page-title">Students Management</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Manage all registered students</p>
    </div>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'];
        // normally we should soft-delete or check constraints, but we'll delete here
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        echo "<script>window.location.href='?page=students';</script>";
        exit;
    }
}

$stmt = $db->query("SELECT * FROM users ORDER BY id DESC LIMIT 100");
$students = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <div class="card-title">All Students</div>
        <div class="search-bar" style="width: 250px; background: white; border: 1px solid var(--border);">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Search student...">
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name / Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Registered Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($students as $u): ?>
            <tr>
                <td>#<?= $u['id'] ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:36px;height:36px;background:var(--primary-light);color:var(--primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;">
                            <?= strtoupper(substr($u['username'], 0, 1)) ?>
                        </div> 
                        <strong><?= htmlspecialchars($u['username']) ?></strong>
                    </div>
                </td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span style="background: #F1F5F9; color: #475569; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;"><?= ucfirst($u['role']) ?></span></td>
                <td><span style="background:#DCFCE7;color:#16A34A;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;">Active</span></td>
                <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                <td>
                    <button class="btn btn-sm btn-outline" style="margin-right: 5px;"><i class="fa-solid fa-pen"></i> Edit</button>
                    <form method="POST" onsubmit="return confirm('Delete this student?');" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $u['id'] ?>">
                        <button type="submit" class="btn btn-sm" style="background:#EF4444; color:white;"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($students)): ?>
            <tr><td colspan="7" style="text-align: center;">No students found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
