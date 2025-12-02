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
        // PERHATIAN: Nama kolom sesuai database Anda
        // Dari screenshot: iduser, username, _nama_lenckap, password, rade (mungkin role)
        $sql = "SELECT * FROM `user` WHERE username = :username LIMIT 1";
        
        $stmt = $this->db->query($sql, ['username' => $username]);
        $user = $stmt->fetch();
        
        // Debug: lihat apa yang didapat dari database
        // error_log("User from DB: " . print_r($user, true));
        // error_log("Input password: " . $password);
        // error_log("DB password: " . ($user['password'] ?? 'NOT FOUND'));
        
        if ($user) {
            // Verifikasi password
            if (password_verify($password, $user['password'])) {
                // Sesuaikan dengan struktur database Anda
                $userData = [
                    'id' => $user['iduser'] ?? $user['id_user'] ?? $user['id'], // coba semua kemungkinan
                    'username' => $user['username'],
                    'nama_lengkap' => $user['nama_lengkap'],
                    'role' => $user['rade'] ?? $user['role'] ?? 'user' // dari screenshot "rade"
                ];
                
                return $userData;
            } else {
                // Password tidak cocok
                error_log("Password verification FAILED for user: $username");
                return false;
            }
        }
        
        return false;
    }

    /**
     * GET BY ID
     */
    public function getById($id)
    {
        $sql = "SELECT iduser as id, username, nama_lengkap, 
                       rade as role FROM `user` WHERE iduser = :id";
        
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->fetch();
    }
}
?>