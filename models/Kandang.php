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
     */
    public function getAll()
    {
        try {
            $sql = "SELECT 
                        k.id_kandang as id, 
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
     */
    public function getAllKandang()
    {
        try {
            $sql = "SELECT 
                        k.id_kandang as id, 
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
     */
    public function updateStatus($id_kandang, $status)
    {
        try {
            $sql = "UPDATE kandang SET status = ? WHERE id_kandang = ?";
            return $this->db->execute($sql, [$status, $id_kandang]);
        } catch (Exception $e) {
            error_log("Error update status kandang: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ambil kandang tersedia berdasarkan jenis dan ukuran hewan
     */
    public function getAvailableKandang($jenis, $ukuran)
    {
        try {
            // Logika pemilihan kandang berdasarkan jenis dan ukuran
            $tipeKandang = [];
            
            if ($jenis === 'Kucing') {
                if ($ukuran === 'Kecil' || $ukuran === 'Sedang' || empty($ukuran)) {
                    $tipeKandang = ['Kecil', 'Sedang'];
                } else if ($ukuran === 'Besar') {
                    $tipeKandang = ['Sedang', 'Besar'];
                }
            } else if ($jenis === 'Anjing') {
                if ($ukuran === 'Kecil' || empty($ukuran)) {
                    $tipeKandang = ['Sedang'];
                } else if ($ukuran === 'Sedang') {
                    $tipeKandang = ['Sedang', 'Besar'];
                } else if ($ukuran === 'Besar') {
                    $tipeKandang = ['Besar'];
                }
            }

            if (empty($tipeKandang)) {
                return [];
            }

            $placeholders = str_repeat('?,', count($tipeKandang) - 1) . '?';
            $sql = "SELECT 
                        k.id_kandang as id, 
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
     */
    public function countByType()
    {
        try {
            $sql = "SELECT tipe, COUNT(*) as jumlah 
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