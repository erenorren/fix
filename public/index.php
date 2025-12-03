<?php
// public/index.php

// ==================================================
// ✅ HAPUS SEMUA OUTPUT/WHITESPACE SEBELUM PHP TAG
// ==================================================

// ==================================================
// TURN OFF ERROR DISPLAY IN PRODUCTION (UNTUK VERCELL)
// ==================================================
$isVercel = isset($_ENV['VERCEL']) || getenv('VERCEL') === '1';

if ($isVercel) {
    // ✅ HAPUS SEMUA ERROR OUTPUT DI PRODUCTION
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// ==================================================
// ✅ SET COOKIE PARAMS SEBELUM SESSION START
// ==================================================
if ($isVercel && session_status() == PHP_SESSION_NONE) {
    // ✅ HARUS sebelum session_start()
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

// ====================================================
// ✅ AUTH MIDDLEWARE - SIMPLIFIED
// ====================================================
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// ✅ Public pages (no auth required)
$publicPages = ['login', 'logout', '404'];

// Check authentication
if (!in_array($page, $publicPages) && empty($_SESSION['user_id'])) {
    // For AJAX requests
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized', 'redirect' => 'index.php?page=login']);
        exit;
    }
    
    // For normal requests
    header('Location: index.php?page=login');
    exit;
}

// If already logged in and trying to access login page
if ($page === 'login' && !empty($_SESSION['user_id'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

// ==================================================
// ✅ ROUTING - NO OUTPUT BEFORE THIS POINT
// ==================================================
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// Handle API/action routes
if ($action) {
    switch ($action) {
        case 'login':
            require_once __DIR__ . '/../controllers/AuthController.php';
            (new AuthController())->login();
            break;
            
        case 'logout':
            require_once __DIR__ . '/../controllers/AuthController.php';
            (new AuthController())->logout();
            break;
            
        case 'searchPelanggan':
            require_once __DIR__ . '/../models/Pelanggan.php';
            $model = new Pelanggan();
            $keyword = $_GET['q'] ?? '';
            $results = $model->search($keyword);
            header('Content-Type: application/json');
            echo json_encode($results);
            exit;
            
        case 'getKandangTersedia':
            require_once __DIR__ . '/../models/Kandang.php';
            $model = new Kandang();
            $jenis = $_GET['jenis'] ?? '';
            $ukuran = $_GET['ukuran'] ?? '';
            $results = $model->getAvailable($jenis, $ukuran);
            header('Content-Type: application/json');
            echo json_encode($results);
            exit;
            
        case 'createTransaksi':
            require_once __DIR__ . '/../controllers/TransaksiController.php';
            (new TransaksiController())->create();
            break;
            
        case 'checkoutTransaksi':
            require_once __DIR__ . '/../controllers/TransaksiController.php';
            (new TransaksiController())->checkout();
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found']);
            exit;
    }
    exit;
}

// Handle page routes
switch ($page) {
    case 'login':
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        require_once __DIR__ . '/../views/login.php';
        break;

    case 'dashboard':
        require_once __DIR__ . '/../views/dashboard.php';
        break;
        
    case 'transaksi':
        require_once __DIR__ . '/../controllers/TransaksiController.php';
        (new TransaksiController())->index();
        break;
        
    case 'logout':
        session_destroy();
        header('Location: index.php?page=login');
        exit;
        
    case 'hewan':
        require_once __DIR__ . '/../views/hewan.php';
        break;
        
    case 'kandang':
        require_once __DIR__ . '/../views/kandang.php';
        break;
        
    case 'layanan':
        require_once __DIR__ . '/../views/layanan.php';
        break;
        
    case 'pemilik':
        require_once __DIR__ . '/../views/pelanggan.php';
        break;
        
    case 'laporan':
        require_once __DIR__ . '/../views/laporan.php';
        break;
        
    default:
        require_once __DIR__ . '/../views/404.php';
        break;
}
?>