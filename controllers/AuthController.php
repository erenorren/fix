<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    
    public function login() {
        // ✅ CLEAR ANY PREVIOUS OUTPUT
        if (ob_get_level()) ob_clean();
        
        // ✅ SET JSON HEADERS IMMEDIATELY
        header('Content-Type: application/json');
        
        // ✅ SUPRESS ERRORS
        ini_set('display_errors', 0);
        
        try {
            // Get POST data
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Debug log
            error_log("Login attempt: " . $username);
            
            // Validate
            if (empty($username) || empty($password)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Username dan password diperlukan'
                ]);
                exit;
            }
            
            // Create user model
            $userModel = new User();
            $user = $userModel->login($username, $password);
            
            if ($user) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
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
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
        
        exit; // IMPORTANT!
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