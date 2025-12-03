<?php
require_once __DIR__ . '/../core/Database.php';

class Pelanggan {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    public function getAll() {
        try {
            error_log("Pelanggan::getAll() called");
            
            // ✅ FIX: PostgreSQL compatible query
            $sql = "SELECT 
                        id_pelanggan,
                        nama_pelanggan,
                        no_hp,
                        alamat
                    FROM pelanggan 
                    ORDER BY nama_pelanggan";
            
            $stmt = $this->db->query($sql);
            $result = $stmt->fetchAll();
            
            error_log("Pelanggan count: " . count($result));
            
            // Format data konsisten
            $formatted = [];
            foreach ($result as $row) {
                $formatted[] = [
                    'id' => $row['id_pelanggan'],
                    'id_pelanggan' => $row['id_pelanggan'],
                    'nama_pelanggan' => $row['nama_pelanggan'],
                    'nama' => $row['nama_pelanggan'],
                    'no_hp' => $row['no_hp'],
                    'hp' => $row['no_hp'],
                    'alamat' => $row['alamat']
                ];
            }
            
            return $formatted;
            
        } catch (Exception $e) {
            error_log("ERROR Pelanggan::getAll(): " . $e->getMessage());
            return [];
        }
    }
    
    public function getById($id) {
        $sql = "SELECT * FROM pelanggan WHERE id_pelanggan = ?";
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        try {
            error_log("Pelanggan::create() - Data: " . print_r($data, true));
            
            // ✅ FIX: PostgreSQL dengan RETURNING clause
            $sql = "INSERT INTO pelanggan (nama_pelanggan, no_hp, alamat) 
                    VALUES (:nama, :hp, :alamat) 
                    RETURNING id_pelanggan"; // ← INI PENTING!
            
            $params = [
                ':nama' => $data['nama_pelanggan'] ?? '',
                ':hp' => $data['no_hp'] ?? '',
                ':alamat' => $data['alamat'] ?? ''
            ];
            
            // ✅ FIX: Gunakan query() bukan execute() untuk dapatkan RETURNING value
            $stmt = $this->db->query($sql, $params);
            $result = $stmt->fetch();
            
            if ($result && isset($result['id_pelanggan'])) {
                $newId = $result['id_pelanggan'];
                error_log("Pelanggan::create() - New ID: " . $newId);
                return $newId;
            }
            
            error_log("Pelanggan::create() - No ID returned");
            return false;
            
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
            ':nama' => $data['nama_pelanggan'] ?? '',
            ':hp' => $data['no_hp'] ?? '',
            ':alamat' => $data['alamat'] ?? ''
        ];
        
        return $this->db->execute($sql, $params);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM pelanggan WHERE id_pelanggan = :id";
        return $this->db->execute($sql, [':id' => $id]);
    }
}
?>