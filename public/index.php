<?php
// public/index.php - SIMPLIFIED VERSION

// ✅ Start session first
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ✅ Disable errors on Vercel
if (isset($_ENV['VERCEL'])) {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// ==================================================
// ✅ SIMPLE AUTH CHECK
// ==================================================
$page = $_GET['page'] ?? 'dashboard';

// Skip auth for login page
if ($page !== 'login' && empty($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// If already logged in, redirect from login
if ($page === 'login' && !empty($_SESSION['user_id'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

// ==================================================
// ✅ HANDLE API ACTIONS FIRST
// ==================================================
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if ($action === 'login') {
    require_once __DIR__ . '/../controllers/AuthController.php';
    $auth = new AuthController();
    $auth->login();
    exit; // IMPORTANT: Stop further execution
}

// ==================================================
// ✅ PAGE ROUTING
// ==================================================
switch ($page) {
    case 'login':
        require_once __DIR__ . '/../views/login.php';
        break;
        
    case 'dashboard':
        require_once __DIR__ . '/../views/dashboard.php';
        break;
        
    case 'transaksi':
        require_once __DIR__ . '/../views/transaksi.php';
        break;
        
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
        
    case 'logout':
        session_destroy();
        header('Location: index.php?page=login');
        break;
        
    default:
        require_once __DIR__ . '/../views/404.php';
        break;
}
?>