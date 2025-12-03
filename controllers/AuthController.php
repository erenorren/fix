<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function login() {
        header('Content-Type: application/json');
        
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
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            
            // Regenerate session ID
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