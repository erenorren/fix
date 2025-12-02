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
        // Start output buffering untuk debug
        ob_start();
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Log request data
        error_log("=== LOGIN REQUEST ===");
        error_log("POST data: " . print_r($_POST, true));
        error_log("Session ID: " . session_id());
        
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // HEADER JSON harus di-set di awal
        header('Content-Type: application/json');
        
        // Validasi
        if (empty($username) || empty($password)) {
            $response = [
                'success' => false,
                'message' => 'Username dan password harus diisi'
            ];
            echo json_encode($response);
            error_log("Validation failed: " . json_encode($response));
            exit;
        }

        try {
            // Panggil model
            error_log("Calling UserModel->login with: $username");
            $user = $this->userModel->login($username, $password);
            
            if ($user && is_array($user)) {
                // Set session
                $_SESSION['user_id'] = $user['id']; 
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role'] = $user['role'];
                
                $response = [
                    'success' => true, 
                    'message' => 'Login berhasil',
                    'redirect' => 'index.php?page=dashboard'
                ];
                
                error_log("Login SUCCESS: " . json_encode($response));
                echo json_encode($response);
                
            } else {
                // Coba bypass untuk testing
                error_log("Normal login failed, trying bypass...");
                
                // BYPASS FOR TESTING - HAPUS INI SETELAH BERHASIL
                if ($username === 'admin' || $username === 'kasir1') {
                    $_SESSION['user_id'] = 1;
                    $_SESSION['username'] = $username;
                    $_SESSION['nama_lengkap'] = $username === 'admin' ? 'Administrator' : 'Kasir Satu';
                    $_SESSION['role'] = $username === 'admin' ? 'admin' : 'kasir';
                    
                    $response = [
                        'success' => true, 
                        'message' => 'Login berhasil (bypass)',
                        'redirect' => 'index.php?page=dashboard'
                    ];
                    
                    error_log("Bypass login SUCCESS: " . json_encode($response));
                    echo json_encode($response);
                    exit;
                }
                // END BYPASS
                
                $response = [
                    'success' => false,
                    'message' => 'Username atau password salah'
                ];
                
                error_log("Login FAILED: " . json_encode($response));
                echo json_encode($response);
            }
            
        } catch (Exception $e) {
            error_log("Login EXCEPTION: " . $e->getMessage());
            
            $response = [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ];
            
            echo json_encode($response);
        }
        
        // Clean output buffer
        ob_end_flush();
        exit;
    }

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