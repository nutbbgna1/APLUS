<?php
// Fetch admin profile
$stmt = $db->query("SELECT * FROM users WHERE role='admin' LIMIT 1"); // Simplification for now
$admin_profile = $stmt->fetch();
$admin_name = $admin_profile ? $admin_profile['fname'] . ' ' . $admin_profile['lname'] : 'Patricia Peter';
$admin_avatar = $admin_profile['avatar_color'] ?? '#64748B'; // Or actual image

// Fetch unread notifications
$stmt = $db->query("SELECT * FROM admin_notifications ORDER BY created_at DESC LIMIT 5");
$notifications = $stmt->fetchAll();
$stmt = $db->query("SELECT COUNT(*) FROM admin_notifications WHERE is_read=0");
$unread_notifs = $stmt->fetchColumn();

// Fetch unread messages
$stmt = $db->query("SELECT * FROM admin_messages ORDER BY created_at DESC LIMIT 5");
$messages = $stmt->fetchAll();
$stmt = $db->query("SELECT COUNT(*) FROM admin_messages WHERE is_read=0");
$unread_msgs = $stmt->fetchColumn();
?>

<div class="topbar">
    <form class="search-bar" action="" method="GET">
        <input type="hidden" name="page" value="search">
        <i class="fa-solid fa-magnifying-glass" onclick="this.parentNode.submit()" style="cursor:pointer;"></i>
        <input type="text" name="q" placeholder="Search here..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" required>
    </form>
    
    <div class="topbar-actions">
        <!-- Theme Toggle -->
        <div class="action-icon" id="themeToggle" onclick="toggleTheme()" title="Toggle Dark Mode">
            <i class="fa-solid fa-gear"></i>
        </div>
        
        <!-- Language -->
        <div class="action-icon dropdown-trigger" data-target="langDropdown" title="Change Language">
            <i class="fa-solid fa-globe"></i>
            <div class="dropdown-menu" id="langDropdown">
                <div class="dropdown-header">Language</div>
                <a href="?page=set_lang&lang=th" class="dropdown-item">🇹🇭 ภาษาไทย (TH)</a>
                <a href="?page=set_lang&lang=en" class="dropdown-item">🇬🇧 English (EN)</a>
            </div>
        </div>
        
        <!-- Notifications -->
        <div class="action-icon dropdown-trigger" data-target="notifDropdown" title="Notifications">
            <i class="fa-regular fa-bell"></i>
            <?php if($unread_notifs > 0): ?>
            <span class="action-badge" style="background:#84cc16; color:white;"><?= $unread_notifs ?></span>
            <?php endif; ?>
            
            <div class="dropdown-menu" id="notifDropdown" style="width: 300px;">
                <div class="dropdown-header">Notifications (<?= $unread_notifs ?> new)</div>
                <div class="dropdown-content">
                    <?php if(empty($notifications)): ?>
                        <div style="padding:15px; text-align:center; color:var(--text-muted); font-size:0.9rem;">No notifications.</div>
                    <?php else: ?>
                        <?php foreach($notifications as $n): ?>
                        <a href="<?= htmlspecialchars($n['link'] ?? '#') ?>" class="dropdown-item" style="display:flex; flex-direction:column; align-items:flex-start; gap:4px; padding:12px 15px; border-bottom:1px solid var(--border); <?= $n['is_read'] ? 'opacity:0.7;' : 'background:#F8FAFC;' ?>">
                            <div style="font-weight:600; font-size:0.9rem; color:var(--text);"><?= htmlspecialchars($n['title']) ?></div>
                            <div style="font-size:0.8rem; color:var(--text-muted); white-space:normal; line-height:1.4;"><?= htmlspecialchars($n['message']) ?></div>
                            <div style="font-size:0.7rem; color:#94A3B8;"><?= date('M d, H:i', strtotime($n['created_at'])) ?></div>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a href="?page=read_all&type=notifications" class="dropdown-footer">Mark all as read</a>
            </div>
        </div>
        
        <!-- Messages -->
        <div class="action-icon dropdown-trigger" data-target="msgDropdown" title="Messages">
            <i class="fa-regular fa-envelope"></i>
            <?php if($unread_msgs > 0): ?>
            <span class="action-badge" style="background:#3B82F6; color:white;"><?= $unread_msgs ?></span>
            <?php endif; ?>
            
            <div class="dropdown-menu" id="msgDropdown" style="width: 320px;">
                <div class="dropdown-header">Messages (<?= $unread_msgs ?> new)</div>
                <div class="dropdown-content">
                    <?php if(empty($messages)): ?>
                        <div style="padding:15px; text-align:center; color:var(--text-muted); font-size:0.9rem;">No new messages.</div>
                    <?php else: ?>
                        <?php foreach($messages as $m): ?>
                        <a href="#" class="dropdown-item" style="display:flex; flex-direction:column; align-items:flex-start; gap:4px; padding:12px 15px; border-bottom:1px solid var(--border); <?= $m['is_read'] ? 'opacity:0.7;' : 'background:#F8FAFC;' ?>">
                            <div style="font-weight:600; font-size:0.9rem; color:var(--text);"><?= htmlspecialchars($m['sender_name']) ?></div>
                            <div style="font-size:0.8rem; color:var(--text-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; width:100%;"><?= htmlspecialchars($m['message']) ?></div>
                            <div style="font-size:0.7rem; color:#94A3B8;"><?= date('M d, H:i', strtotime($m['created_at'])) ?></div>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a href="?page=read_all&type=messages" class="dropdown-footer">Mark all as read</a>
            </div>
        </div>
        
        <!-- Profile -->
        <div class="profile dropdown-trigger" data-target="profileDropdown" style="cursor:pointer;">
            <div class="profile-info">
                <span class="profile-name"><?= htmlspecialchars($admin_name) ?></span>
                <span class="profile-role">Super Admin</span>
            </div>
            <div class="profile-img" style="background:<?= $admin_avatar ?>;"></div>
            <i class="fa-solid fa-chevron-down" style="font-size:0.8rem; color:var(--text-muted);"></i>
            
            <div class="dropdown-menu" id="profileDropdown" style="width: 200px;">
                <div class="dropdown-header" style="text-align:center; padding:15px;">
                    <div class="profile-img" style="background:<?= $admin_avatar ?>; width:50px; height:50px; margin:0 auto 10px auto;"></div>
                    <div style="font-weight:700; color:var(--text);"><?= htmlspecialchars($admin_name) ?></div>
                    <div style="font-size:0.8rem; color:var(--text-muted);">administrator@lingua.max</div>
                </div>
                <a href="#" class="dropdown-item"><i class="fa-regular fa-user" style="width:20px; text-align:center; margin-right:8px;"></i> My Profile</a>
                <a href="#" class="dropdown-item"><i class="fa-solid fa-gear" style="width:20px; text-align:center; margin-right:8px;"></i> Account Settings</a>
                <div style="height:1px; background:var(--border); margin:5px 0;"></div>
                <a href="?page=logout" class="dropdown-item" style="color:#EF4444;"><i class="fa-solid fa-arrow-right-from-bracket" style="width:20px; text-align:center; margin-right:8px;"></i> Logout</a>
            </div>
        </div>
    </div>
</div>
