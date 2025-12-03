// public/index.php - Perbaikan bagian session
<?php
// SESSION START - FIXED FOR VERCEL
$isVercel = isset($_SERVER['VERCEL']) || (isset($_ENV['VERCEL']) && $_ENV['VERCEL'] === '1');

if (session_status() == PHP_SESSION_NONE) {
    if ($isVercel) {
        // Vercel specific settings - PASTIKAN SAMA PERSIS
        ini_set('session.cookie_secure', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        
        session_set_cookie_params([
            'lifetime' => 86400,
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
    
    session_name('PHPSESSID'); // PASTIKAN nama session
    session_start();
    
    // Debug: Cek session
    error_log("Session started. ID: " . session_id());
    error_log("User ID in session: " . ($_SESSION['user_id'] ?? 'not set'));
}

// ==================================================
// AUTOLOAD
// ==================================================
require_once __DIR__ . '/../vendor/autoload.php';

// ====================================================
// AUTH CHECK - PERBAIKI LOGIKA
// ====================================================
$publicPages = ['login', 'logout'];
$publicActions = ['login', 'searchPelanggan', 'getKandangTersedia'];

$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? null;

// Cek jika user sudah login
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Debug log
error_log("Page: $page, Action: $action, Logged in: " . ($isLoggedIn ? 'YES' : 'NO'));

// Jika belum login dan mencoba akses halaman terproteksi
if (!$isLoggedIn) {
    // Izinkan akses ke halaman publik dan action publik
    $isPublic = in_array($page, $publicPages) || in_array($action, $publicActions);
    
    if (!$isPublic) {
        // Jika request AJAX/API
        if ($action) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized', 'session_id' => session_id()]);
            exit;
        }
        // Redirect ke login untuk request biasa
        header('Location: index.php?page=login');
        exit;
    }
}

// Jika sudah login tapi mencoba akses halaman login
if ($isLoggedIn && $page === 'login') {
    header('Location: index.php?page=dashboard');
    exit;
}

// ==================================================
// ROUTING
// ==================================================
$action = $_GET['action'] ?? null;

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