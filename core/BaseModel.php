<?php
// Pastikan file Database.php sudah di-include di sini
require_once 'Database.php'; 

/**
 * Kelas Dasar (Induk) untuk semua Model.
 * Menerapkan Pewarisan (Inheritance) dan Encapsulation.
 */
abstract class BaseModel {
    protected $db; 
    protected $tableName; 
    protected $primaryKey = 'id';

    public function __construct() {
        // Instansiasi koneksi Database (Encapsulation)
        $this->db = new Database(); 
    }

    /**
     * Ambil satu record berdasarkan primary key
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->tableName} WHERE {$this->primaryKey} = :id";
        return $this->db->query($sql, ['id' => $id])->fetch();
    }
    
    /**
     * [Contoh] Metode create di model anak:
     * PostgreSQL membutuhkan RETURNING id untuk mendapatkan last inserted ID
     * 
     * Di model anak, tulis seperti ini:
     * 
     * $columns = implode(', ', array_keys($data));
     * $placeholders = ':' . implode(', :', array_keys($data));
     * $sql = "INSERT INTO {$this->tableName} ({$columns}) VALUES ({$placeholders}) RETURNING id";
     * $stmt = $this->db->query($sql, $data);
     * $result = $stmt->fetch();
     * $idBaru = $result['id'] ?? null;
     */
}
