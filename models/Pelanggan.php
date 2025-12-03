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
        
        $sql = "SELECT 
                    id_pelanggan,
                    nama_pelanggan,
                    no_hp,
                    alamat
                FROM pelanggan 
                ORDER BY nama_pelanggan";
        
        $stmt = $this->db->query($sql);
        $result = $stmt->fetchAll();
        
        // DEBUG: Cek hasil query
        error_log("SQL Query: " . $sql);
        error_log("Query result count: " . count($result));
        
        if (empty($result)) {
            error_log("WARNING: Query pelanggan mengembalikan hasil KOSONG!");
            error_log("Check if table 'pelanggan' exists and has data");
            
            // Coba query alternatif untuk debugging
            $testSql = "SHOW TABLES LIKE 'pelanggan'";
            $testStmt = $this->db->query($testSql);
            $tableExists = $testStmt->fetch();
            error_log("Table pelanggan exists: " . ($tableExists ? 'YES' : 'NO'));
            
            // Cek data di table
            $countSql = "SELECT COUNT(*) as total FROM pelanggan";
            $countStmt = $this->db->query($countSql);
            $countResult = $countStmt->fetch();
            error_log("Total records in pelanggan table: " . ($countResult['total'] ?? 0));
        }
        
        // Format untuk consistency
        $formatted = [];
        foreach ($result as $row) {
            $formatted[] = [
                'id' => $row['id_pelanggan'],
                'id_pelanggan' => $row['id_pelanggan'],
                'nama_pelanggan' => $row['nama_pelanggan'],
                'nama' => $row['nama_pelanggan'], // alias
                'no_hp' => $row['no_hp'],
                'hp' => $row['no_hp'], // alias
                'alamat' => $row['alamat']
            ];
        }
        
        error_log("Pelanggan::getAll() - Returning " . count($formatted) . " records");
        
        return $formatted;
        
    } catch (Exception $e) {
        error_log("ERROR Pelanggan::getAll(): " . $e->getMessage());
        error_log("Full error: " . $e->getTraceAsString());
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
            
            $sql = "INSERT INTO pelanggan (nama_pelanggan, no_hp, alamat) 
                    VALUES (?, ?, ?)";
            
            $params = [
                $data['nama_pelanggan'] ?? '',
                $data['no_hp'] ?? '',
                $data['alamat'] ?? ''
            ];
            
            $result = $this->db->execute($sql, $params);
            
            if ($result) {
                $newId = $this->db->lastInsertId();
                error_log("Pelanggan::create() - New ID: " . $newId);
                return $newId;
            }
            
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