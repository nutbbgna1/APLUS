<?php
// ============================================================
// LinguaMax — Help & Support Page
// ============================================================
include __DIR__ . '/../../includes/header.php';
?>

<div class="animate-fade-in" style="padding-bottom: 80px;">
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #EF4444 0%, #F87171 100%); margin: -20px -20px 20px -20px; padding: 40px 20px 30px; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; color: white; text-align: center; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2); position: relative;">
        <a href="?page=profile" style="position: absolute; left: 20px; top: 20px; color: white; text-decoration: none; font-size: 1.2rem; background: rgba(255,255,255,0.2); width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1 style="color: white; font-weight: 900; margin-bottom: 8px; font-size: 1.8rem;">Help & Support</h1>
        <p style="opacity: 0.9; font-size: 0.95rem;">How can we help you today?</p>
    </div>

    <!-- FAQ Section -->
    <div style="margin-bottom: 32px; padding: 0 4px;">
        <h2 style="font-size: 1.2rem; font-weight: 900; color: #1E293B; margin-bottom: 16px;">Frequently Asked Questions</h2>
        
        <div class="flex-col gap-12">
            <div style="background: white; border: 1px solid #F1F5F9; border-radius: 16px; padding: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                <div style="font-weight: 800; color: #1E293B; margin-bottom: 8px;">How do I earn XP?</div>
                <div style="font-size: 0.85rem; color: #64748B; line-height: 1.5;">You earn XP by completing lessons, passing exams, playing games, and reading articles. The harder the task, the more XP you get!</div>
            </div>
            
            <div style="background: white; border: 1px solid #F1F5F9; border-radius: 16px; padding: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                <div style="font-weight: 800; color: #1E293B; margin-bottom: 8px;">What is a Streak?</div>
                <div style="font-size: 0.85rem; color: #64748B; line-height: 1.5;">A streak tracks how many consecutive days you have learned on the app. Practice every day to keep your fire alive!</div>
            </div>
            
            <div style="background: white; border: 1px solid #F1F5F9; border-radius: 16px; padding: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                <div style="font-weight: 800; color: #1E293B; margin-bottom: 8px;">I can't access an Exam!</div>
                <div style="font-size: 0.85rem; color: #64748B; line-height: 1.5;">Some exams require permission from your teacher or administrator to unlock. Contact them to request access.</div>
            </div>
        </div>
    </div>

    <!-- Contact Form -->
    <div style="background: white; border-radius: 24px; padding: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); margin: 0 4px;">
        <h2 style="font-size: 1.2rem; font-weight: 900; color: #1E293B; margin-bottom: 16px; text-align: center;">Still need help?</h2>
        <form id="supportForm" onsubmit="submitSupport(event)">
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 8px;">Subject</label>
                <select name="subject" style="width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid #E2E8F0; font-size: 1rem; outline: none; background: white;" required>
                    <option value="">Select a topic...</option>
                    <option value="bug">Report a Bug</option>
                    <option value="content">Content Error</option>
                    <option value="account">Account Issue</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div style="margin-bottom: 24px;">
                <label style="display: block; font-weight: 700; color: #1E293B; margin-bottom: 8px;">Message</label>
                <textarea name="message" rows="4" style="width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid #E2E8F0; font-size: 1rem; outline: none; resize: none;" required placeholder="Describe your issue..."></textarea>
            </div>
            <button type="submit" style="width: 100%; background: #EF4444; color: white; padding: 16px; border-radius: 30px; border: none; font-weight: 800; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3); cursor: pointer; transition: transform 0.2s;">Send Message</button>
        </form>
    </div>
</div>

<script>
function submitSupport(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Sending...';
    
    // Simulate API call for support
    setTimeout(() => {
        alert('Thank you for contacting us! We will get back to you soon.');
        e.target.reset();
        btn.disabled = false;
        btn.textContent = 'Send Message';
        window.scrollTo(0,0);
    }, 800);
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
