<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    // AuthController.php - Perbaikan login()
public function login() {
    header('Content-Type: application/json');
    
    // Mulai session jika belum
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Get input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $username = trim($input['username'] ?? '');
    $password = $input['password'] ?? '';
    
    // Validation
    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Username dan password harus diisi'
        ]);
        exit;
    }
    
    // Try login
    $user = $this->userModel->login($username, $password);
    
    if ($user) {
        // HAPUS session lama jika ada
        $_SESSION = array();
        
        // Set session baru
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['role'] = $user['role'];
        
        // Regenerate session ID
        session_regenerate_id(true);
        
        // Set cookie headers untuk Vercel
        if (getenv('VERCEL') === '1' || isset($_ENV['VERCEL'])) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                session_id(),
                [
                    'expires' => time() + 86400,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite']
                ]
            );
        }
        
        error_log("Login success. Session ID: " . session_id());
        error_log("User ID set to: " . $_SESSION['user_id']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Login berhasil',
            'session_id' => session_id(),
            'redirect' => 'index.php?page=dashboard'
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Username atau password salah'
        ]);
    }
}
    
    public function logout() {
        session_destroy();
        echo json_encode([
            'success' => true,
            'redirect' => 'index.php?page=login'
        ]);
    }
}
?>