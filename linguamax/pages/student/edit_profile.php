<?php
// ============================================================
// LinguaMax — Edit Profile Page
// ============================================================
include __DIR__ . '/../../includes/header.php';
$user = $currentUser;
?>

<div class="animate-fade-in" style="padding-bottom: 80px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #8CB3FF 0%, #C084FC 100%); margin: -20px -20px 20px -20px; padding: 40px 20px 30px; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; color: white; text-align: center; box-shadow: 0 4px 15px rgba(74, 140, 255, 0.2); position: relative;">
        <a href="?page=profile" style="position: absolute; left: 20px; top: 20px; color: white; text-decoration: none; font-size: 1.2rem; background: rgba(255,255,255,0.2); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1 style="color: white; font-weight: 900; margin-bottom: 8px; font-size: 1.8rem;">Edit Profile</h1>
        <p style="opacity: 0.9; font-size: 0.95rem;">Update your personal information</p>
    </div>

    <!-- Form -->
    <div style="background: white; border-radius: 24px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin-top: -10px;">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: #E2E8F0; display: inline-flex; align-items: center; justify-content: center; font-size: 2.2rem; font-weight: 900; color: white; margin-bottom: 12px;">
                <?= mb_substr($user['fname'], 0, 1) ?>
            </div>
            <div style="color: #4A8CFF; font-weight: 700; font-size: 0.9rem;">Change Avatar</div>
        </div>

        <form id="profileForm" onsubmit="saveProfile(event)">
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 8px;">First Name</label>
                <input type="text" name="fname" value="<?= sanitize($user['fname']) ?>" required style="width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid #E2E8F0; font-size: 1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#8CB3FF'" onblur="this.style.borderColor='#E2E8F0'">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 8px;">Last Name</label>
                <input type="text" name="lname" value="<?= sanitize($user['lname']) ?>" required style="width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid #E2E8F0; font-size: 1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#8CB3FF'" onblur="this.style.borderColor='#E2E8F0'">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 8px;">Nickname</label>
                <input type="text" name="nickname" value="<?= sanitize($user['nickname']) ?>" required style="width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid #E2E8F0; font-size: 1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#8CB3FF'" onblur="this.style.borderColor='#E2E8F0'">
            </div>
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 8px;">New Password (Optional)</label>
                <input type="password" name="password" placeholder="Leave blank to keep current" style="width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid #E2E8F0; font-size: 1rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#8CB3FF'" onblur="this.style.borderColor='#E2E8F0'">
            </div>

            <button type="submit" style="width: 100%; background: #8CB3FF; color: white; padding: 16px; border-radius: 30px; border: none; font-weight: 800; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(74, 140, 255, 0.3); cursor: pointer; transition: transform 0.2s;">Save Changes</button>
        </form>
    </div>
</div>

<script>
async function saveProfile(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Saving...';

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    data.action = 'update_profile';

    try {
        const res = await fetch('<?= SITE_URL ?>/api/user.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        const result = await res.json();
        if (result.success) {
            alert('Profile updated successfully!');
            window.location.href = '?page=profile';
        } else {
            alert(result.error || 'Failed to update profile');
        }
    } catch(err) {
        alert('Connection error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Save Changes';
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
