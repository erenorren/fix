<?php
require_once __DIR__ . '/../core/Database.php';

/**
 * Model User
 * Untuk autentikasi user saat login (Kriteria Fungsionalitas)
 */
class User
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * LOGIN - Autentikasi user (READ)
     * @param string $username
     * @param string $password (plain text)
     * @return array|false User data atau false jika gagal
     * 
     * PostgreSQL/Supabase compatible:
     * - Query parameter binding menggunakan :username
     * - LIMIT 1 tetap valid di PostgreSQL
     */
    public function login($username, $password)
    {
        $sql = "SELECT * FROM \"user\" WHERE username = :username LIMIT 1"; 
        // NOTE: PostgreSQL sensitif terhadap keyword, "user" di-quote

        // Menggunakan query() wrapper dengan parameter
        $stmt = $this->db->query($sql, ['username' => $username]);
        $user = $stmt->fetch();
        // $pw = password_hash('admin123', PASSWORD_BCRYPT);
        // var_dump(value: $pw);
        // var_dump($password);
        // var_dump($user['password']);
        // Cek password (diasumsikan password di database di-hash bcrypt $2y$)
        
        if ($user && password_verify($password, $user['password'])) {
            $user['id'] = $user['id_user']; // FIX: Tambahkan key 'id' untuk konsistensi
            unset($user['password']);
            return $user;
        }

        return false;
    }

    /**
     * GET BY ID (READ - Digunakan untuk memuat data user ke session)
     * @param int $id
     * @return array|false
     * 
     * PostgreSQL/Supabase compatible:
     * - Query parameter binding menggunakan :id
     * - Alias AS tetap valid
     */
    public function getById($id)
    {
        $sql = "SELECT id_user as id, username, nama_lengkap, role, created_at FROM \"user\" WHERE id_user = :id";
        // NOTE: Quote "user" karena keyword di PostgreSQL

        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->fetch();
    }
}
