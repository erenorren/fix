<?php
$pageTitle  = 'Data Hewan & Kandang';
$activeMenu = 'hewan';
include __DIR__ . '/template/header.php';

// Load data dari database
require_once __DIR__ . '/../models/Hewan.php';
require_once __DIR__ . '/../models/Kandang.php';

$hewanModel = new Hewan();
$kandangModel = new Kandang();

// Ambil data hewan
$hewanList = $hewanModel->getAll();

// Ambil ringkasan hewan
$summary = $hewanModel->getSummary();
$totalHewan = $summary['total_hewan'] ?? 0;
$totalKucing = $summary['total_kucing'] ?? 0;
$totalAnjing = $summary['total_anjing'] ?? 0;

// Ambil data kandang
$kandangList = $kandangModel->getAll();

// Hitung total kandang per tipe
$totalkandangKecil = $kandangModel->countByType('Kecil');
$totalkandangBesar = $kandangModel->countByType('Besar');
?>

<!-- HTML Anda tetap sama -->
<h2 class="mb-3">Data Hewan</h2>

<!-- Ringkasan Hewan -->
<div class="row g-3 mb-3">
    <div class="col-lg-4 col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small text-uppercase mb-1">Total Hewan Terdaftar</div>
                    <span class="fs-3 fw-semibold"><?= (int)$totalHewan; ?></span>
                </div>
                <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="bi bi-paw text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small text-uppercase mb-1">Kucing</div>
                    <span class="fs-3 fw-semibold"><?= (int)$totalKucing; ?></span>
                </div>
                <div class="rounded-circle bg-info-subtle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="bi bi-cat text-info"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small text-uppercase mb-1">Anjing</div>
                    <span class="fs-3 fw-semibold"><?= (int)$totalAnjing; ?></span>
                </div>
                <div class="rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                    <i class="bi bi-dog text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabel Data Hewan -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Daftar Hewan</h5>
        <span class="text-muted small">
            Data hewan diperbarui otomatis dari transaksi penitipan.
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Nama Hewan</th>
                        <th>Jenis</th>
                        <th>Ras</th>
                        <th>Pemilik</th>
                        <th>No. Telp Pemilik</th>
                        <th>Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($hewanList)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">
                                Belum ada data hewan.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1;
                        foreach ($hewanList as $h): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= htmlspecialchars($h['nama']); ?></td>
                                <td><?= htmlspecialchars($h['jenis']); ?></td>
                                <td><?= htmlspecialchars($h['ras']); ?></td>
                                <td><?= htmlspecialchars($h['pemilik']); ?></td>
                                <td><?= htmlspecialchars($h['no_telp']); ?></td>
                                <td class="small text-muted"><?= htmlspecialchars($h['catatan'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Data Kandang Section -->
<h2 class="mb-3">Data Kandang</h2>

<!-- Ringkasan Kandang -->
<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small text-uppercase mb-1">Kandang Kecil (KK)</div>
                    <span class="fs-3 fw-semibold"><?= (int)$totalkandangKecil; ?></span>
                </div>
                <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center"
                    style="width:40px;height:40px;">
                    <i class="bi bi-house-heart text-primary"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small text-uppercase mb-1">Kandang Besar (KB)</div>
                    <span class="fs-3 fw-semibold"><?= (int)$totalkandangBesar; ?></span>
                </div>
                <div class="rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center"
                    style="width:40px;height:40px;">
                    <i class="bi bi-building text-warning"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- DAFTAR KANDANG (READ-ONLY) -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-transparent">
        <h5 class="card-title mb-0">Daftar Kandang</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Kode</th>
                        <th>Tipe</th>
                        <th>Catatan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kandangList)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-3 text-muted">
                                Tidak ada data kandang.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1;
                        foreach ($kandangList as $k): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($k['kode']); ?></td>
                                <td>
                                    <span class="badge <?= $k['tipe'] === 'Kecil' ? 'bg-primary' : 'bg-warning'; ?>">
                                        <?= htmlspecialchars($k['tipe']); ?>
                                    </span>
                                </td>
                                <td class="text-muted small"><?= htmlspecialchars($k['catatan'] ?: '-'); ?></td>
                                <td>
                                    <span class="badge bg-success">Tersedia</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/template/footer.php'; ?>