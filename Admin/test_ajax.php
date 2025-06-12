<?php
// Simple test file to check AJAX functionality
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'message' => 'AJAX is working',
    'timestamp' => date('Y-m-d H:i:s')
]);
?>