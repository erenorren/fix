<?php
$pageTitle  = 'Dashboard';
$activeMenu = 'dashboard';
include __DIR__ . '/template/header.php';

/*
  Controller bisa mengisi variabel ini:

  $totalHewan
  $totalKucing
  $totalAnjing
  $totalPendapatanHariIni
  $totalKamar
  $kamarTerisi
  $kapasitasKucingMaks
  $kapasitasAnjingMaks
  $transaksiTerbaru = [
      [
        'no_form' => 'F-001',
        'pemilik' => 'Budi',
        'hewan'   => 'Mochi',
        'paket'   => 'B001 - Paket Harian + Grooming',
        'status'  => 'Lunas',
        'total'   => 300000
      ],
      ...
  ];
*/

// nilai default jika controller belum isi
$totalHewan             = $totalHewan             ?? 0;
$totalKucing            = $totalKucing            ?? 0;
$totalAnjing            = $totalAnjing            ?? 0;
$totalPendapatanHariIni = $totalPendapatanHariIni ?? 0;
$totalKamar             = $totalKamar             ?? 0;
$kamarTerisi           = $kamarTerisi           ?? 0;
$kapasitasKucingMaks    = $kapasitasKucingMaks    ?? 0;
$kapasitasAnjingMaks    = $kapasitasAnjingMaks    ?? 0;
$transaksiTerbaru       = $transaksiTerbaru       ?? [];

// hitungan turunan
$persenTerisi   = $totalKamar > 0 ? round(($kamarTerisi / $totalKamar) * 100) : 0;
$kamarKosong    = max($totalKamar - $kamarTerisi, 0);

$sisaSlotKucing = $kapasitasKucingMaks > 0
    ? max($kapasitasKucingMaks - $totalKucing, 0)
    : 0;

$sisaSlotAnjing = $kapasitasAnjingMaks > 0
    ? max($kapasitasAnjingMaks - $totalAnjing, 0)
    : 0;
?>

<h2 class="mb-4">Dashboard Penitipan</h2>

<!-- ROW 1: STAT UTAMA -->
<div class="row g-4 mb-4">
    <!-- Total Hewan Dititipkan -->
    <div class="col-xl-3 col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted text-uppercase small mb-2">Total Hewan Dititipkan</h6>
                        <h2 class="fw-bold text-primary mb-0" data-count="<?= (int)$totalHewan; ?>">0</h2>
                        <span class="text-muted small">Hewan aktif saat ini</span>
                    </div>
                    <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center ms-3"
                        style="width:60px;height:60px;">
                        <i class="bi bi-house-heart fs-5 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Kucing -->
    <div class="col-xl-3 col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted text-uppercase small mb-2">Kucing Dititipkan</h6>
                        <h2 class="fw-bold text-info mb-0" data-count="<?= (int)$totalKucing; ?>">0</h2>
                        <span class="text-muted small">Dalam perawatan</span>
                    </div>
                    <div class="rounded-circle bg-info bg-opacity-10 d-flex align-items-center justify-content-center ms-3"
                        style="width:60px;height:60px;">
                        <i class="bi bi-cat fs-5 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Anjing -->
    <div class="col-xl-3 col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted text-uppercase small mb-2">Anjing Dititipkan</h6>
                        <h2 class="fw-bold text-warning mb-0" data-count="<?= (int)$totalAnjing; ?>">0</h2>
                        <span class="text-muted small">Dalam perawatan</span>
                    </div>
                    <div class="rounded-circle bg-warning bg-opacity-10 d-flex align-items-center justify-content-center ms-3"
                        style="width:60px;height:60px;">
                        <i class="bi bi-dog fs-5 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pendapatan Hari Ini -->
    <div class="col-xl-3 col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h6 class="text-muted text-uppercase small mb-2">Pendapatan Hari Ini</h6>
                        <h2 class="fw-bold text-success mb-0" data-money="<?= (int)$totalPendapatanHariIni; ?>">Rp 0</h2>
                        <span class="text-muted small">Total transaksi hari ini</span>
                    </div>
                    <div class="rounded-circle bg-success bg-opacity-10 d-flex align-items-center justify-content-center ms-3"
                        style="width:60px;height:60px;">
                        <i class="bi bi-cash-coin fs-5 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ROW 2: RINGKASAN + TRANSAKSI TERBARU -->
<div class="row g-4">
    <!-- Ringkasan Kapasitas -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent border-bottom-0 pb-0">
                <h5 class="card-title mb-3">Ringkasan Kapasitas</h5>
            </div>
            <div class="card-body">
                <!-- Okupansi Kamar -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Okupansi Kamar</h6>
                        <span class="fw-bold text-primary"><?= $persenTerisi; ?>%</span>
                    </div>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: <?= $persenTerisi; ?>%;"></div>
                    </div>
                    <div class="row text-center small">
                        <div class="col-4">
                            <div class="fw-semibold text-primary"><?= (int)$kamarTerisi; ?></div>
                            <div class="text-muted">Terisi</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-semibold text-success"><?= (int)$kamarKosong; ?></div>
                            <div class="text-muted">Kosong</div>
                        </div>
                        <div class="col-4">
                            <div class="fw-semibold text-secondary"><?= (int)$totalKamar; ?></div>
                            <div class="text-muted">Total</div>
                        </div>
                    </div>
                </div>

                <!-- Sisa Slot Hewan -->
                <div class="border-top pt-3">
                    <h6 class="mb-3">Sisa Slot Tersedia</h6>
                    
                    <!-- Slot Kucing -->
                    <div class="d-flex align-items-center justify-content-between mb-3 p-3 rounded bg-info bg-opacity-5">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-info bg-opacity-10 p-2 me-3">
                                <i class="bi bi-cat text-info"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">Kucing</div>
                                <small class="text-muted">Maksimal: <?= (int)$kapasitasKucingMaks; ?> slot</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="h5 fw-bold text-info mb-0"><?= (int)$sisaSlotKucing; ?></div>
                            <small class="text-muted">Tersisa</small>
                        </div>
                    </div>

                    <!-- Slot Anjing -->
                    <div class="d-flex align-items-center justify-content-between p-3 rounded bg-warning bg-opacity-5">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle bg-warning bg-opacity-10 p-2 me-3">
                                <i class="bi bi-dog text-warning"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">Anjing</div>
                                <small class="text-muted">Maksimal: <?= (int)$kapasitasAnjingMaks; ?> slot</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="h5 fw-bold text-warning mb-0"><?= (int)$sisaSlotAnjing; ?></div>
                            <small class="text-muted">Tersisa</small>
                        </div>
                    </div>
                </div>

                <!-- Info Penting -->
                <div class="mt-4 p-3 rounded bg-light border">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle text-primary me-2 mt-1"></i>
                        <div class="small">
                            <strong>Informasi Penting:</strong> Pastikan tersedia slot yang cukup sebelum menerima penitipan baru.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaksi Terbaru -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-transparent border-bottom-0 d-flex justify-content-between align-items-center pb-3">
                <div>
                    <h5 class="card-title mb-1">Transaksi Terbaru</h5>
                </div>
                <a href="index.php?page=transaksi&tab=pendaftaran" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Transaksi Baru
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 ps-4">No. Form</th>
                                <th class="border-0">Pemilik</th>
                                <th class="border-0">Hewan</th>
                                <th class="border-0">Paket</th>
                                <th class="border-0 text-end">Total</th>
                                <th class="border-0 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transaksiTerbaru)): ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <div class="py-3">
                                            <i class="bi bi-receipt display-4 text-muted opacity-50"></i>
                                            <p class="mt-3 mb-0">Belum ada transaksi terbaru</p>
                                            <small>Transaksi yang dibuat akan muncul di sini</small>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($transaksiTerbaru as $trx): ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold"><?= htmlspecialchars($trx['no_form']); ?></td>
                                        <td>
                                            <div class="fw-medium"><?= htmlspecialchars($trx['pemilik']); ?></div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php 
                                                $hewanIcon = strtolower($trx['hewan']) === 'kucing' ? 'bi-cat text-info' : 'bi-dog text-warning';
                                                ?>
                                                <i class="bi <?= $hewanIcon; ?> me-2"></i>
                                                <?= htmlspecialchars($trx['hewan']); ?>
                                            </div>
                                        </td>
                                        <td class="small"><?= htmlspecialchars($trx['paket']); ?></td>
                                        <td class="text-end fw-semibold">
                                            Rp <?= number_format($trx['total'], 0, ',', '.'); ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $status = $trx['status'];
                                            $badgeClass = 'secondary';
                                            if ($status === 'Lunas') $badgeClass = 'success';
                                            elseif ($status === 'Menginap') $badgeClass = 'primary';
                                            elseif ($status === 'Pending') $badgeClass = 'warning';
                                            elseif ($status === 'Batal') $badgeClass = 'danger';
                                            ?>
                                            <span class="badge text-bg-<?= $badgeClass; ?>">
                                                <?= htmlspecialchars($status); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Footer dengan link lihat semua -->
                <?php if (!empty($transaksiTerbaru)): ?>
                    <div class="card-footer bg-transparent border-0 pt-3">
                        <div class="text-center">
                            <a href="index.php?page=transaksi&tab=pengembalian" class="text-decoration-none small">
                                <i class="bi bi-arrow-right me-1"></i> Lihat Semua Transaksi
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Animasi angka count-up -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animasi untuk angka biasa
        document.querySelectorAll('[data-count]').forEach(function(el) {
            const target = parseInt(el.dataset.count || '0');
            let current = 0;
            const duration = 1500; // ms
            const steps = 60;
            const increment = target / steps;
            const stepTime = duration / steps;

            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                el.textContent = Math.floor(current).toLocaleString('id-ID');
            }, stepTime);
        });

        // Animasi untuk uang
        document.querySelectorAll('[data-money]').forEach(function(el) {
            const target = parseInt(el.dataset.money || '0');
            let current = 0;
            const duration = 1500;
            const steps = 60;
            const increment = target / steps;
            const stepTime = duration / steps;

            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                el.textContent = 'Rp ' + Math.floor(current).toLocaleString('id-ID');
            }, stepTime);
        });
    });
</script>

<?php include __DIR__ . '/template/footer.php'; ?>