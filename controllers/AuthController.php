<?php
// controllers/AuthController.php
class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        // Validasi server-side
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            echo json_encode(['error' => 'Username dan password harus diisi']);
            return;
        }

        if (strlen($username) < 3 || strlen($password) < 6) {
            echo json_encode(['error' => 'Username minimal 3 karakter, password minimal 6 karakter']);
            return;
        }

        $user = $this->userModel->login($username, $password);
        if ($user) {
            // Set session
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            echo json_encode(['success' => 'Login berhasil', 'user' => $user]);
        } else {
            echo json_encode(['error' => 'Username atau password salah']);
        }
    }

    public function logout() {
        session_destroy();
        echo json_encode(['success' => 'Logout berhasil']);
    }
}
?>