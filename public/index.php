<?php
// public/index.php - HARUS tanpa spasi sebelum <?php

// ==================================================
// 1. SESSION CONFIGURATION - PALING ATAS
// ==================================================
$isVercel = (isset($_SERVER['VERCEL']) || getenv('VERCEL') === '1');

if ($isVercel) {
    // Konfigurasi khusus Vercel
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'None'); // Coba None dulu
    
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None' // Untuk cross-domain
    ]);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug
error_log("=== SESSION STARTED ===");
error_log("Session ID: " . session_id());
error_log("Vercel Env: " . ($isVercel ? 'YES' : 'NO'));

// ==================================================
// 2. TURN OFF OUTPUT BUFFERING UNTUK DEBUG
// ==================================================
ob_start();

// ==================================================
// 3. ERROR REPORTING
// ==================================================
error_reporting(E_ALL);
ini_set('display_errors', 0); // Nonaktifkan di production
ini_set('log_errors', 1);

// ==================================================
// 4. AUTOLOAD
// ==================================================
require_once __DIR__ . '/../vendor/autoload.php';

// ==================================================
// 5. SIMPLE BASE URL
// ==================================================
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$base_url = $protocol . $_SERVER['HTTP_HOST'];
define('BASE_URL', $base_url);

// ==================================================
// 6. SIMPLE AUTH CHECK
// ==================================================
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? null;

// Cek session user
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

error_log("Page: $page, Logged in: " . ($isLoggedIn ? 'YES' : 'NO'));

// Public pages (bisa diakses tanpa login)
$publicPages = ['login'];

// Routing untuk action (API calls)
if ($action === 'login') {
    require_once __DIR__ . '/../controllers/AuthController.php';
    $auth = new AuthController();
    $auth->login();
    exit;
}

// Auth check
if (!$isLoggedIn && $page !== 'login') {
    // Jika belum login dan bukan halaman login
    header('Location: index.php?page=login');
    exit;
}

if ($isLoggedIn && $page === 'login') {
    // Jika sudah login tapi akses halaman login
    header('Location: index.php?page=dashboard');
    exit;
}

// ==================================================
// 7. SIMPLE ROUTING
// ==================================================
switch ($page) {
    case 'login':
        require_once __DIR__ . '/../views/login.php';
        break;
        
    case 'dashboard':
        $pageTitle = 'Dashboard';
        require_once __DIR__ . '/../views/template/header.php';
        require_once __DIR__ . '/../views/dashboard.php';
        require_once __DIR__ . '/../views/template/footer.php';
        break;
        
    case 'logout':
        session_destroy();
        setcookie(session_name(), '', time() - 3600, '/');
        header('Location: index.php?page=login');
        exit;
        
    default:
        $pageTitle = 'Dashboard';
        require_once __DIR__ . '/../views/template/header.php';
        require_once __DIR__ . '/../views/dashboard.php';
        require_once __DIR__ . '/../views/404.php';
        require_once __DIR__ . '/../views/template/footer.php';
        break;
    }
ob_end_flush();
?>