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
// FIX SESSION FOR VERCEL
// ==================================================
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => parse_url($origin, PHP_URL_HOST),
    'secure' => true,
    'httponly' => true,
    'samesite' => 'None'
]);

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
    // Assets sudah dihandle oleh vercel.json, tapi masuk ke sini
    // Redirect ke public folder
    $publicPath = __DIR__ . '/../public' . $requestUri;
    if (file_exists($publicPath)) {
        if (strpos($requestUri, '.css')) {
            header('Content-Type: text/css');
        } elseif (strpos($requestUri, '.js')) {
            header('Content-Type: application/javascript');
        } elseif (strpos($requestUri, '.png')) {
            header('Content-Type: image/png');
        } elseif (strpos($requestUri, '.jpg') || strpos($requestUri, '.jpeg')) {
            header('Content-Type: image/jpeg');
        }
        readfile($publicPath);
        exit;
    }
}

// ==================================================
// START SESSION
// ==================================================
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ==================================================
// INCLUDE MAIN APPLICATION
// ==================================================
require_once __DIR__ . '/../public/index.php';
?>