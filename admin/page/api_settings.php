<?php
if (!isset($_SESSION['admin_logged_in'])) {
    die("Unauthorized Access");
}

$errorMsg = '';
$successMsg = '';

// Auto-migrate: create system_settings table if it doesn't exist
try {
    $db->exec("CREATE TABLE IF NOT EXISTS system_settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT
    )");
} catch (Exception $e) {
    $errorMsg = "Database error: " . $e->getMessage();
}

// Default settings
$settings = [
    'openrouter_api_key' => ''
];

// Fetch from DB
try {
    $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings");
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (Exception $e) {
    // Ignore if table doesn't exist yet
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apiKey = trim($_POST['openrouter_api_key'] ?? '');
    
    try {
        $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('openrouter_api_key', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$apiKey, $apiKey]);
        
        $settings['openrouter_api_key'] = $apiKey;
        $successMsg = 'Settings saved successfully.';
    } catch (Exception $e) {
        $errorMsg = 'Failed to save settings to database: ' . $e->getMessage();
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">AI API Settings</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">Configure your API keys for AI generated content</p>
    </div>
</div>

<?php if ($errorMsg): ?>
<div style="background: #FEE2E2; color: #DC2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
    <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($errorMsg) ?>
</div>
<?php endif; ?>

<?php if ($successMsg): ?>
<div style="background: #DCFCE7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($successMsg) ?>
</div>
<?php endif; ?>

<div class="card" style="max-width: 600px;">
    <form method="POST">
        <div style="margin-bottom: 20px;">
            <label style="display:block; font-weight: 600; margin-bottom: 8px; color: var(--text-main);">
                <i class="fa-solid fa-key" style="color: #8B5CF6;"></i> OpenRouter API Key
            </label>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 10px;">
                Required to generate exam questions using AI. Get your key from <a href="https://openrouter.ai/keys" target="_blank" style="color: var(--primary);">openrouter.ai</a>.
            </p>
            <input type="password" name="openrouter_api_key" value="<?= htmlspecialchars($settings['openrouter_api_key']) ?>" placeholder="sk-or-v1-..." style="width: 100%; padding: 12px; border: 1px solid var(--border); border-radius: 8px;">
            
            <?php if(!empty($settings['openrouter_api_key'])): ?>
                <div style="margin-top: 10px; font-size: 0.85rem; color: #16A34A; font-weight: 600;">
                    <i class="fa-solid fa-check"></i> API Key is configured
                </div>
            <?php else: ?>
                <div style="margin-top: 10px; font-size: 0.85rem; color: #EF4444; font-weight: 600;">
                    <i class="fa-solid fa-xmark"></i> API Key is not set
                </div>
            <?php endif; ?>
        </div>

        <div style="border-top: 1px solid var(--border); padding-top: 20px; margin-top: 10px;">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Save Settings</button>
        </div>
    </form>
</div>
