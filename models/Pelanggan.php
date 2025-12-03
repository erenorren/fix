<?php
// models/Pelanggan.php
require_once __DIR__ . '/../core/Database.php';

class Pelanggan {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getAll() {
    try {
        $sql = "SELECT 
                    id_pelanggan,
                    COALESCE(kode_pelanggan, 'P' || LPAD(id_pelanggan::text, 3, '0')) as kode, -- Generate kode jika tidak ada
                    nama_pelanggan,
                    no_hp,
                    alamat
                FROM pelanggan 
                ORDER BY nama_pelanggan";
        
        $stmt = $this->db->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Format untuk konsistensi
        $formatted = [];
        foreach ($result as $row) {
            $formatted[] = [
                'id' => $row['id_pelanggan'],
                'kode_pelanggan' => $row['kode'], // Pakai kode yang di-generate
                'nama' => $row['nama_pelanggan'],
                'hp' => $row['no_hp'],
                'alamat' => $row['alamat']
            ];
        }
        
        error_log("Pelanggan data sample: " . print_r($formatted[0] ?? 'empty', true));
        return $formatted;
        
    } catch (Exception $e) {
        error_log("ERROR Pelanggan::getAll(): " . $e->getMessage());
        return [];
    }
}
    
    public function getById($id) {
        $sql = "SELECT * FROM pelanggan WHERE id_pelanggan = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt->fetch();
    }
    
    public function search($keyword) {
        $sql = "SELECT * FROM pelanggan 
                WHERE nama_pelanggan ILIKE :keyword 
                OR no_hp ILIKE :keyword 
                OR alamat ILIKE :keyword 
                ORDER BY nama_pelanggan";
        
        $stmt = $this->db->query($sql, ['keyword' => "%{$keyword}%"]);
        return $stmt->fetchAll();
    }
    
    public function create($data) {
        try {
            $sql = "INSERT INTO pelanggan (nama_pelanggan, no_hp, alamat) 
                    VALUES (:nama, :hp, :alamat) 
                    RETURNING id_pelanggan";
            
            $params = [
                ':nama' => $data['nama_pelanggan'],
                ':hp' => $data['no_hp'],
                ':alamat' => $data['alamat']
            ];
            
            $stmt = $this->db->query($sql, $params);
            $result = $stmt->fetch();
            
            return $result['id_pelanggan'] ?? false;
            
        } catch (Exception $e) {
            error_log("Error create pelanggan: " . $e->getMessage());
            return false;
        }
    }
    
    public function update($id, $data) {
        $sql = "UPDATE pelanggan SET 
                nama_pelanggan = :nama,
                no_hp = :hp,
                alamat = :alamat
                WHERE id_pelanggan = :id";
        
        $params = [
            ':id' => $id,
            ':nama' => $data['nama_pelanggan'],
            ':hp' => $data['no_hp'],
            ':alamat' => $data['alamat']
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM pelanggan WHERE id_pelanggan = :id";
        return $this->db->execute($sql, [':id' => $id]);
    }
}
?>