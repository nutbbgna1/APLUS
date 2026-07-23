<?php
$raw = file_get_contents('php://input');
echo "Content-Length: " . ($_SERVER['CONTENT_LENGTH'] ?? 'none') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "Raw length: " . strlen($raw) . "\n";
