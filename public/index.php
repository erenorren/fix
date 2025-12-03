<?php
// public/index.php - VERSION FINAL

// ==================================================
// 1. SESSION CONFIGURATION - PALING ATAS
// ==================================================
$isVercel = (isset($_SERVER['VERCEL']) || getenv('VERCEL') === '1');

if ($isVercel) {
    // Konfigurasi khusus Vercel
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'None');
    
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => true,
        'httponly' => true,
        'samesite' => 'None'
    ]);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==================================================
// 2. AUTOLOAD
// ==================================================
require_once __DIR__ . '/../vendor/autoload.php';

// ==================================================
// 3. BASE URL DETECTION (UNTUK CSS/JS)
// ==================================================
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
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
// 4. SIMPLE AUTH CHECK (TAMPILAN TETAP SAMA)
// ==================================================
$currentPage = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? null;

// Halaman yang boleh diakses tanpa login
$publicPages = ['login', 'logout'];
$publicActions = ['login', 'searchPelanggan', 'getKandangTersedia'];

// Cek apakah user sudah login
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Logika autentikasi SAMA seperti sebelumnya
if (!$isLoggedIn) {
    // Jika belum login
    if (!in_array($currentPage, $publicPages) && !in_array($action, $publicActions)) {
        if ($action) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        header('Location: index.php?page=login');
        exit;
    }
} else {
    // Jika sudah login
    if ($currentPage === 'login') {
        header('Location: index.php?page=dashboard');
        exit;
    }
}

// ==================================================
// 5. ROUTING UNTUK ACTION (API CALLS) - SAMA
// ==================================================
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

// ==================================================
// 6. PAGE ROUTES - TETAP SAMA
// ==================================================
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