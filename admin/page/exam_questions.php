<?php
if (!isset($_SESSION['admin_logged_in'])) {
    die("Unauthorized Access");
}

$exam_id = isset($_GET['exam_id']) ? (int)$_GET['exam_id'] : 0;
$action = $_GET['action'] ?? 'list';
$errorMsg = '';
$successMsg = '';

// Fetch Exam Info
$stmt = $db->prepare("SELECT * FROM exams WHERE id = ?");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch();

if (!$exam) {
    echo "<script>window.location.href='?page=exams';</script>";
    exit;
}

// Handle Delete Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_q'])) {
    $del_id = (int)$_POST['q_id'];
    $stmt = $db->prepare("DELETE FROM exam_questions WHERE id = ? AND exam_id = ?");
    $stmt->execute([$del_id, $exam_id]);
    $successMsg = "Question deleted successfully.";
}

// Handle Add Manual Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_manual_q'])) {
    $passage_text = trim($_POST['passage_text'] ?? '');
    
    // Handle Image Upload
    $image_path = $_POST['existing_image_path'] ?? '';
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/exams/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = time() . '_' . basename($_FILES['image_file']['name']);
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $upload_dir . $filename)) {
            $image_path = 'uploads/exams/' . $filename;
        }
    }
    
    $q_texts = $_POST['question_text'] ?? [];
    $c_1s = $_POST['choice_a'] ?? [];
    $c_2s = $_POST['choice_b'] ?? [];
    $c_3s = $_POST['choice_c'] ?? [];
    $c_4s = $_POST['choice_d'] ?? [];
    $corrects = $_POST['correct_answer'] ?? [];

    $inserted = 0;
    try {
        $db->beginTransaction();
        $stmt = $db->prepare("INSERT INTO exam_questions (exam_id, passage_text, image_path, question_text, choice_1, choice_2, choice_3, choice_4, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        for ($i = 0; $i < count($q_texts); $i++) {
            $q_text = trim($q_texts[$i] ?? '');
            $c_1 = trim($c_1s[$i] ?? '');
            $c_2 = trim($c_2s[$i] ?? '');
            $c_3 = trim($c_3s[$i] ?? '');
            $c_4 = trim($c_4s[$i] ?? '');
            $correct = (int)($corrects[$i] ?? 0);
            
            if (!empty($q_text) && !empty($c_1) && !empty($c_2)) {
                $stmt->execute([$exam_id, $passage_text, $image_path, $q_text, $c_1, $c_2, $c_3, $c_4, $correct]);
                $inserted++;
            }
        }
        $db->commit();
        $successMsg = "$inserted Question(s) added manually successfully.";
        
        if (isset($_POST['keep_context'])) {
            echo "<script>sessionStorage.setItem('savedImage', '" . addslashes($image_path) . "');</script>";
        } else {
            echo "<script>sessionStorage.removeItem('savedImage');</script>";
        }
    } catch (Exception $e) {
        $db->rollBack();
        $errorMsg = "DB Error: " . $e->getMessage();
    }
}

// Fetch all questions for this exam
$stmt = $db->prepare("SELECT * FROM exam_questions WHERE exam_id = ? ORDER BY id ASC");
$stmt->execute([$exam_id]);
$questions = $stmt->fetchAll();
$current_count = count($questions);
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Manage Questions: <?= htmlspecialchars($exam['title']) ?></h1>
        <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 5px;">
            Subject: <?= htmlspecialchars($exam['subject']) ?> | Level: <?= ucfirst($exam['level']) ?> | Target: <?= $exam['total_questions'] ?> questions
        </p>
    </div>
    <a href="?page=exams" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to Exams</a>
</div>

<?php if ($errorMsg): ?>
<div style="background: #FEE2E2; color: #DC2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
    Error: <?= htmlspecialchars($errorMsg) ?>
</div>
<?php endif; ?>
<?php if ($successMsg): ?>
<div style="background: #DCFCE7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
    <?= htmlspecialchars($successMsg) ?>
</div>
<?php endif; ?>

<div style="display: flex; gap: 15px; margin-bottom: 20px;">
    <!-- AI Generation Button -->
    <button onclick="openAIModal()" class="btn btn-primary" id="aiBtn" style="background: #8B5CF6; border: none;">
        <i class="fa-solid fa-wand-magic-sparkles"></i> Generate Questions with AI
    </button>
    <!-- Manual Add Button -->
    <button onclick="openManualModal()" class="btn btn-outline" style="border: 1px solid var(--primary); color: var(--primary);">
        <i class="fa-solid fa-plus"></i> Add Question Manually
    </button>
</div>

<!-- Modal for AI Generation -->
<div id="aiModalOverlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: 16px; width: 100%; max-width: 700px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); transform: scale(0.95); transition: transform 0.2s; max-height: 90vh; display: flex; flex-direction: column;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h2 style="margin: 0; font-size: 1.25rem;"><i class="fa-solid fa-wand-magic-sparkles" style="color:#8B5CF6;"></i> AI Exam Generator</h2>
            <button onclick="closeAIModal()" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div style="display: flex; gap: 10px; margin-bottom: 15px; border-bottom: 2px solid #F1F5F9; padding-bottom: 10px;">
            <button onclick="switchAITab('auto')" id="tabAuto" class="btn btn-primary" style="flex:1;">Auto Generate (Random)</button>
            <button onclick="switchAITab('custom')" id="tabCustom" class="btn btn-outline" style="flex:1;">Use Custom Source (Paste/File)</button>
        </div>

        <div id="aiTabContent" style="flex: 1; overflow-y: auto; padding-right: 10px;">
            
            <!-- Auto Mode -->
            <div id="aiModeAuto">
                <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 20px;">
                    The AI will automatically write <strong id="neededAuto">0</strong> questions based on the exam's topic and level.
                </p>
            </div>

            <!-- Custom Mode -->
            <div id="aiModeCustom" style="display: none;">
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 15px;">
                    Provide your own exam text and answer key. The AI will parse them and format them perfectly into the system. You can copy & paste from Word/PDF, or upload a <code>.txt, .csv, .pdf</code> file.<br>
                    <strong style="color:var(--primary);">💡 Tip:</strong> If your file already contains both the questions and answers together, you only need to upload it to the first box!
                </p>
                
                <div style="margin-bottom: 15px;">
                    <label style="display:flex; justify-content:space-between; font-weight:600; margin-bottom:5px; font-size:0.9rem;">
                        <span>1. Questions & Answers Source (ไฟล์ข้อสอบและเฉลย)</span>
                        <input type="file" id="fileQuestions" accept=".txt,.csv,.pdf" style="font-size:0.8rem;" onchange="loadTextFile(this, 'sourceQuestions')">
                    </label>
                    <textarea id="sourceQuestions" rows="6" style="width:100%; padding:10px; border-radius:8px; border:1px solid #CBD5E1; font-family:monospace; font-size:0.85rem;" placeholder="Paste exam questions (and answers) here..."></textarea>
                </div>
                
                <div style="margin-bottom: 10px;">
                    <label style="display:flex; justify-content:space-between; font-weight:600; margin-bottom:5px; font-size:0.9rem;">
                        <span>2. Answers Key (ไฟล์เฉลยแยก) <span style="color:#94A3B8; font-weight:normal;">- Optional / ไม่บังคับ</span></span>
                        <input type="file" id="fileAnswers" accept=".txt,.csv,.pdf" style="font-size:0.8rem;" onchange="loadTextFile(this, 'sourceAnswers')">
                    </label>
                    <textarea id="sourceAnswers" rows="4" style="width:100%; padding:10px; border-radius:8px; border:1px solid #CBD5E1; font-family:monospace; font-size:0.85rem;" placeholder="Paste answer key here (if separate)..."></textarea>
                </div>
            </div>

        </div>

        <div style="display: flex; gap: 12px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border);">
            <button class="btn btn-outline" style="flex:1;" onclick="closeAIModal()">Cancel</button>
            <button class="btn btn-primary" style="flex:2; background:#8B5CF6; border:none;" id="startAIBtn" onclick="generateWithAI()">
                <i class="fa-solid fa-play"></i> Start Generation
            </button>
        </div>
    </div>
</div>

<!-- Modal for Manual Add -->
<div id="manualModalOverlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: 16px; width: 100%; max-width: 600px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); transform: scale(0.95); transition: transform 0.2s; max-height: 90vh; display: flex; flex-direction: column;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h2 style="margin: 0; font-size: 1.25rem;"><i class="fa-solid fa-pen" style="color:var(--primary);"></i> Add Manual Question</h2>
            <button type="button" onclick="closeManualModal()" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="POST" enctype="multipart/form-data" style="flex: 1; overflow-y: auto; padding-right: 10px;" id="manualForm" onsubmit="handleManualSubmit(event)">
            <input type="hidden" name="add_manual_q" value="1">
            <input type="hidden" name="existing_image_path" id="existingImagePath" value="">
            
            <div style="background: #F8FAFC; padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #E2E8F0;">
                <div style="margin-bottom: 10px;">
                    <label style="display:flex; justify-content:space-between; font-weight:600; margin-bottom:5px;">
                        <span>Passage Context / Conversation <span style="color:#94A3B8; font-weight:normal;">- Optional</span></span>
                    </label>
                    <textarea name="passage_text" id="manualPassage" rows="4" style="width:100%; padding:10px; border-radius:8px; border:1px solid #CBD5E1; font-family:inherit; font-size:0.85rem;" placeholder="Paste any reading passage or context here..."></textarea>
                </div>
                
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:5px;">Visual Image <span style="color:#94A3B8; font-weight:normal;">- Optional</span></label>
                    <input type="file" name="image_file" id="manualImage" accept="image/*" style="font-size:0.85rem;">
                    <div id="imageStatus" style="font-size: 0.8rem; color: #16A34A; margin-top: 5px; display: none;"><i class="fa-solid fa-check-circle"></i> Image kept from previous question</div>
                </div>
            </div>
            
            <div id="manualQuestionsContainer">
                <div class="manual-question-block" style="background: white; border: 1px solid #E2E8F0; border-radius: 8px; padding: 15px; margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
                        <label style="font-weight:600; font-size: 1rem; color: #334155;">Question 1 <span style="color:red;">*</span></label>
                        <button type="button" class="remove-q-btn" onclick="removeQuestionBlock(this)" style="display:none; color: #EF4444; background: none; border: none; cursor: pointer; font-size: 0.9rem;"><i class="fa-solid fa-trash"></i> Remove</button>
                    </div>
                    <textarea name="question_text[]" rows="3" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #CBD5E1; font-family:inherit; font-size:0.9rem; margin-bottom: 15px;" placeholder="Enter your question here..."></textarea>
                    
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Choice A <span style="color:red;">*</span></label>
                            <input type="text" name="choice_a[]" required style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1; font-size:0.85rem;">
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Choice B <span style="color:red;">*</span></label>
                            <input type="text" name="choice_b[]" required style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1; font-size:0.85rem;">
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Choice C</label>
                            <input type="text" name="choice_c[]" style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1; font-size:0.85rem;">
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Choice D</label>
                            <input type="text" name="choice_d[]" style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1; font-size:0.85rem;">
                        </div>
                    </div>

                    <div>
                        <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Correct Answer <span style="color:red;">*</span></label>
                        <select name="correct_answer[]" required style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1; background:white; font-size:0.85rem;">
                            <option value="0">Choice A</option>
                            <option value="1">Choice B</option>
                            <option value="2">Choice C</option>
                            <option value="3">Choice D</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div style="text-align: center; margin-bottom: 20px;">
                <button type="button" class="btn btn-outline" onclick="addQuestionBlock()" style="width: 100%; border-style: dashed; padding: 10px; color: #4F46E5; border-color: #4F46E5; background: #F5F3FF;">
                    <i class="fa-solid fa-plus"></i> Add Another Question for this Context
                </button>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border); padding-top: 20px;">
                <label style="display:flex; align-items:center; gap:8px; font-size:0.9rem; cursor:pointer;">
                    <input type="checkbox" name="keep_context" id="keepContext" style="width:16px; height:16px;">
                    Keep Passage & Image for next question
                </label>
                <div style="display: flex; gap: 12px;">
                    <button type="button" class="btn btn-outline" onclick="closeManualModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Save Question
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; margin-bottom: 15px; align-items: center;">
        <h3 style="margin:0;">Current Questions (<span id="qCount"><?= $current_count ?></span> / <?= $exam['total_questions'] ?>)</h3>
    </div>
    
    <div id="loadingIndicator" style="display: none; padding: 40px; text-align: center;">
        <i class="fa-solid fa-spinner fa-spin fa-3x" style="color: #8B5CF6; margin-bottom: 15px;"></i>
        <h3 style="color: #4C1D95; margin: 0;">AI is writing the exam...</h3>
        <p style="color: var(--text-muted); margin-top: 5px;">Please wait. This might take 10-20 seconds.</p>
    </div>

    <table id="questionsTable" <?= empty($questions) ? 'style="display:none;"' : '' ?> style="width: 100%; text-align: left; border-collapse: collapse;">
        <thead>
            <tr style="border-bottom: 2px solid #E2E8F0;">
                <th style="padding: 10px;">#</th>
                <th style="padding: 10px;">Question</th>
                <th style="padding: 10px;">Choices (Correct is Highlighted)</th>
                <th style="padding: 10px; width: 80px;">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($questions as $index => $q): ?>
            <tr style="border-bottom: 1px solid #F1F5F9;">
                <td style="padding: 10px;"><?= $index + 1 ?></td>
                <td style="padding: 10px;">
                    <?php if (!empty($q['passage_text'])): ?>
                        <div style="font-size: 0.8rem; background: #F1F5F9; color: #475569; padding: 4px 8px; border-radius: 4px; margin-bottom: 8px; display: inline-block; border: 1px solid #CBD5E1;">
                            <i class="fa-solid fa-align-left"></i> Passage / Context Attached
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($q['image_path'])): ?>
                        <div style="font-size: 0.8rem; background: #F0FDF4; color: #166534; padding: 4px 8px; border-radius: 4px; margin-bottom: 8px; display: inline-block; border: 1px solid #BBF7D0;">
                            <i class="fa-solid fa-image"></i> Image Attached
                        </div>
                        <br>
                    <?php endif; ?>
                    <strong><?= htmlspecialchars($q['question_text']) ?></strong>
                </td>
                <td style="padding: 10px;">
                    <div style="font-size: 0.9rem; color: var(--text-muted);">
                        A) <span style="<?= $q['correct_answer'] == 0 ? 'color: #16A34A; font-weight:bold;' : '' ?>"><?= htmlspecialchars($q['choice_1'] ?? '') ?></span><br>
                        B) <span style="<?= $q['correct_answer'] == 1 ? 'color: #16A34A; font-weight:bold;' : '' ?>"><?= htmlspecialchars($q['choice_2'] ?? '') ?></span><br>
                        C) <span style="<?= $q['correct_answer'] == 2 ? 'color: #16A34A; font-weight:bold;' : '' ?>"><?= htmlspecialchars($q['choice_3'] ?? '') ?></span><br>
                        D) <span style="<?= $q['correct_answer'] == 3 ? 'color: #16A34A; font-weight:bold;' : '' ?>"><?= htmlspecialchars($q['choice_4'] ?? '') ?></span>
                    </div>
                </td>
                <td style="padding: 10px; display: flex; gap: 5px;">
                    <button type="button" class="btn btn-sm" style="background:#F59E0B; color:white; border:none; padding:6px 12px; cursor:pointer; border-radius:8px;" onclick='openEditModal(<?= htmlspecialchars(json_encode($q), ENT_QUOTES, "UTF-8") ?>)' title="Edit"><i class="fa-solid fa-pen"></i></button>
                    <form method="POST" onsubmit="return confirm('Delete this question?');">
                        <input type="hidden" name="delete_q" value="1">
                        <input type="hidden" name="q_id" value="<?= $q['id'] ?>">
                        <button type="submit" class="btn btn-sm" style="background:#EF4444; color:white; border:none; padding:6px 12px; cursor:pointer; border-radius:8px;" title="Delete"><i class="fa-solid fa-trash"></i></button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if(empty($questions)): ?>
    <div id="emptyState" style="text-align: center; padding: 40px; color: var(--text-muted);">
        <i class="fa-solid fa-folder-open fa-3x" style="color: #CBD5E1; margin-bottom: 15px;"></i>
        <p>No questions yet. Click the "Generate Questions with AI" button above to get started!</p>
    </div>
    <?php endif; ?>
</div>

<!-- Include PDF.js for reading PDF files -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
<script>
// Set worker source for PDF.js
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

let currentAiMode = 'auto';

function switchAITab(mode) {
    currentAiMode = mode;
    document.getElementById('aiModeAuto').style.display = mode === 'auto' ? 'block' : 'none';
    document.getElementById('aiModeCustom').style.display = mode === 'custom' ? 'block' : 'none';
    
    if (mode === 'auto') {
        document.getElementById('tabAuto').className = 'btn btn-primary';
        document.getElementById('tabCustom').className = 'btn btn-outline';
    } else {
        document.getElementById('tabAuto').className = 'btn btn-outline';
        document.getElementById('tabCustom').className = 'btn btn-primary';
    }
}

async function loadTextFile(input, targetId) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const target = document.getElementById(targetId);
        
        target.value = "Reading file... Please wait.";
        target.disabled = true;

        if (file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) {
            // Read PDF
            try {
                const arrayBuffer = await file.arrayBuffer();
                const pdf = await pdfjsLib.getDocument(arrayBuffer).promise;
                let fullText = '';
                
                for (let i = 1; i <= pdf.numPages; i++) {
                    const page = await pdf.getPage(i);
                    const textContent = await page.getTextContent();
                    const pageText = textContent.items.map(item => item.str).join(' ');
                    fullText += pageText + '\n\n';
                }
                
                target.value = fullText;
            } catch (err) {
                target.value = "Error reading PDF: " + err.message;
            }
        } else {
            // Read TXT or CSV
            const reader = new FileReader();
            reader.onload = function(e) {
                target.value = e.target.result;
            };
            reader.readAsText(file);
        }
        
        target.disabled = false;
    }
}

function openAIModal() {
    const target = <?= $exam['total_questions'] ?>;
    const current = <?= $current_count ?>;
    const needed = target - current;
    
    if (needed <= 0) {
        alert('You already have enough questions for this exam!');
        return;
    }
    
    document.getElementById('neededAuto').innerText = needed;
    
    const modal = document.getElementById('aiModalOverlay');
    modal.style.display = 'flex';
    setTimeout(() => modal.children[0].style.transform = 'scale(1)', 10);
}

function closeAIModal() {
    const modal = document.getElementById('aiModalOverlay');
    modal.children[0].style.transform = 'scale(0.95)';
    setTimeout(() => modal.style.display = 'none', 200);
}

function openManualModal() {
    const modal = document.getElementById('manualModalOverlay');
    modal.style.display = 'flex';
    setTimeout(() => modal.children[0].style.transform = 'scale(1)', 10);
    
    // Restore context if requested previously
    if (sessionStorage.getItem('keepContext') === 'true') {
        document.getElementById('keepContext').checked = true;
        document.getElementById('manualPassage').value = sessionStorage.getItem('savedPassage') || '';
        document.getElementById('existingImagePath').value = sessionStorage.getItem('savedImage') || '';
        if (sessionStorage.getItem('savedImage')) {
            document.getElementById('imageStatus').style.display = 'block';
        }
    } else {
        document.getElementById('manualPassage').value = '';
        document.getElementById('existingImagePath').value = '';
        document.getElementById('imageStatus').style.display = 'none';
        document.getElementById('keepContext').checked = false;
    }
}

function closeManualModal() {
    const modal = document.getElementById('manualModalOverlay');
    modal.children[0].style.transform = 'scale(0.95)';
    setTimeout(() => modal.style.display = 'none', 200);
}

function handleManualSubmit(e) {
    if (document.getElementById('keepContext').checked) {
        sessionStorage.setItem('keepContext', 'true');
        sessionStorage.setItem('savedPassage', document.getElementById('manualPassage').value);
        // We can't save the actual file through sessionStorage, but if they upload a new file, it will overwrite in PHP.
        // For simplicity, we just keep the passage text. If they need to keep the image, we rely on the PHP response to pass it back.
        // Since it reloads, PHP doesn't pass it back yet. I will set a cookie in PHP and read it here!
    } else {
        sessionStorage.removeItem('keepContext');
        sessionStorage.removeItem('savedPassage');
        sessionStorage.removeItem('savedImage');
    }
    // Let the form submit normally
}

function addQuestionBlock() {
    const container = document.getElementById('manualQuestionsContainer');
    const blocks = container.getElementsByClassName('manual-question-block');
    const newCount = blocks.length + 1;
    
    const block = document.createElement('div');
    block.className = 'manual-question-block';
    block.style.cssText = 'background: white; border: 1px solid #E2E8F0; border-radius: 8px; padding: 15px; margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);';
    block.innerHTML = `
        <div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
            <label style="font-weight:600; font-size: 1rem; color: #334155;">Question ${newCount} <span style="color:red;">*</span></label>
            <button type="button" class="remove-q-btn" onclick="removeQuestionBlock(this)" style="color: #EF4444; background: none; border: none; cursor: pointer; font-size: 0.9rem;"><i class="fa-solid fa-trash"></i> Remove</button>
        </div>
        <textarea name="question_text[]" rows="3" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #CBD5E1; font-family:inherit; font-size:0.9rem; margin-bottom: 15px;" placeholder="Enter your question here..."></textarea>
        
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
            <div>
                <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Choice A <span style="color:red;">*</span></label>
                <input type="text" name="choice_a[]" required style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1; font-size:0.85rem;">
            </div>
            <div>
                <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Choice B <span style="color:red;">*</span></label>
                <input type="text" name="choice_b[]" required style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1; font-size:0.85rem;">
            </div>
            <div>
                <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Choice C</label>
                <input type="text" name="choice_c[]" style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1; font-size:0.85rem;">
            </div>
            <div>
                <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Choice D</label>
                <input type="text" name="choice_d[]" style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1; font-size:0.85rem;">
            </div>
        </div>

        <div>
            <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Correct Answer <span style="color:red;">*</span></label>
            <select name="correct_answer[]" required style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1; background:white; font-size:0.85rem;">
                <option value="0">Choice A</option>
                <option value="1">Choice B</option>
                <option value="2">Choice C</option>
                <option value="3">Choice D</option>
            </select>
        </div>
    `;
    container.appendChild(block);
    updateQuestionLabels();
}

function removeQuestionBlock(btn) {
    btn.closest('.manual-question-block').remove();
    updateQuestionLabels();
}

function updateQuestionLabels() {
    const blocks = document.getElementById('manualQuestionsContainer').getElementsByClassName('manual-question-block');
    for (let i = 0; i < blocks.length; i++) {
        const label = blocks[i].querySelector('label');
        label.innerHTML = `Question ${i + 1} <span style="color:red;">*</span>`;
        const removeBtn = blocks[i].querySelector('.remove-q-btn');
        if (i === 0 && blocks.length === 1) {
            removeBtn.style.display = 'none';
        } else {
            removeBtn.style.display = 'inline-block';
        }
    }
}

async function generateWithAI() {
    const target = <?= $exam['total_questions'] ?>;
    const current = <?= $current_count ?>;
    const needed = target - current;
    
    let source_questions = '';
    let source_answers = '';
    
    if (currentAiMode === 'custom') {
        source_questions = document.getElementById('sourceQuestions').value.trim();
        source_answers = document.getElementById('sourceAnswers').value.trim();
        if (!source_questions) {
            alert('Please paste or upload the questions source text.');
            return;
        }
    }

    if (!confirm(`Are you sure you want AI to generate ${needed} questions?`)) {
        return;
    }
    
    closeAIModal();

    const aiBtn = document.getElementById('aiBtn');
    const loading = document.getElementById('loadingIndicator');
    const table = document.getElementById('questionsTable');
    const emptyState = document.getElementById('emptyState');

    // Update UI
    aiBtn.disabled = true;
    loading.style.display = 'block';
    if(emptyState) emptyState.style.display = 'none';
    table.style.display = 'none';

    try {
        if (currentAiMode === 'custom' && needed > 20) {
            // Batch processing for Custom mode
            const batchSize = 20;
            let totalProcessed = 0;
            
            for (let start = 1; start <= needed; start += batchSize) {
                let end = Math.min(start + batchSize - 1, needed);
                let currentCount = end - start + 1;
                
                aiBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Generating Batch ${start}-${end}...`;
                
                const formData = new FormData();
                formData.append('exam_id', <?= (int)$exam_id ?>);
                formData.append('topic', <?= json_encode($exam['title']) ?>);
                formData.append('subject', <?= json_encode($exam['subject']) ?>);
                formData.append('level', <?= json_encode($exam['level']) ?>);
                formData.append('count', currentCount);
                formData.append('source_questions', source_questions);
                formData.append('source_answers', source_answers);
                formData.append('mode', currentAiMode);
                formData.append('start_q', start);
                formData.append('end_q', end);

                const response = await fetch('ajax/generate_ai_questions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if (!result.success) {
                    throw new Error(result.error || 'Unknown error in batch');
                }
                totalProcessed += currentCount;
            }
            alert(`Successfully generated all ${totalProcessed} questions in batches!`);
            window.location.reload();
            
        } else {
            // Standard processing
            aiBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
            
            const formData = new FormData();
            formData.append('exam_id', <?= (int)$exam_id ?>);
            formData.append('topic', <?= json_encode($exam['title']) ?>);
            formData.append('subject', <?= json_encode($exam['subject']) ?>);
            formData.append('level', <?= json_encode($exam['level']) ?>);
            formData.append('count', needed);
            formData.append('source_questions', source_questions);
            formData.append('source_answers', source_answers);
            formData.append('mode', currentAiMode);

            const response = await fetch('ajax/generate_ai_questions.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert('Questions successfully generated and saved!');
                window.location.reload();
            } else {
                alert('Failed: ' + (result.error || 'Unknown error'));
                resetUI();
            }
        }
    } catch(e) {
        alert('Error: ' + e.message);
        console.error(e);
        resetUI();
    }
}

function resetUI() {
    document.getElementById('aiBtn').disabled = false;
    document.getElementById('aiBtn').innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> Generate Questions with AI';
    document.getElementById('loadingIndicator').style.display = 'none';
    document.getElementById('questionsTable').style.display = <?= empty($questions) ? "'none'" : "'table'" ?>;
    if(document.getElementById('emptyState')) document.getElementById('emptyState').style.display = 'block';
}
function openEditModal(q) {
    const modal = document.getElementById('editModalOverlay');
    document.getElementById('edit_q_id').value = q.id;
    document.getElementById('edit_passage_text').value = q.passage_text || '';
    
    document.getElementById('existing_edit_image_path').value = q.image_path || '';
    if (q.image_path) {
        document.getElementById('editImageStatus').style.display = 'block';
    } else {
        document.getElementById('editImageStatus').style.display = 'none';
    }
    
    document.getElementById('edit_question_text').value = q.question_text || '';
    document.getElementById('edit_choice_a').value = q.choice_1 || '';
    document.getElementById('edit_choice_b').value = q.choice_2 || '';
    document.getElementById('edit_choice_c').value = q.choice_3 || '';
    document.getElementById('edit_choice_d').value = q.choice_4 || '';
    document.getElementById('edit_correct_answer').value = q.correct_answer || '0';
    
    modal.style.display = 'flex';
    setTimeout(() => modal.children[0].style.transform = 'scale(1)', 10);
}

function closeEditModal() {
    const modal = document.getElementById('editModalOverlay');
    modal.children[0].style.transform = 'scale(0.95)';
    setTimeout(() => modal.style.display = 'none', 200);
}
</script>

<!-- Modal for Editing Question -->
<div id="editModalOverlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: white; border-radius: 16px; width: 100%; max-width: 600px; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); transform: scale(0.95); transition: transform 0.2s; max-height: 90vh; overflow-y: auto;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="margin: 0; font-size: 1.25rem;"><i class="fa-solid fa-pen" style="color:var(--primary);"></i> Edit Question</h2>
            <button onclick="closeEditModal()" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="edit_q" value="1">
            <input type="hidden" name="edit_q_id" id="edit_q_id" value="">
            <input type="hidden" name="existing_edit_image_path" id="existing_edit_image_path" value="">

            <div style="margin-bottom: 15px;">
                <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.9rem;">Passage / Shared Context (Optional)</label>
                <textarea name="edit_passage_text" id="edit_passage_text" rows="2" style="width:100%; padding:10px; border-radius:8px; border:1px solid #CBD5E1; font-family:inherit;"></textarea>
            </div>
            
            <div style="margin-bottom: 15px; border: 1px dashed #CBD5E1; padding: 15px; border-radius: 8px; background: #F8FAFC;">
                <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.9rem;">Attached Image (Optional)</label>
                <input type="file" name="edit_image_file" accept="image/*" style="width:100%; font-size:0.9rem; margin-bottom: 5px;">
                <div id="editImageStatus" style="display:none; margin-top:5px; font-size:0.85rem; color:#166534; font-weight:600;">
                    <i class="fa-solid fa-circle-check"></i> Image is currently attached. <label style="margin-left:10px; color:#EF4444;"><input type="checkbox" name="remove_image" value="1"> Remove image</label>
                </div>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display:block; font-weight:600; margin-bottom:5px; font-size: 0.9rem;">Question Text <span style="color:red;">*</span></label>
                <textarea name="edit_question_text" id="edit_question_text" rows="3" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #CBD5E1; font-family:inherit;"></textarea>
            </div>
            
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Choice A <span style="color:red;">*</span></label>
                    <input type="text" name="edit_choice_a" id="edit_choice_a" required style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1;">
                </div>
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Choice B <span style="color:red;">*</span></label>
                    <input type="text" name="edit_choice_b" id="edit_choice_b" required style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1;">
                </div>
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Choice C</label>
                    <input type="text" name="edit_choice_c" id="edit_choice_c" style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1;">
                </div>
                <div>
                    <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.85rem;">Choice D</label>
                    <input type="text" name="edit_choice_d" id="edit_choice_d" style="width:100%; padding:8px; border-radius:6px; border:1px solid #CBD5E1;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display:block; font-weight:600; margin-bottom:5px; font-size:0.9rem;">Correct Answer <span style="color:red;">*</span></label>
                <select name="edit_correct_answer" id="edit_correct_answer" style="width:100%; padding:10px; border-radius:8px; border:1px solid #CBD5E1; background:white;">
                    <option value="0">Choice A</option>
                    <option value="1">Choice B</option>
                    <option value="2">Choice C</option>
                    <option value="3">Choice D</option>
                </select>
            </div>

            <div style="text-align: right;">
                <button type="button" onclick="closeEditModal()" class="btn btn-outline" style="margin-right: 10px;">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
