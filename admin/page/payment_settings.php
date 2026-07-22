<?php
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$errorMsg = '';
$uploadDir = __DIR__ . '/../uploads/payments/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['delete'])) {
            $del_id = (int)$_POST['id'];
            
            // Delete image if exists
            $stmt = $db->prepare("SELECT qr_code_image FROM payment_methods WHERE id = ?");
            $stmt->execute([$del_id]);
            $oldImg = $stmt->fetchColumn();
            if ($oldImg && file_exists(__DIR__ . '/../../' . $oldImg)) {
                unlink(__DIR__ . '/../../' . $oldImg);
            }
            
            $stmt = $db->prepare("DELETE FROM payment_methods WHERE id = ?");
            $stmt->execute([$del_id]);
            echo "<script>window.location.href='?page=payment_settings';</script>";
            exit;
        } else {
            $bank_name = trim($_POST['bank_name'] ?? '');
            $account_name = trim($_POST['account_name'] ?? '');
            $account_number = trim($_POST['account_number'] ?? '');
            $is_active = isset($_POST['is_active']) ? 1 : 0;
            
            // Handle QR Image upload
            $qr_code_image = $_POST['existing_image'] ?? '';
            if (isset($_FILES['qr_code']) && $_FILES['qr_code']['error'] === UPLOAD_ERR_OK) {
                $ext = pathinfo($_FILES['qr_code']['name'], PATHINFO_EXTENSION);
                $fileName = 'qr_' . time() . '_' . rand(100, 999) . '.' . $ext;
                
                if (move_uploaded_file($_FILES['qr_code']['tmp_name'], $uploadDir . $fileName)) {
                    $qr_code_image = 'admin/uploads/payments/' . $fileName;
                    
                    // delete old if editing
                    if ($action === 'edit' && !empty($_POST['existing_image'])) {
                        $oldFile = __DIR__ . '/../../' . $_POST['existing_image'];
                        if (file_exists($oldFile)) unlink($oldFile);
                    }
                }
            }
            
            if ($action === 'edit' && $id) {
                $stmt = $db->prepare("UPDATE payment_methods SET bank_name=?, account_name=?, account_number=?, qr_code_image=?, is_active=? WHERE id=?");
                $stmt->execute([$bank_name, $account_name, $account_number, $qr_code_image, $is_active, $id]);
            } else {
                $stmt = $db->prepare("INSERT INTO payment_methods (bank_name, account_name, account_number, qr_code_image, is_active) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$bank_name, $account_name, $account_number, $qr_code_image, $is_active]);
            }
            echo "<script>window.location.href='?page=payment_settings';</script>";
            exit;
        }
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    }
}

if ($action === 'edit' || $action === 'add'):
    $item = null;
    if ($action === 'edit' && $id) {
        $stmt = $db->prepare("SELECT * FROM payment_methods WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
    }
?>
    <div class="page-header">
        <div>
            <h1 class="page-title"><?= $item ? 'Edit Payment Method' : 'Add New Payment Method' ?></h1>
        </div>
        <a href="?page=payment_settings" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back</a>
    </div>

    <?php if ($errorMsg): ?>
    <div style="background: #FEE2E2; color: #DC2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
        Error: <?= htmlspecialchars($errorMsg) ?>
    </div>
    <?php endif; ?>

    <div class="card" style="max-width: 600px;">
        <form method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px;">
            <div>
                <label style="display:block; font-weight: 600; margin-bottom: 8px;">Bank Name</label>
                <select name="bank_name" required style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
                    <?php 
                    $banks = ['Kasikorn Bank (KBank)', 'Siam Commercial Bank (SCB)', 'Bangkok Bank (BBL)', 'Krungthai Bank (KTB)', 'PromptPay'];
                    foreach($banks as $b):
                        $sel = ($item['bank_name'] ?? '') === $b ? 'selected' : '';
                    ?>
                    <option value="<?= $b ?>" <?= $sel ?>><?= $b ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label style="display:block; font-weight: 600; margin-bottom: 8px;">Account Name</label>
                <input type="text" name="account_name" required value="<?= htmlspecialchars($item['account_name'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;" placeholder="John Doe">
            </div>

            <div>
                <label style="display:block; font-weight: 600; margin-bottom: 8px;">Account Number (or PromptPay)</label>
                <input type="text" name="account_number" required value="<?= htmlspecialchars($item['account_number'] ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;" placeholder="123-4-56789-0">
            </div>
            
            <div>
                <label style="display:block; font-weight: 600; margin-bottom: 8px;">QR Code Image (Optional)</label>
                <?php if(!empty($item['qr_code_image'])): ?>
                    <img src="../<?= $item['qr_code_image'] ?>" alt="QR" style="height: 150px; border-radius: 8px; margin-bottom: 10px; display: block; border: 1px solid var(--border);">
                    <input type="hidden" name="existing_image" value="<?= htmlspecialchars($item['qr_code_image']) ?>">
                <?php endif; ?>
                <input type="file" name="qr_code" accept="image/*" style="width: 100%; padding: 10px; border: 1px dashed var(--border); border-radius: 8px; background: #f8fafc;">
            </div>
            
            <div>
                <label style="display: flex; align-items: center; gap: 10px; font-weight: 600; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" <?= ($item['is_active'] ?? 1) ? 'checked' : '' ?> style="width: 18px; height: 18px;">
                    Active (Show to students)
                </label>
            </div>
            
            <div style="margin-top: 10px; border-top: 1px solid var(--border); padding-top: 20px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Payment Method</button>
            </div>
        </form>
    </div>

<?php else: 
    $stmt = $db->query("SELECT * FROM payment_methods ORDER BY id DESC");
    $items = $stmt->fetchAll();
?>
    <div class="page-header">
        <div>
            <h1 class="page-title">Payment Settings</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Manage Bank Accounts and QR Codes for course purchases</p>
        </div>
        <a href="?page=payment_settings&action=add" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Account</a>
    </div>

    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Bank</th>
                    <th>Account Info</th>
                    <th>QR Code</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($items as $item): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($item['bank_name']) ?></strong></td>
                    <td>
                        <div style="font-weight: 600;"><?= htmlspecialchars($item['account_number']) ?></div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($item['account_name']) ?></div>
                    </td>
                    <td>
                        <?php if($item['qr_code_image']): ?>
                            <img src="../<?= $item['qr_code_image'] ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border);">
                        <?php else: ?>
                            <span style="color: var(--text-muted); font-size: 0.8rem;">No QR</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if($item['is_active']): ?>
                            <span style="background:#DCFCE7;color:#16A34A;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;">Active</span>
                        <?php else: ?>
                            <span style="background:#F1F5F9;color:#475569;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?page=payment_settings&action=edit&id=<?= $item['id'] ?>" class="btn btn-sm btn-outline" style="margin-right: 5px;"><i class="fa-solid fa-pen"></i></a>
                        <form method="POST" onsubmit="return confirm('Delete this account?');" style="display:inline;">
                            <input type="hidden" name="delete" value="1">
                            <input type="hidden" name="id" value="<?= $item['id'] ?>">
                            <button type="submit" class="btn btn-sm" style="background:#EF4444; color:white; border:none; padding:6px 12px; cursor:pointer; border-radius:8px;"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($items)): ?>
                <tr><td colspan="5" style="text-align: center; padding: 20px;">No payment methods configured.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
