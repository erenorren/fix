<?php
$pageTitle  = 'Data Pelanggan';
$activeMenu = 'pemilik';
include __DIR__ . '/template/header.php';

// Load data dari database
require_once __DIR__ . '/../models/Pelanggan.php';

$pelangganModel = new Pelanggan();
$pelangganList = $pelangganModel->getAll();
?>

<h2 class="mb-3">Data Pelanggan</h2>

<!-- BARIS ATAS: judul daftar -->
<div class="card shadow-sm mb-3">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="card-title mb-1">Daftar Pelanggan</h5>
            <p class="text-muted small mb-0">
                Data pemilik hewan yang sudah terdaftar pada sistem.
            </p>
        </div>

        <div class="d-flex gap-2">
            <!-- Kotak pencarian -->
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control border-start-0"
                       placeholder="Cari nama / no. HP" autocomplete="off"
                       id="searchInput">
            </div>
        </div>
    </div>
</div>

<!-- TABEL DATA PELANGGAN -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 90px;">Kode</th>
                        <th>Nama Pelanggan</th>
                        <th style="width: 150px;">No. HP</th>
                        <th>Alamat</th>
                        <th style="width: 120px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="pelangganTableBody">
                <?php if (empty($pelangganList)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <div class="d-flex flex-column align-items-center">
                                <i class="bi bi-people fs-3 mb-1"></i>
                                <span>Belum ada data pelanggan.</span>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pelangganList as $p): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($p['kode']); ?></td>
                            <td><?= htmlspecialchars($p['nama']); ?></td>
                            <td><?= htmlspecialchars($p['hp']); ?></td>
                            <td class="small"><?= htmlspecialchars($p['alamat'] ?? '-'); ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary" 
                                            onclick="editPelanggan(<?= $p['id'] ?>)">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger"
                                            onclick="deletePelanggan(<?= $p['id'] ?>, '<?= htmlspecialchars($p['nama']) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
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

<!-- Modal untuk Tambah/Edit Pelanggan -->
<div class="modal fade" id="pelangganModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="pelangganForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Pelanggan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="pelangganId" name="id">
                    <div class="mb-3">
                        <label class="form-label">Nama Pelanggan *</label>
                        <input type="text" class="form-control" id="namaPelanggan" name="nama_pelanggan" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. HP *</label>
                        <input type="tel" class="form-control" id="noHp" name="no_hp" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Fungsi untuk pencarian real-time
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#pelangganTableBody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Fungsi edit pelanggan
function editPelanggan(id) {
    // Implementasi edit pelanggan
    alert('Edit pelanggan ID: ' + id);
}

// Fungsi hapus pelanggan
function deletePelanggan(id, nama) {
    if (confirm(`Hapus pelanggan "${nama}"?`)) {
        // Implementasi hapus pelanggan
        alert('Hapus pelanggan: ' + nama);
    }
}
</script>

<?php include __DIR__ . '/template/footer.php'; ?>