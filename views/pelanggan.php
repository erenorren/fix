<?php
$pageTitle  = 'Data Pelanggan | Sistem Penitipan Hewan';
$activeMenu = 'pemilik';

// Load template header
include __DIR__ . '/template/header.php';

// Load model
require_once __DIR__ . '/../models/Pelanggan.php';
$pelangganModel = new Pelanggan();

// Ambil semua data pelanggan
$pelangganList = $pelangganModel->getAll();
?>

<h2 class="mb-3">Data Pelanggan</h2>

<!-- HEADER: Judul + Pencarian -->
<div class="card shadow-sm mb-3">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">

        <div>
            <h5 class="card-title mb-1">Daftar Pelanggan</h5><br>
            <small class="text-muted d-block mt-1">
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
                    placeholder="Cari nama / no. HP / alamat"
                    id="searchInput"
                    autocomplete="off">
            </div>
        </div>

    </div>
</div>

<!-- TABEL DATA PELANGGAN SEDERHANA -->
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Nama Pelanggan</th>
                        <th style="width: 180px;">No. HP</th>
                        <th>Alamat</th>
                    </tr>
                </thead>

                <tbody id="pelangganTableBody">
                    <?php if (empty($pelangganList)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
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
                                <td><?= htmlspecialchars($p['nama'] ?? '-'); ?></td>
                                <td><?= htmlspecialchars($p['hp'] ?? '-'); ?></td>
                                <td class="small"><?= htmlspecialchars($p['alamat'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>
</div>

<script>
    // Pencarian realtime (nama / hp / alamat)
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const keyword = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#pelangganTableBody tr');

        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(keyword) ? '' : 'none';
        });
    });
</script>

<?php include __DIR__ . '/template/footer.php'; ?>