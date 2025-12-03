<?php
// api/index.php - Vercel Entry Point

// ==================================================
// ✅ SET VERCEL ENVIRONMENT SEBELUM APA PUN
// ==================================================
putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';

// ==================================================
// ✅ CORS & HEADERS - HARUS PERTAMA
// ==================================================
$origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '*';
header('Access-Control-Allow-Origin: ' . $origin);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ==================================================
// ✅ FIX SESSION FOR VERCEL - SEBELUM session_start()
// ==================================================
$isVercel = getenv('VERCEL') === '1' || isset($_ENV['VERCEL']);

if ($isVercel) {
    // ✅ Gunakan ini untuk Vercel
    ini_set('session.save_handler', 'files');
    ini_set('session.save_path', sys_get_temp_dir());
    
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None'
    ]);
}

// ==================================================
// ✅ START SESSION
// ==================================================
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ==================================================
// FIX REQUEST URI & SCRIPT NAME
// ==================================================
// Simulate direct access to public/index.php
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Fix untuk assets yang masih pakai path lama
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
if (strpos($requestUri, '/css/') === 0 || 
    strpos($requestUri, '/js/') === 0 || 
    strpos($requestUri, '/img/') === 0) {
    
    $publicPath = __DIR__ . '/../public' . $requestUri;
    if (file_exists($publicPath)) {
        $extension = pathinfo($requestUri, PATHINFO_EXTENSION);
        
        if ($extension === 'css') {
            header('Content-Type: text/css');
        } elseif ($extension === 'js') {
            header('Content-Type: application/javascript');
        } elseif ($extension === 'png') {
            header('Content-Type: image/png');
        } elseif ($extension === 'jpg' || $extension === 'jpeg') {
            header('Content-Type: image/jpeg');
        } elseif ($extension === 'svg') {
            header('Content-Type: image/svg+xml');
        }
        
        readfile($publicPath);
        exit;
    }
}

// ==================================================
// INCLUDE MAIN APPLICATION
// ==================================================
require_once __DIR__ . '/../public/index.php';
?>