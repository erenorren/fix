<?php
require_once __DIR__ . '/../config/database.php'; //penerapan konsep OOP Class Transaksi berhubungan dengan database (PDO)
require_once __DIR__ . '/PaymentMethod.php'; // PaymentMethod + CashPayment, TransferPayment, dll. //penerapan konsep OOP Class Transaksi berhubungan dengan PaymentMethod

/**
 * Menghubungkan Semua Model 
 * CRUD untuk transaksi penitipan hewan
 * 
 */
class Transaksi // Menggunakan Encapsulation private $db; 
{
    private $db;

    public function __construct()
    {
        $this->db = getDB();
    }

    /**
     * Ambil transaksi aktif (hewan yang sedang menginap)
     */

    public function getAll() // Penerapan Konsep OOP Class Transaksi membutuhkan data dari hewan, layanan, dan pelanggan
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
                ORDER BY t.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

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
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Buat transaksi baru
     */
/**
 * Buat transaksi baru - DEBUG VERSION
 */
public function create($data) {
    try {
        error_log("=== MODEL TRANSAKSI CREATE ===");
        error_log("Data received: " . print_r($data, true));
        
        // Generate kode transaksi
        $kodeTransaksi = $this->generateKodeTransaksi();
        error_log("Generated kode: " . $kodeTransaksi);
        
        $sql = "INSERT INTO transaksi 
                (kode_transaksi, id_pelanggan, id_hewan, id_kandang, id_layanan, 
                 biaya_paket, tanggal_masuk, durasi, total_biaya, status)
                VALUES 
                (:kode_transaksi, :id_pelanggan, :id_hewan, :id_kandang, :id_layanan,
                 :biaya_paket, :tanggal_masuk, :durasi, :total_biaya, 'active')";
        
        error_log("SQL: " . $sql);
        
        $stmt = $this->db->prepare($sql);
        
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
        
        error_log("SQL params: " . print_r($params, true));
        
        $result = $stmt->execute($params);
        error_log("Execute result: " . ($result ? 'true' : 'false'));
        
        if ($result) {
            $lastId = $this->db->lastInsertId();
            error_log("Last insert ID: " . $lastId);
            return $lastId;
        } else {
            error_log("Execute failed");
            $errorInfo = $stmt->errorInfo();
            error_log("PDO error: " . print_r($errorInfo, true));
            return false;
        }
        
    } catch (Exception $e) {
        error_log(" MODEL ERROR create transaksi: " . $e->getMessage());
        return false;
    }
}

    /**
     * Update status transaksi (checkout)
     */
    public function checkout($id)
    {
        try {
            $sql = "UPDATE transaksi SET status = 'completed', tanggal_keluar = CURDATE() WHERE id_transaksi = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error checkout transaksi: " . $e->getMessage());
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
                WHERE t.id_transaksi = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function generateKodeTransaksi()
    {
        $sql = "SELECT MAX(CAST(SUBSTRING(kode_transaksi, 4) AS UNSIGNED)) as max_number 
                FROM transaksi 
                WHERE kode_transaksi LIKE 'TRX%'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        
        $nextNumber = ($result['max_number'] ?? 0) + 1;
        return 'TRX' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    
    /**
     * GET BY ID - Ambil transaksi lengkap dengan detail
     * 
     * @param int $id
     * @return array|false
     */
    
    /**
     * GET BY NOMOR - Ambil transaksi berdasarkan nomor transaksi
     * 
     * @param string $nomorTransaksi
     * @return array|false
     */
    public function getByNomor($nomorTransaksi) {
        $sql = "SELECT t.*, 
                       p.nama_pelanggan, p.no_hp, p.alamat,
                       h.nama_hewan, h.jenis, h.ras, h.ukuran, h.warna,
                       u.nama_lengkap as nama_kasir
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
                LEFT JOIN user u ON t.id_user = u.id_user
                WHERE t.nomor_transaksi = :nomor";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['nomor' => $nomorTransaksi]);
        $transaksi = $stmt->fetch();
        
        if ($transaksi) {
            $transaksi['detail_layanan'] = $this->getDetailLayanan($transaksi['id_transaksi']);
        }
        
        return $transaksi;
    }
    
    /**
     * GET DETAIL LAYANAN - Ambil detail layanan transaksi
     * 
     * @param int $idTransaksi
     * @return array
     */
    public function getDetailLayanan($idTransaksi) {
        $sql = "SELECT dt.*, l.kode_layanan, l.nama_layanan, l.kategori_layanan, dt.harga_satuan, dt.jumlah, dt.subtotal
                FROM detail_transaksi dt
                LEFT JOIN layanan l ON dt.id_layanan = l.id_layanan
                WHERE dt.id_transaksi = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $idTransaksi]);
        return $stmt->fetchAll();
    }
    
    /**
     * SEARCH - Cari transaksi berdasarkan keyword
     * 
     * @param string $keyword
     * @return array
     */
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
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['keyword' => "%{$keyword}%"]);
        return $stmt->fetchAll();
    }
    
    /**
     * UPDATE CHECKOUT - Proses check-out & pembayaran
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateCheckout($id, $data) {
        try {
            $this->db->beginTransaction();

            // Ambil transaksi lama (untuk detail & durasi jika perlu)
            $transaksi = $this->getById($id);

            // Jika total_biaya tidak disediakan, hitung ulang dari detail_transaksi dan durasi
            if (!isset($data['total_biaya']) || empty($data['total_biaya'])) {
                $detailLayananStored = $transaksi['detail_layanan'] ?? [];
                
                // ubah format detail agar cocok dengan calculateTotalFromInputs
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

            // Validasi metode pembayaran
            if (!isset($data['metode_pembayaran']) || empty($data['metode_pembayaran'])) {
                throw new Exception("Metode pembayaran tidak boleh kosong");
            }

            // Pilih kelas pembayaran langsung (tanpa factory)
            $methodKey = strtolower(trim($data['metode_pembayaran']));
            $paymentObj = null;

            // mapping sederhana - sesuaikan nama metode dengan data input yang dikirim
            if (in_array($methodKey, ['cash', 'tunai'])) {
                $paymentObj = new CashPayment();
            } elseif (in_array($methodKey, ['transfer', 'bank_transfer', 'bank transfer', 'bank'])) {
                $paymentObj = new TransferPayment();
            } else {
                // jika ada implementasi lain di PaymentMethod.php, tambahkan elseif di sini
                // fallback: jika class bernama sama ada, coba instansiasi (lebih dinamis)
                $classCandidate = ucfirst($methodKey) . 'Payment'; // contoh 'qris' => 'QrisPayment'
                if (class_exists($classCandidate)) {
                    $paymentObj = new $classCandidate();
                } else {
                    throw new Exception("Metode pembayaran tidak dikenal: {$data['metode_pembayaran']}");
                }
            }

            // Jalankan proses pembayaran (polymorphism)
            $paymentResult = $paymentObj->processPayment((float)$data['total_biaya'], [
                'id_transaksi' => $id,
                'meta' => $data['meta'] ?? []
            ]);

            if (!isset($paymentResult['success']) || $paymentResult['success'] !== true) {
                // jika gagal, batalkan dan rollback
                throw new Exception("Pembayaran gagal: " . ($paymentResult['detail'] ?? 'Unknown'));
            }

            // Update transaksi (simpan metode & tandai lunas)
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
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'id' => $id,
                'tanggal_keluar' => $data['tanggal_keluar_aktual'],
                'jam_keluar' => $data['jam_keluar_aktual'] ?? date('H:i:s'),
                'durasi_hari' => $data['durasi_hari'],
                'diskon' => $data['diskon'] ?? 0,
                'total_biaya' => $data['total_biaya'],
                'metode_pembayaran' => $paymentObj->getName()
            ]);
            
            // Update status hewan jadi sudah_diambil
            $sqlHewan = "UPDATE hewan SET status = 'sudah_diambil' WHERE id_hewan = :id";
            $stmtHewan = $this->db->prepare($sqlHewan);
            $stmtHewan->execute(['id' => $transaksi['id_hewan']]);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error checkout: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * GET SEDANG DITITIPKAN - Ambil semua transaksi yang masih berlangsung
     * 
     * @return array
     */
    public function getSedangDititipkan() {
        $sql = "SELECT t.*, p.nama_pelanggan, h.nama_hewan
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
                WHERE t.status = 'sedang_dititipkan'
                ORDER BY t.tanggal_masuk DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * GET LAPORAN HARIAN
     * 
     * @param string $tanggal (Y-m-d)
     * @return array
     */
    public function getLaporanHarian($tanggal) {
        $sql = "SELECT t.*, p.nama_pelanggan, h.nama_hewan
                FROM transaksi t
                LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
                LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
                WHERE DATE(t.tanggal_masuk) = :tanggal
                ORDER BY t.tanggal_masuk DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['tanggal' => $tanggal]);
        return $stmt->fetchAll();
    }
    
    /**
     * HITUNG TOTAL PENDAPATAN
     * 
     * @param string $tanggalMulai
     * @param string $tanggalAkhir
     * @return float
     */
    public function hitungPendapatan($tanggalMulai, $tanggalAkhir) {
        $sql = "SELECT SUM(total_biaya) as total 
                FROM transaksi 
                WHERE DATE(tanggal_masuk) BETWEEN :mulai AND :akhir
                AND status_pembayaran = 'lunas'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'mulai' => $tanggalMulai,
            'akhir' => $tanggalAkhir
        ]);
        
        $result = $stmt->fetch();
        return (float)($result['total'] ?? 0);
    }

    /**
    * Hitung subtotal & total berdasarkan durasi dan detail layanan
    * $durasiHari = int
    * $detailLayanan = array of ['id_layanan', 'harga', 'qty'] OR ['id_layanan','harga_satuan','jumlah']
    * $paketPerHari = float (jika ada paket harian)
    */
    public function calculateTotalFromInputs(int $durasiHari, array $detailLayanan, float $paketPerHari = 0.0, float $diskon = 0.0) {
        $subtotalLayanan = 0.0;
        foreach ($detailLayanan as $d) {
            // dukung kedua format: ['harga','qty'] atau ['harga_satuan','jumlah']
            $harga = isset($d['harga']) ? (float)$d['harga'] : (isset($d['harga_satuan']) ? (float)$d['harga_satuan'] : 0.0);
            $qty   = isset($d['qty']) ? (int)$d['qty'] : (isset($d['jumlah']) ? (int)$d['jumlah'] : 1);
            $subtotalLayanan += $harga * $qty;
        }

        $biayaPaket = $paketPerHari * max(1, $durasiHari);
        $subtotal = $biayaPaket + $subtotalLayanan;
        $total = $subtotal - $diskon; // sesuaikan jika ada pajak, biaya tambahan, dsb.

        return [
            'biaya_paket' => $biayaPaket,
            'subtotal_layanan' => $subtotalLayanan,
            'subtotal' => $subtotal,
            'diskon' => $diskon,
            'total_biaya' => $total
        ];
    }
}
