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
        // Query sesuai database (lihat kolom dari screenshot)
        $sql = "SELECT * FROM `user` WHERE username = :username LIMIT 1";
        
        $stmt = $this->db->query($sql, ['username' => $username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            return false; // User tidak ditemukan
        }
        
        // Debug: cek hash password
        // error_log("DB Password hash: " . $user['password']);
        // error_log("Input password: '$password'");
        
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Return data user (sesuaikan dengan kolom database)
            return [
                'id' => $user['iduser'] ?? $user['id_user'] ?? $user['id'] ?? 0,
                'username' => $user['username'],
                'nama_lengkap' => $user['_nama_lenckap'] ?? $user['nama_lengkap'] ?? '',
                'role' => $user['rade'] ?? $user['role'] ?? 'user'
            ];
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