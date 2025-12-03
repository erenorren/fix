<?php
// ==================================================
// VERCEL CONFIGURATION
// ==================================================
$isVercel = isset($_ENV['VERCEL']) || getenv('VERCEL') === '1';

if ($isVercel) {
    // Debug
    error_log("=== PUBLIC/INDEX.PHP (Vercel Mode) ===");
    error_log("Session ID: " . (session_id() ?: 'NOT STARTED'));
    error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET'));
    
    // Ensure CORS headers
    if (!headers_sent()) {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '*';
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
    }
}
// ==================================================
// SESSION START
// ==================================================
if (session_status() == PHP_SESSION_NONE) {
    if ($isVercel) {
        session_set_cookie_params([
            'lifetime' => 86400,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'None'
        ]);
    }
    session_start();
}


// ==================================================
// AUTOLOAD
// ==================================================
require_once __DIR__ . '/../vendor/autoload.php';

// ==================================================
// ENVIRONMENT
// ==================================================
if (!$isVercel && file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// ====================================================
// AUTH MIDDLEWARE
// ====================================================
function checkAuth() {
    $publicPages = ['login', 'logout', '404'];
    $publicActions = ['login', 'searchPelanggan', 'getKandangTersedia'];
    
    $page = $_GET['page'] ?? 'dashboard';
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    
    // Skip auth untuk public pages/actions
    if (in_array($page, $publicPages) || in_array($action, $publicActions)) {
        return;
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
        // Untuk AJAX/API requests
        if ($action || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized', 'redirect' => 'index.php?page=login']);
            exit;
        }
        // Untuk browser requests
        header('Location: index.php?page=login');
        exit;
    }
}

checkAuth();

// ==================================================
// ROUTING
// ==================================================
$action = $_GET['action'] ?? $_POST['action'] ?? null;

// API/ACTION ROUTES
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

// PAGE ROUTES
$page = $_GET['page'] ?? 'dashboard';

switch ($page) {
    case 'login':
        // Jika sudah login, redirect ke dashboard
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