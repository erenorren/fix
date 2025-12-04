<?php
$pageTitle  = 'Data Layanan | Sistem Penitipan Hewan';
$activeMenu = 'layanan';

// Load model dulu
require_once __DIR__ . '/../models/Layanan.php';
$layananModel = new Layanan();

// PARAMETER
$mode    = $_GET['mode'] ?? '';   // edit / delete
$status  = $_GET['status'] ?? '';
$message = $_GET['message'] ?? '';

/**
 * PROSES CREATE / UPDATE (POST)
 * (HARUS sebelum output HTML / include header)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = $_POST['id'] ?? null;
    $namaLayanan = trim($_POST['nama_layanan'] ?? '');
    $harga       = $_POST['harga'] ?? '';
    $deskripsi   = $_POST['deskripsi'] ?? '';

    if ($namaLayanan === '' || $harga === '') {
        // tidak redirect, biar pesan error bisa tampil di halaman
        $status  = 'error';
        $message = 'Nama layanan dan harga wajib diisi.';
    } else {
        $data = [
            'nama_layanan' => $namaLayanan,
            'harga'        => $harga,
            'deskripsi'    => $deskripsi,
        ];

        if ($id) {
            $ok = $layananModel->update($id, $data);
            $status  = $ok ? 'success' : 'error';
            $message = $ok ? 'Layanan berhasil diperbarui.' : 'Gagal memperbarui layanan.';
        } else {
            $ok = $layananModel->create($data);
            $status  = $ok ? 'success' : 'error';
            $message = $ok ? 'Layanan berhasil ditambahkan.' : 'Gagal menambahkan layanan.';
        }

        // kalau berhasil / gagal, redirect supaya tidak resubmit form
        header('Location: index.php?page=layanan&status=' . $status . '&message=' . urlencode($message));
        exit;
    }
}

/**
 * PROSES DELETE (GET ?mode=delete)
 */
if ($mode === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $ok = $layananModel->delete($id);

    $status  = $ok ? 'success' : 'error';
    $message = $ok ? 'Layanan berhasil dihapus.' : 'Gagal menghapus layanan.';

    header('Location: index.php?page=layanan&status=' . $status . '&message=' . urlencode($message));
    exit;
}

/**
 * DATA UNTUK MODE EDIT (?mode=edit&id=...)
 */
$editLayanan = null;
if ($mode === 'edit' && isset($_GET['id'])) {
    $editLayanan = $layananModel->getById($_GET['id']);
}

// Ambil semua layanan untuk tabel
$layananList = $layananModel->getAll();

// BARU INCLUDE HEADER DI SINI
include __DIR__ . '/template/header.php';
?>

<h2 class="mb-3">Data Layanan</h2>

<?php if (!empty($status)): ?>
    <div class="alert alert-<?= $status === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
        <?= htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- FORM TAMBAH / EDIT -->
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <?= $editLayanan ? 'Edit Layanan' : 'Tambah Layanan Baru'; ?>
                </h5>
            </div>
            <div class="card-body">
                <?php
                    $idVal        = $editLayanan['id_layanan']   ?? '';
                    $namaVal      = $editLayanan['nama_layanan'] ?? '';
                    $hargaVal     = $editLayanan['harga']        ?? '';
                    $deskripsiVal = $editLayanan['deskripsi']    ?? '';
                ?>

                <!-- ACTION TANPA parameter action -->
                <form method="post" action="index.php?page=layanan">
                    <?php if ($editLayanan): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($idVal); ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Nama Layanan <span class="text-danger">*</span></label>
                        <input type="text"
                               name="nama_layanan"
                               class="form-control"
                               required
                               value="<?= htmlspecialchars($namaVal); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
                        <input type="number"
                               name="harga"
                               class="form-control"
                               min="0"
                               step="1000"
                               required
                               value="<?= htmlspecialchars($hargaVal); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi"
                                  class="form-control"
                                  rows="3"><?= htmlspecialchars($deskripsiVal); ?></textarea>
                    </div>

                    <div class="d-flex justify-content-between">
                        <?php if ($editLayanan): ?>
                            <a href="index.php?page=layanan" class="btn btn-outline-secondary">
                                Batal Edit
                            </a>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary">
                            <?= $editLayanan ? 'Update Layanan' : 'Simpan Layanan'; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- TABEL DAFTAR LAYANAN -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar Layanan</h5>
                    <small class="text-muted">Kelola layanan yang tersedia di sistem</small>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:50px;">No</th>
                                <th>Nama Layanan</th>
                                <th style="width:140px;">Harga</th>
                                <th>Deskripsi Singkat</th>
                                <th style="width:130px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($layananList)): ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        Belum ada data layanan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php $no = 1; ?>
                                <?php foreach ($layananList as $l): ?>
                                    <tr>
                                        <td class="text-muted"><?= $no++; ?></td>
                                        <td class="fw-semibold"><?= htmlspecialchars($l['nama_layanan']); ?></td>
                                        <td>Rp <?= number_format($l['harga'], 0, ',', '.'); ?></td>
                                        <td class="small">
                                            <?php
                                                $d = $l['deskripsi'] ?? '';
                                                $short = mb_strlen($d) > 80
                                                    ? mb_substr($d, 0, 80) . '...' : $d;
                                                echo nl2br(htmlspecialchars($short));
                                            ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="index.php?page=layanan&mode=edit&id=<?= $l['id_layanan']; ?>"
                                                   class="btn btn-outline-secondary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="index.php?page=layanan&mode=delete&id=<?= $l['id_layanan']; ?>"
                                                   class="btn btn-outline-danger"
                                                   onclick="return confirm('Yakin ingin menghapus layanan ini?');">
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
    </div>
</div>

<?php include __DIR__ . '/template/footer.php'; ?>