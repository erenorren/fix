<?php
// public/index.php

// ==================================================
// 1. START SESSION - HARUS PERTAMA
// ==================================================
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ==================================================
// 2. INCLUDE MODELS (UNTUK LOGIN REAL)
// ==================================================
require_once __DIR__ . '/../models/User.php';

// ==================================================
// 3. HANDLE LOGIN ACTION FIRST (BEFORE ANY OUTPUT)
// ==================================================
if (isset($_GET['action']) && $_GET['action'] === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // SET HEADERS untuk JSON response
    header('Content-Type: application/json');
    
    // LOGIN REAL DENGAN DATABASE
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Username dan password harus diisi'
        ]);
        exit;
    }
    
    // Gunakan model User untuk validasi
    $userModel = new User();
    $user = $userModel->login($username, $password);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'] ?? 'Admin';
        $_SESSION['role'] = $user['role'] ?? 'admin';
        
        echo json_encode([
            'success' => true,
            'message' => 'Login berhasil',
            'redirect' => 'index.php?page=dashboard'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Username atau password salah'
        ]);
    }
    exit;
}

// ==================================================
// 4. SIMPLE AUTH CHECK
// ==================================================
$page = $_GET['page'] ?? 'login';

// Public pages (no auth required)
if ($page === 'login') {
    // Jika sudah login, redirect ke dashboard
    if (isset($_SESSION['user_id'])) {
        header('Location: index.php?page=dashboard');
        exit;
    }
    
    // Tampilkan halaman login
    require_once __DIR__ . '/../views/login.php';
    exit;
}

// Private pages (require auth)
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

// ==================================================
// 5. ROUTE PAGES YANG SUDAH LOGIN
// ==================================================
switch ($page) {
    case 'dashboard':
        require_once __DIR__ . '/../views/dashboard.php';
        break;
        
    case 'transaksi':
        require_once __DIR__ . '/../controllers/TransaksiController.php';
        (new TransaksiController())->index();
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