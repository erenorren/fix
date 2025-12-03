<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function login() {
        // ✅ SET HEADERS PERTAMA
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? '*'));
        header('Access-Control-Allow-Credentials: true');
        
        // ✅ Tangkap semua error untuk debugging
        try {
            // Get input
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            // Jika tidak ada POST data, coba dari php://input (untuk testing)
            if (empty($username) || empty($password)) {
                $input = json_decode(file_get_contents('php://input'), true);
                if ($input) {
                    $username = trim($input['username'] ?? '');
                    $password = $input['password'] ?? '';
                }
            }
            
            // Validation
            if (empty($username) || empty($password)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Username dan password harus diisi',
                    'debug' => ['username' => $username, 'password_empty' => empty($password)]
                ]);
                exit;
            }
            
            // Attempt login
            $user = $this->userModel->login($username, $password);
            
            if ($user) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'] ?? 'Admin';
                $_SESSION['role'] = $user['role'] ?? 'admin';
                $_SESSION['logged_in'] = true;
                
                // Untuk debugging
                error_log("Login success for user: " . $username);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Login berhasil',
                    'redirect' => 'index.php?page=dashboard',
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username']
                    ]
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Username atau password salah',
                    'debug' => 'User not found or password incorrect'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Login controller error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
        exit;
    }
    
    public function logout() {
        session_destroy();
        echo json_encode([
            'success' => true,
            'redirect' => 'index.php?page=login'
        ]);
        exit;
    }
}
?>