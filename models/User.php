<?php
require_once __DIR__ . '/../config/database.php'; // configurasi db

/**
 * Model User
 * Untuk autentikasi user saat login
 * Ada CRUD
 */
// encapsulation private $db
class User {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * LOGIN - Autentikasi user
     * 
     * @param string $username
     * @param string $password (plain text)
     * @return array|false User data atau false jika gagal
     */
    public function login($username, $password) {
        $sql = "SELECT * FROM user WHERE username = :username LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        
        // Cek password (harus pakai password_verify karena di-hash)
        if ($user && password_verify($password, $user['password'])) {
            // Jangan return password!
            unset($user['password']);
            return $user;
        }
        
        return false;
    }
    
    /**
     * GET BY ID
     * 
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        $sql = "SELECT id_user, username, nama_lengkap, role, created_at 
                FROM user 
                WHERE id_user = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * GET ALL - Ambil semua user
     * 
     * @return array
     */
    public function getAll() {
        $sql = "SELECT id_user, username, nama_lengkap, role, created_at 
                FROM user 
                ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * CREATE - Tambah user baru
     * 
     * @param array $data ['username', 'password', 'nama_lengkap', 'role']
     * @return bool
     */
    public function create($data) {
        // Hash password dulu!
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO user (username, password, nama_lengkap, role) 
                VALUES (:username, :password, :nama_lengkap, :role)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'username' => $data['username'],
            'password' => $hashedPassword,
            'nama_lengkap' => $data['nama_lengkap'],
            'role' => $data['role'] ?? 'kasir'
        ]);
    }
    
    /**
     * UPDATE - Update data user (tanpa password)
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $sql = "UPDATE user 
                SET username = :username,
                    nama_lengkap = :nama_lengkap,
                    role = :role
                WHERE id_user = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'username' => $data['username'],
            'nama_lengkap' => $data['nama_lengkap'],
            'role' => $data['role']
        ]);
    }
    
    /**
     * UPDATE PASSWORD - Ganti password user
     * 
     * @param int $id
     * @param string $newPassword (plain text)
     * @return bool
     */
    public function updatePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $sql = "UPDATE user SET password = :password WHERE id_user = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id' => $id,
            'password' => $hashedPassword
        ]);
    }
    
    /**
     * DELETE
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $sql = "DELETE FROM user WHERE id_user = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }
    
    /**
     * CHECK USERNAME - Cek apakah username sudah ada
     * 
     * @param string $username
     * @param int|null $excludeId (untuk update, exclude id sendiri)
     * @return bool
     */
    public function isUsernameExists($username, $excludeId = null) {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as total FROM user 
                    WHERE username = :username AND id_user != :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['username' => $username, 'id' => $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as total FROM user WHERE username = :username";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['username' => $username]);
        }
        
        $result = $stmt->fetch();
        return $result['total'] > 0;
    }
}
