<?php
$pageTitle  = 'Data Pelanggan';
$activeMenu = 'pemilik';

// ====== LOAD MODEL PELANGGAN ======
require_once _DIR_ . '/../models/Pelanggan.php';
$pelangganModel = new Pelanggan();

// (Opsional) load model Transaksi untuk riwayat pelanggan
$transaksiModel     = null;
$transaksiModelPath = _DIR_ . '/../models/Transaksi.php';

if (file_exists($transaksiModelPath)) {
    require_once $transaksiModelPath;
    if (class_exists('Transaksi')) {
        $transaksiModel = new Transaksi();
    }
}

// ====== AMBIL PARAMETER ACTION & PESAN ======
$action  = $_GET['action'] ?? '';
$status  = $_GET['status'] ?? '';
$message = $_GET['message'] ?? '';

// ====== PROSES DELETE PELANGGAN ======
if ($action === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    if (method_exists($pelangganModel, 'delete')) {
        $ok = $pelangganModel->delete($id);
    } elseif (method_exists($pelangganModel, 'deleteById')) {
        $ok = $pelangganModel->deleteById($id);
    } else {
        $ok = false;
    }

    $status  = $ok ? 'success' : 'error';
    $message = $ok ? 'Pelanggan berhasil dihapus.' : 'Gagal menghapus pelanggan.';

    header('Location: index.php?page=pemilik&status=' . $status . '&message=' . urlencode($message));
    exit;
}

// ====== DETAIL PELANGGAN (untuk tombol Lihat) ======
$detailPelanggan  = null;
$riwayatTransaksi = [];

if ($action === 'detail' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    if (method_exists($pelangganModel, 'getById')) {
        $detailPelanggan = $pelangganModel->getById($id);
    }

    if ($transaksiModel !== null && method_exists($transaksiModel, 'getByPelanggan')) {
        $riwayatTransaksi = $transaksiModel->getByPelanggan($id);
    }
}

// ====== AMBIL LIST PELANGGAN UNTUK TABEL ======
if (method_exists($pelangganModel, 'getAllWithStats')) {
    $pelangganList = $pelangganModel->getAllWithStats();
} else {
    $pelangganList = $pelangganModel->getAll();
}

// ====== HEADER TEMPLATE ======
include _DIR_ . '/template/header.php';
?>

<h2 class="mb-3">Data Pelanggan</h2>

<?php if (!empty($status)): ?>
    <div class="alert alert-<?= $status === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
        <?= htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- HEADER: Judul + Pencarian -->
<div class="card shadow-sm mb-3">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">

        <div>
            <h5 class="card-title mb-1">Daftar Pelanggan</h5>
            <small class="text-muted">
                Data pemilik hewan yang pernah melakukan penitipan.
            </small>
        </div>

        <div class="d-flex gap-2">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search"></i>
                </span>
                <input
                    type="text"
                    class="form-control border-start-0"
                    placeholder="Cari nama / no. HP / kode pelanggan"
                    id="searchInput"
                    autocomplete="off"
                >
            </div>
        </div>

    </div>
</div>

<?php if ($detailPelanggan): ?>
    <!-- CARD DETAIL PELANGGAN -->
    <div class="card shadow-sm mb-3 border-primary">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <span>Detail Pelanggan</span>
            <a href="index.php?page=pemilik" class="btn btn-light btn-sm">
                <i class="bi bi-x-lg me-1"></i>Tutup
            </a>
        </div>

        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-6">
                    <h5 class="mb-2">
                        <?= htmlspecialchars($detailPelanggan['nama'] ?? '-'); ?>
                    </h5>

                    <div class="mb-1">
                        <span class="text-muted small d-block">Kode Pelanggan</span>
                        <strong><?= htmlspecialchars($detailPelanggan['kode'] ?? '-'); ?></strong>
                    </div>

                    <div class="mb-1">
                        <span class="text-muted small d-block">No. HP</span>
                        <strong><?= htmlspecialchars($detailPelanggan['hp'] ?? '-'); ?></strong>
                    </div>
                </div>

                <div class="col-md-6">
                    <span class="text-muted small d-block">Alamat</span>
                    <div><?= nl2br(htmlspecialchars($detailPelanggan['alamat'] ?? '-')); ?></div>
                </div>
            </div>

            <hr>

            <h6 class="mb-2 text-primary">Riwayat Penitipan</h6>

            <?php if (!empty($riwayatTransaksi)): ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>No. Transaksi</th>
                                <th>Hewan</th>
                                <th>Kandang</th>
                                <th>Tgl Masuk</th>
                                <th>Tgl Keluar</th>
                                <th>Lama</th>
                                <th>Total Biaya</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($riwayatTransaksi as $t): ?>
                            <tr>
                                <td class="fw-semibold">
                                    <?= htmlspecialchars($t['kode_transaksi'] ?? '-'); ?>
                                </td>
                                <td><?= htmlspecialchars($t['nama_hewan'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($t['kode_kandang'] ?? '-'); ?></td>
                                <td>
                                    <?php if (!empty($t['tanggal_masuk'])): ?>
                                        <?= date('d/m/Y', strtotime($t['tanggal_masuk'])); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($t['tanggal_keluar'])): ?>
                                        <?= date('d/m/Y', strtotime($t['tanggal_keluar'])); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= (int)($t['durasi'] ?? 0); ?> hari</td>
                                <td>
                                    Rp <?= number_format((int)($t['total_biaya'] ?? 0), 0, ',', '.'); ?>
                                </td>
                                <td>
                                    <?php
                                        $statusVal   = $t['status'] ?? '';
                                        $statusBadge = $statusVal === 'menginap' ? 'warning' : 'success';
                                        $statusLabel = $statusVal !== '' ? ucfirst($statusVal) : '-';
                                    ?>
                                    <span class="badge bg-<?= $statusBadge; ?>">
                                        <?= htmlspecialchars($statusLabel); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted mb-0">
                    Belum ada riwayat transaksi untuk pelanggan ini
                    (atau fungsi <code>getByPelanggan()</code> belum diimplementasikan).
                </p>
            <?php endif; ?>

        </div>
    </div>
<?php endif; ?>

<!-- TABEL DATA PELANGGAN -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">

            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th style="width: 90px;">Kode</th>
                        <th>Nama Pelanggan</th>
                        <th style="width: 150px;">No. HP</th>
                        <th>Alamat</th>
                        <th style="width: 150px;">Total Transaksi</th>
                        <th style="width: 150px;">Terakhir Titip</th>
                        <th style="width: 130px;">Aksi</th>
                    </tr>
                </thead>

                <tbody id="pelangganTableBody">
                <?php if (empty($pelangganList)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bi bi-people fs-3 mb-1"></i>
                                <span>Belum ada data pelanggan.</span>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; ?>
                    <?php foreach ($pelangganList as $p): ?>
                        <tr>
                            <td class="text-muted"><?= $no++; ?></td>

                            <td class="fw-semibold">
                                <?= htmlspecialchars($p['kode'] ?? '-'); ?>
                            </td>

                            <td><?= htmlspecialchars($p['nama'] ?? '-'); ?></td>

                            <td><?= htmlspecialchars($p['hp'] ?? '-'); ?></td>

                            <td class="small">
                                <?= htmlspecialchars($p['alamat'] ?? '-'); ?>
                            </td>

                            <td class="text-center">
                                <?php
                                    $jumlah = isset($p['total_transaksi']) ? (int)$p['total_transaksi'] : 0;

                                    if ($jumlah > 0) {
                                        echo '<span class="badge bg-primary">' . $jumlah . 'x</span>';
                                    } else {
                                        echo '<span class="text-muted">0</span>';
                                    }
                                ?>
                            </td>

                            <td class="small">
                                <?php if (!empty($p['terakhir_titip'])): ?>
                                    <?= date('d/m/Y', strtotime($p['terakhir_titip'])); ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a
                                        href="index.php?page=pemilik&action=detail&id=<?= $p['id']; ?>"
                                        class="btn btn-outline-primary"
                                        title="Lihat detail"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <a
                                        href="index.php?page=pemilik&action=delete&id=<?= $p['id']; ?>"
                                        class="btn btn-outline-danger"
                                        title="Hapus pelanggan"
                                        onclick="return confirm('Yakin ingin menghapus pelanggan ini? Data transaksi terkait sebaiknya dicek terlebih dahulu.');"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>

            </table>

        </div>
    </div>
</div>

<script>
// Pencarian realtime
document.getElementById('searchInput').addEventListener('input', function (e) {
    const keyword = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#pelangganTableBody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(keyword) ? '' : 'none';
    });
});
</script>

<?php include _DIR_ . '/template/footer.php'; ?>