<?php
require_once _DIR_ . '/../core/Database.php';

// transaksi
class Transaksi 
{
    private $db;
    public function __construct()
    {
        $this->db = new Database(); // FIX: Koneksi OOP yang benar
    }

    /**
     * Ambil semua data transaksi (READ - getAll)
     * PostgreSQL: query tanpa perubahan khusus
     */
    public function getAll()
    {
        $sql = "SELECT 
                    t.*, p.nama_pelanggan, h.nama_hewan, l.nama_layanan, k.kode_kandang
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
                LEFT JOIN layanan l ON t.id_layanan = l.id_layanan
                LEFT JOIN kandang k ON t.id_kandang = k.id_kandang
                ORDER BY t.created_at DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Ambil transaksi aktif (READ - getActiveTransactions)
     * PostgreSQL compatible
     */
    public function getActiveTransactions()
    {
        $sql = "SELECT 
                    t.id_transaksi, t.kode_transaksi, p.nama_pelanggan, h.nama_hewan, h.jenis as jenis_hewan, 
                    k.kode_kandang, t.tanggal_masuk, t.durasi, t.total_biaya
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
     * Buat transaksi baru (CREATE)
     * PostgreSQL compatible
     */
    public function create($data) {
        try {
            $kodeTransaksi = $this->generateKodeTransaksi();
            
            $sql = "INSERT INTO transaksi 
                     (kode_transaksi, id_pelanggan, id_hewan, id_kandang, id_layanan, 
                      biaya_paket, tanggal_masuk, durasi, total_biaya, status)
                     VALUES 
                     (:kode_transaksi, :id_pelanggan, :id_hewan, :id_kandang, :id_layanan,
                      :biaya_paket, :tanggal_masuk, :durasi, :total_biaya, 'active')";
            
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
            
            $result = $this->db->execute($sql, $params);
            
            if ($result) {
                return $this->db->lastInsertId(); // PostgreSQL: pastikan wrapper PDO->lastInsertId() sesuai sequence
            } else {
                return false;
            }
            
        } catch (Exception $e) {
            error_log(" MODEL ERROR create transaksi: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update status transaksi (checkout)
     * PostgreSQL: CURDATE() diganti CURRENT_DATE
     */
    public function checkout($id)
    {
        try {
            $sql = "UPDATE transaksi SET status = 'completed', tanggal_keluar = CURRENT_DATE WHERE id_transaksi = ?";
            $result = $this->db->execute($sql, [$id]);
            return $result;
        } catch (Exception $e) {
            error_log("Error checkout transaksi di model: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ambil data transaksi berdasarkan ID (READ)
     * PostgreSQL compatible
     */
    public function getById($id)
    {
        $sql = "SELECT t.*, p.nama_pelanggan, p.no_hp, p.alamat, h.nama_hewan, h.jenis, h.ras, h.ukuran, h.warna, l.nama_layanan, l.harga as harga_layanan, k.kode_kandang, k.tipe as tipe_kandang
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
                LEFT JOIN layanan l ON t.id_layanan = l.id_layanan
                LEFT JOIN kandang k ON t.id_kandang = k.id_kandang
                WHERE t.id_transaksi = ?";
        
        $stmt = $this->db->query($sql, [$id]);
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
                WHERE t.status = 'active' AND h.jenis = ?";
        $stmt = $this->db->query($sql, [$jenis]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Helper: Generate kode transaksi
     * PostgreSQL compatible
     */
    private function generateKodeTransaksi()
    {
        $sql = "SELECT MAX(CAST(SUBSTRING(kode_transaksi, 4) AS INTEGER)) as max_number 
                FROM transaksi 
                WHERE kode_transaksi LIKE 'TRX%'";
        
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        
        $nextNumber = ($result['max_number'] ?? 0) + 1;
        return 'TRX' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
    
    
    public function getByNomor($nomorTransaksi) {
        $sql = "SELECT t.*, p.nama_pelanggan, p.no_hp, p.alamat, h.nama_hewan, h.jenis, h.ras, h.ukuran, h.warna, u.nama_lengkap as nama_kasir
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
                LEFT JOIN user u ON t.id_user = u.id_user
                WHERE t.nomor_transaksi = :nomor";
        
        $stmt = $this->db->query($sql, ['nomor' => $nomorTransaksi]);
        $transaksi = $stmt->fetch();
        
        if ($transaksi) {
            $transaksi['detail_layanan'] = $this->getDetailLayanan($transaksi['id_transaksi']);
        }
        
        return $transaksi;
    }
    
    public function getDetailLayanan($idTransaksi) {
        $sql = "SELECT dt.*, l.kode_layanan, l.nama_layanan, l.kategori_layanan, dt.harga_satuan, dt.jumlah, dt.subtotal
                FROM detail_transaksi dt
                LEFT JOIN layanan l ON dt.id_layanan = l.id_layanan
                WHERE dt.id_transaksi = :id";
        
        $stmt = $this->db->query($sql, ['id' => $idTransaksi]);
        return $stmt->fetchAll();
    }
    
    public function search($keyword) {
        $sql = "SELECT t.*, 
                       p.nama_pelanggan,
                       h.nama_hewan
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
                WHERE t.nomor_transaksi LIKE :keyword
                OR p.nama_pelanggan LIKE :keyword
                OR h.nama_hewan LIKE :keyword
                ORDER BY t.created_at DESC";
        
        $stmt = $this->db->query($sql);
        $stmt->execute(['keyword' => "%{$keyword}%"]);
        return $stmt->fetchAll();
    }
    
    public function updateCheckout($id, $data) {
        try {
            $this->db->beginTransaction();

            $transaksi = $this->getById($id);

            if (!isset($data['total_biaya']) || empty($data['total_biaya'])) {
                $detailLayananStored = $transaksi['detail_layanan'] ?? [];
                $detailForCalc = [];
                foreach ($detailLayananStored as $d) {
                    $detailForCalc[] = [
                        'harga' => $d['harga_satuan'] ?? $d['harga'] ?? 0,
                        'qty' => $d['jumlah'] ?? $d['qty'] ?? 1
                    ];
                }

                $calc = $this->calculateTotalFromInputs(
                    $data['durasi_hari'] ?? $transaksi['durasi_hari'] ?? 0,
                    $detailForCalc,
                    $data['paket_per_hari'] ?? 0,
                    $data['diskon'] ?? ($transaksi['diskon'] ?? 0)
                );

                $data['total_biaya'] = $calc['total_biaya'];
                $data['diskon'] = $calc['diskon'];
            }

            // PostgreSQL compatible: update transaksi
            $sql = "UPDATE transaksi 
                    SET tanggal_keluar_aktual = :tanggal_keluar,
                        jam_keluar_aktual = :jam_keluar,
                        durasi_hari = :durasi_hari,
                        status = 'selesai',
                        diskon = :diskon,
                        total_biaya = :total_biaya,
                        metode_pembayaran = :metode_pembayaran,
                        status_pembayaran = 'lunas'
                    WHERE id_transaksi = :id";
            
            $this->db->execute($sql, [
                'id' => $id,
                'tanggal_keluar' => $data['tanggal_keluar_aktual'],
                'jam_keluar' => $data['jam_keluar_aktual'] ?? date('H:i:s'),
                'durasi_hari' => $data['durasi_hari'],
                'diskon' => $data['diskon'] ?? 0,
                'total_biaya' => $data['total_biaya'],
                'metode_pembayaran' => $data['metode_pembayaran'] ?? ''
            ]);

            // PostgreSQL compatible: update status hewan
            $sqlHewan = "UPDATE hewan SET status = 'sudah_diambil' WHERE id_hewan = :id";
            $this->db->execute($sqlHewan, ['id' => $transaksi['id_hewan']]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error checkout: " . $e->getMessage());
            return false;
        }
    }
    
    public function getSedangDititipkan() {
        $sql = "SELECT t.*, p.nama_pelanggan, h.nama_hewan
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
                WHERE t.status = 'sedang_dititipkan'
                ORDER BY t.tanggal_masuk DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function hitungPendapatan($tanggalMulai, $tanggalAkhir) {
        // PostgreSQL compatible: casting tanggal_masuk ke date
        $sql = "SELECT SUM(total_biaya) as total 
                FROM transaksi 
                WHERE tanggal_masuk::DATE BETWEEN :mulai AND :akhir
                AND status_pembayaran = 'lunas'";
        
        $stmt = $this->db->query($sql);
        $stmt->execute([
            'mulai' => $tanggalMulai,
            'akhir' => $tanggalAkhir
        ]);
        
        $result = $stmt->fetch();
        return (float)($result['total'] ?? 0);
    }

    public function calculateTotalFromInputs(int $durasiHari, array $detailLayanan, float $paketPerHari = 0.0, float $diskon = 0.0) {
        $subtotalLayanan = 0.0;
        foreach ($detailLayanan as $d) {
            $harga = isset($d['harga']) ? (float)$d['harga'] : (isset($d['harga_satuan']) ? (float)$d['harga_satuan'] : 0.0);
            $qty   = isset($d['qty']) ? (int)$d['qty'] : (isset($d['jumlah']) ? (int)$d['jumlah'] : 1);
            $subtotalLayanan += $harga * $qty;
        }

        $biayaPaket = $paketPerHari * max(1, $durasiHari);
        $subtotal = $biayaPaket + $subtotalLayanan;
        $total = $subtotal - $diskon;

        return [
            'biaya_paket' => $biayaPaket,
            'subtotal_layanan' => $subtotalLayanan,
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'total_biaya' => $total
        ];
    }
}