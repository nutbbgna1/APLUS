<?php
include __DIR__ . '/../../includes/header.php';
$user = $currentUser;
$stats = getUserStats($user['id']);
$streak = getStreak($user['id']);
$db = getDB();

$userId = $_SESSION['user_id'];
$leaders = getLeaderboard(20);

// Find current user rank
$myRank = 0;
foreach ($leaders as $i => $l) { if ($l['id'] == $userId) { $myRank = $i + 1; break; } }

// Derived variables for display (Mocked for Level as per design)
$currentXp = $user['xp'] ?? 1250;
$maxXp = 2000;
$progressPercent = min(100, max(0, ($currentXp / $maxXp) * 100));
?>

<div class="animate-fade-in" style="padding: 16px 4px 40px 4px;">
    
    <!-- Top Header -->
    <div class="flex justify-between items-center" style="margin-bottom: 24px;">
        <div>
            <h1 style="font-size: 1.8rem; font-weight: 900; color: #1E293B; margin: 0; line-height: 1.2;">Profile</h1>
            <div style="color: #94A3B8; font-size: 0.85rem; font-weight: 600; margin-top: 2px;">Keep learning and be the best!</div>
        </div>
        <div class="flex items-center gap-8">
            <!-- Streak Pill -->
            <div style="background: white; padding: 8px 12px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); font-weight: 800; display: flex; align-items: center; gap: 6px; font-size: 0.95rem; color: #1E293B; border: 1px solid #F1F5F9;">
                <span style="font-size: 1.15rem;">🔥</span>
                <span><?= $streak['current_streak'] ?? 0 ?></span>
            </div>
            <!-- Gems/Coins Pill -->
            <div style="background: white; padding: 8px 12px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.04); font-weight: 800; display: flex; align-items: center; gap: 6px; font-size: 0.95rem; color: #1E293B; border: 1px solid #F1F5F9;">
                <span style="font-size: 1.15rem;">💎</span>
                <span><?= number_format($currentUser['coins'] ?? 250) ?></span>
            </div>
        </div>
    </div>

    <!-- User Info Card -->
    <div style="background: white; border: 1px solid #F1F5F9; border-radius: 24px; padding: 20px; margin-bottom: 24px; box-shadow: 0 4px 15px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 20px;">
        <!-- Avatar -->
        <div style="width: 76px; height: 76px; border-radius: 50%; background: #E2E8F0; display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 900; color: white; flex-shrink: 0; overflow: hidden;">
            <?php if (!empty($user['avatar'])): ?>
                <img src="<?= SITE_URL ?>/assets/img/<?= $user['avatar'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
            <?php else: ?>
                <?= mb_substr($user['fname'], 0, 1) ?>
            <?php endif; ?>
        </div>
        
        <!-- Info -->
        <div style="flex: 1;">
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                <h2 style="font-size: 1.4rem; font-weight: 900; color: #1E293B; margin: 0;"><?= sanitize($user['nickname'] ?? $user['fname']) ?></h2>
                <div style="background: #F3E8FF; color: #8CB3FF; padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 800;">Level 12</div>
            </div>
            <div style="color: #8CB3FF; font-size: 0.85rem; font-weight: 700; margin-bottom: 12px;">Rising Learner</div>
            
            <!-- Progress Bar -->
            <div class="flex items-center gap-12">
                <div style="background: #F1F5F9; height: 8px; border-radius: 8px; flex: 1; overflow: hidden;">
                    <div style="background: #4A8CFF; width: <?= $progressPercent ?>%; height: 100%; border-radius: 8px;"></div>
                </div>
                <div style="font-size: 0.75rem; font-weight: 700;">
                    <span style="color: #4A8CFF;"><?= number_format($currentXp) ?></span>
                    <span style="color: #94A3B8;"> / <?= number_format($maxXp) ?> XP</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 32px;">
        <!-- Day Streak -->
        <div style="background: white; border: 1px solid #F1F5F9; border-radius: 20px; padding: 16px 8px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="font-size: 1.8rem; margin-bottom: 8px;">🔥</div>
            <div style="font-size: 1.1rem; font-weight: 900; color: #1E293B; margin-bottom: 2px;"><?= $streak['current_streak'] ?? 0 ?></div>
            <div style="font-size: 0.7rem; font-weight: 700; color: #94A3B8; line-height: 1.2;">Day<br>Streak</div>
        </div>
        
        <!-- Lesson Completed -->
        <div style="background: white; border: 1px solid #F1F5F9; border-radius: 20px; padding: 16px 8px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="font-size: 1.8rem; margin-bottom: 8px;">🏆</div>
            <div style="font-size: 1.1rem; font-weight: 900; color: #1E293B; margin-bottom: 2px;"><?= $stats['lessons_completed'] ?? 0 ?></div>
            <div style="font-size: 0.7rem; font-weight: 700; color: #94A3B8; line-height: 1.2;">Lesson<br>Completed</div>
        </div>
        
        <!-- Avg. Score -->
        <div style="background: white; border: 1px solid #F1F5F9; border-radius: 20px; padding: 16px 8px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="font-size: 1.8rem; margin-bottom: 8px;">🎯</div>
            <div style="font-size: 1.1rem; font-weight: 900; color: #1E293B; margin-bottom: 2px;"><?= $stats['avg_score'] ?? 0 ?>%</div>
            <div style="font-size: 0.7rem; font-weight: 700; color: #94A3B8; line-height: 1.2;">Avg.<br>Score</div>
        </div>
        
        <!-- Total XP (Diamonds icon in mockup) -->
        <div style="background: white; border: 1px solid #F1F5F9; border-radius: 20px; padding: 16px 8px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
            <div style="font-size: 1.8rem; margin-bottom: 8px; color: #38BDF8;">💎</div>
            <div style="font-size: 1.1rem; font-weight: 900; color: #1E293B; margin-bottom: 2px;"><?= number_format($currentUser['coins'] ?? 250) ?></div>
            <div style="font-size: 0.7rem; font-weight: 700; color: #94A3B8; line-height: 1.2;">Total<br>XP</div>
        </div>
    </div>

    <!-- My Course Orders -->
    <?php
    $stmt = $db->prepare("SELECT e.*, c.title as course_title, c.image_url FROM course_enrollments e JOIN courses c ON e.course_id = c.id WHERE e.user_id = ? ORDER BY e.requested_at DESC");
    $stmt->execute([$userId]);
    $my_enrollments = $stmt->fetchAll();
    ?>
    <div style="margin-bottom: 32px;">
        <h2 style="font-size: 1.4rem; font-weight: 900; color: #1E293B; margin: 0 0 16px 0;">My Courses & Status</h2>
        
        <?php if (empty($my_enrollments)): ?>
            <div style="background: white; border-radius: 20px; padding: 20px; text-align: center; color: #94A3B8; border: 1px dashed #E2E8F0;">
                You haven't enrolled in or purchased any courses yet.
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 12px; max-height: 350px; overflow-y: auto; padding-right: 6px;" class="custom-scrollbar">
                <?php foreach($my_enrollments as $enr): ?>
                <div style="background: white; border: 1px solid #F1F5F9; border-radius: 20px; padding: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 15px rgba(0,0,0,0.02); flex-shrink: 0;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 60px; height: 60px; border-radius: 12px; background: #F1F5F9; overflow: hidden; flex-shrink: 0;">
                            <?php if ($enr['image_url']): ?>
                                <img src="<?= SITE_URL ?>/../<?= $enr['image_url'] ?>" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#94A3B8;"><i class="fa-solid fa-book"></i></div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 1.05rem; color: #1E293B; margin-bottom: 4px;"><?= htmlspecialchars($enr['course_title']) ?></div>
                            <div style="font-size: 0.8rem; color: #94A3B8; font-weight: 600;">Requested on <?= date('d M Y', strtotime($enr['requested_at'])) ?></div>
                        </div>
                    </div>
                    <div>
                        <?php if ($enr['status'] === 'pending'): ?>
                            <span style="background: #FEF3C7; color: #D97706; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; white-space: nowrap;">Pending Approval</span>
                        <?php elseif ($enr['status'] === 'approved'): ?>
                            <a href="?page=classroom-view&id=<?= $enr['course_id'] ?>" style="background: #DCFCE7; color: #16A34A; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; text-decoration: none; white-space: nowrap;">Active / Enter</a>
                        <?php else: ?>
                            <span style="background: #FEE2E2; color: #DC2626; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; white-space: nowrap;">Rejected</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Ranking Section -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h2 style="font-size: 1.4rem; font-weight: 900; color: #1E293B; margin: 0;">Ranking</h2>
        <button onclick="openCharacterModal()" style="background: linear-gradient(135deg, #8B5CF6, #6366F1); color: white; padding: 8px 16px; border-radius: 20px; border: none; font-weight: 800; font-size: 0.85rem; cursor: pointer; box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3); display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-user-astronaut"></i> Character
        </button>
    </div>

    <!-- Tabs Filter Row -->
    <div style="background: #F1F5F9; border-radius: 30px; padding: 6px; display: flex; margin-bottom: 24px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
        <div style="flex: 1; text-align: center; background: #4A8CFF; color: white; padding: 10px 0; border-radius: 24px; font-weight: 800; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(74, 140, 255, 0.3);">
            Global
        </div>
        <div style="flex: 1; text-align: center; color: #94A3B8; padding: 10px 0; font-weight: 800; font-size: 0.95rem;">
            Friend
        </div>
        <div style="flex: 1; text-align: center; color: #94A3B8; padding: 10px 0; font-weight: 800; font-size: 0.95rem;">
            Country
        </div>
    </div>

    <!-- Top 3 Podium Card -->
    <?php if (empty($leaders)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">🏆</div>
            <h3>ยังไม่มีผู้เล่นในบอร์ด</h3>
            <p>มาเรียนรู้และสะสม XP เพื่อเป็นที่ 1 กันเถอะ!</p>
        </div>
    <?php elseif (count($leaders) >= 3): ?>
    <div style="background: #4A8CFF; border-radius: 24px; padding: 20px; margin-bottom: 24px; color: white; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(74, 140, 255, 0.15);">
        
        <!-- Header in Purple Card -->
        <div class="flex justify-between items-start" style="margin-bottom: 30px;">
            <div>
                <div style="font-size: 1.15rem; font-weight: 900;">Season 12</div>
                <div style="font-size: 0.8rem; font-weight: 700; opacity: 0.9; margin-top: 2px;">May 1 - May 31</div>
            </div>
            <div style="background: rgba(255, 255, 255, 0.2); padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 800; display: flex; align-items: center; gap: 6px;">
                <i class="fa-regular fa-clock"></i> 20d 10h left
            </div>
        </div>

        <!-- Podium Flex -->
        <div style="display: flex; align-items: flex-end; justify-content: center; gap: 12px; padding-top: 20px; position: relative; z-index: 2;">
            
            <!-- 2nd Place -->
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; margin-bottom: 10px;">
                <div style="position: relative; margin-bottom: 8px;">
                    <div style="width: 56px; height: 56px; border-radius: 50%; border: 3px solid #CBD5E1; display: flex; align-items: center; justify-content: center; background: transparent;">
                        <?php if(!empty($leaders[1]['character_id']) && $leaders[1]['character_id'] !== 'default'): ?>
                            <img src="<?= SITE_URL ?>/assets/images/characters/<?= $leaders[1]['character_id'] ?>.png" alt="Character" style="width: 46px; height: 46px; border-radius: 50%; object-fit: cover; background: <?= $leaders[1]['avatar_color'] ?? '#E2E8F0' ?>;">
                        <?php else: ?>
                            <div style="width: 46px; height: 46px; border-radius: 50%; background: <?= $leaders[1]['avatar_color'] ?? '#E2E8F0' ?>; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.2rem;">
                                <?= mb_substr($leaders[1]['fname'], 0, 1) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%); width: 22px; height: 22px; background: #CBD5E1; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 900; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">2</div>
                </div>
                <div style="background: linear-gradient(180deg, #F8FAFC 0%, #CBD5E1 100%); width: 100%; border-radius: 16px; padding: 12px 4px 16px; text-align: center; color: #1E293B; box-shadow: 0 8px 20px rgba(0,0,0,0.15);">
                    <div style="font-weight: 900; font-size: 0.95rem; margin-bottom: 2px;" class="truncate"><?= sanitize($leaders[1]['nickname']) ?></div>
                    <div style="font-weight: 800; font-size: 0.7rem; opacity: 0.8;"><?= number_format($leaders[1]['xp']) ?> XP</div>
                </div>
            </div>

            <!-- 1st Place -->
            <div style="flex: 1.15; display: flex; flex-direction: column; align-items: center;">
                <div style="position: relative; margin-bottom: 8px;">
                    <div style="width: 70px; height: 70px; border-radius: 50%; border: 3px solid #F59E0B; display: flex; align-items: center; justify-content: center; background: transparent;">
                        <?php if(!empty($leaders[0]['character_id']) && $leaders[0]['character_id'] !== 'default'): ?>
                            <img src="<?= SITE_URL ?>/assets/images/characters/<?= $leaders[0]['character_id'] ?>.png" alt="Character" style="width: 58px; height: 58px; border-radius: 50%; object-fit: cover; background: <?= $leaders[0]['avatar_color'] ?? '#E2E8F0' ?>;">
                        <?php else: ?>
                            <div style="width: 58px; height: 58px; border-radius: 50%; background: <?= $leaders[0]['avatar_color'] ?? '#E2E8F0' ?>; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.5rem;">
                                <?= mb_substr($leaders[0]['fname'], 0, 1) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%); width: 26px; height: 26px; background: #F59E0B; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 900; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">1</div>
                </div>
                <div style="background: linear-gradient(180deg, #FDE68A 0%, #F59E0B 100%); width: 100%; border-radius: 16px; padding: 16px 4px 24px; text-align: center; color: #78350F; box-shadow: 0 8px 20px rgba(0,0,0,0.15);">
                    <div style="font-weight: 900; font-size: 1.05rem; margin-bottom: 2px;" class="truncate"><?= sanitize($leaders[0]['nickname']) ?></div>
                    <div style="font-weight: 800; font-size: 0.75rem; opacity: 0.9;"><?= number_format($leaders[0]['xp']) ?> XP</div>
                </div>
            </div>

            <!-- 3rd Place -->
            <div style="flex: 1; display: flex; flex-direction: column; align-items: center; margin-bottom: 20px;">
                <div style="position: relative; margin-bottom: 8px;">
                    <div style="width: 56px; height: 56px; border-radius: 50%; border: 3px solid #D97706; display: flex; align-items: center; justify-content: center; background: transparent;">
                        <?php if(!empty($leaders[2]['character_id']) && $leaders[2]['character_id'] !== 'default'): ?>
                            <img src="<?= SITE_URL ?>/assets/images/characters/<?= $leaders[2]['character_id'] ?>.png" alt="Character" style="width: 46px; height: 46px; border-radius: 50%; object-fit: cover; background: <?= $leaders[2]['avatar_color'] ?? '#E2E8F0' ?>;">
                        <?php else: ?>
                            <div style="width: 46px; height: 46px; border-radius: 50%; background: <?= $leaders[2]['avatar_color'] ?? '#E2E8F0' ?>; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 1.2rem;">
                                <?= mb_substr($leaders[2]['fname'], 0, 1) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div style="position: absolute; bottom: -8px; left: 50%; transform: translateX(-50%); width: 22px; height: 22px; background: #D97706; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 900; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">3</div>
                </div>
                <div style="background: linear-gradient(180deg, #FDBA74 0%, #D97706 100%); width: 100%; border-radius: 16px; padding: 12px 4px 16px; text-align: center; color: #78350F; box-shadow: 0 8px 20px rgba(0,0,0,0.15);">
                    <div style="font-weight: 900; font-size: 0.95rem; margin-bottom: 2px;" class="truncate"><?= sanitize($leaders[2]['nickname']) ?></div>
                    <div style="font-weight: 800; font-size: 0.7rem; opacity: 0.9;"><?= number_format($leaders[2]['xp']) ?> XP</div>
                </div>
            </div>

        </div>
        
        <!-- Curved background overlay simulation (Optional soft white curve at bottom) -->
        <div style="position: absolute; bottom: -30px; left: -10%; right: -10%; height: 60px; background: #F8FAFC; border-radius: 50%; z-index: 1;"></div>
    </div>
    <?php endif; ?>

    <!-- Full List (Rank 4 onwards) -->
    <div class="flex-col gap-12" style="margin-bottom: 40px;">
        <?php foreach ($leaders as $i => $l): 
            if ($i < 3) continue; // Skip top 3
            $isMe = ($l['id'] == $userId);
            $rank = $i + 1;
        ?>
        <div style="display: flex; align-items: center; padding: 14px 20px; border-radius: 16px; background: <?= $isMe ? '#4A8CFF' : 'white' ?>; color: <?= $isMe ? 'white' : '#1E293B' ?>; border: <?= $isMe ? 'none' : '1px solid #F1F5F9' ?>; box-shadow: <?= $isMe ? '0 8px 20px rgba(74, 140, 255, 0.25)' : '0 4px 15px rgba(0,0,0,0.02)' ?>; gap: 16px;">
            <!-- Rank Num -->
            <div style="width: 24px; text-align: center; font-weight: 900; color: <?= $isMe ? 'rgba(255,255,255,0.7)' : '#94A3B8' ?>; font-size: 1rem;">
                <?= $rank ?>
            </div>

            <!-- Avatar -->
            <div style="width: 48px; height: 48px; border-radius: 16px; background: <?= $l['avatar_color'] ?? '#E2E8F0' ?>; display: flex; align-items: center; justify-content: center; color: white; font-weight: 900; font-size: 1.2rem; position: relative;">
                <?php if(!empty($l['character_id']) && $l['character_id'] !== 'default'): ?>
                    <img src="<?= SITE_URL ?>/assets/images/characters/<?= $l['character_id'] ?>.png" alt="Char" style="width: 48px; height: 48px; border-radius: 16px; object-fit: cover;">
                <?php else: ?>
                    <?= mb_substr($l['fname'], 0, 1) ?>
                <?php endif; ?>
                <!-- Level Badge -->
                <div style="position: absolute; bottom: -6px; right: -6px; background: white; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; color: #F59E0B; font-size: 0.6rem;"><i class="fa-solid fa-star"></i></div>
            </div>
            
            <div style="flex: 1; font-weight: 800; font-size: 1.05rem;" class="truncate">
                <?= $isMe ? 'You' : sanitize($l['nickname']) ?>
            </div>
            
            <div style="font-weight: 900; font-size: 1rem; text-align: right;">
                <?= number_format($l['xp']) ?> XP
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Settings Menu -->
    <div style="background: white; border: 1px solid #F1F5F9; border-radius: 24px; padding: 8px 20px; margin-bottom: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
        
        <!-- Option 1: Edit Profile -->
        <a href="?page=edit-profile" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid #F1F5F9; text-decoration: none;">
            <div class="flex items-center gap-16">
                <div style="width: 48px; height: 48px; border-radius: 16px; background: #F3E8FF; color: #8CB3FF; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div>
                    <div style="font-weight: 800; font-size: 1rem; color: #1E293B; margin-bottom: 4px;">Edit Profile</div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: #94A3B8;">Update your information</div>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right" style="color: #CBD5E1; font-size: 1.1rem;"></i>
        </a>
        
        <!-- Option 2: Settings -->
        <a href="?page=settings" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid #F1F5F9; text-decoration: none;">
            <div class="flex items-center gap-16">
                <div style="width: 48px; height: 48px; border-radius: 16px; background: #E8F4FE; color: #38BDF8; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                    <i class="fa-solid fa-gear"></i>
                </div>
                <div>
                    <div style="font-weight: 800; font-size: 1rem; color: #1E293B; margin-bottom: 4px;">Settings</div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: #94A3B8;">App preferences and notifications</div>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right" style="color: #CBD5E1; font-size: 1.1rem;"></i>
        </a>
        
        <!-- Option 3: Learning Goals -->
        <a href="?page=learning-goals" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 0; border-bottom: 1px solid #F1F5F9; text-decoration: none;">
            <div class="flex items-center gap-16">
                <div style="width: 48px; height: 48px; border-radius: 16px; background: #FFF8E7; color: #F59E0B; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                    <i class="fa-solid fa-chart-column"></i>
                </div>
                <div>
                    <div style="font-weight: 800; font-size: 1rem; color: #1E293B; margin-bottom: 4px;">Learning Goals</div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: #94A3B8;">Update your information</div>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right" style="color: #CBD5E1; font-size: 1.1rem;"></i>
        </a>
        
        <!-- Option 4: Help & Support -->
        <a href="?page=support" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 0; text-decoration: none;">
            <div class="flex items-center gap-16">
                <div style="width: 48px; height: 48px; border-radius: 16px; background: #FDEDF0; color: #EF4444; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                    <i class="fa-solid fa-heart"></i>
                </div>
                <div>
                    <div style="font-weight: 800; font-size: 1rem; color: #1E293B; margin-bottom: 4px;">Help & Support</div>
                    <div style="font-size: 0.75rem; font-weight: 600; color: #94A3B8;">Update your information</div>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right" style="color: #CBD5E1; font-size: 1.1rem;"></i>
        </a>
        
    </div>

    <!-- Log Out Button -->
    <div style="text-align: center;">
        <a href="?page=logout" style="display: inline-flex; align-items: center; justify-content: center; gap: 10px; color: #EF4444; font-weight: 800; font-size: 1.05rem; text-decoration: none; padding: 12px 24px; border-radius: 20px; transition: background 0.2s;">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            Log Out
        </a>
    </div>

</div>

<!-- Character Selection Modal -->
<div id="characterModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); z-index: 1000; align-items: flex-end; justify-content: center; padding: 20px; backdrop-filter: blur(5px);">
    <div style="background: white; width: 100%; max-width: 500px; border-radius: 24px; padding: 24px; animation: slideUp 0.3s ease-out;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="font-size: 1.3rem; font-weight: 900; color: #1E293B; margin: 0;">Select Your Character</h2>
            <button onclick="closeCharacterModal()" style="background: none; border: none; font-size: 1.5rem; color: #94A3B8; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <p style="color: #64748B; font-size: 0.9rem; margin-bottom: 20px;">Choose a character to represent you on the leaderboard!</p>
        
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
            <!-- Placeholder Character 1 -->
            <div onclick="selectCharacter('char1')" class="char-card <?= ($currentUser['character_id'] ?? '') === 'char1' ? 'selected' : '' ?>" style="text-align: center; padding: 16px; border-radius: 16px; border: 2px solid #E2E8F0; cursor: pointer; transition: 0.2s;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: #FFE4E6; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">🦊</div>
                <div style="font-weight: 800; font-size: 0.9rem; color: #1E293B;">Fox</div>
            </div>
            <!-- Placeholder Character 2 -->
            <div onclick="selectCharacter('char2')" class="char-card <?= ($currentUser['character_id'] ?? '') === 'char2' ? 'selected' : '' ?>" style="text-align: center; padding: 16px; border-radius: 16px; border: 2px solid #E2E8F0; cursor: pointer; transition: 0.2s;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: #E0F2FE; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">🐧</div>
                <div style="font-weight: 800; font-size: 0.9rem; color: #1E293B;">Penguin</div>
            </div>
            <!-- Placeholder Character 3 -->
            <div onclick="selectCharacter('char3')" class="char-card <?= ($currentUser['character_id'] ?? '') === 'char3' ? 'selected' : '' ?>" style="text-align: center; padding: 16px; border-radius: 16px; border: 2px solid #E2E8F0; cursor: pointer; transition: 0.2s;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: #DCFCE7; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; font-size: 2rem;">🐸</div>
                <div style="font-weight: 800; font-size: 0.9rem; color: #1E293B;">Frog</div>
            </div>
        </div>

        <button onclick="saveCharacter()" style="width: 100%; background: #4A8CFF; color: white; padding: 14px; border-radius: 16px; border: none; font-weight: 800; font-size: 1rem; cursor: pointer; box-shadow: 0 4px 15px rgba(74, 140, 255, 0.3);">Save Choice</button>
    </div>
</div>

<style>
.truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
@keyframes slideUp {
    from { transform: translateY(100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.char-card:hover {
    border-color: #94A3B8;
    background: #F8FAFC;
}
.char-card.selected {
    border-color: #4A8CFF;
    background: #EFF6FF;
    box-shadow: 0 4px 12px rgba(74, 140, 255, 0.15);
}
</style>

<script>
let selectedCharId = '<?= $currentUser['character_id'] ?? 'default' ?>';

function openCharacterModal() {
    document.getElementById('characterModal').style.display = 'flex';
}

function closeCharacterModal() {
    document.getElementById('characterModal').style.display = 'none';
}

function selectCharacter(charId) {
    selectedCharId = charId;
    document.querySelectorAll('.char-card').forEach(el => el.classList.remove('selected'));
    event.currentTarget.classList.add('selected');
}

async function saveCharacter() {
    try {
        const res = await fetch('<?= SITE_URL ?>/api/update_character.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ character_id: selectedCharId })
        });
        const data = await res.json();
        if(data.success) {
            window.location.reload();
        } else {
            alert('Error saving character');
        }
    } catch(e) {
        alert('Network error');
    }
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
