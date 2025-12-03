<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    private $isVercel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->isVercel = isset($_ENV['VERCEL']) || getenv('VERCEL') === '1';
    }
    
    public function login() {
        // Set JSON header
        header('Content-Type: application/json; charset=utf-8');
        
        if ($this->isVercel) {
            header('Access-Control-Allow-Credentials: true');
        }
        
        // Get input - support both JSON and form-data
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || json_last_error() !== JSON_ERROR_NONE) {
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
        
        try {
            $user = $this->userModel->login($username, $password);
            
            if ($user) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                
                // Regenerate session untuk security
                session_regenerate_id(true);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login berhasil',
                    'redirect' => 'index.php?page=dashboard'
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Username atau password salah'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
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