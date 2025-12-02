<?php
require_once __DIR__ . '/../core/Database.php';

class User
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * LOGIN - Autentikasi user
     */
    public function login($username, $password)
    {
        try {
            // Query sesuai dengan struktur database Anda
            $sql = "SELECT * FROM `user` WHERE username = :username LIMIT 1";
            
            $stmt = $this->db->query($sql, ['username' => $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                error_log("User not found: $username");
                return false;
            }
            
            // Debug
            error_log("User found: " . $user['username']);
            error_log("Password from DB: " . $user['password']);
            
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Return data user - SESUAIKAN DENGAN KOLOM DATABASE ANDA
                return [
                    'id' => $user['id_user'],  // Kolom di DB adalah id_user
                    'username' => $user['username'],
                    'nama_lengkap' => $user['nama_lengkap'],
                    'role' => $user['role']
                ];
            } else {
                // Coba password 'password' (default Laravel)
                if ($password === 'password' && password_verify('password', $user['password'])) {
                    return [
                        'id' => $user['id_user'],
                        'username' => $user['username'],
                        'nama_lengkap' => $user['nama_lengkap'],
                        'role' => $user['role']
                    ];
                }
                error_log("Password verification failed");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
}
?>