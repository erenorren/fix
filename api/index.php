<?php
// api/index.php - Vercel Entry Point

// ==================================================
// ✅ SET VERCEL ENVIRONMENT FIRST
// ==================================================
putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';

// ==================================================
// ✅ SUPPRESS ERRORS FOR PRODUCTION
// ==================================================
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(0);

// ==================================================
// ✅ SET SESSION COOKIE PARAMS BEFORE ANY OUTPUT
// ==================================================
if (session_status() == PHP_SESSION_NONE) {
    // ✅ HARUS SEBELUM session_start()
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None'
    ]);
}

// ==================================================
// ✅ SET HEADERS - MUST BE BEFORE ANY OUTPUT
// ==================================================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ==================================================
// ✅ START SESSION
// ==================================================
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ==================================================
// ✅ HANDLE STATIC FILES
// ==================================================
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';

// Serve static files directly
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg)$/i', $requestUri)) {
    $publicFile = __DIR__ . '/../public' . $requestUri;
    
    if (file_exists($publicFile)) {
        $ext = strtolower(pathinfo($requestUri, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon'
        ];
        
        if (isset($mimeTypes[$ext])) {
            header('Content-Type: ' . $mimeTypes[$ext]);
        }
        
        readfile($publicFile);
        exit;
    }
}

// ==================================================
// ✅ INCLUDE MAIN APPLICATION
// ==================================================
require_once __DIR__ . '/../public/index.php';
?>