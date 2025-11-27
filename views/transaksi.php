<?php
$pageTitle  = 'Transaksi Penitipan Hewan';
$activeMenu = 'transaksi';
include __DIR__ . '/template/header.php';

// Load data dari database
require_once __DIR__ . '/../models/Pelanggan.php';
require_once __DIR__ . '/../models/Layanan.php';
require_once __DIR__ . '/../models/Kandang.php';
require_once __DIR__ . '/../models/Transaksi.php';

$pelangganModel = new Pelanggan();
$layananModel = new Layanan();
$kandangModel = new Kandang();
$transaksiModel = new Transaksi();

// Data paket utama dari database
$paketList = $layananModel->getAll();

// Data kandang yang tersedia
$kandangTersedia = $kandangModel->getAll();

// Data hewan yang sedang menginap (untuk tab pengembalian)
$hewanMenginap = $transaksiModel->getActiveTransactions();

// Default nilai dari backend
$hasilPencarian = $hasilPencarian ?? [];
$transaksi      = $transaksi      ?? null;

$tab = $_GET['tab'] ?? 'pendaftaran';
?>

<!-- TAMPILKAN ALERT JIKA ADA STATUS -->
<?php if (isset($_GET['status'])): ?>
    <div class='alert alert-<?= $_GET['status'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show'>
        <?php if ($_GET['status'] === 'success'): ?>
            <strong>Sukses!</strong> Transaksi berhasil dibuat.
        <?php else: ?>
            <strong>Error!</strong> <?= htmlspecialchars($_GET['message'] ?? 'Terjadi kesalahan') ?>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-12">
        <div class="card shadow-sm">
            <div class="card-header border-0 pb-0">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                    <a class="nav-link <?= $tab === 'pendaftaran' ? 'active' : '' ?>" href="index.php?page=transaksi&tab=pendaftaran">
            <i class="bi bi-box-arrow-in-down me-2"></i>Pendaftaran
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'pengembalian' ? 'active' : '' ?>"
            href="index.php?page=transaksi&tab=pengembalian">
            <i class="bi bi-box-arrow-up me-2"></i>Pengembalian
        </a>
    </li>
</ul>

            </div>

            <div class="card-body">

                <?php if ($tab === 'pendaftaran'): ?>

                    <!-- =======================================================
                         TAB 1 — PENDAFTARAN
                    ======================================================== -->
                    <h5 class="mb-3">Form Pendaftaran Penitipan</h5>

                    <form method="post" action="index.php?action=createTransaksi" id="formPendaftaran">
                    <!-- <form method="post" action="index.php?page=transaksi&action=create"> -->

                    <!-- <form method="post" action="controllers/TransaksiController.php?action=create" id="formPendaftaran"> -->

                        <div class="row g-4">

                            <!-- INFORMASI PEMILIK -->
                            <div class="col-lg-6">
                                <div class="card p-3 h-100 position-relative">
                                    <h6 class="mb-3 text-primary">Informasi Pemilik</h6>

                                    <div class="mb-3">
                                        <label class="form-label">Nama Pemilik <span class="text-danger">*</span></label>
                                        <select name="id_pelanggan" class="form-select" id="selectPelanggan" required>
                                            <option value="">-- Pilih Pemilik --</option>
                                            <?php 
                                            $pelangganList = $pelangganModel->getAll();
                                            foreach ($pelangganList as $p): ?>
                                                <option value="<?= $p['id'] ?>" 
                                                        data-hp="<?= $p['hp'] ?>" 
                                                        data-alamat="<?= htmlspecialchars($p['alamat']) ?>">
                                                    <?= htmlspecialchars($p['nama']) ?> (<?= $p['hp'] ?>)
                                                </option>
                                            <?php endforeach; ?>
                                            <option value="new">+ Tambah Pemilik Baru</option>
                                        </select>
                                        <small class="text-muted">Pilih dari daftar pelanggan terdaftar</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Nomor HP <span class="text-danger">*</span></label>
                                        <input type="text" name="hp" id="p_hp" class="form-control"
                                            placeholder="Contoh: 08123456789" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Alamat <span class="text-danger">*</span></label>
                                        <textarea name="alamat" id="p_alamat" class="form-control"
                                            rows="2" placeholder="Alamat lengkap pemilik" required></textarea>
                                    </div>

                                    <div class="mb-3" id="newCustomerFields" style="display: none;">
                                        <label class="form-label">Nama Pemilik Baru <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_pelanggan_baru" class="form-control"
                                            placeholder="Ketik nama pemilik baru">
                                    </div>
                                </div>
                            </div>

                            <!-- INFORMASI HEWAN -->
                            <div class="col-lg-6">
                                <div class="card p-3 h-100">
                                    <h6 class="mb-3 text-primary">Informasi Hewan</h6>

                                    <div class="mb-3">
                                        <label class="form-label">Nama Hewan <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_hewan" class="form-control"
                                            placeholder="Contoh: Mochi, Blacky" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Jenis Hewan <span class="text-danger">*</span></label>
                                        <select name="jenis_hewan" class="form-select" id="jenisHewanSelect" required>
                                            <option value="">-- Pilih Hewan --</option>
                                            <option value="Kucing">Kucing</option>
                                            <option value="Anjing">Anjing</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Ras</label>
                                        <input type="text" name="ras" class="form-control"
                                            placeholder="Contoh: Persia, Siberian Husky">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Ukuran</label>
                                        <select name="ukuran" class="form-select" id="ukuranHewanSelect">
                                            <option value="">-- Pilih Ukuran --</option>
                                            <option value="Kecil">Kecil</option>
                                            <option value="Sedang">Sedang</option>
                                            <option value="Besar">Besar</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Warna</label>
                                        <input type="text" name="warna" class="form-control"
                                            placeholder="Contoh: Putih, Hitam-Putih">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Catatan Khusus</label>
                                        <textarea name="catatan" class="form-control" rows="2"
                                            placeholder="Alergi, penyakit, kebiasaan khusus, dll."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- LAYANAN -->
                            <div class="col-12">
                                <div class="card p-3">
                                    <h6 class="mb-3 text-primary">Layanan</h6>

                                    <div class="row g-3">
                                        <!-- Paket Utama -->
                                        <div class="col-lg-4">
                                            <label class="form-label">Paket Utama <span class="text-danger">*</span></label>
                                            <select name="id_layanan" class="form-select" id="paketSelect" required>
                                                <option value="">-- Pilih Paket --</option>
                                                <?php foreach ($paketList as $pk): ?>
                                                    <option value="<?= $pk['id_layanan'] ?>" 
                                                            data-harga="<?= $pk['harga'] ?>" 
                                                            data-nama="<?= htmlspecialchars($pk['nama_layanan']) ?>">
                                                        <?= htmlspecialchars($pk['nama_layanan']) ?>
                                                        - Rp <?= number_format($pk['harga'], 0, ',', '.'); ?>/hari
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text">Pilih salah satu paket penitipan</div>
                                        </div>

                                        <!-- Info Paket yang Dipilih -->
                                        <div class="col-lg-8">
                                            <div class="alert alert-info mt-4">
                                                <h6>Info Paket:</h6>
                                                <div id="paketInfo">Pilih paket untuk melihat detail</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- TOTAL -->
                                    <div class="mt-4 p-3 bg-light rounded">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="small text-muted">Total Estimasi Biaya</div>
                                                <h3 id="totalHarga" class="fw-bold text-primary mb-0">Rp 0</h3>
                                            </div>
                                            <div class="text-end">
                                                <small class="text-muted d-block" id="detailPerhitungan">-</small>
                                            </div>
                                        </div>
                                        <input type="hidden" name="total_biaya" id="totalInput" value="0">
                                        <div class="small text-muted mt-1">
                                            Total = (harga paket × lama inap)
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- DETAIL PENITIPAN -->
                            <div class="col-12">
                                <div class="card p-3">
                                    <h6 class="mb-3 text-primary">Detail Penitipan</h6>

                                    <div class="row g-3">
                                        <div class="col-lg-4">
                                            <label class="form-label">Tanggal Masuk <span class="text-danger">*</span></label>
                                            <input type="date" name="tanggal_masuk" class="form-control"
                                                value="<?= date('Y-m-d') ?>" required>
                                        </div>

                                        <div class="col-lg-4">
                                            <label class="form-label">Lama Inap (hari) <span class="text-danger">*</span></label>
                                            <input type="number" name="durasi" class="form-control"
                                                min="1" value="1" required id="lamaInap">
                                        </div>

                                        <div class="col-lg-4">
                                            <label class="form-label">Kandang <span class="text-danger">*</span></label>
                                            <button type="button"
                                                class="btn btn-outline-secondary text-start w-100 d-flex justify-content-between align-items-center"
                                                id="btnPilihKandang">
                                                <span id="kandangLabel">Pilih kandang yang tersedia</span>
                                                <i class="bi bi-chevron-down ms-2 small"></i>
                                            </button>

                                            <div id="panelKandang" class="border rounded p-2 mt-1 d-none"
                                                style="max-height: 200px; overflow-y: auto;">
                                                <div class="text-center">
                                                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    <span class="text-muted">Memuat kandang tersedia...</span>
                                                </div>
                                            </div>

                                            <input type="hidden" name="id_kandang" id="id_kandang">
                                            <small class="text-muted d-block mt-1" id="kandangInfo">
                                                Pilih kandang yang sesuai dengan jenis dan ukuran hewan
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /.row -->

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Simpan
                            </button>
                        </div>
                    </form>

                <?php else: ?>

                    <!-- =======================================================
                         TAB 2 — PENGEMBALIAN (CHECK-OUT)
                    ======================================================== -->
                    <h5 class="mb-3">Form Pengembalian Hewan</h5>

                    <!-- Pencarian Transaksi Aktif -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="mb-3 text-primary">Cari Hewan yang Sedang Menginap</h6>
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <label class="form-label">Cari berdasarkan Nama Pemilik atau Hewan</label>
                                    <input type="text" id="searchCheckout" class="form-control"
                                        placeholder="Ketik nama pemilik atau hewan...">
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">Kandang</label>
                                    <select id="filterKandang" class="form-select">
                                        <option value="">Semua Kandang</option>
                                        <option value="KK">Kandang Kecil (KK)</option>
                                        <option value="KB">Kandang Besar (KB)</option>
                                    </select>
                                </div>
                                <div class="col-lg-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-primary w-100" id="btnCariCheckout">
                                        <i class="bi bi-search me-2"></i>Cari
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Hewan Menginap -->
                    <div class="card">
                        <div class="card-header bg-transparent">
                            <h6 class="mb-0 text-primary">Daftar Hewan yang Sedang Menginap</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No. Transaksi</th>
                                            <th>Pemilik</th>
                                            <th>Hewan</th>
                                            <th>Kandang</th>
                                            <th>Tgl Masuk</th>
                                            <th>Lama Inap</th>
                                            <th>Total Biaya</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($hewanMenginap)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    <i class="bi bi-inbox display-4 text-muted opacity-50"></i>
                                                    <p class="mt-3 mb-0">Tidak ada hewan yang sedang menginap</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($hewanMenginap as $hewan): ?>
                                                <tr>
                                                    <td class="fw-semibold"><?= htmlspecialchars($hewan['kode_transaksi']); ?></td>
                                                    <td><?= htmlspecialchars($hewan['nama_pelanggan']); ?></td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php
                                                            $hewanIcon = $hewan['jenis_hewan'] === 'Kucing' ? 'bi-cat text-info' : 'bi-dog text-warning';
                                                            ?>
                                                            <i class="bi <?= $hewanIcon; ?> me-2"></i>
                                                            <?= htmlspecialchars($hewan['nama_hewan']); ?>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?= htmlspecialchars($hewan['kode_kandang']); ?></span>
                                                    </td>
                                                    <td><?= date('d/m/Y', strtotime($hewan['tanggal_masuk'])); ?></td>
                                                    <td><?= $hewan['durasi']; ?> hari</td>
                                                    <td class="fw-semibold text-primary">
                                                        Rp <?= number_format($hewan['total_biaya'], 0, ',', '.'); ?>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-success btn-sm"
                                                            onclick="prosesCheckout('<?= $hewan['id_transaksi']; ?>')">
                                                            <i class="bi bi-check-lg me-1"></i>Check-out
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Checkout -->
                    <div class="modal fade" id="modalCheckout" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Proses Check-out Hewan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body" id="checkoutContent">
                                    <!-- Content akan diisi via JavaScript -->
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                    <button type="button" class="btn btn-primary" id="btnConfirmCheckout">
                                        <i class="bi bi-check-lg me-2"></i>Konfirmasi Check-out
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<!-- Modal Bukti Pembayaran -->
<div class="modal fade" id="modalBuktiBayar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bukti Pembayaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="buktiBayarContent">
                <!-- Content akan diisi via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="cetakBuktiBayar()">
                    <i class="bi bi-printer me-2"></i>Cetak
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log("=== SCRIPT DIMULAI ===");

        // =============================================
        // AUTO-FILL DATA PELANGGAN
        // =============================================
        const selectPelanggan = document.getElementById('selectPelanggan');
        const noHpInput = document.getElementById('p_hp');
        const alamatInput = document.getElementById('p_alamat');
        const newCustomerFields = document.getElementById('newCustomerFields');

        if (selectPelanggan) {
            selectPelanggan.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                
                if (selectedOption.value === 'new') {
                    // Tampilkan field untuk pelanggan baru
                    newCustomerFields.style.display = 'block';
                    noHpInput.value = '';
                    alamatInput.value = '';
                    noHpInput.required = true;
                    alamatInput.required = true;
                } else if (selectedOption.value) {
                    // Auto-fill data pelanggan yang dipilih
                    newCustomerFields.style.display = 'none';
                    noHpInput.value = selectedOption.dataset.hp || '';
                    alamatInput.value = selectedOption.dataset.alamat || '';
                    noHpInput.required = true;
                    alamatInput.required = true;
                } else {
                    // Reset jika tidak ada yang dipilih
                    newCustomerFields.style.display = 'none';
                    noHpInput.value = '';
                    alamatInput.value = '';
                }
            });
        }

        // =============================================
        // KALKULASI TOTAL HARGA - FIXED VERSION
        // =============================================
        const paketSelect = document.getElementById('paketSelect');
        const lamaInapInput = document.getElementById('lamaInap');
        const totalHargaElement = document.getElementById('totalHarga');
        const totalInput = document.getElementById('totalInput');
        const detailPerhitungan = document.getElementById('detailPerhitungan');
        const paketInfo = document.getElementById('paketInfo');

        function hitungTotal() {
            console.log("=== KALKULASI TOTAL DIMULAI ===");
            
            let total = 0;
            let hargaPaket = 0;
            let lamaInap = 1;
            let namaPaket = '';

            // Hitung harga paket
            if (paketSelect && paketSelect.value) {
                const selectedOption = paketSelect.options[paketSelect.selectedIndex];
                hargaPaket = parseInt(selectedOption.getAttribute('data-harga')) || 0;
                namaPaket = selectedOption.getAttribute('data-nama') || '';
                lamaInap = parseInt(lamaInapInput.value) || 1;
                
                total = hargaPaket * lamaInap;
                
                // Update info paket
                if (paketInfo) {
                    paketInfo.innerHTML = `
                        <strong>${namaPaket}</strong><br>
                        <small>Harga: Rp ${hargaPaket.toLocaleString('id-ID')} / hari</small>
                    `;
                }
            } else {
                if (paketInfo) {
                    paketInfo.textContent = 'Pilih paket untuk melihat detail';
                }
            }

            // Update tampilan
            if (totalHargaElement) {
                totalHargaElement.textContent = 'Rp ' + total.toLocaleString('id-ID');
            }
            if (totalInput) {
                totalInput.value = total;
            }
            if (detailPerhitungan) {
                if (hargaPaket > 0) {
                    detailPerhitungan.textContent = `Rp ${hargaPaket.toLocaleString('id-ID')} × ${lamaInap} hari`;
                } else {
                    detailPerhitungan.textContent = '-';
                }
            }

            console.log("Total calculated:", total);
        }

        // Event listeners untuk kalkulasi
        if (paketSelect) {
            paketSelect.addEventListener('change', hitungTotal);
        }

        if (lamaInapInput) {
            lamaInapInput.addEventListener('input', hitungTotal);
        }

        // Hitung total awal saat page load
        hitungTotal();

        // =============================================
        // PEMILIHAN KANDANG - FIXED VERSION
        // =============================================
        const btnPilihKandang = document.getElementById('btnPilihKandang');
        const panelKandang = document.getElementById('panelKandang');
        const kandangLabel = document.getElementById('kandangLabel');
        const idKandangInput = document.getElementById('id_kandang');
        const kandangInfo = document.getElementById('kandangInfo');
        const jenisHewanSelect = document.getElementById('jenisHewanSelect');
        const ukuranHewanSelect = document.getElementById('ukuranHewanSelect');

        if (btnPilihKandang) {
            console.log("Button pilih kandang ditemukan");
            
            btnPilihKandang.addEventListener('click', function() {
                console.log("Button pilih kandang diklik");
                
                if (!jenisHewanSelect || !jenisHewanSelect.value) {
                    alert('Pilih jenis hewan terlebih dahulu');
                    return;
                }

                // Tampilkan loading
                panelKandang.innerHTML = `
                    <div class="text-center py-2">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="text-muted">Memuat kandang tersedia...</span>
                    </div>
                `;
                panelKandang.classList.remove('d-none');

                // Filter kandang berdasarkan jenis dan ukuran hewan
                const jenisHewan = jenisHewanSelect.value;
                const ukuranHewan = ukuranHewanSelect ? ukuranHewanSelect.value : '';
                
                // Tentukan tipe kandang yang sesuai
                let tipeKandangYangCocok = ['Kecil', 'Besar'];
                
                if (jenisHewan === 'Anjing') {
                    tipeKandangYangCocok = ['Besar'];
                } else if (ukuranHewan === 'Besar') {
                    tipeKandangYangCocok = ['Besar'];
                }

                // Tunggu sebentar lalu tampilkan kandang
                setTimeout(() => {
                    panelKandang.innerHTML = '';
                    
                    let kandangDitemukan = false;
                    const kandangTersedia = <?= json_encode($kandangTersedia) ?>;
                    
                    kandangTersedia.forEach(kandang => {
                        // Filter kandang
                        if (kandang.status === 'tersedia' && tipeKandangYangCocok.includes(kandang.tipe)) {
                            kandangDitemukan = true;
                            
                            const kandangItem = document.createElement('div');
                            kandangItem.className = 'p-2 border-bottom cursor-pointer hover-bg-light';
                            kandangItem.style.cursor = 'pointer';
                            kandangItem.innerHTML = `
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-semibold">${kandang.kode}</span>
                                        <small class="text-muted ms-2">${kandang.tipe}</small>
                                    </div>
                                    <span class="badge bg-success">Tersedia</span>
                                </div>
                                ${kandang.catatan ? `<small class="text-muted">${kandang.catatan}</small>` : ''}
                            `;
                            
                            kandangItem.addEventListener('click', function() {
                                kandangLabel.textContent = `${kandang.kode} - ${kandang.tipe}`;
                                idKandangInput.value = kandang.id;
                                panelKandang.classList.add('d-none');
                                kandangInfo.innerHTML = `<span class="text-success"><i class="bi bi-check-circle"></i> Kandang ${kandang.kode} dipilih</span>`;
                                validateKandang();
                            });
                            
                            panelKandang.appendChild(kandangItem);
                        }
                    });
                    
                    if (!kandangDitemukan) {
                        panelKandang.innerHTML = `
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-inbox display-6 opacity-50"></i>
                                <p class="mt-2 mb-0">Tidak ada kandang tersedia</p>
                                <small>Untuk ${jenisHewan} ${ukuranHewan ? 'ukuran ' + ukuranHewan : ''}</small>
                            </div>
                        `;
                    }
                    
                }, 300);
            });

            function validateKandang() {
                if (idKandangInput.value) {
                    btnPilihKandang.classList.remove('btn-outline-secondary');
                    btnPilihKandang.classList.add('btn-outline-success');
                } else {
                    btnPilihKandang.classList.remove('btn-outline-success');
                    btnPilihKandang.classList.add('btn-outline-secondary');
                }
            }

            // Update ketika jenis/ukuran hewan berubah
            if (jenisHewanSelect) {
                jenisHewanSelect.addEventListener('change', resetKandangPilihan);
            }
            
            if (ukuranHewanSelect) {
                ukuranHewanSelect.addEventListener('change', resetKandangPilihan);
            }

            function resetKandangPilihan() {
                idKandangInput.value = '';
                kandangLabel.textContent = 'Pilih kandang yang tersedia';
                kandangInfo.innerHTML = 'Pilih kandang yang sesuai dengan jenis dan ukuran hewan';
                panelKandang.classList.add('d-none');
                validateKandang();
            }

            // Sembunyikan panel ketika klik di luar
            document.addEventListener('click', function(e) {
                if (btnPilihKandang && !btnPilihKandang.contains(e.target) && !panelKandang.contains(e.target)) {
                    panelKandang.classList.add('d-none');
                }
            });

            // Initial validation
            validateKandang();
        }

        // =============================================
        // FORM SUBMIT HANDLER
        // =============================================
        const formPendaftaran = document.getElementById('formPendaftaran');
        if (formPendaftaran) {
            formPendaftaran.addEventListener('submit', function(e) {
                console.log("Form submitted");
                
                if (!validateForm()) {
                    e.preventDefault();
                    return false;
                }
                
                // Jika valid, form akan dikirim ke server
                console.log("Form valid, submitting...");
            });
        }

        // =============================================
        // FUNGSI CHECKOUT (Tab Pengembalian)
        // =============================================
        window.prosesCheckout = function(idTransaksi) {
            // Simulasi data transaksi
            const transaksiData = {
                id: idTransaksi,
                pemilik: 'Budi Santoso',
                hewan: 'Mochi',
                jenis: 'Kucing',
                kandang: 'KK01',
                tgl_masuk: '2024-01-15',
                lama_inap: 3,
                total_biaya: 360000
            };

            // Isi modal checkout
            const checkoutContent = document.getElementById('checkoutContent');
            checkoutContent.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Detail Transaksi</h6>
                    <table class="table table-sm">
                        <tr>
                            <td>No. Transaksi:</td>
                            <td class="fw-semibold">${transaksiData.id}</td>
                        </tr>
                        <tr>
                            <td>Pemilik:</td>
                            <td>${transaksiData.pemilik}</td>
                        </tr>
                        <tr>
                            <td>Hewan:</td>
                            <td>${transaksiData.hewan} (${transaksiData.jenis})</td>
                        </tr>
                        <tr>
                            <td>Kandang:</td>
                            <td>${transaksiData.kandang}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Rincian Biaya</h6>
                    <table class="table table-sm">
                        <tr>
                            <td>Tanggal Masuk:</td>
                            <td>${new Date(transaksiData.tgl_masuk).toLocaleDateString('id-ID')}</td>
                        </tr>
                        <tr>
                            <td>Lama Inap:</td>
                            <td>${transaksiData.lama_inap} hari</td>
                        </tr>
                        <tr class="table-primary">
                            <td><strong>Total Biaya:</strong></td>
                            <td><strong>Rp ${transaksiData.total_biaya.toLocaleString('id-ID')}</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="alert alert-success mt-3">
                <i class="bi bi-info-circle me-2"></i>
                Pastikan hewan dalam kondisi baik sebelum melakukan check-out.
            </div>
        `;

            // Tampilkan modal
            const modal = new bootstrap.Modal(document.getElementById('modalCheckout'));
            modal.show();

            // Setup confirm button
            document.getElementById('btnConfirmCheckout').onclick = function() {
                // Redirect ke controller untuk checkout
                // window.location.href = `controllers/TransaksiController.php?action=checkout&id=${idTransaksi}`;
                // window.location.href = `index.php?action=checkoutTransaksi&id=${idTransaksi}`;
                window.location.href = `index.php?action=checkoutTransaksi&id=${idTransaksi}`;


            };
        };

        // =============================================
        // PENCARIAN CHECKOUT (Tab Pengembalian)
        // =============================================
        const btnCariCheckout = document.getElementById('btnCariCheckout');
        if (btnCariCheckout) {
            btnCariCheckout.addEventListener('click', function() {
                const keyword = document.getElementById('searchCheckout').value;
                const kandang = document.getElementById('filterKandang').value;

                // Simulasi pencarian
                alert(`Mencari: ${keyword} - Kandang: ${kandang || 'Semua'}`);
            });
        }
    });

    // =============================================
    // FUNGSI VALIDASI FORM
    // =============================================
    function validateForm() {
        const requiredFields = document.querySelectorAll('#formPendaftaran [required]');
        let isValid = true;

        // Validasi field required
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
                
                // Cari label untuk field ini
                const label = document.querySelector(`label[for="${field.id}"]`);
                if (label) {
                    console.log(`Field ${label.textContent} belum diisi`);
                }
            } else {
                field.classList.remove('is-invalid');
            }
        });

        // Validasi kandang
        const idKandangInput = document.getElementById('id_kandang');
        if (!idKandangInput || !idKandangInput.value) {
            isValid = false;
            alert('Harap pilih kandang terlebih dahulu!');
        }

        // Validasi pelanggan baru
        const selectPelanggan = document.getElementById('selectPelanggan');
        if (selectPelanggan && selectPelanggan.value === 'new') {
            const namaPelangganBaru = document.querySelector('[name="nama_pelanggan_baru"]');
            if (!namaPelangganBaru || !namaPelangganBaru.value.trim()) {
                isValid = false;
                alert('Harap isi nama pemilik baru!');
                if (namaPelangganBaru) {
                    namaPelangganBaru.classList.add('is-invalid');
                }
            }
        }

        if (!isValid) {
            alert('Harap lengkapi semua field yang wajib diisi!');
            return false;
        }

        return true;
    }

    // =============================================
    // FUNGSI BUKTI PEMBAYARAN
    // =============================================
    function tampilkanBuktiBayar(transaksiData) {
        const buktiContent = document.getElementById('buktiBayarContent');
        
        buktiContent.innerHTML = `
            <div class="text-center mb-4">
                <h2 class="text-primary mb-1">PetCare Center</h2>
                <p class="text-muted mb-0">Jl. Kesehatan Hewan No. 45, Jakarta</p>
                <p class="text-muted">Telp: (021) 123-4567 | Email: info@petcare.com</p>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3">Informasi Transaksi</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>No. Transaksi</strong></td>
                                    <td>: ${transaksiData.no_transaksi}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal</strong></td>
                                    <td>: ${transaksiData.tgl_transaksi}</td>
                                </tr>
                                <tr>
                                    <td><strong>Kandang</strong></td>
                                    <td>: ${transaksiData.no_kandang}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title text-primary mb-3">Periode Penitipan</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="40%"><strong>Check-in</strong></td>
                                    <td>: ${new Date(transaksiData.tgl_masuk).toLocaleDateString('id-ID')}</td>
                                </tr>
                                <tr>
                                    <td><strong>Check-out</strong></td>
                                    <td>: ${transaksiData.tgl_keluar}</td>
                                </tr>
                                <tr>
                                    <td><strong>Lama Inap</strong></td>
                                    <td>: ${transaksiData.lama_inap} hari</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-light py-2">
                            <h6 class="mb-0 text-primary">Informasi Pemilik</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="35%"><strong>Nama</strong></td>
                                    <td>: ${transaksiData.nama_pemilik}</td>
                                </tr>
                                <tr>
                                    <td><strong>No. HP</strong></td>
                                    <td>: ${transaksiData.no_hp}</td>
                                </tr>
                                <tr>
                                    <td><strong>Alamat</strong></td>
                                    <td>: ${transaksiData.alamat}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-light py-2">
                            <h6 class="mb-0 text-primary">Informasi Hewan</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="35%"><strong>Nama</strong></td>
                                    <td>: ${transaksiData.nama_hewan}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis</strong></td>
                                    <td>: ${transaksiData.jenis_hewan}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ras</strong></td>
                                    <td>: ${transaksiData.ras || '-'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ukuran</strong></td>
                                    <td>: ${transaksiData.ukuran || '-'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Warna</strong></td>
                                    <td>: ${transaksiData.warna || '-'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Catatan</strong></td>
                                    <td>: ${transaksiData.catatan || '-'}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light py-2">
                    <h6 class="mb-0 text-primary">Rincian Biaya</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="60%">Item</th>
                                <th width="15%" class="text-center">Qty</th>
                                <th width="25%" class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong>${transaksiData.paket}</strong><br>
                                    <small class="text-muted">Rp ${transaksiData.harga_paket.toLocaleString('id-ID')} / hari</small>
                                </td>
                                <td class="text-center">${transaksiData.lama_inap} hari</td>
                                <td class="text-end">Rp ${(transaksiData.harga_paket * transaksiData.lama_inap).toLocaleString('id-ID')}</td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="2" class="text-end"><strong>TOTAL</strong></td>
                                <td class="text-end"><strong>Rp ${transaksiData.total_biaya.toLocaleString('id-ID')}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="alert alert-info">
                <h6 class="alert-heading mb-2"><i class="bi bi-info-circle me-2"></i>Informasi Penting</h6>
                <ul class="mb-0 small">
                    <li>Simpan bukti pembayaran ini sebagai tanda pengambilan hewan</li>
                    <li>Penambahan hari penitipan akan dikenakan biaya tambahan</li>
                    <li>Pengambilan hewan setelah jam 18:00 akan dikenakan biaya tambahan</li>
                    <li>Hubungi kami jika ada perubahan atau pertanyaan</li>
                </ul>
            </div>

            <div class="row mt-4">
                <div class="col-md-6 text-center">
                    <p class="mb-4">Hormat Kami,</p>
                    <div style="border-bottom: 1px solid #000; width: 200px; margin: 0 auto 10px;"></div>
                    <p class="mb-0"><small>PetCare Center</small></p>
                </div>
                <div class="col-md-6 text-center">
                    <p class="mb-4">Pemilik Hewan,</p>
                    <div style="border-bottom: 1px solid #000; width: 200px; margin: 0 auto 10px;"></div>
                    <p class="mb-0"><small>${transaksiData.nama_pemilik}</small></p>
                </div>
            </div>
        `;

        const modal = new bootstrap.Modal(document.getElementById('modalBuktiBayar'));
        modal.show();
    }

    function cetakBuktiBayar() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalBuktiBayar'));
        modal.hide();
        
        // Buka window baru untuk print
        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Bukti Pembayaran - PetCare Center</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
                <style>
                    @media print {
                        body { margin: 0; padding: 20px; }
                        .no-print { display: none !important; }
                    }
                    .border-bottom-dotted { border-bottom: 1px dotted #000; }
                </style>
            </head>
            <body>
                ${document.getElementById('buktiBayarContent').innerHTML}
                <div class="text-center mt-4 no-print">
                    <button class="btn btn-primary" onclick="window.print()">Cetak</button>
                    <button class="btn btn-secondary" onclick="window.close()">Tutup</button>
                </div>
            </body>
            </html>
        `);
        printWindow.document.close();
        
        // Auto print setelah window terbuka
        printWindow.onload = function() {
            printWindow.print();
        };
    }
</script>

<?php include __DIR__ . '/template/footer.php'; ?>