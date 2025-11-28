<?php

// Struktur folder MVC : index.php - Entry Point Gabungan (Frontend + Backend)
// models/ → berisi class untuk logika data
// controllers/ → berisi logika proses dan request handling
// views/ → berisi tampilan dengan HTML

// Autoload untuk load class otomatis dari /models dan /controllers
spl_autoload_register(function ($className) {
    $paths = [
        __DIR__ . '/models/' . $className . '.php',
        __DIR__ . '/controllers/' . $className . '.php',
        __DIR__ . '/core/' . $className . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) {
            // Cek jika class sudah ada untuk menghindari duplicate
            if (!class_exists($className)) {
                require_once $path;
            }
            return;
        }
    }
});

// Mulai session untuk login
session_start();

// Cek apakah ada action (backend routing) - PERBAIKI: DEFINE $action SEBELUM DIGUNAKAN
$action = $_GET['action'] ?? $_POST['action'] ?? null;
error_log("ACTION REQ: " . ($action ?? 'NULL'));

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
            require_once __DIR__ . '/models/Pelanggan.php';
            $pelangganModel = new Pelanggan();
            
            $keyword = $_GET['q'] ?? '';
            error_log("Searching pelanggan with keyword: " . $keyword);
            
            $results = $pelangganModel->searchForAutocomplete($keyword);
            error_log("Found " . count($results) . " results");
            
            header('Content-Type: application/json');
            echo json_encode($results);
            exit;

        case 'getKandangTersedia':
            require_once __DIR__ . '/models/Kandang.php';
            $kandangModel = new Kandang();
            
            $jenis = $_GET['jenis'] ?? '';
            $ukuran = $_GET['ukuran'] ?? '';
            
            $kandangTersedia = $kandangModel->getAvailableKandang($jenis, $ukuran);
            
            header('Content-Type: application/json');
            echo json_encode($kandangTersedia);
            exit;

        // TRANSAKSI ACTIONS     
        case 'createTransaksi':
            $controller = new TransaksiController();
            $controller->create();
            break;
        case 'checkoutTransaksi': // PASTIKAN ADA INI
            error_log("=== CHECKOUT ACTION DIPANGGIL ===");
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
error_log("PAGE REQ: " . $page);

switch ($page) {
    case 'dashboard':
        include 'views/dashboard.php';
        break;
    case 'transaksi':
        error_log("=== LOADING TRANSAKSI CONTROLLER ===");
        $controller = new TransaksiController();
        $controller->index();
        break;
    case 'checkoutTransaksi':
        $controller = new TransaksiController();
        $controller->checkout();
        break;
    case 'layanan':
        include 'views/layanan.php';
        break;
    case 'hewan':
        include 'views/hewan.php';
        break;
    case 'pemilik':
        include 'views/pelanggan.php';
        break;
    case 'laporan':
        include 'views/laporan.php';
        break;
    case 'login':
        include 'views/login.php';
        break;
    case 'logout':
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    case 'cetakbukti':
        include 'views/cetak_bukti.php';
        break;
    default:
        include 'views/404.php';
        break;
}