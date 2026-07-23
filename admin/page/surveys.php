<?php
if (!isset($_SESSION['admin_logged_in'])) {
    die("Unauthorized Access");
}

// Ensure the table exists before querying to prevent fatal errors on fresh installs
try {
    $db->query("SELECT 1 FROM user_surveys LIMIT 1");
    $tableExists = true;
} catch (Exception $e) {
    $tableExists = false;
}

$results = [];
if ($tableExists) {
    // Fetch all survey results
    $stmt = $db->query("
        SELECT s.*, 
               u.fname, u.lname, u.nickname, u.code as student_code, u.avatar_color
        FROM user_surveys s
        JOIN users u ON s.user_id = u.id
        ORDER BY s.submitted_at DESC
        LIMIT 200
    ");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<style>
    .table-responsive { width: 100%; overflow-x: auto; }
    .survey-card { background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); overflow: hidden; border: 1px solid #F1F5F9; }
    .survey-header { padding: 20px; border-bottom: 1px solid #F1F5F9; display: flex; justify-content: space-between; align-items: center; }
    .survey-title { font-size: 1.1rem; font-weight: 700; color: #1E293B; margin: 0; }
    .search-box { position: relative; width: 250px; }
    .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94A3B8; }
    .search-box input { width: 100%; padding: 8px 12px 8px 35px; border: 1px solid #E2E8F0; border-radius: 8px; outline: none; transition: 0.2s; }
    .search-box input:focus { border-color: #3B82F6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); }
    
    .survey-table { width: 100%; border-collapse: collapse; }
    .survey-table th { text-align: left; padding: 12px 20px; font-weight: 600; color: #64748B; font-size: 0.85rem; background: #F8FAFC; border-bottom: 1px solid #E2E8F0; }
    .survey-table td { padding: 16px 20px; border-bottom: 1px solid #F1F5F9; vertical-align: middle; }
    .survey-table tr:hover td { background: #F8FAFC; }
    
    .student-info { display: flex; align-items: center; gap: 12px; }
    .avatar-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1rem; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .student-name { font-weight: 700; color: #0F172A; font-size: 0.95rem; }
    .student-nickname { color: #64748B; font-weight: 400; font-size: 0.85rem; }
    .student-code { font-size: 0.8rem; color: #94A3B8; margin-top: 2px; }
    
    .btn-view { background: #3B82F6; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.2s; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px; }
    .btn-view:hover { background: #2563EB; transform: translateY(-1px); box-shadow: 0 4px 6px rgba(59,130,246,0.2); }
    
    /* Modal Styles */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px); padding: 20px; opacity: 0; transition: opacity 0.2s; }
    .modal-overlay.show { display: flex; opacity: 1; }
    .modal-box { background: white; border-radius: 20px; width: 100%; max-width: 600px; max-height: 85vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); transform: scale(0.95) translateY(10px); transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    .modal-overlay.show .modal-box { transform: scale(1) translateY(0); }
    
    .modal-header { padding: 24px; border-bottom: 1px solid #E2E8F0; display: flex; justify-content: space-between; align-items: center; }
    .modal-title { margin: 0; font-size: 1.25rem; font-weight: 800; color: #0F172A; display: flex; align-items: center; gap: 10px; }
    .btn-close { background: #F1F5F9; border: none; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #64748B; cursor: pointer; transition: 0.2s; }
    .btn-close:hover { background: #E2E8F0; color: #0F172A; transform: rotate(90deg); }
    
    .modal-body { padding: 24px; overflow-y: auto; flex: 1; }
    .modal-footer { padding: 16px 24px; border-top: 1px solid #E2E8F0; text-align: right; background: #F8FAFC; border-radius: 0 0 20px 20px; }
    
    .answer-block { background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px; margin-bottom: 16px; transition: 0.2s; }
    .answer-block:hover { border-color: #CBD5E1; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
    .answer-q { font-weight: 700; color: #334155; margin-bottom: 8px; font-size: 0.95rem; }
    .answer-a { color: #0F172A; font-size: 1rem; line-height: 1.6; padding-left: 12px; border-left: 4px solid #3B82F6; }
    
    @media (max-width: 768px) {
        .survey-header { flex-direction: column; align-items: flex-start; gap: 15px; }
        .search-box { width: 100%; }
        .modal-box { max-height: 100vh; height: 100vh; border-radius: 0; }
        .modal-footer { border-radius: 0; }
        .modal-overlay { padding: 0; }
    }
</style>

<div style="margin-bottom: 24px;">
    <h1 style="margin:0; font-size: 1.5rem; color: #0F172A; font-weight: 800;">📝 Student Surveys</h1>
    <p style="color: #64748B; margin-top: 5px; font-size: 0.95rem;">Review all submitted survey responses from students.</p>
</div>

<div class="survey-card">
    <div class="survey-header">
        <h2 class="survey-title">Recent Submissions</h2>
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Search by name or code..." onkeyup="filterSurveys()">
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="survey-table">
            <thead>
                <tr>
                    <th style="width: 60px;">#</th>
                    <th>Student Info</th>
                    <th>Submitted At</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody id="surveyTableBody">
                <?php if(!$tableExists): ?>
                    <tr><td colspan="4" style="text-align: center; padding: 40px; color: #DC2626; font-weight: 600;"><i class="fa-solid fa-triangle-exclamation"></i> The user_surveys table does not exist in this database yet.</td></tr>
                <?php elseif(empty($results)): ?>
                    <tr><td colspan="4" style="text-align: center; padding: 40px; color: #94A3B8;"><i class="fa-solid fa-inbox" style="font-size: 2rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i> No survey submissions found yet.</td></tr>
                <?php else: ?>
                    <?php $seq = 1; foreach($results as $r): ?>
                    <tr class="survey-row">
                        <td style="color: #94A3B8; font-weight: 600;"><?= $seq++ ?></td>
                        <td>
                            <div class="student-info">
                                <div class="avatar-circle" style="background: <?= htmlspecialchars($r['avatar_color'] ?? '#3B82F6') ?>;">
                                    <?= strtoupper(substr($r['fname'] ?? 'U', 0, 1)) ?>
                                </div>
                                <div>
                                    <div class="student-name">
                                        <?= htmlspecialchars(trim(($r['fname'] ?? '') . ' ' . ($r['lname'] ?? ''))) ?>
                                        <?php if(!empty($r['nickname'])): ?>
                                            <span class="student-nickname">(<?= htmlspecialchars($r['nickname']) ?>)</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="student-code"><i class="fa-solid fa-id-card"></i> <?= htmlspecialchars($r['student_code']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="color: #64748B; font-size: 0.9rem;">
                            <i class="fa-regular fa-clock"></i> <?= date('d M Y, H:i', strtotime($r['submitted_at'])) ?>
                        </td>
                        <td style="text-align: right;">
                            <?php 
                                $ans = $r['answers_json'] ? trim($r['answers_json']) : '{}';
                                if ($ans === '') $ans = '{}';
                            ?>
                            <button class="btn-view" 
                                    data-json="<?= htmlspecialchars($ans, ENT_QUOTES, 'UTF-8') ?>"
                                    data-name="<?= htmlspecialchars(trim(($r['fname'] ?? "") . ' ' . ($r['lname'] ?? "")), ENT_QUOTES, 'UTF-8') ?>"
                                    onclick="openSurvey(this)">
                                <i class="fa-solid fa-file-lines"></i> View Answers
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Beautiful Modal -->
<div class="modal-overlay" id="surveyModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 class="modal-title"><i class="fa-solid fa-clipboard-check" style="color: #3B82F6;"></i> Survey Answers</h3>
            <button class="btn-close" onclick="closeSurvey()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px dashed #E2E8F0;">
                <div style="font-size: 0.85rem; color: #64748B; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Student Name</div>
                <div id="modalSName" style="font-size: 1.1rem; font-weight: 700; color: #0F172A; margin-top: 4px;"></div>
            </div>
            <div id="modalContent"></div>
        </div>
        <div class="modal-footer">
            <button class="btn-view" style="background: #E2E8F0; color: #475569;" onclick="closeSurvey()">Close Window</button>
        </div>
    </div>
</div>

<script>
function filterSurveys() {
    const input = document.getElementById("searchInput").value.toLowerCase();
    const rows = document.querySelectorAll(".survey-row");
    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(input) ? "" : "none";
    });
}

const Q_MAP = {
    'nickname': '1. ชื่อเล่น',
    'grade': '2. ระดับชั้น',
    'fav': '3. วิชาที่สนุกที่สุด',
    'hard': '4. วิชาที่อยากเก่งขึ้น',
    'helper': '5. ตัวช่วยวิเศษที่อยากได้',
    'gametype': '6. แนวเกมที่ชอบ',
    'reward': '7. รางวัลที่อยากได้',
    'play': '8. รูปแบบการเล่น',
    'dream': '9. เกมในฝัน',
    'note': '10. ข้อความถึงครู'
};

function openSurvey(btn) {
    const jsonStr = btn.getAttribute('data-json');
    const sName = btn.getAttribute('data-name');
    
    document.getElementById('modalSName').textContent = sName || 'Unknown';
    const content = document.getElementById('modalContent');
    
    let data = {};
    if (jsonStr) {
        try { data = JSON.parse(jsonStr); } catch (e) { console.error("Parse Error:", e); }
    }
    
    if (Object.keys(data).length === 0) {
        content.innerHTML = `<div style="text-align:center; padding:30px; color:#94A3B8;">No answers provided.</div>`;
    } else {
        let html = '';
        for (const [k, v] of Object.entries(data)) {
            if(k === 'submittedAt') continue;
            
            const title = Q_MAP[k] || k;
            const text = Array.isArray(v) ? v.join(', ') : (v || '-');
            
            html += `
                <div class="answer-block">
                    <div class="answer-q">${title}</div>
                    <div class="answer-a">${text}</div>
                </div>
            `;
        }
        content.innerHTML = html;
    }
    
    const modal = document.getElementById('surveyModal');
    modal.classList.add('show');
}

function closeSurvey() {
    document.getElementById('surveyModal').classList.remove('show');
}

// Close on click outside
document.getElementById('surveyModal').addEventListener('click', function(e) {
    if (e.target === this) closeSurvey();
});
</script>
