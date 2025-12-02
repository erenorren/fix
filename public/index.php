<?php
// Autoload untuk load class otomatis dari /models, /controllers, /core (di luar public)
spl_autoload_register(function ($className) {
    $paths = [
        __DIR__ . '/../models/' . $className . '.php',
        __DIR__ . '/../controllers/' . $className . '.php',
        __DIR__ . '/../core/' . $className . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            if (!class_exists($className)) {
                require_once $path;
            }
            return;
        }
    }
});

// Mulai session untuk login
session_start();

// ====================================================
// MIDDLEWARE: CEK APAKAH USER SUDAH LOGIN
// ====================================================
function requireLogin() {
    // Halaman yang BOLEH diakses tanpa login
    $publicPages = ['login', 'logout', '404'];
    
    $page = $_GET['page'] ?? 'dashboard';
    $action = $_GET['action'] ?? $_POST['action'] ?? null;
    
    // Action API tertentu yang boleh tanpa login
    $publicActions = ['login', 'searchPelanggan', 'getKandangTersedia'];
    
    // Jika mencoba akses action publik, izinkan
    if ($action && in_array($action, $publicActions)) {
        return;
    }
    
    // Jika mencoba akses halaman publik, izinkan
    if (in_array($page, $publicPages)) {
        return;
    }
    
    // Cek apakah user sudah login (sesuai dengan AuthController)
    // AuthController set: $_SESSION['user_id'], $_SESSION['username']
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
        // Redirect ke halaman login
        header('Location: index.php?page=login');
        exit;
    }
}

// Jalankan middleware
requireLogin(); // ← INI ADALAH PEMANGGILAN FUNGSI, BUKAN DEKLARASI BARU

// Cek action untuk API/backend
$action = $_GET['action'] ?? $_POST['action'] ?? null;

if ($action) {
    // Routing untuk backend (controllers)
    switch ($action) {
        case 'login':
            $controller = new AuthController();
            $controller->login();
            break;
        case 'logout':
            $controller = new AuthController();
            $controller->logout();
            break;
            
        case 'searchPelanggan':
            // API ini TIDAK perlu login (digunakan di form transaksi sebelum login)
            require_once __DIR__ . '/../models/Pelanggan.php';
            $pelangganModel = new Pelanggan();
            
            $keyword = $_GET['q'] ?? '';
            
            // Gunakan method getAll() yang sudah ada dan filter manual
            $allPelanggan = $pelangganModel->getAll();
            $results = [];
            
            foreach ($allPelanggan as $pelanggan) {
                if (stripos($pelanggan['nama'], $keyword) !== false || 
                    stripos($pelanggan['hp'], $keyword) !== false) {
                    $results[] = $pelanggan;
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode($results);
            exit;

        case 'getKandangTersedia':
            // API ini TIDAK perlu login (digunakan di form transaksi sebelum login)
            require_once __DIR__ . '/../models/Kandang.php';
            $kandangModel = new Kandang();
            
            $jenis = $_GET['jenis'] ?? '';
            $ukuran = $_GET['ukuran'] ?? '';
            
            $kandangTersedia = $kandangModel->getAvailableKandang($jenis, $ukuran);
            
            header('Content-Type: application/json');
            echo json_encode($kandangTersedia);
            exit;

        // TRANSAKSI ACTIONS - PERLU LOGIN
        case 'createTransaksi':
            // Cek login dulu untuk action yang butuh auth
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            $controller = new TransaksiController();
            $controller->create();
            break;
        case 'checkoutTransaksi':
            // Cek login dulu untuk action yang butuh auth
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            $controller = new TransaksiController();
            $controller->checkout();
            break;

        default:
            echo json_encode(['error' => 'Action not found']);
            break;
    }
    exit;
}

// Jika tidak ada action, lanjut ke frontend routing (page)
$page = $_GET['page'] ?? 'dashboard';

switch ($page) {
    case 'dashboard':
        // Dashboard hanya bisa diakses setelah login
        include __DIR__ . '/../views/dashboard.php';
        break;
    case 'transaksi':
        $controller = new TransaksiController();
        $controller->index();
        break;
    case 'layanan':
        include __DIR__ . '/../views/layanan.php';
        break;
    case 'hewan':
        include __DIR__ . '/../views/hewan.php';
        break;
    case 'pemilik':
        include __DIR__ . '/../views/pelanggan.php';
        break;
    case 'laporan':
        include __DIR__ . '/../views/laporan.php';
        break;
    case 'login':
        // Jika sudah login, redirect ke dashboard
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?page=dashboard');
            exit;
        }
        include __DIR__ . '/../views/login.php';
        break;
    case 'kandang':
        include __DIR__ . '/../views/kandang.php';
        break;
    case 'logout':
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    default:
        include __DIR__ . '/../views/404.php';
        break;
}