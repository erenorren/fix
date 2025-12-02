<?php
require_once __DIR__ . '/../models/User.php'; 

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Menangani proses login
     */
    public function login() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Validasi sederhana
        if (empty($username) || empty($password)) {
            echo json_encode([
                'success' => false,
                'message' => 'Username dan password harus diisi'
            ]);
            exit;
        }

        $user = $this->userModel->login($username, $password);
        
        if ($user) {
            // Set session
            $_SESSION['user_id'] = $user['id']; 
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'] ?? '';
            $_SESSION['role'] = $user['role'] ?? 'user';
            
            echo json_encode([
                'success' => true, 
                'message' => 'Login berhasil',
                'redirect' => 'index.php?page=dashboard'
            ]);
            exit;
            
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Username atau password salah'
            ]);
            exit;
        }
    }


    /**
     * Menangani proses logout
     */
    public function logout() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    }
}
?>