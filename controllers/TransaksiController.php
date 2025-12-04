<?php
require_once __DIR__ . '/../core/BaseController.php'; 
require_once __DIR__ . '/../models/Transaksi.php';
require_once __DIR__ . '/../models/DetailTransaksi.php';
require_once __DIR__ . '/../models/Pelanggan.php';
require_once __DIR__ . '/../models/Hewan.php';
require_once __DIR__ . '/../models/Kandang.php';
require_once __DIR__ . '/../models/Layanan.php';

class TransaksiController extends BaseController {
    
    private $transaksiModel;
    private $detailTransaksiModel;
    private $pelangganModel;
    private $hewanModel;
    private $kandangModel;
    private $layananModel;

    public function __construct() {
        $this->transaksiModel = new Transaksi();
        $this->pelangganModel = new Pelanggan();
        $this->hewanModel = new Hewan(); 
        $this->detailTransaksiModel = new DetailTransaksi();
        $this->kandangModel = new Kandang();
        $this->layananModel = new Layanan();
    }
        
    public function index() {
        error_log("=== TRANSAKSI CONTROLLER INDEX ===");
        
        $tab = $_GET['tab'] ?? 'pendaftaran';
        
        // DEBUG: Log semua data
        $pelangganList = $this->pelangganModel->getAll();
        $paketList = $this->layananModel->getAll();
        $kandangTersedia = $this->kandangModel->getAll();
        
        // DEBUG LOGGING
        error_log("DEBUG TRANSAKSI CONTROLLER:");
        error_log("Pelanggan count: " . count($pelangganList));
        error_log("Paket count: " . count($paketList));
        error_log("Kandang count: " . count($kandangTersedia));
        
        if (count($pelangganList) === 0) {
            error_log("WARNING: Data pelanggan KOSONG!");
        }
        
        // Send data to view
        $this->view('transaksi', [
            'tab' => $tab,
            'pelangganList' => $pelangganList,
            'paketList' => $paketList,
            'kandangTersedia' => $kandangTersedia,
            'hewanMenginap' => $this->transaksiModel->getActiveTransactions()
        ]);
    }

    public function createTransaksi() {
        try {
            // ========== DEBUG START ==========
            error_log("========== CREATE TRANSAKSI START ==========");
            error_log("POST Data: " . print_r($_POST, true));
            error_log("REQUEST METHOD: " . $_SERVER['REQUEST_METHOD']);
            
            // Simpan debug ke file
            $debugFile = __DIR__ . '/../../debug_transaksi.txt';
            file_put_contents($debugFile, "========== " . date('Y-m-d H:i:s') . " ==========\n", FILE_APPEND);
            file_put_contents($debugFile, "POST Data:\n" . print_r($_POST, true) . "\n", FILE_APPEND);
            // ========== DEBUG END ==========
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception("Invalid request method. Use POST.");
            }
            
            // VALIDASI LENGKAP
            $errors = [];
            
            // 1. Validasi pelanggan
            $id_pelanggan_input = $_POST['id_pelanggan'] ?? '';
            if (empty($id_pelanggan_input)) {
                $errors[] = "Pilih pemilik atau tambah pelanggan baru";
            }
            
            // 2. Validasi paket (FIX: pastikan ini yang utama)
            $id_layanan = $_POST['id_layanan'] ?? '';
            if (empty($id_layanan)) {
                $errors[] = "Pilih paket layanan";
                error_log("ERROR: id_layanan KOSONG!");
                file_put_contents($debugFile, "ERROR: id_layanan KOSONG!\n", FILE_APPEND);
            }
            
            // 3. Validasi kandang
            $id_kandang = $_POST['id_kandang'] ?? '';
            if (empty($id_kandang)) {
                $errors[] = "Pilih kandang";
            }
            
            // 4. Validasi hewan
            $nama_hewan = $_POST['nama_hewan'] ?? '';
            $jenis_hewan = $_POST['jenis_hewan'] ?? '';
            if (empty($nama_hewan)) $errors[] = "Nama hewan harus diisi";
            if (empty($jenis_hewan)) $errors[] = "Jenis hewan harus dipilih";
            
            // 5. Validasi kontak
            $no_hp = $_POST['no_hp'] ?? '';
            $alamat = $_POST['alamat'] ?? '';
            if (empty($no_hp)) $errors[] = "Nomor HP harus diisi";
            if (empty($alamat)) $errors[] = "Alamat harus diisi";
            
            // Jika ada error
            if (!empty($errors)) {
                $errorMessage = implode(", ", $errors);
                throw new Exception($errorMessage);
            }
            
            error_log("=== VALIDASI PASSED ===");
            file_put_contents($debugFile, "Validasi passed\n", FILE_APPEND);
            
            // ========== PROSES DATA ==========
            
            // 1. HANDLE PELANGGAN
            $id_pelanggan = $this->handlePelanggan($_POST);
            if (empty($id_pelanggan)) {
                throw new Exception("Gagal mendapatkan/membuat data pelanggan");
            }
            error_log("ID Pelanggan: " . $id_pelanggan);
            file_put_contents($debugFile, "ID Pelanggan: $id_pelanggan\n", FILE_APPEND);
            
            // 2. HANDLE HEWAN
            $id_hewan = $this->handleHewan($_POST, $id_pelanggan);
            if (empty($id_hewan)) {
                throw new Exception("Gagal membuat data hewan");
            }
            error_log("ID Hewan: " . $id_hewan);
            file_put_contents($debugFile, "ID Hewan: $id_hewan\n", FILE_APPEND);
            
            // 3. HITUNG BIAYA
            $biayaData = $this->hitungBiaya($_POST);
            error_log("Biaya Data: " . print_r($biayaData, true));
            file_put_contents($debugFile, "Biaya Data: " . print_r($biayaData, true) . "\n", FILE_APPEND);
            
            // 4. BUAT DATA TRANSAKSI
            $transaksiData = [
                'id_pelanggan' => $id_pelanggan,
                'id_hewan' => $id_hewan,
                'id_kandang' => $id_kandang,
                'id_layanan' => $id_layanan,
                'biaya_paket' => $biayaData['biaya_paket'],
                'tanggal_masuk' => $_POST['tanggal_masuk'] ?? date('Y-m-d'),
                'durasi' => $_POST['durasi'] ?? 1,
                'total_biaya' => $biayaData['total_biaya']
            ];
            
            error_log("Transaksi Data: " . print_r($transaksiData, true));
            file_put_contents($debugFile, "Transaksi Data: " . print_r($transaksiData, true) . "\n", FILE_APPEND);
            
            // 5. CREATE TRANSAKSI
            $id_transaksi = $this->transaksiModel->create($transaksiData);
            
            if (!$id_transaksi) {
                throw new Exception("Gagal menyimpan transaksi ke database. Periksa log error.");
            }
            
            error_log("Transaksi berhasil dibuat dengan ID: " . $id_transaksi);
            file_put_contents($debugFile, "Transaksi ID: $id_transaksi\n", FILE_APPEND);
            
            // 6. BUAT DETAIL TRANSAKSI
            $this->handleDetailTransaksi($_POST, $id_transaksi, $biayaData);
            
            // 7. UPDATE STATUS KANDANG
            $kandangResult = $this->kandangModel->updateStatus($id_kandang, 'terpakai');
            error_log("Update kandang result: " . ($kandangResult ? 'success' : 'failed'));
            file_put_contents($debugFile, "Update kandang: " . ($kandangResult ? 'success' : 'failed') . "\n", FILE_APPEND);
            
            // 8. UPDATE STATUS HEWAN
            $hewanResult = $this->hewanModel->updateStatus($id_hewan, 'sedang_dititipan');
            error_log("Update hewan result: " . ($hewanResult ? 'success' : 'failed'));
            file_put_contents($debugFile, "Update hewan: " . ($hewanResult ? 'success' : 'failed') . "\n", FILE_APPEND);
            
            // ========== SUCCESS ==========
            file_put_contents($debugFile, "=== TRANSAKSI BERHASIL DIBUAT ===\n\n", FILE_APPEND);
            
            // Redirect dengan sukses
            $message = "Penitipan berhasil didaftarkan dengan ID: TRX-" . $id_transaksi;
            $redirectUrl = 'index.php?page=transaksi&status=success&tab=pendaftaran&message=' . urlencode($message);
            
            error_log("Redirect ke: " . $redirectUrl);
            header('Location: ' . $redirectUrl);
            exit;
            
        } catch (Exception $e) {
            // ========== ERROR HANDLING ==========
            $errorMessage = $e->getMessage();
            error_log("ERROR in createTransaksi: " . $errorMessage);
            
            $debugFile = __DIR__ . '/../../debug_transaksi.txt';
            file_put_contents($debugFile, "ERROR: " . $errorMessage . "\n\n", FILE_APPEND);
            
            // Redirect dengan error
            $errorUrl = 'index.php?page=transaksi&status=error&tab=pendaftaran&message=' . urlencode($errorMessage);
            header('Location: ' . $errorUrl);
            exit;
        }
    }

    private function handleDetailTransaksi($data, $id_transaksi, $biayaData) {
        error_log("=== DETAIL TRANSAKSI ===");
        
        // Detail paket utama
        $paket = $this->layananModel->getById($data['id_layanan']);
        if ($paket) {
            $detailPaket = [
                'id_transaksi' => $id_transaksi,
                'kode_layanan' => 'PAKET',
                'nama_layanan' => $paket['nama_layanan'] . ' (' . ($data['durasi'] ?? 1) . ' hari)',
                'harga' => $paket['harga'],
                'quantity' => $data['durasi'] ?? 1,
                'subtotal' => $biayaData['biaya_paket']
            ];
            
            $result = $this->detailTransaksiModel->create($detailPaket);
            error_log("Detail paket dibuat: " . ($result ? 'success' : 'failed'));
        }
    }

    private function hitungBiaya($data) {
        // Ambil harga paket dari database
        $id_layanan = $data['id_layanan'] ?? '';
        
        if (empty($id_layanan)) {
            throw new Exception("ID layanan tidak ditemukan");
        }
        
        $paket = $this->layananModel->getById($id_layanan);
        
        if (!$paket) {
            throw new Exception("Paket layanan tidak ditemukan untuk ID: " . $id_layanan);
        }
        
        $hargaPaket = floatval($paket['harga']);
        $durasi = intval($data['durasi']) ?: 1;
        $biayaPaket = $hargaPaket * $durasi;
        
        // Hitung biaya layanan tambahan
        $biayaTambahan = 0;
        if (!empty($data['layanan_tambahan'])) {
            $layananTambahanList = [
                'G001' => 100000, 
                'G002' => 170000, 
                'L003' => 50000, 
                'L004' => 260000 
            ];
            
            foreach ($data['layanan_tambahan'] as $kodeLayanan) {
                if (isset($layananTambahanList[$kodeLayanan])) {
                    $biayaTambahan += $layananTambahanList[$kodeLayanan];
                }
            }
        }
        
        $totalBiaya = $biayaPaket + $biayaTambahan;
        
        error_log("Hitung biaya - Paket: $hargaPaket, Durasi: $durasi, Total: $totalBiaya");
        
        return [
            'biaya_paket' => $biayaPaket,
            'biaya_tambahan' => $biayaTambahan,
            'total_biaya' => $totalBiaya
        ];
    }

    private function handlePelanggan($data) {
        $id_pelanggan = $data['id_pelanggan'] ?? null;
        
        error_log("Handle pelanggan - Input: " . $id_pelanggan);
        
        // Jika pelanggan existing (bukan 'new' dan tidak kosong)
        if (!empty($id_pelanggan) && $id_pelanggan !== 'new') {
            error_log("Menggunakan pelanggan existing ID: " . $id_pelanggan);
            
            // Validasi ID pelanggan
            $pelangganExists = $this->pelangganModel->getById($id_pelanggan);
            if (!$pelangganExists) {
                error_log("Pelanggan ID $id_pelanggan tidak ditemukan, buat baru");
                // Fallback ke buat baru
            } else {
                return $id_pelanggan;
            }
        }
        
        error_log("Membuat pelanggan baru...");
        
        // Buat pelanggan baru
        $nama_pelanggan = '';
        $no_hp = $data['no_hp'] ?? '';
        $alamat = $data['alamat'] ?? '';
        
        if ($id_pelanggan === 'new') {
            // Mode tambah pelanggan baru
            $nama_pelanggan = $data['nama_pelanggan_baru'] ?? '';
        } else {
            // Fallback: ambil dari dropdown jika ada data-nama
            $selectPelanggan = $data['id_pelanggan'] ?? '';
            if (!empty($selectPelanggan) && $selectPelanggan !== 'new') {
                // Coba ambil nama dari option yang dipilih (ini perlu JavaScript)
                $nama_pelanggan = "Pelanggan-" . time(); // fallback
            } else {
                $nama_pelanggan = "Pelanggan-" . time();
            }
        }
        
        if (empty($nama_pelanggan)) {
            $nama_pelanggan = "Pelanggan-" . time();
        }
        
        $pelangganData = [
            'nama_pelanggan' => $nama_pelanggan,
            'no_hp' => $no_hp,
            'alamat' => $alamat
        ];

        error_log("Data pelanggan baru: " . print_r($pelangganData, true));

        // Create pelanggan
        $newPelangganId = $this->pelangganModel->create($pelangganData); 
        if ($newPelangganId) {
            error_log("Pelanggan baru berhasil dibuat ID: " . $newPelangganId);
            return $newPelangganId;
        } else {
            throw new Exception("Gagal membuat pelanggan baru");
        }
    }

    private function handleHewan($data, $id_pelanggan) {
        error_log("Handle hewan untuk pelanggan ID: " . $id_pelanggan);
        
        if (empty($id_pelanggan)) {
            throw new Exception("ID pelanggan tidak valid");
        }
        
        $hewanData = [
            'id_pelanggan' => $id_pelanggan,
            'nama_hewan' => $data['nama_hewan'] ?? '',
            'jenis' => $data['jenis_hewan'] ?? '',
            'ras' => $data['ras'] ?? '',
            'ukuran' => $data['ukuran'] ?? '',
            'warna' => $data['warna'] ?? '',
            'catatan' => $data['catatan'] ?? '',
            'status' => 'tersedia' 
        ];

        error_log("Data hewan: " . print_r($hewanData, true));

        // Create hewan
        $result = $this->hewanModel->create($hewanData);
        if ($result) {
            $lastId = $this->hewanModel->getLastInsertId();
            error_log("Hewan berhasil dibuat ID: " . $lastId);
            return $lastId;
        } else {
            throw new Exception("Gagal membuat data hewan");
        }
    }

    public function checkout() {
        $id_transaksi = $_GET['id'] ?? $_POST['id'] ?? null;
        
        error_log("=== CHECKOUT TRANSAKSI ===");
        error_log("ID Transaksi: " . $id_transaksi);
            
        if (!$id_transaksi) {
            error_log("Checkout error: ID tidak valid");
            header('Location: index.php?page=transaksi&status=error&tab=pengembalian&message=' . urlencode('ID transaksi tidak valid'));
            exit;
        }

        try {
            // Update status transaksi menjadi 'completed'
            $result = $this->transaksiModel->checkout($id_transaksi);
            
            if ($result) {            
                error_log("Transaksi checkout berhasil");
                
                // Ambil data transaksi untuk update kandang dan hewan
                $transaksi = $this->transaksiModel->getById($id_transaksi);
                
                if ($transaksi) {
                    error_log("Data transaksi ditemukan: " . print_r($transaksi, true));
                    
                    // Update status kandang
                    if (!empty($transaksi['id_kandang'])) {
                        $kandangResult = $this->kandangModel->updateStatus($transaksi['id_kandang'], 'tersedia');
                        error_log("Update kandang result: " . ($kandangResult ? 'success' : 'failed'));
                    }
                    
                    // Update status hewan
                    if (!empty($transaksi['id_hewan'])) {
                        $hewanResult = $this->hewanModel->updateStatus($transaksi['id_hewan'], 'sudah_diambil');
                        error_log("Update hewan result: " . ($hewanResult ? 'success' : 'failed'));
                    }
                } else {
                    error_log("Transaksi tidak ditemukan");
                }
                
                // Redirect sukses
                header('Location: index.php?page=transaksi&status=success&tab=pengembalian&message=' . urlencode('Checkout berhasil'));   
                exit;
            } else {
                throw new Exception("Gagal melakukan checkout di database");
            }

        } catch (Exception $e) {
            error_log("Error in checkout transaksi: " . $e->getMessage());
            header('Location: index.php?page=transaksi&status=error&tab=pengembalian&message=' . urlencode($e->getMessage()));
            exit;
        }
    }
}
?>