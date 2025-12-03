<?php
// api/test.php - Simple API test
header('Content-Type: application/json');
echo json_encode([
    'status' => 'ok',
    'message' => 'API is working',
    'time' => date('Y-m-d H:i:s'),
    'session' => session_id()
]);
?>