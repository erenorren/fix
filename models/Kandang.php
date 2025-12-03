<?php
require_once __DIR__ . '/../core/Database.php';

class Kandang {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // models/Kandang.php
public function getAll() {
    $sql = "SELECT id_kandang, kode_kandang, tipe, status FROM kandang";
    $stmt = $this->db->query($sql);
    $result = $stmt->fetchAll();
    
    // Format data
    $formatted = [];
    foreach ($result as $row) {
        $formatted[] = [
            'id' => $row['id_kandang'],
            'kode_kandang' => $row['kode_kandang'],
            'tipe' => $row['tipe'],
            'status' => $row['status']
        ];
    }
    
    return $formatted;
}

    public function updateStatus($id_kandang, $status) {
        try {
            error_log("Kandang::updateStatus($id_kandang, $status)");
            
            $sql = "UPDATE kandang SET status = ? WHERE id_kandang = ?";
            $result = $this->db->execute($sql, [$status, $id_kandang]);
            
            error_log("Kandang::updateStatus result: " . ($result ? 'success' : 'failed'));
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error update status kandang: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ambil kandang tersedia berdasarkan jenis dan ukuran hewan
     */
    public function getAvailableKandang($jenis, $ukuran) {
        try {
            $tipeKandang = [];
            
            if ($jenis === 'Kucing') {
                switch ($ukuran) {
                    case 'Kecil': $tipeKandang = ['Kecil']; break;
                    case 'Sedang': $tipeKandang = ['Sedang']; break;
                    case 'Besar': $tipeKandang = ['Besar']; break;
                    default: $tipeKandang = ['Kecil', 'Sedang', 'Besar']; break;
                }
            } 
            else if ($jenis === 'Anjing') {
                switch ($ukuran) {
                    case 'Kecil': $tipeKandang = ['Sedang']; break;
                    case 'Sedang': $tipeKandang = ['Sedang', 'Besar']; break;
                    case 'Besar': $tipeKandang = ['Besar']; break;
                    default: $tipeKandang = ['Sedang', 'Besar']; break;
                }
            }

            if (empty($tipeKandang)) {
                return [];
            }

            // PostgreSQL IN array
            $placeholders = implode(',', array_fill(0, count($tipeKandang), '?'));
            $sql = "SELECT 
                        k.id_kandang AS id, 
                        k.kode_kandang, 
                        k.tipe, 
                        k.status
                    FROM kandang k 
                    WHERE k.status = 'tersedia' 
                      AND k.tipe IN ($placeholders)
                    ORDER BY k.kode_kandang";
            
            $stmt = $this->db->query($sql, $tipeKandang);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("Error getAvailableKandang: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Hitung jumlah kandang berdasarkan tipe
     * PostgreSQL compatible
     */
    public function countByType()
    {
        try {
            $sql = "SELECT tipe, COUNT(*) AS jumlah 
                    FROM kandang 
                    WHERE status = 'tersedia' 
                    GROUP BY tipe";
            
            $stmt = $this->db->query($sql);
            $result = $stmt->fetchAll();
            
            // Format hasil menjadi array asosiatif [tipe => jumlah]
            $counts = [];
            foreach ($result as $row) {
                $counts[$row['tipe']] = $row['jumlah'];
            }
            
            return $counts;
            
        } catch (Exception $e) {
            error_log("Error countByType kandang: " . $e->getMessage());
            return ['Kecil' => 0, 'Sedang' => 0, 'Besar' => 0];
        }
    }
}
?>