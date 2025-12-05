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
     * ✅ FIXED: Buat transaksi baru (SIMPLE & CLEAR)
     * Kompatibel PostgreSQL dengan RETURNING
     */
    public function create($data) {
        try {
            // Generate kode transaksi
            $kodeTransaksi = $this->generateKodeTransaksi();

            // ✅ FIX: Validasi status - harus sesuai ENUM
            $validStatuses = ['active', 'completed', 'sedang_dititipkan', 'selesai'];
            $status = $data['status'] ?? 'active';
            
            if (!in_array($status, $validStatuses)) {
                $status = 'active'; // Default ke 'active' kalau invalid
            }

            // ✅ FIX: Prepare parameters dengan validasi
            $params = [
                ':kode_transaksi' => $kodeTransaksi,
                ':id_pelanggan' => $data['id_pelanggan'] ?? null,
                ':id_hewan' => $data['id_hewan'] ?? null,
                ':id_kandang' => $data['id_kandang'] ?? null,
                ':id_layanan' => $data['id_layanan'] ?? null,
                ':biaya_paket' => floatval($data['biaya_paket'] ?? 0),
                ':tanggal_masuk' => $data['tanggal_masuk'] ?? date('Y-m-d'),
                ':durasi' => intval($data['durasi'] ?? 1),
                ':durasi_hari' => intval($data['durasi_hari'] ?? $data['durasi'] ?? 1),
                ':total_biaya' => floatval($data['total_biaya'] ?? 0),
                ':status' => $status
            ];

            // Log untuk debugging
            error_log("Transaksi::create() - Params: " . json_encode($params));

            // ✅ SIMPLE: Pakai RETURNING langsung (PostgreSQL style)
            $sql = "INSERT INTO transaksi 
                    (kode_transaksi, id_pelanggan, id_hewan, id_kandang, id_layanan, 
                     biaya_paket, tanggal_masuk, durasi, durasi_hari, total_biaya, status)
                    VALUES 
                    (:kode_transaksi, :id_pelanggan, :id_hewan, :id_kandang, :id_layanan,
                     :biaya_paket, :tanggal_masuk, :durasi, :durasi_hari, :total_biaya, :status)
                    RETURNING id_transaksi";

            // Execute query
            $stmt = $this->db->query($sql, $params);
            
            if (!$stmt) {
                error_log("Transaksi::create() - Query returned null");
                return false;
            }

            // Ambil ID dari RETURNING
            $result = $stmt->fetch();
            
            if ($result && isset($result['id_transaksi'])) {
                $newId = $result['id_transaksi'];
                error_log("Transaksi::create() - SUCCESS! ID: " . $newId);
                return $newId;
            }

            error_log("Transaksi::create() - No ID returned from RETURNING clause");
            return false;

        } catch (PDOException $e) {
            error_log("Transaksi::create() - PDO ERROR: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            return false;
            
        } catch (Exception $e) {
            error_log("Transaksi::create() - ERROR: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ FIXED: Checkout transaksi dengan RETURNING
     */
    public function checkout($id)
    {
        try {
            $sql = "UPDATE transaksi 
                    SET status = 'completed', 
                        tanggal_keluar = CURRENT_DATE 
                    WHERE id_transaksi = :id
                    RETURNING id_transaksi";

            $stmt = $this->db->query($sql, [':id' => $id]);
            
            if (!$stmt) {
                return false;
            }
            
            $result = $stmt->fetch();
            return $result ? true : false;

        } catch (Exception $e) {
            error_log("Error checkout transaksi: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get transaksi by ID
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
        return $stmt->fetch()['total'] ?? 0;
    }

    public function getTotalHewanAktifByJenis($jenis) {
        $sql = "SELECT COUNT(*) as total 
                FROM transaksi t 
                JOIN hewan h ON t.id_hewan = h.id_hewan 
                WHERE t.status = 'active' AND h.jenis = :jenis";
        
        $stmt = $this->db->query($sql, [':jenis' => $jenis]);
        return $stmt->fetch()['total'] ?? 0;
    }

    /**
     * ✅ FIXED: Generate kode transaksi - PostgreSQL compatible
     */
    private function generateKodeTransaksi()
    {
        try {
            $sql = "SELECT 
                        COALESCE(MAX(CAST(SUBSTRING(kode_transaksi FROM 4) AS INTEGER)), 0) AS max_number
                    FROM transaksi 
                    WHERE kode_transaksi LIKE 'TRX%'";
            
            $result = $this->db->query($sql)->fetch();
            $nextNumber = ($result['max_number'] ?? 0) + 1;
            
            return 'TRX' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            
        } catch (Exception $e) {
            error_log("Error generateKodeTransaksi: " . $e->getMessage());
            // Fallback: generate random
            return 'TRX' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Search transaksi
     */
    public function getByNomor($nomorTransaksi) {
        $sql = "SELECT t.*, p.nama_pelanggan, p.no_hp, p.alamat, h.nama_hewan, h.jenis, h.ras, 
                       h.ukuran, h.warna, u.nama_lengkap as nama_kasir
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
                LEFT JOIN \"user\" u ON t.id_user = u.id_user
                WHERE t.kode_transaksi = :nomor";
        
        $stmt = $this->db->query($sql, [':nomor' => $nomorTransaksi]);
        $transaksi = $stmt->fetch();

        if ($transaksi) {
            $transaksi['detail_layanan'] = $this->getDetailLayanan($transaksi['id_transaksi']);
        }

        return $transaksi;
    }

    public function getDetailLayanan($idTransaksi) {
        $sql = "SELECT dt.*, l.nama_layanan
                FROM detail_transaksi dt
                LEFT JOIN layanan l ON dt.id_layanan = l.id_layanan
                WHERE dt.id_transaksi = :id";
        
        return $this->db->query($sql, [':id' => $idTransaksi])->fetchAll();
    }

    public function search($keyword) {
        $sql = "SELECT t.*, 
                       p.nama_pelanggan,
                       h.nama_hewan
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
                WHERE t.kode_transaksi ILIKE :keyword
                OR p.nama_pelanggan ILIKE :keyword
                OR h.nama_hewan ILIKE :keyword
                ORDER BY t.created_at DESC";

        return $this->db->query($sql, [':keyword' => "%{$keyword}%"])->fetchAll();
    }

    /**
     * ✅ FIXED: Checkout update dengan transaction
     */
    public function updateCheckout($id, $data) {
        try {
            $this->db->beginTransaction();

            $transaksi = $this->getById($id);

            if (!$transaksi) {
                throw new Exception("Transaksi tidak ditemukan");
            }

            // Perhitungan biaya
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
                ':id' => $id,
                ':tanggal_keluar' => $data['tanggal_keluar_aktual'],
                ':jam_keluar' => $data['jam_keluar_aktual'] ?? date('H:i:s'),
                ':durasi_hari' => $data['durasi_hari'],
                ':diskon' => $data['diskon'],
                ':total_biaya' => $data['total_biaya'],
                ':metode_pembayaran' => $data['metode_pembayaran'] ?? 'tunai'
            ]);

            // Update status hewan
            $sqlHewan = "UPDATE hewan SET status = 'sudah_diambil' WHERE id_hewan = :id";
            $this->db->execute($sqlHewan, [':id' => $transaksi['id_hewan']]);

            // Update status kandang
            if ($transaksi['id_kandang']) {
                $sqlKandang = "UPDATE kandang SET status = 'tersedia' WHERE id_kandang = :id";
                $this->db->execute($sqlKandang, [':id' => $transaksi['id_kandang']]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updateCheckout: " . $e->getMessage());
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

        return $this->db->query($sql)->fetchAll();
    }

    public function hitungPendapatan($tanggalMulai, $tanggalAkhir) {
        $sql = "SELECT COALESCE(SUM(total_biaya), 0) as total 
                FROM transaksi 
                WHERE tanggal_masuk::DATE BETWEEN :mulai AND :akhir
                AND status_pembayaran = 'lunas'";

        return $this->db->query($sql, [
            ':mulai' => $tanggalMulai,
            ':akhir' => $tanggalAkhir
        ])->fetch()['total'] ?? 0;
    }

    public function calculateTotalFromInputs(int $durasiHari, array $detailLayanan, float $paketPerHari = 0.0, float $diskon = 0.0) {
        $subtotalLayanan = 0.0;
        foreach ($detailLayanan as $d) {
            $harga = $d['harga'] ?? ($d['harga_satuan'] ?? 0);
            $qty   = $d['qty'] ?? ($d['jumlah'] ?? 1);
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
?>