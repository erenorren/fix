<?php
// ==================================================
// VERCEL CONFIGURATION
// ==================================================
$isVercel = getenv('VERCEL') === '1' || isset($_ENV['VERCEL']);

if ($isVercel) {
    // CORS headers untuk Vercel
    header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    
    // Handle preflight
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
    
    // Session config untuk Vercel
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => parse_url($_SERVER['HTTP_ORIGIN'] ?? '', PHP_URL_HOST),
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None'
    ]);
}

// ==================================================
// SESSION START
// ==================================================
if (session_status() == PHP_SESSION_NONE) {
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
    $publicPages = ['login', 'logout'];
    $publicActions = ['login', 'searchPelanggan', 'getKandangTersedia'];
    
    $page = $_GET['page'] ?? 'dashboard';
    $action = $_GET['action'] ?? null;
    
    if (in_array($page, $publicPages) || in_array($action, $publicActions)) {
        return;
    }
    
    if (!isset($_SESSION['user_id'])) {
        if ($action) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        header('Location: index.php?page=login');
        exit;
    }
}

checkAuth();

// ==================================================
// ROUTING
// ==================================================
$action = $_GET['action'] ?? null;

// API/ACTION ROUTES
if ($action) {
    switch ($action) {
        case 'login':
            require_once __DIR__ . '/../controllers/AuthController.php';
            (new AuthController())->login();
            break;
            
        case 'logout':
            require_once __DIR__ . '/../controllers/AuthController.php';
            $controller = new AuthController();
            $controller->logout();
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
            $controller = new TransaksiController();
            $controller->create();
            break;
            
        case 'checkoutTransaksi':
            require_once __DIR__ . '/../controllers/TransaksiController.php';
            $controller = new TransaksiController();
            $controller->checkout();
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
        $controller = new TransaksiController();
        $controller->index();
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