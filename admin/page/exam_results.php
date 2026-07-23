<?php
if (!isset($_SESSION['admin_logged_in'])) {
    die("Unauthorized Access");
}

// Fetch all exam results, join with users and exams
$stmt = $db->query("
    SELECT r.*, 
           u.fname, u.lname, u.nickname, u.code as student_code, u.avatar_color,
           e.title as exam_title, e.subject, e.level
    FROM exam_results r
    JOIN users u ON r.user_id = u.id
    JOIN exams e ON r.exam_id = e.id
    ORDER BY r.completed_at DESC
    LIMIT 200
");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<div class="page-header">
    <div>
        <h1 class="page-title">Exam Results & Reports</h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">View detailed performance of students on exams</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Recent Submissions</div>
        <div class="search-bar" style="width: 250px; background: white; border: 1px solid var(--border);">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Search student or exam..." onkeyup="filterTable()">
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 50px;">#</th>
                <th>Student</th>
                <th>Exam Title</th>
                <th style="text-align: center;">Score</th>
                <th style="text-align: center;">%</th>
                <th style="text-align: center;">Time Spent</th>
                <th>Completed At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="resultsTableBody">
            <?php $seq = 1; foreach($results as $r): ?>
            <tr>
                <td><?= $seq++ ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:32px;height:32px;background:<?= htmlspecialchars($r['avatar_color'] ?? 'var(--primary-light)') ?>;color:white;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700; font-size: 0.8rem; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">
                            <?= strtoupper(substr($r['fname'] ?? 'U', 0, 1)) ?>
                        </div> 
                        <div>
                            <div style="font-weight: 700; color: var(--text-main); font-size: 0.95rem;">
                                <?= htmlspecialchars(trim(($r['fname'] ?? '') . ' ' . ($r['lname'] ?? ''))) ?>
                                <?php if(!empty($r['nickname'])): ?>
                                    <span style="color: var(--text-muted); font-weight: normal; font-size: 0.85rem;">(<?= htmlspecialchars($r['nickname']) ?>)</span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted);"><i class="fa-solid fa-id-card"></i> <?= htmlspecialchars($r['student_code']) ?></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div style="font-weight: 600; color: var(--primary);"><?= htmlspecialchars($r['exam_title']) ?></div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($r['subject']) ?> &bull; Level: <?= ucfirst($r['level']) ?></div>
                </td>
                <td style="text-align: center;">
                    <span style="font-weight: 800; font-size: 1.1rem; color: <?= $r['percentage'] >= 50 ? '#16A34A' : '#DC2626' ?>;">
                        <?= $r['score'] ?> <span style="font-size:0.8rem; color:var(--text-muted); font-weight: 500;">/ <?= $r['total'] ?></span>
                    </span>
                </td>
                <td style="text-align: center;">
                    <span style="background: <?= $r['percentage'] >= 80 ? '#DCFCE7' : ($r['percentage'] >= 50 ? '#FEF9C3' : '#FEE2E2') ?>; 
                                 color: <?= $r['percentage'] >= 80 ? '#166534' : ($r['percentage'] >= 50 ? '#854D0E' : '#991B1B') ?>; 
                                 padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">
                        <?= $r['percentage'] ?>%
                    </span>
                </td>
                <td style="text-align: center; color: var(--text-muted); font-size: 0.9rem;">
                    <?= gmdate("i:s", $r['time_spent']) ?> min
                </td>
                <td style="font-size: 0.85rem; color: var(--text-muted);">
                    <?= date('d M Y, H:i', strtotime($r['completed_at'])) ?>
                </td>
                <td>
                    <?php 
                        // Safely encode answers JSON for JS
                        $answersJson = $r['answers_json'] ? $r['answers_json'] : '[]';
                        // Ensure it's valid JSON string, if empty string make it empty array
                        if (trim($answersJson) === '') $answersJson = '[]';
                    ?>
                    <button class="btn btn-sm" style="background:var(--primary); color:white; border:none; padding:6px 12px; cursor:pointer; border-radius:8px;" 
                            onclick='openDetailModal(<?= htmlspecialchars($answersJson, ENT_QUOTES, "UTF-8") ?>, "<?= htmlspecialchars(trim(($r['fname'] ?? "") . ' ' . ($r['lname'] ?? ""))) ?>", "<?= htmlspecialchars($r['exam_title']) ?>")'>
                        <i class="fa-solid fa-chart-pie"></i> Details
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($results)): ?>
            <tr><td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">No exam results found yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal for Viewing Detailed Results -->
<div id="detailModalOverlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: 16px; width: 100%; max-width: 800px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); transform: scale(0.95); transition: transform 0.2s; max-height: 90vh; display: flex; flex-direction: column;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #E2E8F0; padding-bottom: 15px;">
            <div>
                <h2 style="margin: 0; font-size: 1.25rem; color: var(--text-main);"><i class="fa-solid fa-chart-pie" style="color:var(--primary);"></i> Detailed Report</h2>
                <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 5px;">
                    Student: <strong id="modalStudentName" style="color:var(--text-main);"></strong> &nbsp;|&nbsp; Exam: <strong id="modalExamTitle" style="color:var(--text-main);"></strong>
                </div>
            </div>
            <button onclick="closeDetailModal()" style="background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div id="modalDetailsContent" style="flex: 1; overflow-y: auto; padding-right: 10px;">
            <!-- Content Injected via JS -->
        </div>
        
        <div style="text-align: right; margin-top: 20px; padding-top: 15px; border-top: 1px solid #E2E8F0;">
            <button type="button" onclick="closeDetailModal()" class="btn btn-outline">Close</button>
        </div>
    </div>
</div>

<script>
function filterTable() {
    let input = document.getElementById("searchInput").value.toLowerCase();
    let rows = document.querySelectorAll("#resultsTableBody tr");
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(input) ? "" : "none";
    });
}

function openDetailModal(answers, studentName, examTitle) {
    document.getElementById('modalStudentName').textContent = studentName || 'Unknown Student';
    document.getElementById('modalExamTitle').textContent = examTitle || 'Unknown Exam';
    
    const container = document.getElementById('modalDetailsContent');
    
    // Fallback if answers is a string instead of object
    if (typeof answers === 'string') {
        try { answers = JSON.parse(answers); } catch (e) { answers = []; }
    }
    
    if (!answers || !Array.isArray(answers) || answers.length === 0) {
        container.innerHTML = `
            <div style="text-align:center; padding:40px; color:var(--text-muted);">
                <i class="fa-solid fa-circle-info fa-3x" style="color:#CBD5E1; margin-bottom:15px;"></i>
                <p style="font-size: 1.1rem; margin:0;">No detailed answers were recorded for this session.</p>
                <p style="font-size: 0.9rem; margin-top:5px;">This is likely an old result from before the tracking system was enabled.</p>
            </div>
        `;
    } else {
        let html = '';
        answers.forEach((ans, index) => {
            const isCorrect = ans.is_correct;
            const icon = isCorrect 
                ? '<i class="fa-solid fa-circle-check" style="color:#16A34A;"></i>' 
                : '<i class="fa-solid fa-circle-xmark" style="color:#DC2626;"></i>';
            const bgClass = isCorrect ? 'background: #F0FDF4; border: 1px solid #BBF7D0;' : 'background: #FEF2F2; border: 1px solid #FECACA;';
            
            html += `
                <div style="padding: 15px; border-radius: 8px; margin-bottom: 12px; ${bgClass}">
                    <div style="font-weight: 700; font-size: 1rem; color: var(--text-main); margin-bottom: 10px;">
                        ${index + 1}. ${ans.question_text || 'Question text unavailable'}
                    </div>
                    
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px; font-size: 0.9rem;">
                        <div>
                            <span style="color: var(--text-muted); display: block; font-size: 0.8rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Student Answered</span>
                            <div style="margin-top: 4px; font-weight: 600; color: ${isCorrect ? '#166534' : '#991B1B'}; display: flex; align-items: flex-start; gap: 6px;">
                                <div style="margin-top:3px;">${icon}</div> <div>${ans.selected_text || '(No answer text)'}</div>
                            </div>
                        </div>
                        
                        ${!isCorrect ? `
                        <div>
                            <span style="color: var(--text-muted); display: block; font-size: 0.8rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Correct Answer</span>
                            <div style="margin-top: 4px; font-weight: 600; color: #166534; display: flex; align-items: flex-start; gap: 6px;">
                                <div style="margin-top:3px;"><i class="fa-solid fa-check" style="color:#16A34A;"></i></div> <div>${ans.correct_text || '(Unknown)'}</div>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }
    
    const modal = document.getElementById('detailModalOverlay');
    modal.style.display = 'flex';
    setTimeout(() => modal.children[0].style.transform = 'scale(1)', 10);
}

function closeDetailModal() {
    const modal = document.getElementById('detailModalOverlay');
    modal.children[0].style.transform = 'scale(0.95)';
    setTimeout(() => modal.style.display = 'none', 200);
}
</script>
