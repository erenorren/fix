<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {

    private $userModel;

    public function __construct() 
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->userModel = new User();
    }

    public function login() 
    {
        // Bersihkan output buffer biar JSON tidak rusak
        if (ob_get_level()) ob_clean();

        header('Content-Type: application/json');
        ini_set('display_errors', 0);

        // Ambil input JSON atau POST
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';

        // Validasi input
        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Username dan password harus diisi'
            ]);
            exit;
        }

        // Cek ke database
        try {
            $user = $this->userModel->login($username, $password);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ]);
            exit;
        }

        // Jika user ditemukan
        if ($user) {

            // Regenerate session ID untuk keamanan
            session_regenerate_id(true);

            // Set session lengkap
            /** @var array $user */
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'] ?? '';
            $_SESSION['role']         = $user['role'] ?? 'user';


            // Set cookie fallback - tanpa domain, path "/" agar dipakai di seluruh host
            $cookieOptions = [
            'expires' => time() + 60 * 60 * 24, // 1 hari, sesuaikan
            'path' => '/',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'), // true di Vercel
            'httponly' => true,
            'samesite' => 'Lax' // Lax biasanya bekerja; jika cross-site req diperlukan, gunakan 'None' + secure
            ];

        // PHP < 7.3 fallback
        if (PHP_VERSION_ID >= 70300) {
            setcookie('user_id', $user['id'], $cookieOptions);
        } else {
            setcookie('user_id', $user['id'], $cookieOptions['expires'], $cookieOptions['path']);
        }

            echo json_encode([
                'success'  => true,
                'message'  => 'Login berhasil',
                'redirect' => 'index.php?page=dashboard'
            ]);
            exit;
        }

        // Jika user salah
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Username atau password salah'
        ]);
        exit;
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_unset();
        session_destroy();

        echo json_encode([
            'success'  => true,
            'message'  => 'Logout berhasil',
            'redirect' => 'index.php?page=login'
        ]);
        exit;
    }
}
?>
