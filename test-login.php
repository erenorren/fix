<?php
// test-login.php - Untuk test login API saja
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Simple test response
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    echo json_encode([
        'success' => true,
        'message' => 'Test API working',
        'received' => $data,
        'server' => [
            'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
            'CONTENT_TYPE' => $_SERVER['CONTENT_TYPE'] ?? 'none'
        ]
    ]);
    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'Use POST method',
    'endpoint' => 'test-login.php'
]);
?>