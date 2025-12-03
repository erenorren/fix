<?php
// public/index.php - HARUS TANPA SPASI SEBELUM <?php

// ==================================================
// SESSION HARUS DIATAS SEGALANYA
// ==================================================
$isVercel = isset($_SERVER['VERCEL']) || (isset($_ENV['VERCEL']) && $_ENV['VERCEL'] === '1');

// Set session configuration SEBELUM session_start()
if ($isVercel) {
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
}

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ==================================================
// DEBUG MODE
// ==================================================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==================================================
// AUTOLOAD
// ==================================================
require_once __DIR__ . '/../vendor/autoload.php';

// ==================================================
// BASE URL CONFIG
// ==================================================
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    
    // Jika di Vercel, path langsung dari root
    if (isset($_SERVER['VERCEL']) || (isset($_ENV['VERCEL']) && $_ENV['VERCEL'] === '1')) {
        return $protocol . $host;
    } else {
        // Localhost
        return $protocol . $host . '/public';
    }
}

define('BASE_URL', getBaseUrl());

// ==================================================
// SIMPLE AUTH CHECK
// ==================================================
$currentPage = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? null;

// Halaman yang boleh diakses tanpa login
$publicPages = ['login', 'logout'];

// Cek apakah user sudah login
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Logika autentikasi
if (!$isLoggedIn) {
    // Jika belum login
    if (!in_array($currentPage, $publicPages)) {
        // Redirect ke login jika mencoba akses halaman terproteksi
        header('Location: index.php?page=login');
        exit;
    }
} else {
    // Jika sudah login
    if ($currentPage === 'login') {
        // Redirect ke dashboard jika sudah login tapi mencoba akses login page
        header('Location: index.php?page=dashboard');
        exit;
    }
}

// ==================================================
// ROUTING UNTUK ACTION (API CALLS)
// ==================================================
if ($action) {
    switch ($action) {
        case 'login':
            require_once __DIR__ . '/../controllers/AuthController.php';
            $auth = new AuthController();
            $auth->login();
            exit;
            
        case 'logout':
            session_destroy();
            echo json_encode(['success' => true, 'redirect' => 'index.php?page=login']);
            exit;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found']);
            exit;
    }
}

// ==================================================
// ROUTING UNTUK PAGES
// ==================================================
switch ($currentPage) {
    case 'login':
        // Tampilkan halaman login
        require_once __DIR__ . '/../views/login.php';
        break;
        
    case 'dashboard':
        // Tampilkan dashboard
        $pageTitle = 'Dashboard';
        require_once __DIR__ . '/../views/template/header.php';
        require_once __DIR__ . '/../views/dashboard.php';
        require_once __DIR__ . '/../views/template/footer.php';
        break;
        
    case 'hewan':
        // Tampilkan halaman hewan
        $pageTitle = 'Data Hewan';
        require_once __DIR__ . '/../views/template/header.php';
        require_once __DIR__ . '/../views/hewan.php';
        require_once __DIR__ . '/../views/template/footer.php';
        break;
        
    case 'kandang':
        // Tampilkan halaman kandang
        $pageTitle = 'Data Kandang';
        require_once __DIR__ . '/../views/template/header.php';
        require_once __DIR__ . '/../views/kandang.php';
        require_once __DIR__ . '/../views/template/footer.php';
        break;
        
    case 'logout':
        // Logout dan redirect ke login
        session_destroy();
        header('Location: index.php?page=login');
        exit;
        
    default:
        // 404 Page
        $pageTitle = 'Page Not Found';
        require_once __DIR__ . '/../views/template/header.php';
        require_once __DIR__ . '/../views/404.php';
        require_once __DIR__ . '/../views/template/footer.php';
        break;
}