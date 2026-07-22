<?php
// ============================================================
// LinguaMax — API: Pronunciation Practice (OpenRouter + Deepgram)
// ============================================================
session_start();
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$audioBase64 = $data['audio'] ?? '';
$targetWord = $data['target_word'] ?? '';

if (empty($audioBase64)) {
    echo json_encode(['success' => false, 'error' => 'No audio data received']);
    exit;
}

// Extract base64 data
$parts = explode(',', $audioBase64);
$base64Data = count($parts) === 2 ? $parts[1] : $audioBase64;

// Load API Key from global settings (or config)
$settingsPath = __DIR__ . '/../includes/global_settings.json';
$openRouterKey = '';
if (file_exists($settingsPath)) {
    $settings = json_decode(file_get_contents($settingsPath), true);
    $openRouterKey = $settings['openrouter_api_key'] ?? '';
}

// --------------------------------------------------------
// MOCK FALLBACK if no API key is provided
// --------------------------------------------------------
if (empty($openRouterKey)) {
    // Simulate a slight delay
    usleep(1000000); // 1 second
    
    // Simulate a mock transcription and score calculation
    $mockTranscription = (rand(0, 100) > 30) ? $targetWord : "yellow"; 
    
    $targetClean = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $targetWord)));
    $transcriptionClean = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $mockTranscription)));
    
    $score = 0;
    if ($targetClean === $transcriptionClean) {
        $score = rand(85, 100);
    } else {
        similar_text($targetClean, $transcriptionClean, $percent);
        $score = round($percent);
        if ($score < 20) $score = rand(20, 50); // Give some pity points
    }

    echo json_encode([
        'success' => true,
        'transcription' => $mockTranscription . " (Mocked - No API Key)",
        'score' => $score
    ]);
    exit;
}

// --------------------------------------------------------
// OPENROUTER / DEEPGRAM INTEGRATION
// --------------------------------------------------------

$openRouterUrl = "https://openrouter.ai/api/v1/audio/transcriptions";

// Save base64 to a temporary file
$tempFile = sys_get_temp_dir() . '/' . uniqid('audio_') . '.webm';
file_put_contents($tempFile, base64_decode($base64Data));

// Create a CURLFile object
$cfile = new CURLFile($tempFile, 'audio/webm', 'audio.webm');

$payload = [
    "file" => $cfile,
    "model" => "deepgram/nova-3",
    "language" => "en" // Suggesting English for better accuracy
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $openRouterUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer " . $openRouterKey,
    "HTTP-Referer: " . SITE_URL,
    "X-Title: LinguaMax"
]);

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

// Clean up temp file
@unlink($tempFile);

if ($err) {
    echo json_encode(['success' => false, 'error' => 'cURL Error: ' . $err]);
    exit;
}

$responseData = json_decode($response, true);

if (isset($responseData['error'])) {
    echo json_encode(['success' => false, 'error' => 'OpenRouter API Error: ' . ($responseData['error']['message'] ?? 'Unknown Error')]);
    exit;
}

$transcription = $responseData['text'] ?? '';
$transcription = trim($transcription);

if (empty($transcription)) {
    echo json_encode(['success' => false, 'error' => 'Could not transcribe audio.']);
    exit;
}

// Very basic scoring algorithm: Check similarity
$targetClean = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $targetWord)));
$transcriptionClean = strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $transcription)));

$score = 0;
if ($targetClean === $transcriptionClean) {
    // Perfect match
    $score = rand(95, 100);
} else {
    // Use similar_text for a basic percentage score
    similar_text($targetClean, $transcriptionClean, $percent);
    $score = round($percent);
    if ($score < 10) $score = rand(10, 30);
}

echo json_encode([
    'success' => true,
    'transcription' => $transcription,
    'score' => $score
]);
