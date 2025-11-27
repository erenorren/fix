<?php
// Wajib: Include BaseController
require_once __DIR__ . '/../core/BaseController.php'; 

// Wajib: Include semua Model
require_once __DIR__ . '/../models/Transaksi.php';
require_once __DIR__ . '/../models/DetailTransaksi.php';
require_once __DIR__ . '/../models/Pelanggan.php';
require_once __DIR__ . '/../models/Hewan.php';
require_once __DIR__ . '/../models/Kandang.php';
require_once __DIR__ . '/../models/Layanan.php'; // Tambahkan Layanan

/**
 * Kelas TransaksiController
 * Menerapkan Pewarisan dari BaseController (Kriteria OOP)
 */
class TransaksiController extends BaseController { // <--- IMPLEMENTASI PEWARISAN
    
    // Properti Model (Encapsulation)
    private $transaksiModel;
    private $detailTransaksiModel;
    private $pelangganModel;
    private $hewanModel;
    private $kandangModel;
    private $layananModel; // Tambahkan Layanan

    public function __construct() {
        // COMMENT: Pemanggilan Model sebagai objek & Encapsulation
        $this->transaksiModel = new Transaksi();
        $this->pelangganModel = new Pelanggan();
        $this->hewanModel = new Hewan();
        
        // Inisialisasi Model yang sebelumnya berulang (MENGHILANGKAN PERULANGAN)
        $this->detailTransaksiModel = new DetailTransaksi();
        $this->kandangModel = new Kandang();
        $this->layananModel = new Layanan();
    }

    /**
     * Menyiapkan dan memuat tampilan utama transaksi (Pendaftaran & Pengembalian).
     * Ini adalah Controller murni yang menyiapkan data untuk View.
     * Kriteria OOP: Modularitas & Reusability.
     */
    public function index() {
        try {
        $test_data = $this->pelangganModel->getAll();
        if (empty($test_data)) {
             // Kirim pesan error jika data kosong (tapi koneksi sukses)
             error_log("Koneksi sukses, tapi tabel pelanggan kosong.");
        }
    } catch (Exception $e) {
        // Tampilkan error koneksi yang lebih besar
        die("FATAL: Koneksi/Query GAGAL. Pesan: " . $e->getMessage());
    }
        // Ambil parameter tab dari URL
        $tab = $_GET['tab'] ?? 'pendaftaran';
        
        // Ambil semua data yang dibutuhkan oleh views/transaksi.php
        $data = [
            'tab' => $tab,
            
            // 1. Data untuk Tab Pendaftaran (Form)
            'pelangganList' => $this->pelangganModel->getAll(), // <--- Tambah ini
            'paketList' => $this->layananModel->getAll(),
            'kandangTersedia' => $this->kandangModel->getAll(),
            
            // 2. Data untuk Tab Pengembalian
            'hewanMenginap' => $this->transaksiModel->getActiveTransactions(),
            
            // Default nilai dari backend
            'hasilPencarian' => $hasilPencarian ?? [],
            'transaksi'      => $transaksi ?? null, 
        ];

        // Memuat View menggunakan BaseController (Reusability)
        $this->view('transaksi', $data); 
    }

    /**
     * Menangani proses Check-in (CRUD → Create)
     * Menerapkan Validasi input dan Error Handling (Kriteria Fungsionalitas)
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validasi data required
                if (empty($_POST['id_layanan']) || empty($_POST['id_kandang']) || empty($_POST['nama_hewan'])) {
                    throw new Exception("Data required tidak lengkap");
                }

                // 1. Handle Pelanggan
                $id_pelanggan = $this->handlePelanggan($_POST);
                
                // 2. Handle Hewan (Wajib: HewanModel::create() harus mengembalikan ID)
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

                // 5. Create transaksi
                $id_transaksi = $this->transaksiModel->create($transaksiData);
                
                if ($id_transaksi) {
                    // 6. Create detail transaksi (layanan tambahan)
                    $this->handleDetailTransaksi($_POST, $id_transaksi);
                    
                    // 7. Update status kandang dan hewan (Menggunakan Model property yang sudah diinisialisasi)
                    $this->kandangModel->updateStatus($transaksiData['id_kandang'], 'terpakai'); // <--- PENGHILANGAN PERULANGAN
                    $this->hewanModel->updateStatus($id_hewan, 'sedang_dititipan');
                    
                    // Redirect ke halaman sukses menggunakan metode warisan (Reusability)
                    $this->redirect('index.php?page=transaksi&status=success&tab=pendaftaran&id=' . $id_transaksi);
                } else {
                    throw new Exception("Gagal membuat transaksi");
                }

            } catch (Exception $e) {
                error_log("Error in create transaksi: " . $e->getMessage());
                $this->redirect('index.php?page=transaksi&status=error&message=' . urlencode($e->getMessage()) . '&tab=pendaftaran');
            }
        } else {
            $this->redirect('index.php?page=transaksi&status=error&message=Invalid request method&tab=pendaftaran');
        }
    }


    /**
    * Hitung biaya transaksi (menghilangkan perulangan instansiasi)
    */
    private function hitungBiaya($data) {
        
        // Ambil harga paket dari database (Menggunakan property Model $this->layananModel)
        $paket = $this->layananModel->getById($data['id_layanan']); // <--- PENGHILANGAN PERULANGAN
        
        if (!$paket) {
            throw new Exception("Paket layanan tidak ditemukan dengan ID: " . $data['id_layanan']);
        }
        
        $hargaPaket = floatval($paket['harga']);
        $durasi = intval($data['durasi']) ?: 1;
        
        // Hitung biaya paket
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
        if (empty($data['layanan_tambahan'])) {
            return;
        }
        
        // Menggunakan property Model $this->detailTransaksiModel
        $detailModel = $this->detailTransaksiModel; // <--- PENGHILANGAN PERULANGAN
        
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
        
        if (!empty($id_pelanggan)) {
            return $id_pelanggan;
        }
        
        $pelangganData = [
            'nama_pelanggan' => $data['search_pemilik'] ?? '',
            'no_hp' => $data['no_hp'] ?? '',
            'alamat' => $data['alamat'] ?? ''
        ];

        // Model harus mengembalikan ID baru jika berhasil
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
            'status' => 'tersedia' 
        ];

        // KOREKSI ENCAPSULATION: Assume HewanModel::create() mengembalikan lastInsertId.
        if ($newHewanId = $this->hewanModel->create($hewanData)) { 
            return $newHewanId;
        } else {
            throw new Exception("Gagal membuat data hewan");
        }
    }

    public function read() {
        // ... (Logika read tetap sama) ...
    }

    public function update() {
        // ... (Logika update tetap sama) ...
    }

    public function delete() {
        // ... (Logika delete tetap sama) ...
    }

    public function search() {
        // ... (Logika search tetap sama) ...
    }

    /**
     * Menangani proses Check-out (CRUD → Update Status)
     */
    public function checkout() {
        // ... (Logika checkout tetap sama) ...
    }

    /**
     * Memuat tampilan cetak bukti
     * Menggunakan metode view() yang diwariskan dari BaseController
     */
    public function cetakBukti() {
        if (!isset($_GET['id'])) {
            $this->redirect('index.php?page=transaksi&status=error&message=ID transaksi diperlukan');
        }

        $id = $_GET['id'];
        $transaksiData = $this->transaksiModel->getById($id);

        if (!$transaksiData) {
            $this->view('404'); // Muat halaman 404 menggunakan metode warisan
            return;
        }
        
        // Memuat view menggunakan metode warisan (Reusability)
        $this->view('cetak_bukti', ['transaksiData' => $transaksiData]); // <--- PENGGUNAAN BASECONTROLLER
    }
}