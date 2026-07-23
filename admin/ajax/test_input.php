<?php
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

// For debugging: write what we received to a file so we can see what the user sent
file_put_contents(__DIR__ . '/debug_input.txt', "RAW:\n" . $raw . "\n\nJSON_LAST_ERROR: " . json_last_error_msg());

if (!$input || empty($input['exam_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid input. Raw: ' . $raw . ' Error: ' . json_last_error_msg()]);
    exit;
}
