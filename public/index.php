<?php
var_dump(getenv('DB_HOST'));
var_dump(getenv('DB_USER'));
var_dump(getenv('DB_PASS'));
exit;

// ==================================================
// 1. START SESSION
// ==================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../models/User.php';


// ==================================================
// 2. HANDLE LOGIN PROSES (POST action=login)
// ==================================================
if (isset($_GET['action']) && $_GET['action'] === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    header('Content-Type: application/json');

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Username dan password harus diisi'
        ]);
        exit;
    }

    $userModel = new User();
    $user = $userModel->login($username, $password);

    if ($user) {

        // SET SESSION — WAJIB UNTUK AUTH
        $_SESSION['user_id']      = $user['id'];
        $_SESSION['username']     = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'] ?? '';
        $_SESSION['role']         = $user['role'] ?? 'user';

        // OPTIONAL: cookie tetapi bukan untuk auth
        setcookie("user_id", $user['id'], time() + 3600, "/");

        echo json_encode([
            'success' => true,
            'message' => 'Login berhasil',
            'redirect' => 'index.php?page=dashboard'
        ]);
        exit;
    }

    echo json_encode([
        'success' => false,
        'message' => 'Username atau password salah'
    ]);
    exit;
}


// ==================================================
// 3. ROUTE HALAMAN LOGIN (PUBLIC PAGE)
// ==================================================
$page = $_GET['page'] ?? 'login';

if ($page === 'login') {

    // Jika SUDAH login -> arahkan ke dashboard
    if (isset($_SESSION['user_id'])) {
        header("Location: index.php?page=dashboard");
        exit;
    }

    require_once __DIR__ . '/../views/login.php';
    exit;
}


// ==================================================
// 4. AUTH CHECK UNTUK SEMUA HALAMAN LAIN
// ==================================================
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login");
    exit;
}


// ==================================================
// 5. HANDLE ACTION LAIN (SETELAH LOGIN)
// ==================================================
$action = $_GET['action'] ?? '';

if ($action === 'createTransaksi' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../controllers/TransaksiController.php';
    (new TransaksiController())->createTransaksi();
    exit;
}

if ($action === 'checkoutTransaksi') {
    require_once __DIR__ . '/../controllers/TransaksiController.php';
    (new TransaksiController())->checkout();
    exit;
}


// ==================================================
// 6. ROUTING
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

    case 'pelanggan':
        require_once __DIR__ . '/../views/pelanggan.php';
        break;

    case 'logout':
        session_destroy();
        header("Location: index.php?page=login");
        break;

    default:
        require_once __DIR__ . '/../views/404.php';
        break;
}
?>
