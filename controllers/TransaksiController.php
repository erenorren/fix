<?php

class TransaksiController {
    // COMMENT: Controller class → memisahkan logika bisnis dari tampilan (konsep MVC)
    // COMMENT: Dependency injection ke model-model & modular → mendukung reusability & clean architecture
    private $transaksiModel;
    private $detailTransaksiModel;
    private $pelangganModel;
    private $hewanModel;
    private $kandangModel;

    public function __construct() {
        // COMMENT: require_once → dependency management manual dalam arsitektur MVC
    // COMMENT: Penerapan Encapsulation → property model disimpan private dan hanya akses melalui object
    // COMMENT: Pemanggilan Model sebagai objek → implementasi dasar OOP (instansiasi class)
        require_once __DIR__ . '/../models/Transaksi.php';
        require_once __DIR__ . '/../models/DetailTransaksi.php';
        require_once __DIR__ . '/../models/Pelanggan.php';
        require_once __DIR__ . '/../models/Hewan.php';
        require_once __DIR__ . '/../models/Kandang.php';
        
        $this->transaksiModel = new Transaksi();
        $this->pelangganModel = new Pelanggan();
        $this->hewanModel = new Hewan();
    }

   public function create() {
    // COMMENT: Method ini meng-handle pembuatan transaksi (CRUD → Create)
    // COMMENT: Validasi input + Error Handling menggunakan try-catch (bagian dari fungsionalitas penilaian)
    error_log("TransaksiController::create() called");
    error_log("POST data: " . print_r($_POST, true));

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // COMMENT: Proteksi request method → security & good practice dalam request handling
        try {
            // Validasi data required
            if (empty($_POST['id_layanan']) || empty($_POST['id_kandang']) || empty($_POST['nama_hewan'])) {
                throw new Exception("Data required tidak lengkap");
            }

            // 1. Handle Pelanggan (create new if doesn't exist)
            $id_pelanggan = $this->handlePelanggan($_POST);
            
            // 2. Handle Hewan (create new)
            $id_hewan = $this->handleHewan($_POST, $id_pelanggan);
            
            // 3. Hitung biaya
            $biayaData = $this->hitungBiaya($_POST);
            
            // 4. Prepare transaksi data
            $transaksiData = [
                'id_pelanggan' => $id_pelanggan,
                'id_hewan' => $id_hewan,
                'id_kandang' => $_POST['id_kandang'],
                'id_layanan' => $_POST['id_layanan'],
                'biaya_paket' => $biayaData['biaya_paket'],
                'tanggal_masuk' => $_POST['tanggal_masuk'] ?? date('Y-m-d'),
                'durasi' => $_POST['durasi'] ?? 1,
                'total_biaya' => $biayaData['total_biaya']
            ];

            error_log("Transaksi data: " . print_r($transaksiData, true));

            // 5. Create transaksi
            $id_transaksi = $this->transaksiModel->create($transaksiData);
            
            if ($id_transaksi) {
                // 6. Create detail transaksi (layanan tambahan)
                $this->handleDetailTransaksi($_POST, $id_transaksi);
                
                // 7. Update status kandang dan hewan
                $kandangModel = new Kandang();
                $kandangModel->updateStatus($transaksiData['id_kandang'], 'terpakai');
                
                $this->hewanModel->updateStatus($id_hewan, 'sedang_dititipan');
                
                // Redirect ke halaman sukses
                header('Location: index.php?page=transaksi&status=success&tab=pendaftaran&id=' . $id_transaksi);
                exit;
            } else {
                throw new Exception("Gagal membuat transaksi");
            }

        } catch (Exception $e) {
            error_log("Error in create transaksi: " . $e->getMessage());
            header('Location: index.php?page=transaksi&status=error&message=' . urlencode($e->getMessage()) . '&tab=pendaftaran');
            exit;
        }
    } else {
        header('Location: index.php?page=transaksi&status=error&message=Invalid request method&tab=pendaftaran');
        exit;
    }

    } catch (Exception $e) {
        echo "Error create transaksi: " . $e->getMessage();
    }
}


/**
 * Hitung biaya transaksi - FIXED VERSION
 */
private function hitungBiaya($data) {
    // COMMENT: Encapsulation (method private) → hanya digunakan internal dalam controller
    // COMMENT: Polimorfisme POTENSIAL: daftar layanan tambahan dapat dipisah ke subclass Payment/Layanan
    error_log("=== HITUNG BIAYA STARTED ===");
    error_log("Data for calculation: " . print_r($data, true));
    
    // Ambil harga paket dari database
    require_once __DIR__ . '/../models/Layanan.php';
    $layananModel = new Layanan();
    $paket = $layananModel->getById($data['id_layanan']);
    
    error_log("Paket data from DB: " . print_r($paket, true));
    
    if (!$paket) {
        throw new Exception("Paket layanan tidak ditemukan dengan ID: " . $data['id_layanan']);
    }
    
    $hargaPaket = floatval($paket['harga']);
    $durasi = intval($data['durasi']) ?: 1;
    
    error_log("Harga paket: " . $hargaPaket . ", Durasi: " . $durasi);
    
    // Hitung biaya paket
    $biayaPaket = $hargaPaket * $durasi;
    
    // Hitung biaya layanan tambahan
    $biayaTambahan = 0;
    if (!empty($data['layanan_tambahan'])) {
        error_log("Layanan tambahan found: " . print_r($data['layanan_tambahan'], true));
        
        $layananTambahanList = [
            'G001' => 100000, // Grooming Dasar
            'G002' => 170000, // Grooming Lengkap
            'L003' => 50000,  // Vitamin / Suplemen
            'L004' => 260000  // Vaksin
        ];
        
        foreach ($data['layanan_tambahan'] as $kodeLayanan) {
            if (isset($layananTambahanList[$kodeLayanan])) {
                $hargaLayanan = $layananTambahanList[$kodeLayanan];
                $biayaTambahan += $hargaLayanan;
                error_log("Layanan {$kodeLayanan} added: Rp " . $hargaLayanan);
            } else {
                error_log("Layanan {$kodeLayanan} not found in price list");
            }
        }
    } else {
        error_log("No layanan tambahan selected");
    }
    
    $totalBiaya = $biayaPaket + $biayaTambahan;
    
    error_log("Biaya paket: " . $biayaPaket . ", Biaya tambahan: " . $biayaTambahan . ", Total: " . $totalBiaya);
    
    return [
        'biaya_paket' => $biayaPaket,
        'biaya_tambahan' => $biayaTambahan,
        'total_biaya' => $totalBiaya
    ];
}

/**
 * Handle detail transaksi (layanan tambahan)
 */
private function handleDetailTransaksi($data, $id_transaksi) {
    // COMMENT: Mengimplementasikan relationship 1-to-many (transaksi → detail_layanan)
    // COMMENT: CRUD Table detail transaksi
    if (empty($data['layanan_tambahan'])) {
        return;
    }
    
    require_once __DIR__ . '/../models/DetailTransaksi.php';
    $detailModel = new DetailTransaksi();
    
    $layananTambahanList = [
        'G001' => ['nama' => 'Grooming Dasar', 'harga' => 100000],
        'G002' => ['nama' => 'Grooming Lengkap', 'harga' => 170000],
        'L003' => ['nama' => 'Vitamin / Suplemen', 'harga' => 50000],
        'L004' => ['nama' => 'Vaksin', 'harga' => 260000]
    ];
    
    foreach ($data['layanan_tambahan'] as $kodeLayanan) {
        if (isset($layananTambahanList[$kodeLayanan])) {
            $layanan = $layananTambahanList[$kodeLayanan];
            
            $detailData = [
                'id_transaksi' => $id_transaksi,
                'kode_layanan' => $kodeLayanan,
                'nama_layanan' => $layanan['nama'],
                'harga' => $layanan['harga'],
                'quantity' => 1,
                'subtotal' => $layanan['harga']
            ];
            
            $detailModel->create($detailData);
        }
    }
}

    private function handlePelanggan($data) {
    $id_pelanggan = $data['id_pelanggan'] ?? null;
    
    // Jika id_pelanggan ada, berarti pelanggan sudah terdaftar
    if (!empty($id_pelanggan)) {
        return $id_pelanggan;
    }
    
    // Jika tidak ada id_pelanggan, buat pelanggan baru
    $pelangganData = [
        'nama_pelanggan' => $data['search_pemilik'] ?? '',
        'no_hp' => $data['no_hp'] ?? '',
        'alamat' => $data['alamat'] ?? ''
    ];

    $newPelangganId = $this->pelangganModel->create($pelangganData);
    if ($newPelangganId) {
        return $newPelangganId;
    } else {
        throw new Exception("Gagal membuat pelanggan baru");
    }
}

    private function handleHewan($data, $id_pelanggan) {
        $hewanData = [
            'id_pelanggan' => $id_pelanggan,
            'nama_hewan' => $data['nama_hewan'] ?? '',
            'jenis' => $data['jenis'] ?? '',
            'ras' => $data['ras'] ?? '',
            'ukuran' => $data['ukuran'] ?? '',
            'warna' => $data['warna'] ?? '',
            'catatan' => $data['catatan'] ?? '',
            'status' => 'tersedia' // Akan diupdate setelah transaksi berhasil
        ];

        // Create hewan dan langsung return ID-nya
        if ($this->hewanModel->create($hewanData)) {
            // Ambil last insert ID dari database connection
            require_once __DIR__ . '/../config/database.php';
            $db = getDB();
            return $db->lastInsertId();
        } else {
            throw new Exception("Gagal membuat data hewan");
        }
    }

    public function read() {
        $id = $_GET['id'] ?? null;
        $nomor = $_GET['nomor'] ?? null;

        if ($id) {
            $data = $this->transaksiModel->getById($id);
        } elseif ($nomor) {
            $data = $this->transaksiModel->getByNomor($nomor);
        } else {
            $data = $this->transaksiModel->getSedangDititipkan(); // Default: yang sedang dititipkan
        }

        echo json_encode($data ?: ['error' => 'Data tidak ditemukan']);
    }

    public function update() {
        // Untuk update checkout, gunakan method checkout di bawah
        echo json_encode(['error' => 'Update umum belum diimplementasi, gunakan checkout untuk selesai']);
    }

    public function delete() {
        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['error' => 'ID transaksi diperlukan']);
            return;
        }

        // Asumsi hanya bisa delete jika belum selesai (opsional, sesuai bisnis logic)
        $transaksi = $this->transaksiModel->getById($id);
        if (!$transaksi || $transaksi['status'] !== 'sedang_dititipkan') {
            echo json_encode(['error' => 'Transaksi tidak bisa dihapus']);
            return;
        }

        // Model Transaksi tidak punya delete, jadi tambahkan jika perlu, atau skip
        echo json_encode(['error' => 'Delete belum diimplementasi di model']);
    }

    public function search() {
        $keyword = $_GET['keyword'] ?? '';
        if (empty($keyword)) {
            echo json_encode(['error' => 'Keyword diperlukan']);
            return;
        }

        $data = $this->transaksiModel->search($keyword);
        echo json_encode($data);
    }
    public function cetakBukti() {
    if (!isset($_GET['id'])) {
        die("ID transaksi tidak ditemukan");
    }

    $id = $_GET['id'];

    // Ambil data transaksi lengkap
    $transaksiData = $this->transaksiModel->getById($id);

    if (!$transaksiData) {
        die("Transaksi tidak ditemukan");
    }

    // Load view khusus untuk bukti
    include __DIR__ . '/../views/cetak_bukti.php';
}


    public function checkout() {
        $id = $_POST['id'] ?? '';
        $tanggalKeluar = $_POST['tanggal_keluar_aktual'] ?? '';
        $durasiHari = $_POST['durasi_hari'] ?? '';
        $totalBiaya = $_POST['total_biaya'] ?? '';
        $metodePembayaran = $_POST['metode_pembayaran'] ?? '';

        if (empty($id) || empty($tanggalKeluar) || !is_numeric($durasiHari) || !is_numeric($totalBiaya)) {
            echo json_encode(['error' => 'Data checkout tidak lengkap']);
            return;
        }

        $data = [
            'tanggal_keluar_aktual' => $tanggalKeluar,
            'durasi_hari' => $durasiHari,
            'total_biaya' => $totalBiaya,
            'metode_pembayaran' => $metodePembayaran,
        ];

        if ($this->transaksiModel->updateCheckout($id, $data)) {
            echo json_encode(['success' => 'Checkout berhasil']);
        } else {
            echo json_encode(['error' => 'Gagal checkout']);
        }
        
    }
    
    

    public function cetakBukti($id_transaksi){
    require_once __DIR__ . '/../models/Transaksi.php';

    $transaksiModel = new Transaksi();

    // Ambil data transaksi lengkap
    $dataTransaksi = $transaksiModel->getById($id_transaksi);

    if (!$dataTransaksi) {
        echo "Transaksi tidak ditemukan!";
        return;
    }

    // Data hewan sudah ada di hasil query (JOIN)
    $dataHewan = [
        'nama' => $dataTransaksi['nama_hewan'],
        'jenis' => $dataTransaksi['jenis'],
        'ras' => $dataTransaksi['ras'],
        'ukuran' => $dataTransaksi['ukuran'],
        'warna' => $dataTransaksi['warna'],
    ];

    // Detail layanan menggunakan tabel detail_layanan
    $dataLayanan = $dataTransaksi['detail_layanan'] ?? [];

    include "views/cetak_bukti.php";
    }

}

?>
