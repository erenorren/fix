<?php
require_once __DIR__ . '/../core/Database.php';

class Transaksi 
{
    private $db;
    
    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Ambil semua data transaksi
     */
    public function getAll()
    {
        $sql = "SELECT 
                t.*, 
                p.nama_pelanggan, 
                h.nama_hewan, 
                l.nama_layanan, 
                k.kode_kandang
            FROM transaksi t
            LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
            LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
            LEFT JOIN layanan l ON t.id_layanan = l.id_layanan
            LEFT JOIN kandang k ON t.id_kandang = k.id_kandang
            ORDER BY t.tanggal_masuk DESC"; 
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Ambil transaksi aktif
     */
    public function getActiveTransactions()
    {
        $sql = "SELECT 
                    t.id_transaksi, 
                    t.kode_transaksi, 
                    p.nama_pelanggan, 
                    h.nama_hewan, 
                    h.jenis as jenis_hewan, 
                    k.kode_kandang, 
                    t.tanggal_masuk, 
                    t.durasi, 
                    t.total_biaya
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
                LEFT JOIN kandang k ON t.id_kandang = k.id_kandang
                WHERE t.status = 'active'
                ORDER BY t.tanggal_masuk DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Buat transaksi baru - FIXED FOR POSTGRESQL
     */
    public function create($data) {
        try {
            $kodeTransaksi = $this->generateKodeTransaksi();
            
            // ✅ FIX: PostgreSQL dengan RETURNING clause
            $sql = "INSERT INTO transaksi 
                     (kode_transaksi, id_pelanggan, id_hewan, id_kandang, id_layanan, 
                      biaya_paket, tanggal_masuk, durasi, total_biaya, status)
                     VALUES 
                     (:kode_transaksi, :id_pelanggan, :id_hewan, :id_kandang, :id_layanan,
                      :biaya_paket, :tanggal_masuk, :durasi, :total_biaya, 'active')
                     RETURNING id_transaksi"; // ← INI YANG DITAMBAH!
            
            $params = [
                "kode_transaksi" => $kodeTransaksi,
                "id_pelanggan" => $data["id_pelanggan"],
                "id_hewan" => $data["id_hewan"], 
                "id_kandang" => $data["id_kandang"],
                "id_layanan" => $data["id_layanan"],
                "biaya_paket" => $data["biaya_paket"],
                "tanggal_masuk" => $data["tanggal_masuk"],
                "durasi" => $data["durasi"],
                "total_biaya" => $data["total_biaya"]
            ];
            
            // ✅ FIX: Gunakan query() untuk dapatkan RETURNING value
            $stmt = $this->db->query($sql, $params);
            $result = $stmt->fetch();
            
            if ($result && isset($result['id_transaksi'])) {
                return $result['id_transaksi'];
            } else {
                error_log("CREATE TRANSAKSI ERROR: No ID returned");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("MODEL ERROR create transaksi: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update status transaksi (checkout) - FIXED FOR POSTGRESQL
     */
    public function checkout($id)
    {
        try {
            // ✅ FIX: PostgreSQL CURRENT_DATE
            $sql = "UPDATE transaksi 
                    SET status = 'completed', 
                        tanggal_keluar = CURRENT_DATE 
                    WHERE id_transaksi = :id
                    RETURNING id_transaksi";
            
            $stmt = $this->db->query($sql, [':id' => $id]);
            $result = $stmt->fetch();
            
            return $result ? true : false;
            
        } catch (Exception $e) {
            error_log("Error checkout transaksi di model: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ambil data transaksi berdasarkan ID
     */
    public function getById($id)
    {
        $sql = "SELECT 
                t.*, 
                p.nama_pelanggan, 
                p.no_hp, 
                p.alamat, 
                h.nama_hewan, 
                h.jenis, 
                h.ras, 
                h.ukuran, 
                h.warna, 
                l.nama_layanan, 
                l.harga as harga_layanan, 
                k.kode_kandang, 
                k.tipe as tipe_kandang
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
                LEFT JOIN layanan l ON t.id_layanan = l.id_layanan
                LEFT JOIN kandang k ON t.id_kandang = k.id_kandang
                WHERE t.id_transaksi = :id";
        
        $stmt = $this->db->query($sql, [':id' => $id]);
        return $stmt->fetch();
    }

    public function getTotalHewanAktif() {
        $sql = "SELECT COUNT(*) as total FROM transaksi WHERE status = 'active'";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function getTotalHewanAktifByJenis($jenis) {
        $sql = "SELECT COUNT(*) as total 
                FROM transaksi t 
                JOIN hewan h ON t.id_hewan = h.id_hewan 
                WHERE t.status = 'active' AND h.jenis = :jenis";
        
        $stmt = $this->db->query($sql, [':jenis' => $jenis]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Generate kode transaksi - FIXED FOR POSTGRESQL
     */
    private function generateKodeTransaksi()
    {
        $sql = "SELECT 
                    CASE 
                        WHEN MAX(CAST(SUBSTRING(kode_transaksi FROM 4) AS INTEGER)) IS NULL THEN 0
                        ELSE MAX(CAST(SUBSTRING(kode_transaksi FROM 4) AS INTEGER))
                    END as max_number 
                FROM transaksi 
                WHERE kode_transaksi LIKE 'TRX%'";
        
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        
        $nextNumber = ($result['max_number'] ?? 0) + 1;
        return 'TRX' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
?>