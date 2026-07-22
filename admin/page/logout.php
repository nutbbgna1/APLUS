<?php
session_start();
session_destroy();
?>
<div class="card" style="text-align: center; padding: 50px; max-width: 500px; margin: 50px auto;">
    <i class="fa-solid fa-circle-check" style="font-size: 4rem; color: #10B981; margin-bottom: 20px;"></i>
    <h2 style="font-weight: 700; margin-bottom: 15px;">Logged Out Successfully</h2>
    <p style="color: var(--text-muted); margin-bottom: 24px;">You have been securely logged out of the admin panel.</p>
    <a href="../login.php" class="btn btn-primary">Return to Login</a>
</div>
<script>
    // In a real system, you might redirect immediately
    setTimeout(() => {
        window.location.href = '../login.php';
    }, 3000);
</script>
