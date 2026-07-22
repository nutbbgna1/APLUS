<?php
// ============================================================
// LinguaMax — Pronunciation Practice
// ============================================================
include __DIR__ . '/../../includes/header.php';

// Mock words for practice
$words = [
    ['id'=>1, 'word'=>'Hello', 'phonetic'=>'hɛˈləʊ', 'level'=>'A1', 'focus'=>'/h/ sound'],
    ['id'=>2, 'word'=>'Apple', 'phonetic'=>'ˈæp.əl', 'level'=>'A1', 'focus'=>'/æ/ sound'],
    ['id'=>3, 'word'=>'School', 'phonetic'=>'skuːl', 'level'=>'A1', 'focus'=>'/uː/ sound'],
    ['id'=>4, 'word'=>'Beautiful', 'phonetic'=>'ˈbjuː.tɪ.fəl', 'level'=>'A2', 'focus'=>'/bjuː/ sound'],
    ['id'=>5, 'word'=>'Chocolate', 'phonetic'=>'ˈtʃɒk.lət', 'level'=>'A2', 'focus'=>'/tʃ/ sound'],
    ['id'=>6, 'word'=>'Family', 'phonetic'=>'ˈfæm.ɪ.li', 'level'=>'A1', 'focus'=>'/æ/ sound'],
    ['id'=>7, 'word'=>'Important', 'phonetic'=>'ɪmˈpɔː.tənt', 'level'=>'A2', 'focus'=>'/ɔː/ sound'],
    ['id'=>8, 'word'=>'English', 'phonetic'=>'ˈɪŋ.ɡlɪʃ', 'level'=>'A1', 'focus'=>'/ɪ/ sound'],
    ['id'=>9, 'word'=>'Language', 'phonetic'=>'ˈlæŋ.ɡwɪdʒ', 'level'=>'A2', 'focus'=>'/dʒ/ sound'],
    ['id'=>10, 'word'=>'Student', 'phonetic'=>'ˈstjuː.dənt', 'level'=>'A1', 'focus'=>'/st/ cluster'],
    ['id'=>11, 'word'=>'Teacher', 'phonetic'=>'ˈtiː.tʃər', 'level'=>'A1', 'focus'=>'/tʃ/ sound'],
    ['id'=>12, 'word'=>'Country', 'phonetic'=>'ˈkʌn.tri', 'level'=>'A1', 'focus'=>'/ʌ/ sound'],
    ['id'=>13, 'word'=>'Water', 'phonetic'=>'ˈwɔː.tər', 'level'=>'A1', 'focus'=>'/w/ sound'],
    ['id'=>14, 'word'=>'Coffee', 'phonetic'=>'ˈkɒf.i', 'level'=>'A1', 'focus'=>'/ɒ/ sound'],
    ['id'=>15, 'word'=>'Morning', 'phonetic'=>'ˈmɔː.nɪŋ', 'level'=>'A1', 'focus'=>'/ɔː/ sound'],
    ['id'=>16, 'word'=>'Tomorrow', 'phonetic'=>'təˈmɒr.əʊ', 'level'=>'A1', 'focus'=>'/əʊ/ sound'],
    ['id'=>17, 'word'=>'Information', 'phonetic'=>'ˌɪn.fəˈmeɪ.ʃən', 'level'=>'B1', 'focus'=>'/ʃ/ sound'],
    ['id'=>18, 'word'=>'Technology', 'phonetic'=>'tekˈnɒl.ə.dʒi', 'level'=>'B1', 'focus'=>'/ɒ/ sound'],
    ['id'=>19, 'word'=>'University', 'phonetic'=>'ˌjuː.nɪˈvɜː.sɪ.ti', 'level'=>'B1', 'focus'=>'/ɜː/ sound'],
    ['id'=>20, 'word'=>'Development', 'phonetic'=>'dɪˈvel.əp.mənt', 'level'=>'B1', 'focus'=>'/v/ sound'],
    ['id'=>21, 'word'=>'Community', 'phonetic'=>'kəˈmjuː.nə.ti', 'level'=>'B1', 'focus'=>'/mjuː/ sound'],
    ['id'=>22, 'word'=>'Environment', 'phonetic'=>'ɪnˈvaɪə.rən.mənt', 'level'=>'B1', 'focus'=>'/aɪ/ sound'],
    ['id'=>23, 'word'=>'Success', 'phonetic'=>'səkˈses', 'level'=>'A2', 'focus'=>'/s/ sound'],
    ['id'=>24, 'word'=>'Experience', 'phonetic'=>'ɪkˈspɪə.ri.əns', 'level'=>'B1', 'focus'=>'/ɪə/ sound'],
    ['id'=>25, 'word'=>'Knowledge', 'phonetic'=>'ˈnɒl.ɪdʒ', 'level'=>'B1', 'focus'=>'/ɒ/ sound'],
    ['id'=>26, 'word'=>'Professional', 'phonetic'=>'prəˈfeʃ.ən.əl', 'level'=>'B1', 'focus'=>'/ʃ/ sound'],
    ['id'=>27, 'word'=>'Education', 'phonetic'=>'ˌedʒ.ʊˈkeɪ.ʃən', 'level'=>'B1', 'focus'=>'/dʒ/ sound'],
    ['id'=>28, 'word'=>'International', 'phonetic'=>'ˌɪn.təˈnæʃ.ən.əl', 'level'=>'B1', 'focus'=>'/næ/ sound'],
    ['id'=>29, 'word'=>'Responsibility', 'phonetic'=>'rɪˌspɒn.sɪˈbɪl.ə.ti', 'level'=>'B1', 'focus'=>'/ɒ/ sound'],
    ['id'=>30, 'word'=>'Opportunity', 'phonetic'=>'ˌɒp.əˈtjuː.nə.ti', 'level'=>'B1', 'focus'=>'/ɒ/ sound']
];
?>

<div class="dashboard-container animate-fade-in" style="background: #F8FAFC; min-height: 100vh; padding: 24px 16px 100px 16px; margin: -16px -16px 0 -16px;">
    
    <!-- Header -->
    <div style="margin-bottom: 24px;">
        <a href="?page=dashboard" style="display: inline-flex; align-items: center; gap: 8px; color: #64748B; text-decoration: none; font-weight: 700; font-size: 0.9rem; margin-bottom: 16px; background: white; padding: 8px 16px; border-radius: 50px; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
            <i class="fa-solid fa-arrow-left"></i> Back
        </a>
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
            <div>
                <h1 style="font-size: 1.5rem; font-weight: 900; color: #1E293B; margin: 0; font-family: var(--font-display);">Pronunciation Practice</h1>
                <div style="color: #64748B; font-size: 0.9rem; font-weight: 500;">Pronunciation Practice</div>
            </div>
            <div style="background: #CCFBF1; color: #0F766E; padding: 6px 16px; border-radius: 50px; font-weight: 800; font-size: 0.9rem;">
                <span id="currentIndexDisplay">1</span>/<?= count($words) ?>
            </div>
        </div>
    </div>

    <div id="practiceSection">
        <!-- Word Card -->
        <div style="background: white; border-radius: 24px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-bottom: 16px; border: 1px solid #F1F5F9;">
            <!-- Navigation -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
                <button id="prevBtn" style="width: 40px; height: 40px; border-radius: 12px; background: #F1F5F9; border: none; color: #64748B; font-size: 1.1rem; cursor: pointer; transition: 0.2s;">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #64748B; font-size: 0.9rem; background: #F8FAFC; padding: 6px 16px; border-radius: 50px;">
                    <span id="levelBadge" style="background: #CCFBF1; color: #0F766E; padding: 2px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 800;">A1</span>
                    <span id="counterBadge">1/<?= count($words) ?></span>
                    <i class="fa-solid fa-chevron-down" style="font-size: 0.7rem; margin-left: 4px;"></i>
                </div>
                
                <button id="nextBtn" style="height: 40px; padding: 0 16px; border-radius: 12px; background: #E2E8F0; border: none; color: #94A3B8; font-size: 1rem; font-weight: 700; cursor: not-allowed; transition: 0.2s;">
                    <i class="fa-solid fa-chevron-right"></i> Next
                </button>
            </div>

            <!-- Word Content -->
            <div style="margin-bottom: 24px;">
                <div style="color: #0F766E; font-weight: 700; font-size: 0.95rem; margin-bottom: 8px;">Read this sentence aloud</div>
                <div id="targetWord" style="font-size: 2.5rem; font-weight: 900; color: #1E293B; font-family: var(--font-display); line-height: 1.2; margin-bottom: 4px;">
                    Hello
                </div>
                <div id="targetPhonetic" style="color: #94A3B8; font-size: 1.1rem; font-family: monospace; margin-bottom: 20px;">
                    hɛˈləʊ
                </div>

                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <button id="listenBtn" style="background: #CCFBF1; color: #0F766E; border: none; padding: 10px 20px; border-radius: 50px; font-weight: 800; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: 0.2s;">
                        <i class="fa-solid fa-volume-high"></i> Listen
                    </button>
                    <div id="focusBadge" style="background: #F1F5F9; color: #64748B; padding: 10px 20px; border-radius: 50px; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center;">
                        Focus: /h/ sound
                    </div>
                </div>
            </div>
        </div>

        <!-- Recording Card -->
        <div style="background: white; border-radius: 24px; padding: 32px 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid #F1F5F9; text-align: center;">
            <div style="font-size: 1.1rem; font-weight: 800; color: #1E293B; margin-bottom: 8px;">Ready?</div>
            <div style="color: #64748B; font-size: 0.95rem; font-weight: 500; margin-bottom: 32px;">Tap the mic and read the sentence above</div>
            
            <!-- Mic Button Area -->
            <div id="micContainer" style="position: relative; width: 100px; height: 100px; margin: 0 auto; display: flex; justify-content: center; align-items: center;">
                <!-- Ripple effect backgrounds (hidden by default) -->
                <div id="ripple1" style="position: absolute; width: 100%; height: 100%; background: rgba(239, 68, 68, 0.1); border-radius: 50%; opacity: 0; transform: scale(1);"></div>
                <div id="ripple2" style="position: absolute; width: 100%; height: 100%; background: rgba(239, 68, 68, 0.2); border-radius: 50%; opacity: 0; transform: scale(1);"></div>
                
                <button id="recordBtn" style="position: relative; z-index: 10; width: 80px; height: 80px; border-radius: 50%; background: #EF4444; color: white; border: none; font-size: 2rem; cursor: pointer; box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3); transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
                    <i class="fa-solid fa-microphone"></i>
                </button>
            </div>

            <div id="recordingStatus" style="margin-top: 24px; color: #EF4444; font-weight: 700; height: 20px; opacity: 0; transition: opacity 0.3s;">
                Recording... Tap again to stop
            </div>

            <div id="resultArea" style="margin-top: 24px; display: none; padding-top: 24px; border-top: 1px solid #F1F5F9;">
                <div style="font-size: 0.9rem; color: #64748B; font-weight: 600; margin-bottom: 8px;">Your Score</div>
                <div style="display: flex; justify-content: center; align-items: baseline; gap: 4px; margin-bottom: 8px;">
                    <span id="scoreValue" style="font-size: 3rem; font-weight: 900; color: #10B981; line-height: 1; font-family: var(--font-display);">95</span>
                    <span style="font-size: 1.2rem; font-weight: 800; color: #64748B;">/100</span>
                </div>
                <div id="transcriptionText" style="color: #64748B; font-style: italic; margin-bottom: 16px; font-size: 0.95rem;">
                    "Hello"
                </div>
                <button id="tryAgainBtn" style="background: #F1F5F9; color: #475569; border: none; padding: 8px 24px; border-radius: 50px; font-weight: 700; cursor: pointer; transition: 0.2s;">
                    Try Again
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Section (Hidden initially) -->
    <div id="summarySection" style="display: none; background: white; border-radius: 24px; padding: 40px 24px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid #F1F5F9; margin-top: 20px;">
        <div style="font-size: 4rem; margin-bottom: 16px;">🎉</div>
        <h2 style="font-size: 1.8rem; font-weight: 900; color: #1E293B; margin-bottom: 8px; font-family: var(--font-display);">Excellent!</h2>
        <p style="color: #64748B; font-size: 1rem; margin-bottom: 32px;">You have completed all pronunciation exercises</p>
        
        <div style="background: #F8FAFC; border-radius: 16px; padding: 24px; margin-bottom: 32px; display: flex; justify-content: space-around;">
            <div>
                <div style="color: #64748B; font-size: 0.85rem; font-weight: 700; margin-bottom: 4px;">Average Score</div>
                <div id="finalScoreDisplay" style="font-size: 2rem; font-weight: 900; color: #10B981;">0</div>
            </div>
            <div>
                <div style="color: #64748B; font-size: 0.85rem; font-weight: 700; margin-bottom: 4px;">XP Earned</div>
                <div id="finalXpDisplay" style="font-size: 2rem; font-weight: 900; color: #F59E0B;">+0</div>
            </div>
            <div>
                <div style="color: #64748B; font-size: 0.85rem; font-weight: 700; margin-bottom: 4px;">Coins</div>
                <div id="finalCoinsDisplay" style="font-size: 2rem; font-weight: 900; color: #EAB308;">+0</div>
            </div>
        </div>

        <a href="?page=dashboard" style="display: inline-block; background: #0EA5E9; color: white; text-decoration: none; font-weight: 800; padding: 14px 32px; border-radius: 50px; font-size: 1.1rem; box-shadow: 0 4px 15px rgba(14, 165, 233, 0.3);">
            Return to Dashboard
        </a>
    </div>

</div>

<!-- CSS for Ripple Animation -->
<style>
@keyframes ripple {
    0% { transform: scale(1); opacity: 0.8; }
    100% { transform: scale(1.8); opacity: 0; }
}
.recording #ripple1 {
    animation: ripple 1.5s linear infinite;
}
.recording #ripple2 {
    animation: ripple 1.5s linear infinite;
    animation-delay: 0.75s;
}
.recording #recordBtn {
    transform: scale(0.9);
    background: #DC2626 !important;
}
</style>

<!-- Pass PHP data to JS -->
<script>
    const P_WORDS = <?= json_encode($words) ?>;
    const API_URL = '<?= SITE_URL ?>/api/pronunciation.php';
</script>
<script src="<?= SITE_URL ?>/assets/js/pronunciation.js"></script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
