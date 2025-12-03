<?php
// views/transaksi.php
// FILE INI HANYA MENAMPILKAN DATA (VIEW)

$pageTitle = 'Transaksi Penitipan Hewan';
$activeMenu = 'transaksi';

// Nilai default dari Controller (Wajib)
$pelangganList = $pelangganList ?? [];
$paketList = $paketList ?? [];
$kandangTersedia = $kandangTersedia ?? [];
$hewanMenginap = $hewanMenginap ?? []; 
$tab = $tab ?? ($_GET['tab'] ?? 'pendaftaran');

include __DIR__ . '/template/header.php';
?>

<?php if (isset($_GET['status'])): ?>
    <div class='alert alert-<?= $_GET['status'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show'>
        <?php if ($_GET['status'] === 'success'): ?>
            <strong>Sukses!</strong> <?= htmlspecialchars($_GET['message'] ?? 'Transaksi berhasil dibuat') ?>
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
                    <h5 class="mb-3">Form Pendaftaran Penitipan</h5>
                    <form method="POST" action="index.php?action=createTransaksi" id="formPendaftaran">
                        <div class="row g-4">
                            <!-- INFORMASI PEMILIK -->
                            <div class="col-lg-6">
                                <div class="card p-3 h-100 position-relative">
                                    <h6 class="mb-3 text-primary">Informasi Pemilik</h6>

                                    <!-- DROPDOWN PELANGGAN -->
                                    <div class="mb-3">
                                        <label class="form-label">Pilih Pemilik <span class="text-danger">*</span></label>
                                        <select name="id_pelanggan" class="form-select" id="selectPelanggan" required>
                                            <option value="">-- Pilih Pemilik --</option>
                                            <?php if (!empty($pelangganList)): ?>
                                                <?php foreach ($pelangganList as $p): ?>
                                                    <?php 
                                                    $id = $p['id'] ?? $p['id_pelanggan'] ?? '';
                                                    $nama = $p['nama'] ?? $p['nama_pelanggan'] ?? '';
                                                    $hp = $p['hp'] ?? $p['no_hp'] ?? '';
                                                    $alamat = $p['alamat'] ?? '';
                                                    ?>
                                                    <option value="<?= $id ?>" 
                                                        data-hp="<?= htmlspecialchars($hp) ?>" 
                                                        data-alamat="<?= htmlspecialchars($alamat) ?>"
                                                        data-nama="<?= htmlspecialchars($nama) ?>">
                                                        <?= htmlspecialchars($nama) ?> 
                                                        (<?= htmlspecialchars($hp) ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                            <option value="new">+ Tambah Pemilik Baru</option>
                                        </select>
                                        <small class="text-muted">Pilih dari daftar pelanggan terdaftar</small>
                                    </div>

                                    <!-- FIELDS UNTUK PELANGGAN BARU -->
                                    <div id="newCustomerFields" style="display: none;">
                                        <div class="mb-3">
                                            <label class="form-label">Nama Pemilik Baru <span class="text-danger">*</span></label>
                                            <input type="text" name="nama_pelanggan_baru" class="form-control" 
                                                placeholder="Masukkan nama lengkap pemilik">
                                        </div>
                                    </div>

                                    <!-- NO HP -->
                                    <div class="mb-3">
                                        <label class="form-label">Nomor HP <span class="text-danger">*</span></label>
                                        <input type="text" name="no_hp" id="p_hp" class="form-control"
                                            placeholder="Contoh: 08123456789">
                                    </div>

                                    <!-- ALAMAT -->
                                    <div class="mb-3">
                                        <label class="form-label">Alamat <span class="text-danger">*</span></label>
                                        <textarea name="alamat" id="p_alamat" class="form-control"
                                            rows="2" placeholder="Alamat lengkap pemilik"></textarea>
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
                                        <div class="col-lg-4">
                                            <label class="form-label">Paket Utama <span class="text-danger">*</span></label>
                                            <select name="id_layanan" class="form-select" id="paketSelect" required>
                                                <option value="">-- Pilih Paket --</option>
                                                <?php foreach ($paketList as $pk): ?>
                                                    <?php 
                                                    $id = $pk['id_layanan'] ?? $pk['id'] ?? '';
                                                    $nama = $pk['nama_layanan'] ?? $pk['nama'] ?? '';
                                                    $harga = $pk['harga'] ?? 0;
                                                    $hargaFormatted = number_format($harga, 0, ',', '.');
                                                    ?>
                                                    <option value="<?= $id ?>" 
                                                            data-harga="<?= $harga ?>"
                                                            data-nama="<?= htmlspecialchars($nama) ?>">
                                                        <?= htmlspecialchars($nama) ?> - Rp <?= $hargaFormatted ?>/hari
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>                                
                                            <div class="form-text">Pilih salah satu paket penitipan</div>
                                        </div>

                                        <div class="col-lg-8">
                                            <div class="alert alert-info mt-4">
                                                <h6>Info Paket:</h6>
                                                <div id="paketInfo">Pilih paket untuk melihat detail</div>
                                            </div>
                                        </div>
                                    </div>

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
                                                    Pilih kandang: 
                                                    <span id="kandangRuleInfo">
                                                        <?php 
                                                        echo "Kucing kecil: semua kandang | Kucing sedang: sedang & besar | Kucing besar: besar saja | ";
                                                        echo "Anjing kecil/sedang: sedang & besar | Anjing besar: besar saja";
                                                        ?>
                                                    </span>
                                                </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Simpan
                            </button>
                        </div>
                    </form>

                <?php else: ?>
                    <!-- TAB PENGEMBALIAN -->
                    <h5 class="mb-3">Form Pengembalian Hewan</h5>
                    
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="mb-3 text-primary">Cari Hewan yang Sedang Menginap</h6>
                            <div class="row g-3">
                                <div class="col-lg-6">
                                    <label class="form-label">Cari berdasarkan Nama Pemilik atau Hewan</label>
                                    <input type="text" id="searchCheckout" class="form-control" placeholder="Ketik nama pemilik atau hewan...">
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
                                                            <?php $hewanIcon = $hewan['jenis_hewan'] === 'Kucing' ? 'bi-cat text-info' : 'bi-dog text-warning'; ?>
                                                            <i class="bi <?= $hewanIcon; ?> me-2"></i>
                                                            <?= htmlspecialchars($hewan['nama_hewan']); ?>
                                                        </div>
                                                    </td>
                                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($hewan['kode_kandang']); ?></span></td>
                                                    <td><?= date('d/m/Y', strtotime($hewan['tanggal_masuk'])); ?></td>
                                                    <td><?= $hewan['durasi']; ?> hari</td>
                                                    <td class="fw-semibold text-primary">Rp <?= number_format($hewan['total_biaya'], 0, ',', '.'); ?></td>
                                                    <td>
                                                        <button type="button" class="btn btn-success btn-sm" onclick="prosesCheckout('<?= $hewan['id_transaksi']; ?>')">
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
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($tab === 'pendaftaran'): ?>
<script>
// =============================================
// JAVASCRIPT UNTUK FORM PENDAFTARAN TRANSAKSI
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    console.log("Transaksi JS loaded");
    
    // 1. AUTO-FILL DATA PELANGGAN
    const selectPelanggan = document.getElementById('selectPelanggan');
    const noHpInput = document.getElementById('p_hp');
    const alamatInput = document.getElementById('p_alamat');
    const newCustomerFields = document.getElementById('newCustomerFields');
    
    if (selectPelanggan && noHpInput) {
        selectPelanggan.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            
            if (this.value === 'new') {
                // Mode pelanggan baru
                if (newCustomerFields) newCustomerFields.style.display = 'block';
                noHpInput.value = '';
                alamatInput.value = '';
            } 
            else if (this.value && this.value !== '') {
                // Mode pelanggan existing
                if (newCustomerFields) newCustomerFields.style.display = 'none';
                noHpInput.value = selectedOption.getAttribute('data-hp') || '';
                alamatInput.value = selectedOption.getAttribute('data-alamat') || '';
            }
            else {
                // Kosong
                if (newCustomerFields) newCustomerFields.style.display = 'none';
                noHpInput.value = '';
                alamatInput.value = '';
            }
        });
        
        // Trigger change awal
        if (selectPelanggan.value) {
            selectPelanggan.dispatchEvent(new Event('change'));
        }
    }
    
    // 2. KALKULASI HARGA OTOMATIS
    const paketSelect = document.getElementById('paketSelect');
    const lamaInapInput = document.getElementById('lamaInap');
    const totalDisplay = document.getElementById('totalHarga');
    const totalInput = document.getElementById('totalInput');
    
    if (paketSelect && totalDisplay) {
        function hitungTotal() {
            const selectedOption = paketSelect.options[paketSelect.selectedIndex];
            const harga = parseInt(selectedOption.getAttribute('data-harga')) || 0;
            const hari = parseInt(lamaInapInput?.value) || 1;
            const total = harga * hari;
            
            totalDisplay.textContent = 'Rp ' + total.toLocaleString('id-ID');
            if (totalInput) totalInput.value = total;
            
            // Update detail perhitungan
            const detailElement = document.getElementById('detailPerhitungan');
            if (detailElement) {
                detailElement.textContent = `Rp ${harga.toLocaleString('id-ID')} × ${hari} hari`;
            }
            
            // Update info paket
            const paketInfo = document.getElementById('paketInfo');
            if (paketInfo) {
                const namaPaket = selectedOption.getAttribute('data-nama') || '';
                paketInfo.innerHTML = `<strong>${namaPaket}</strong><br>Harga per hari: Rp ${harga.toLocaleString('id-ID')}`;
            }
        }
        
        paketSelect.addEventListener('change', hitungTotal);
        if (lamaInapInput) {
            lamaInapInput.addEventListener('input', hitungTotal);
        }
        
        // Hitung awal
        setTimeout(hitungTotal, 100);
    }
    
    // 3. PILIH KANDANG
    const btnPilihKandang = document.getElementById('btnPilihKandang');
    const panelKandang = document.getElementById('panelKandang');
    const jenisHewanSelect = document.getElementById('jenisHewanSelect');
    const ukuranHewanSelect = document.getElementById('ukuranHewanSelect');
    
    if (btnPilihKandang && panelKandang) {
        const kandangLabel = document.getElementById('kandangLabel');
        const idKandangInput = document.getElementById('id_kandang');
        
        // Data kandang dari PHP (di-inject dari controller)
        const kandangData = <?= json_encode($kandangTersedia ?? []) ?>;
        console.log("Kandang data:", kandangData);
        
        function tampilkanKandangTersedia() {
            const jenis = jenisHewanSelect?.value;
            const ukuran = ukuranHewanSelect?.value;
            
            if (!jenis) {
                alert('Pilih jenis hewan terlebih dahulu');
                return [];
            }
            
            // Filter kandang yang tersedia
            let filtered = kandangData.filter(k => k.status === 'tersedia');
            
            // Filter berdasarkan aturan
            if (jenis === 'Kucing') {
                if (ukuran === 'Sedang') {
                    filtered = filtered.filter(k => k.tipe === 'Sedang' || k.tipe === 'Besar');
                } else if (ukuran === 'Besar') {
                    filtered = filtered.filter(k => k.tipe === 'Besar');
                }
            } 
            else if (jenis === 'Anjing') {
                if (ukuran === 'Kecil') {
                    filtered = filtered.filter(k => k.tipe === 'Sedang');
                } else if (ukuran === 'Sedang') {
                    filtered = filtered.filter(k => k.tipe === 'Sedang' || k.tipe === 'Besar');
                } else if (ukuran === 'Besar') {
                    filtered = filtered.filter(k => k.tipe === 'Besar');
                } else {
                    // Default untuk anjing tanpa ukuran
                    filtered = filtered.filter(k => k.tipe === 'Sedang' || k.tipe === 'Besar');
                }
            }
            
            return filtered;
        }
        
        btnPilihKandang.addEventListener('click', function() {
            const kandangTersedia = tampilkanKandangTersedia();
            panelKandang.innerHTML = '';
            panelKandang.classList.remove('d-none');
            
            if (kandangTersedia.length === 0) {
                panelKandang.innerHTML = `
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-inbox display-6 opacity-50"></i>
                        <p class="mt-2 mb-0">Tidak ada kandang tersedia</p>
                        <small>Untuk jenis hewan yang dipilih</small>
                    </div>
                `;
                return;
            }
            
            kandangTersedia.forEach(kandang => {
                const item = document.createElement('div');
                item.className = 'p-2 border-bottom hover-bg-light';
                item.style.cursor = 'pointer';
                item.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="fw-semibold">${kandang.kode_kandang}</span>
                            <small class="text-muted ms-2">${kandang.tipe}</small>
                        </div>
                        <span class="badge bg-success">Tersedia</span>
                    </div>
                `;
                
                item.addEventListener('click', function() {
                    if (kandangLabel) {
                        kandangLabel.textContent = `${kandang.kode_kandang} - ${kandang.tipe}`;
                    }
                    if (idKandangInput) {
                        idKandangInput.value = kandang.id_kandang || kandang.id;
                    }
                    panelKandang.classList.add('d-none');
                    
                    // Update tampilan tombol
                    btnPilihKandang.classList.remove('btn-outline-secondary');
                    btnPilihKandang.classList.add('btn-outline-success');
                    
                    console.log("Kandang dipilih:", kandang);
                });
                
                panelKandang.appendChild(item);
            });
        });
        
        // Reset saat jenis/ukuran berubah
        if (jenisHewanSelect) {
            jenisHewanSelect.addEventListener('change', function() {
                if (idKandangInput) idKandangInput.value = '';
                if (kandangLabel) kandangLabel.textContent = 'Pilih kandang yang tersedia';
                btnPilihKandang.classList.remove('btn-outline-success');
                btnPilihKandang.classList.add('btn-outline-secondary');
            });
        }
        
        if (ukuranHewanSelect) {
            ukuranHewanSelect.addEventListener('change', function() {
                if (idKandangInput) idKandangInput.value = '';
                if (kandangLabel) kandangLabel.textContent = 'Pilih kandang yang tersedia';
                btnPilihKandang.classList.remove('btn-outline-success');
                btnPilihKandang.classList.add('btn-outline-secondary');
            });
        }
        
        // Tutup panel saat klik di luar
        document.addEventListener('click', function(e) {
            if (panelKandang && !panelKandang.contains(e.target) && 
                e.target !== btnPilihKandang && 
                !btnPilihKandang.contains(e.target)) {
                panelKandang.classList.add('d-none');
            }
        });
    }
    
    // 4. VALIDASI FORM SEBELUM SUBMIT
    const formPendaftaran = document.getElementById('formPendaftaran');
    
    if (formPendaftaran) {
        formPendaftaran.addEventListener('submit', function(e) {
            console.log("Form validation started");
            
            // Validasi 1: Kandang sudah dipilih
            const idKandang = document.getElementById('id_kandang');
            if (!idKandang || !idKandang.value) {
                e.preventDefault();
                alert('⚠️ Silakan pilih kandang terlebih dahulu');
                if (btnPilihKandang) btnPilihKandang.focus();
                return false;
            }
            
            // Validasi 2: Paket sudah dipilih
            const paketSelect = document.getElementById('paketSelect');
            if (!paketSelect || !paketSelect.value) {
                e.preventDefault();
                alert('⚠️ Silakan pilih paket layanan');
                paketSelect.focus();
                return false;
            }
            
            // Validasi 3: Jika pelanggan baru, nama harus diisi
            const selectPelanggan = document.getElementById('selectPelanggan');
            if (selectPelanggan && selectPelanggan.value === 'new') {
                const namaPelangganBaru = document.querySelector('input[name="nama_pelanggan_baru"]');
                if (namaPelangganBaru && !namaPelangganBaru.value.trim()) {
                    e.preventDefault();
                    alert('⚠️ Nama pemilik baru harus diisi');
                    namaPelangganBaru.focus();
                    return false;
                }
            }
            
            console.log("Form validation passed");
            
            // Tampilkan loading
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Menyimpan...';
                submitBtn.disabled = true;
            }
            
            return true;
        });
    }
});
</script>
<?php endif; ?>

<?php if ($tab === 'pengembalian'): ?>
<script>
// =============================================
// JAVASCRIPT UNTUK TAB PENGEMBALIAN
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    console.log("Pengembalian JS loaded");
    
    // 1. FUNGSI CHECKOUT GLOBAL
    window.prosesCheckout = function(id_transaksi) {
        if (confirm('Apakah Anda yakin ingin melakukan check-out hewan ini?')) {
            // Tampilkan loading pada tombol yang diklik
            const btn = event.target.closest('button');
            if (btn) {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Processing...';
                btn.disabled = true;
            }
            
            // Redirect ke action checkout
            window.location.href = 'index.php?action=checkoutTransaksi&id=' + id_transaksi;
        }
    };
    
    // 2. PENCARIAN DI TAB PENGEMBALIAN
    const btnCariCheckout = document.getElementById('btnCariCheckout');
    const searchInput = document.getElementById('searchCheckout');
    const filterKandang = document.getElementById('filterKandang');
    
    if (btnCariCheckout && searchInput) {
        function performSearch() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const filterValue = filterKandang ? filterKandang.value : '';
            
            const rows = document.querySelectorAll('.table tbody tr');
            let foundAny = false;
            
            rows.forEach(row => {
                if (row.cells.length < 8) return;
                
                const pemilik = row.cells[1]?.textContent?.toLowerCase() || '';
                const hewan = row.cells[2]?.textContent?.toLowerCase() || '';
                const kandang = row.cells[3]?.textContent || '';
                
                const matchesSearch = !searchTerm || 
                    pemilik.includes(searchTerm) || 
                    hewan.includes(searchTerm);
                const matchesFilter = !filterValue || 
                    kandang.includes(filterValue);
                
                if (matchesSearch && matchesFilter) {
                    row.style.display = '';
                    foundAny = true;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Tampilkan pesan jika tidak ada hasil
            const existingMessage = document.querySelector('.no-results-row');
            if (existingMessage) existingMessage.remove();
            
            if (!foundAny && rows.length > 0) {
                const tbody = document.querySelector('.table tbody');
                const messageRow = document.createElement('tr');
                messageRow.className = 'no-results-row';
                messageRow.innerHTML = `
                    <td colspan="8" class="text-center py-4">
                        <i class="bi bi-search display-6 text-muted opacity-50"></i>
                        <p class="mt-2 mb-0 text-muted">Tidak ditemukan hasil pencarian</p>
                    </td>
                `;
                tbody.appendChild(messageRow);
            }
        }
        
        // Event listeners
        btnCariCheckout.addEventListener('click', performSearch);
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
        
        if (filterKandang) {
            filterKandang.addEventListener('change', performSearch);
        }
    }
});
</script>
<?php endif; ?>

<!-- CSS tambahan untuk hover effect -->
<style>
.hover-bg-light:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s;
}
</style>

<?php include __DIR__ . '/template/footer.php'; ?>