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

// Cek apakah ada action (backend routing)
$action = $_GET['action'] ?? $_POST['action'] ?? null;
if (empty($action) && isset($_POST['action'])) {
    $action = $_POST['action'];
}


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
            
        // PELANGGAN ACTIONS
        // Di bagian switch($action), pastikan case searchPelanggan ada:
        case 'searchPelanggan':
        // models/ → berisi class untuk logika data
            require_once __DIR__ . '/models/Pelanggan.php';
            $pelangganModel = new Pelanggan();
            
            $keyword = $_GET['q'] ?? '';
            error_log("Searching pelanggan with keyword: " . $keyword);
            
            $results = $pelangganModel->searchForAutocomplete($keyword);
            error_log("Found " . count($results) . " results");
            
            header('Content-Type: application/json');
            echo json_encode($results);
            exit;

        // Tambahkan case ini di switch($action) di index.php
        case 'getKandangTersedia':
        // models/ → berisi class untuk logika data
            require_once __DIR__ . '/models/Kandang.php';
            $kandangModel = new Kandang();
            
            $jenis = $_GET['jenis'] ?? '';
            $ukuran = $_GET['ukuran'] ?? '';
            
            // Filter kandang berdasarkan jenis dan ukuran hewan
            $kandangTersedia = $kandangModel->getAvailableKandang($jenis, $ukuran);
            
            header('Content-Type: application/json');
            echo json_encode($kandangTersedia);
            exit;
            // break;
            
        // TRANSAKSI ACTIONS     
        case 'createTransaksi':
            $controller = new TransaksiController();
            $controller->create();
            break;
        case 'readTransaksi':
            $controller = new TransaksiController();
            $controller->read();
            break;
        case 'updateTransaksi':
            $controller = new TransaksiController();
            $controller->update();
            break;
        case 'deleteTransaksi':
            $controller = new TransaksiController();
            $controller->delete();
            break;
        case 'searchTransaksi':
            $controller = new TransaksiController();
            $controller->search();
            break;

        case 'checkoutTransaksi':
            $controller = new TransaksiController();
            $controller->checkout(); 
            break;

        case 'cetakBukti':
            $controller = new TransaksiController();
            $controller->cetakBukti($_GET['id']);
            break;

        default:
            echo json_encode(['error' => 'Action not found']);
            break;
    }
    exit;
}

// Jika tidak ada action, lanjut ke frontend routing (page)
// views/ → berisi tampilan dengan HTML
$page = $_GET['page'] ?? 'dashboard';
error_log("ACTION REQ: " . $action);


switch ($page) {
    case 'dashboard':
        include 'views/dashboard.php';
        break;
    case 'transaksi':
        include 'views/transaksi.php';
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
    default:
        include 'views/404.php';
        break;
}
