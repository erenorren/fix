<?php
require_once __DIR__ . '/../core/Database.php';

class Kandang
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Ambil semua data kandang yang tersedia (untuk transaksi)
     * PostgreSQL compatible
     */
    public function getAll()
    {
        try {
            $sql = "SELECT 
                        k.id_kandang AS id, 
                        k.kode_kandang, 
                        k.tipe, 
                        k.status
                    FROM kandang k 
                    WHERE k.status = 'tersedia' 
                    ORDER BY k.kode_kandang";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("ERROR di Kandang::getAll(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Ambil semua data kandang (untuk halaman data kandang)
     * PostgreSQL compatible
     */
    public function getAllKandang()
    {
        try {
            $sql = "SELECT 
                        k.id_kandang AS id, 
                        k.kode_kandang, 
                        k.tipe, 
                        k.status,
                        k.catatan
                    FROM kandang k 
                    ORDER BY k.kode_kandang";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            error_log("ERROR di Kandang::getAllKandang(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Update status kandang
     * PostgreSQL compatible
     * Menggunakan named parameter untuk Supabase
     */
    public function updateStatus($id_kandang, $status)
    {
        try {
            $sql = "UPDATE kandang 
                    SET status = :status 
                    WHERE id_kandang = :id_kandang";
            return $this->db->execute($sql, [
                "status" => $status,
                "id_kandang" => $id_kandang
            ]);
        } catch (Exception $e) {
            error_log("Error update status kandang: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ambil kandang tersedia berdasarkan jenis dan ukuran hewan
     * PostgreSQL compatible
     * Menggunakan named parameter untuk array tipe kandang
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

            // PostgreSQL IN array dengan ANY()
            $sql = "SELECT 
                        k.id_kandang AS id, 
                        k.kode_kandang, 
                        k.tipe, 
                        k.status
                    FROM kandang k 
                    WHERE k.status = 'tersedia' 
                      AND k.tipe = ANY(:tipe_kandang)
                    ORDER BY k.kode_kandang";
            
            $stmt = $this->db->query($sql, ["tipe_kandang" => $tipeKandang]);
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