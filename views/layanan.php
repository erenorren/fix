<?php
$pageTitle  = 'Data Layanan';
$activeMenu = 'layanan';
include __DIR__ . '/template/header.php';

/*
    Catatan:
    - Nanti array ini bisa diganti menjadi fetch dari database.
    - Untuk sekarang dibuat statis supaya tampilannya jadi.
*/

// PAKET PENITIPAN
$layananUtama = [
    [
        'kode'   => 'P001',
        'nama'   => 'Paket Daycare (Tanpa Menginap) ≤ 5 kg',
        'harga'  => 50000,
        'satuan' => '/ hari',
        'detail' => "Makan 2x\nMinum\nKandang & pasir\nTidak menginap",
    ],
    [
        'kode'   => 'P002',
        'nama'   => 'Paket Daycare (Tanpa Menginap) > 5 kg',
        'harga'  => 60000,
        'satuan' => '/ hari',
        'detail' => "Makan 2x\nMinum\nKandang & pasir\nTidak menginap",
    ],
    [
        'kode'   => 'P003',
        'nama'   => 'Paket Boarding',
        'harga'  => 120000,
        'satuan' => '/ hari',
        'detail' => "Makan\nMinum\nKandang & pasir\nMenginap 24 jam",
    ],
    [
        'kode'   => 'P004',
        'nama'   => 'Paket Boarding > 5 kg',
        'harga'  => 120000,
        'satuan' => '/ hari',
        'detail' => "Makan\nMinum\nKandang & pasir\nMenginap 24 jam",
    ],
    [
        'kode'   => 'P005',
        'nama'   => 'Paket Boarding VIP',
        'harga'  => 250000,
        'satuan' => '/ hari',
        'detail' => "Makan\nMinum\nKandang & pasir\nMenginap 24 jam\nGrooming lengkap (potong kuku, rapih bulu, bersih telinga, mandi, pengeringan, sisir, parfum)",
    ],
];

// LAYANAN TAMBAHAN
$layananTambahan = [
    [
        'kode'   => 'G001',
        'nama'   => 'Grooming Dasar',
        'harga'  => 100000,
        'satuan' => '/ sesi',
        'detail' => "Pemotongan kuku\nPerapihan bulu\nPembersihan telinga\nMandi & pengeringan\nSisir & parfum",
    ],
    [
        'kode'   => 'G002',
        'nama'   => 'Grooming Lengkap',
        'harga'  => 170000,
        'satuan' => '/ sesi',
        'detail' => "Termasuk grooming dasar\nTrimming / bentuk bulu",
    ],
    [
        'kode'   => 'L003',
        'nama'   => 'Vitamin / Suplemen',
        'harga'  => 50000,
        'satuan' => '/ sekali pemberian',
        'detail' => "Pemberian vitamin / suplemen sesuai kebutuhan hewan",
    ],
    [
        'kode'   => 'L004',
        'nama'   => 'Vaksin',
        'harga'  => 260000,
        'satuan' => '/ dosis',
        'detail' => "Kucing: Tricat Trio / Felocell 3 / Purevax\nAnjing: DHPPi / setara",
    ],
];
?>

<h2 class="mb-3">Data Layanan</h2>

<!-- ===========================
     PAKET PENITIPAN
=============================== -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white">
        <div class="d-flex justify-content-between align-items-center w-100">
            <div class="me-3">
                <h5 class="mb-1">Paket Penitipan (Daycare &amp; Boarding)</h5>
                <small class="text-white-50">
                    Paket utama yang dipilih saat pendaftaran penitipan.
                </small>
            </div>

            <!-- TOMBOL DI UJUNG KANAN -->
            <button type="button"
                    class="btn btn-light btn-sm fw-semibold ms-auto"
                    data-bs-toggle="modal"
                    data-bs-target="#modal_tambah_paket">
                <i class="bi bi-plus-lg me-1"></i> Tambah Paket
            </button>
        </div>
    </div>

    <div class="card-body">
        <div class="row g-3">

            <?php foreach ($layananUtama as $l): ?>
                <?php
                    $kode       = htmlspecialchars($l['kode']);
                    $nama       = htmlspecialchars($l['nama']);
                    $harga      = (int)$l['harga'];
                    $satuan     = htmlspecialchars($l['satuan']);
                    $detailRaw  = $l['detail'];
                    $detailList = explode("\n", $detailRaw);
                    $modalId    = 'modal_' . $kode;
                ?>

                <div class="col-lg-4 col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h5 class="fw-semibold mb-0"><?= $nama; ?></h5>
                                <span class="badge bg-primary ms-2"><?= $kode; ?></span>
                            </div>

                            <p class="fw-semibold mt-2 mb-1">
                                Rp <?= number_format($harga, 0, ',', '.'); ?>
                                <span class="small text-muted"><?= $satuan; ?></span>
                            </p>

                            <ul class="text-muted small ps-3 mb-3 flex-grow-1">
                                <?php foreach ($detailList as $d): ?>
                                    <li><?= htmlspecialchars($d); ?></li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="d-flex gap-2">
                                <button type="button"
                                        class="btn btn-outline-primary btn-sm flex-fill"
                                        data-bs-toggle="modal"
                                        data-bs-target="#<?= $modalId; ?>">
                                    <i class="bi bi-pencil-square me-1"></i> Kelola
                                </button>

                                <!-- Tombol hapus paket -->
                                <form method="post"
                                      action="index.php?page=layanan&action=delete_paket"
                                      onsubmit="return confirm('Yakin ingin menghapus paket <?= $kode; ?> ?');">
                                    <input type="hidden" name="kode" value="<?= $kode; ?>">
                                    <button type="submit"
                                            class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Edit Paket -->
                <div class="modal fade" id="<?= $modalId; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form class="modal-content"
                              method="post"
                              action="index.php?page=layanan&action=update_paket"
                              onsubmit="return confirm('Yakin ingin mengubah paket <?= $kode; ?> ?');">

                            <div class="modal-header">
                                <h5 class="modal-title">Edit Paket: <?= $kode; ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>

                            <div class="modal-body">
                                <input type="hidden" name="kode" value="<?= $kode; ?>">

                                <div class="mb-3">
                                    <label class="form-label">Nama Paket</label>
                                    <input type="text" name="nama" class="form-control" value="<?= $nama; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Harga (Rp)</label>
                                    <input type="number" name="harga" class="form-control" value="<?= $harga; ?>" min="0" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Satuan</label>
                                    <input type="text" name="satuan" class="form-control" value="<?= $satuan; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Detail Paket</label>
                                    <textarea name="detail" class="form-control" rows="4"><?= htmlspecialchars($detailRaw); ?></textarea>
                                    <small class="text-muted">Satu fasilitas per baris.</small>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>

                        </form>
                    </div>
                </div>

            <?php endforeach; ?>

        </div>
    </div>
</div>

<!-- Modal Tambah Paket Baru -->
<div class="modal fade" id="modal_tambah_paket" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content"
              method="post"
              action="index.php?page=layanan&action=create_paket"
              onsubmit="return confirm('Yakin ingin menambahkan paket baru?');">

            <div class="modal-header">
                <h5 class="modal-title">Tambah Paket Penitipan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Kode Paket</label>
                    <input type="text" name="kode" class="form-control" placeholder="Misal: P006" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Paket</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga (Rp)</label>
                    <input type="number" name="harga" class="form-control" min="0" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Satuan</label>
                    <input type="text" name="satuan" class="form-control" value="/ hari" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Detail Paket</label>
                    <textarea name="detail" class="form-control" rows="4" placeholder="Satu fasilitas per baris"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Paket</button>
            </div>

        </form>
    </div>
</div>

<!-- ===========================
     LAYANAN TAMBAHAN
=============================== -->
<div class="card shadow-sm mb-4">
    <div class="d-flex justify-content-between align-items-center p-3 bg-secondary text-white rounded-top">
    <div>
        <h5 class="mb-0">Layanan Tambahan</h5>
        <small>Grooming, vaksin, vitamin, dan layanan opsional lainnya.</small>
    </div>

    <button class="btn btn-light"
            data-bs-toggle="modal"
            data-bs-target="#modalAddTambahan">
        <i class="bi bi-plus"></i> Tambah Layanan
    </button>
</div>

    <div class="card-body">
        <div class="row g-3">

            <?php foreach ($layananTambahan as $l): ?>
                <?php
                    $kode       = htmlspecialchars($l['kode']);
                    $nama       = htmlspecialchars($l['nama']);
                    $harga      = (int)$l['harga'];
                    $satuan     = htmlspecialchars($l['satuan']);
                    $detailRaw  = $l['detail'];
                    $detailList = explode("\n", $detailRaw);
                    $modalId    = 'modal_' . $kode;
                ?>

                <div class="col-lg-4 col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h5 class="fw-semibold mb-0"><?= $nama; ?></h5>
                                <span class="badge bg-secondary ms-2"><?= $kode; ?></span>
                            </div>

                            <p class="fw-semibold mt-2 mb-1">
                                Rp <?= number_format($harga, 0, ',', '.'); ?>
                                <span class="small text-muted"><?= $satuan; ?></span>
                            </p>

                            <ul class="text-muted small ps-3 mb-3 flex-grow-1">
                                <?php foreach ($detailList as $d): ?>
                                    <li><?= htmlspecialchars($d); ?></li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="d-flex gap-2">
                                <button type="button"
                                        class="btn btn-outline-secondary btn-sm flex-fill"
                                        data-bs-toggle="modal"
                                        data-bs-target="#<?= $modalId; ?>">
                                    <i class="bi bi-pencil-square me-1"></i> Kelola
                                </button>

                                <!-- Tombol hapus layanan tambahan -->
                                <form method="post"
                                      action="index.php?page=layanan&action=delete_tambahan"
                                      onsubmit="return confirm('Yakin ingin menghapus layanan <?= $kode; ?> ?');">
                                    <input type="hidden" name="kode" value="<?= $kode; ?>">
                                    <button type="submit"
                                            class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Edit Layanan Tambahan -->
                <div class="modal fade" id="<?= $modalId; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form class="modal-content"
                              method="post"
                              action="index.php?page=layanan&action=update_tambahan"
                              onsubmit="return confirm('Yakin ingin mengubah layanan <?= $kode; ?> ?');">

                            <div class="modal-header">
                                <h5 class="modal-title">Edit Layanan: <?= $kode; ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                            </div>

                            <div class="modal-body">
                                <input type="hidden" name="kode" value="<?= $kode; ?>">

                                <div class="mb-3">
                                    <label class="form-label">Nama Layanan</label>
                                    <input type="text" name="nama" class="form-control" value="<?= $nama; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Harga (Rp)</label>
                                    <input type="number" name="harga" class="form-control" value="<?= $harga; ?>" min="0" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Satuan</label>
                                    <input type="text" name="satuan" class="form-control" value="<?= $satuan; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Detail</label>
                                    <textarea name="detail" class="form-control" rows="4"><?= htmlspecialchars($detailRaw); ?></textarea>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>

                        </form>
                    </div>
                </div>

            <?php endforeach; ?>

        </div>
    </div>
</div>

<!-- Modal Tambah Layanan Tambahan -->
<div class="modal fade" id="modal_tambah_tambahan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content"
              method="post"
              action="index.php?page=layanan&action=create_tambahan"
              onsubmit="return confirm('Yakin ingin menambahkan layanan tambahan baru?');">

            <div class="modal-header">
                <h5 class="modal-title">Tambah Layanan Tambahan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Kode Layanan</label>
                    <input type="text" name="kode" class="form-control" placeholder="Misal: G003 / L005" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Layanan</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Harga (Rp)</label>
                    <input type="number" name="harga" class="form-control" min="0" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Satuan</label>
                    <input type="text" name="satuan" class="form-control" value="/ sesi" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Detail</label>
                    <textarea name="detail" class="form-control" rows="4" placeholder="Satu fasilitas / penjelasan per baris"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Layanan</button>
            </div>

        </form>
    </div>
</div>

<?php include __DIR__ . '/template/footer.php'; ?>
