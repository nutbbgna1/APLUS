<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Admin Auth Check
if (!isset($_SESSION['admin_logged_in'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/config.php';
$db = getDB();

try {
    $stmt = $db->query("SELECT setting_value FROM system_settings WHERE setting_key = 'openrouter_api_key'");
    $apiKey = $stmt->fetchColumn();
} catch (Exception $e) {
    $apiKey = '';
}

if (empty($apiKey)) {
    echo json_encode(['success' => false, 'error' => 'OpenRouter API Key is missing. Please add it in AI API Settings']);
    exit;
}

// Try reading from $_POST first (FormData)
$input = $_POST;

// Fallback to JSON payload if $_POST is empty
if (empty($input)) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true) ?: [];
}

if (empty($input['exam_id'])) {
    $cl = $_SERVER['CONTENT_LENGTH'] ?? 'none';
    $pms = ini_get('post_max_size');
    $method = $_SERVER['REQUEST_METHOD'] ?? 'unknown';
    echo json_encode(['success' => false, 'error' => "Invalid input. Request may be too large. CL: $cl | Max: $pms | Method: $method"]);
    exit;
}

$exam_id = (int)$input['exam_id'];
$topic = $input['topic'] ?? 'General Knowledge';
$subject = $input['subject'] ?? 'English';
$level = $input['level'] ?? 'Beginner';
$count = intval($input['count'] ?? 10);
$mode = $input['mode'] ?? 'auto';
$source_questions = $input['source_questions'] ?? '';
$source_answers = $input['source_answers'] ?? '';
$start_q = isset($input['start_q']) ? (int)$input['start_q'] : null;
$end_q = isset($input['end_q']) ? (int)$input['end_q'] : null;

if ($count <= 0) {
    echo json_encode(['success' => false, 'error' => 'Count must be greater than 0']);
    exit;
}

// 1. Call OpenRouter API
if ($mode === 'custom' && !empty($source_questions)) {
    if ($start_q !== null && $end_q !== null) {
        $prompt = "You are given a text containing an exam. Extract EXACTLY the questions numbered from {$start_q} to {$end_q} (inclusive) into the specified JSON format. 
There should be exactly {$count} questions. Do NOT extract any questions outside this range.
Match the questions with their correct answers from the text or answer key.

Source Text (Questions and Answers):
{$source_questions}";
        if (!empty($source_answers)) {
            $prompt .= "\n\nSource Answers Key:\n{$source_answers}";
        }
        $prompt .= "\n\nReturn ONLY a valid JSON array of objects. Do not include markdown code blocks, just raw JSON.\nEach object MUST have these exact keys:";
    } else {
        $prompt = "You are given a text containing exam questions and optionally another text with the answer key.
Your task is to parse these source texts, match the questions with their correct answers, and format EXACTLY {$count} questions in Thai language.
If the source provides fewer questions than requested, format all available questions.

Source Questions:
{$source_questions}";
        if (!empty($source_answers)) {
            $prompt .= "\n\nSource Answers Key:\n{$source_answers}";
        }
        $prompt .= "\n\nReturn ONLY a valid JSON array of objects. Do not include markdown code blocks, just raw JSON.\nEach object MUST have these exact keys:";
    }
} else {
    $prompt = "Generate exactly {$count} multiple choice questions in Thai language for the topic '{$topic}', subject '{$subject}', at '{$level}' level. 
Return ONLY a valid JSON array of objects. Do not include markdown code blocks, just raw JSON.
Each object MUST have these exact keys:";
}

$prompt .= "
- 'passage_text': If the question relies on a reading passage, a long conversation, a news report, or any shared context, put the ENTIRE shared context here. If it is a standalone question with no shared context, leave this as an empty string. Keep the formatting of the conversation/passage intact.
- 'question_text': The question string
- 'choice_a': First choice
- 'choice_b': Second choice
- 'choice_c': Third choice
- 'choice_d': Fourth choice
- 'correct_answer': Integer from 0 to 3 (0=a, 1=b, 2=c, 3=d)";

$ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
$data = [
    'model' => 'openai/gpt-4o-mini-2024-07-18',
    'messages' => [
        ['role' => 'system', 'content' => 'You are a helpful AI that generates educational quiz questions in valid JSON format.'],
        ['role' => 'user', 'content' => $prompt]
    ],
    'response_format' => ['type' => 'json_object']
];

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer {$apiKey}",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(['success' => false, 'error' => 'cURL Error: ' . $err]);
    exit;
}

$resData = json_decode($response, true);
if (isset($resData['error'])) {
    echo json_encode(['success' => false, 'error' => 'API Error: ' . ($resData['error']['message'] ?? json_encode($resData['error']))]);
    exit;
}

$content = $resData['choices'][0]['message']['content'] ?? '';

// Try to clean up markdown if the AI includes it despite instructions
$content = preg_replace('/```json/i', '', $content);
$content = preg_replace('/```/', '', $content);
$content = trim($content);

$parsedJSON = json_decode($content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    if(strpos($content, '{') === 0) {
        $attempt2 = json_decode($content, true);
        if(isset($attempt2['questions'])) {
            $parsedJSON = $attempt2['questions'];
        } else {
             echo json_encode(['success' => false, 'error' => 'Failed to parse JSON', 'raw' => $content]);
             exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to parse JSON', 'raw' => $content]);
        exit;
    }
}

if (!is_array($parsedJSON) || empty($parsedJSON)) {
    echo json_encode(['success' => false, 'error' => 'AI returned empty or invalid data array']);
    exit;
}

// 2. Insert into Database
try {
    $db->beginTransaction();
    $stmt = $db->prepare("INSERT INTO exam_questions (exam_id, passage_text, question_text, choice_1, choice_2, choice_3, choice_4, correct_answer) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    $insertedCount = 0;
    foreach ($parsedJSON as $q) {
        if (!isset($q['question_text'])) continue;
        
        $stmt->execute([
            $exam_id,
            $q['passage_text'] ?? '',
            $q['question_text'],
            $q['choice_a'] ?? '',
            $q['choice_b'] ?? '',
            $q['choice_c'] ?? '',
            $q['choice_d'] ?? '',
            intval($q['correct_answer'] ?? 0)
        ]);
        $insertedCount++;
        
        // Stop if we reached the requested count (just in case AI generated more)
        if ($insertedCount >= $count) break;
    }
    
    $db->commit();
    echo json_encode(['success' => true, 'inserted' => $insertedCount]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'error' => 'DB Error: ' . $e->getMessage()]);
}
