<?php
// public/index.php - VERCEL COMPATIBLE

// ==================================================
// DETECT ENVIRONMENT
// ==================================================
$isVercel = getenv('VERCEL') === '1' || isset($_ENV['VERCEL']);

if ($isVercel) {
    // Vercel specific settings
    ini_set('session.save_handler', 'files');
    ini_set('session.save_path', sys_get_temp_dir());
    
    error_log("=== VERCELL MODE ===");
} else {
    error_log("=== LOCAL MODE ===");
}

// ==================================================
// START SESSION (MUST BE BEFORE ANY OUTPUT)
// ==================================================
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ==================================================
// SET BASE URL FOR VERCELL
// ==================================================
if (!isset($base_url)) {
    if ($isVercel) {
        $base_url = 'https://' . $_SERVER['HTTP_HOST'];
    } else {
        $base_url = 'http://' . $_SERVER['HTTP_HOST'];
    }
}

// ==================================================
// ROUTING - SIMPLE VERSION FOR VERCELL
// ==================================================
$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// Handle login first
if ($page === 'login') {
    if (isset($_SESSION['user_id'])) {
        header('Location: index.php?page=dashboard');
        exit;
    }
    require_once __DIR__ . '/../views/login.php';
    exit;
}

// Auth check for other pages
if (!isset($_SESSION['user_id']) && $page !== 'login') {
    header('Location: index.php?page=login');
    exit;
}

// Handle actions
if ($action === 'login') {
    require_once __DIR__ . '/../controllers/AuthController.php';
    (new AuthController())->login();
    exit;
}

if ($action === 'createTransaksi') {
    require_once __DIR__ . '/../controllers/TransaksiController.php';
    (new TransaksiController())->create();
    exit;
}

if ($action === 'checkoutTransaksi') {
    require_once __DIR__ . '/../controllers/TransaksiController.php';
    (new TransaksiController())->checkout();
    exit;
}

// Handle pages
switch ($page) {
    case 'dashboard':
        require_once __DIR__ . '/../views/dashboard.php';
        break;
        
    case 'transaksi':
        require_once __DIR__ . '/../controllers/TransaksiController.php';
        (new TransaksiController())->index();
        break;
        
    case 'pemilik':
    case 'pelanggan':
        require_once __DIR__ . '/../controllers/PelangganController.php';
        $controller = new PelangganController();
        
        $action = $_GET['action'] ?? 'index';
        $id = $_GET['id'] ?? null;
        
        if ($action === 'create') $controller->create();
        elseif ($action === 'edit' && $id) $controller->edit($id);
        elseif ($action === 'delete' && $id) $controller->delete($id);
        else $controller->index();
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
        
    case 'logout':
        session_destroy();
        header('Location: index.php?page=login');
        break;
        
    default:
        require_once __DIR__ . '/../views/404.php';
        break;
}
?>