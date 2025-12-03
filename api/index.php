<?php
// api/index.php - Vercel Entry Point

// ==================================================
// DEBUG LOG
// ==================================================
error_log("=== API/INDEX.PHP ACCESSED ===");
error_log("REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'null'));
error_log("SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'null'));
error_log("QUERY_STRING: " . ($_SERVER['QUERY_STRING'] ?? 'null'));

// ==================================================
// SET VERCEL ENVIRONMENT
// ==================================================
putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';

// ==================================================
// CORS & HEADERS
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
// FIX SESSION COOKIE FOR VERCEL
// ==================================================
$isVercel = getenv('VERCEL') === '1' || isset($_ENV['VERCEL']);

if ($isVercel) {
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

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ✅ DEBUG: Log session info
error_log("Session ID: " . session_id());
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));

// ==================================================
// FIX FOR STATIC FILES (CSS/JS/IMG)
// ==================================================
$requestUri = $_SERVER['REQUEST_URI'] ?? '';

// ✅ Handle static files - langsung redirect ke public folder
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg)$/', $requestUri)) {
    $publicFile = __DIR__ . '/../public' . $requestUri;
    if (file_exists($publicFile)) {
        $extension = pathinfo($requestUri, PATHINFO_EXTENSION);
        $contentTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon'
        ];
        
        if (isset($contentTypes[$extension])) {
            header('Content-Type: ' . $contentTypes[$extension]);
        }
        
        readfile($publicFile);
        exit;
    }
}

// Continue to main app
require_once __DIR__ . '/../public/index.php';
?>