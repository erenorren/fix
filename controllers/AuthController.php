<?php
// controllers/AuthController.php

require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function login() {
        // Set header JSON
        header('Content-Type: application/json');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN'] ?? BASE_URL);
        
        // Get input
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || empty($input)) {
            $input = $_POST;
        }
        
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        
        // Validation
        if (empty($username) || empty($password)) {
            echo json_encode([
                'success' => false,
                'message' => 'Username dan password harus diisi'
            ]);
            exit;
        }
        
        // SIMPLE LOGIN FOR TESTING
        if ($username === 'admin' && $password === 'password123') {
            // Set session
            $_SESSION['user_id'] = 1;
            $_SESSION['username'] = 'admin';
            $_SESSION['nama_lengkap'] = 'Administrator';
            $_SESSION['role'] = 'admin';
            
            // Debug
            error_log("Simple login success for admin");
            
            echo json_encode([
                'success' => true,
                'message' => 'Login berhasil',
                'redirect' => 'index.php?page=dashboard'
            ]);
            exit;
        }
        
        // Try database login
        $user = $this->userModel->login($username, $password);
        
        if ($user) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            
            // Regenerate session ID
            session_regenerate_id(true);
            
            // Set cookie untuk Vercel
            if (getenv('VERCEL') || isset($_SERVER['VERCEL'])) {
                setcookie(
                    session_name(),
                    session_id(),
                    [
                        'expires' => time() + 86400,
                        'path' => '/',
                        'domain' => $_SERVER['HTTP_HOST'],
                        'secure' => true,
                        'httponly' => true,
                        'samesite' => 'None'
                    ]
                );
            }
            
            error_log("Database login success for: " . $username);
            
            echo json_encode([
                'success' => true,
                'message' => 'Login berhasil',
                'session_id' => session_id(),
                'redirect' => 'index.php?page=dashboard'
            ]);
        } else {
            error_log("Login failed for: " . $username);
            
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
            'message' => 'Logout berhasil',
            'redirect' => 'index.php?page=login'
        ]);
    }
}
?>