<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    private $isVercel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->isVercel = getenv('VERCEL') === '1' || isset($_ENV['VERCEL']);
    }
    
    public function login() {
        // Set headers
        header('Content-Type: application/json; charset=utf-8');
        
        if ($this->isVercel) {
            header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
            header('Access-Control-Allow-Credentials: true');
        }
        
        // Get input
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input && !empty($_POST)) {
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
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                
                // Regenerate session untuk security
                session_regenerate_id(true);
                
                // Response
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
            error_log("AuthController error: " . $e->getMessage());
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