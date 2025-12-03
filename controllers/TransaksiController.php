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
        error_log("Check query in Pelanggan model");
    }
    
    // Print first pelanggan untuk debug
    if (!empty($pelangganList)) {
        error_log("First pelanggan data: " . print_r($pelangganList[0], true));
    }

    // Format data dengan benar
    $formattedKandang = [];
    foreach ($kandangTersedia as $k) {
        $formattedKandang[] = [
            'id' => $k['id_kandang'] ?? $k['id'] ?? 0,
            'kode_kandang' => $k['kode_kandang'] ?? '',
            'tipe' => $k['tipe'] ?? '',
            'status' => $k['status'] ?? 'tersedia'
        ];
    }

    // Send data to view - HARUS INI YANG DIPAKAI
    $this->view('transaksi', [
        'tab' => $tab,
        'pelangganList' => $pelangganList, // ← HARUS PASTIKAN INI TIDAK KOSONG
        'paketList' => $paketList,
        'kandangTersedia' => $kandangTersedia,
        'hewanMenginap' => $this->transaksiModel->getActiveTransactions()
    ]);
}

    public function createTransaksi() {
        try {
            error_log("=== CREATE TRANSAKSI - START ===");
            error_log("POST Data: " . print_r($_POST, true));
            
            // Validasi minimal
            $required = ['id_layanan', 'id_kandang', 'nama_hewan', 'jenis_hewan'];
            foreach ($required as $field) {
                if (empty($_POST[$field])) {
                    throw new Exception("Field '$field' harus diisi");
                }
            }
            
            error_log("Validasi passed");

            // Handle Pelanggan
            $id_pelanggan = $this->handlePelanggan($_POST);
            error_log("ID Pelanggan: " . $id_pelanggan);
            
            // Handle Hewan
            $id_hewan = $this->handleHewan($_POST, $id_pelanggan);
            error_log("ID Hewan: " . $id_hewan);
            
            // Hitung biaya
            $biayaData = $this->hitungBiaya($_POST);
            error_log("Biaya Data: " . print_r($biayaData, true));
            
            // Prepare transaksi data
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

            error_log("Data Transaksi: " . print_r($transaksiData, true));

            // Create transaksi
            $id_transaksi = $this->transaksiModel->create($transaksiData);
            if (!$id_transaksi) {
                throw new Exception("Gagal membuat transaksi utama");
            }
            
            error_log("Transaksi berhasil dibuat dengan ID: " . $id_transaksi);

            // BUAT DETAIL TRANSAKSI
            $this->handleDetailTransaksi($_POST, $id_transaksi, $biayaData);
            
            // Update status kandang
            $updateKandang = $this->kandangModel->updateStatus($_POST['id_kandang'], 'terpakai');
            error_log("Update kandang result: " . ($updateKandang ? 'success' : 'failed'));
            
            // Update status hewan
            $updateHewan = $this->hewanModel->updateStatus($id_hewan, 'sedang_dititipan');
            error_log("Update hewan result: " . ($updateHewan ? 'success' : 'failed'));
            
            // Redirect dengan sukses
            $redirectUrl = 'index.php?page=transaksi&status=success&tab=pendaftaran&id=' . $id_transaksi . '&message=' . urlencode('Penitipan berhasil didaftarkan');
            error_log("Redirect ke: " . $redirectUrl);
            
            header('Location: ' . $redirectUrl);
            exit;
            
        } catch (Exception $e) {
            error_log("ERROR in create transaksi: " . $e->getMessage());
            $errorUrl = 'index.php?page=transaksi&status=error&message=' . urlencode($e->getMessage()) . '&tab=pendaftaran';
            error_log("Redirect error ke: " . $errorUrl);
            
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
        $paket = $this->layananModel->getById($data['id_layanan']);
        
        if (!$paket) {
            throw new Exception("Paket layanan tidak ditemukan");
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
        
        error_log("Handle pelanggan - ID awal: " . $id_pelanggan);
        
        // Jika pelanggan existing
        if (!empty($id_pelanggan) && $id_pelanggan !== 'new' && $id_pelanggan !== '') {
            error_log("Menggunakan pelanggan existing ID: " . $id_pelanggan);
            return $id_pelanggan;
        }
        
        error_log("Membuat pelanggan baru...");
        
        // Buat pelanggan baru - FIXED LOGIC
        $nama_pelanggan = '';
        $no_hp = '';
        $alamat = '';
        
        if ($id_pelanggan === 'new') {
            // Mode tambah pelanggan baru
            $nama_pelanggan = $data['nama_pelanggan'] ?? '';
            $no_hp = $data['no_hp'] ?? '';
            $alamat = $data['alamat'] ?? '';
        } else {
            // Fallback: ambil dari form existing
            $nama_pelanggan = $data['search_pemilik'] ?? '';
            $no_hp = $data['no_hp'] ?? '';
            $alamat = $data['alamat'] ?? '';
        }
        
        if (empty($nama_pelanggan)) {
            throw new Exception("Nama pelanggan harus diisi");
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