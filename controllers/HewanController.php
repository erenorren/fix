<?php

// ... bagian atas dari index.php Anda (sebelum logic routing) ...
require_once __DIR__ . '/models/kandang.php';
// ...

// ... Di dalam logic routing Anda:
$page = $_GET['page'] ?? 'home';
$action = $_GET['action'] ?? null;

if ($page === 'kandang') {
    $kandangModel = new Kandang();

    if ($action === 'store' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        // Ambil data dari form
        $dataKandang = [
            'kode'    => $_POST['kode'] ?? '',
            'tipe'    => $_POST['tipe'] ?? '',
            'catatan' => $_POST['catatan'] ?? ''
        ];

        if ($kandangModel->create($dataKandang)) {
            // Jika berhasil disimpan, redirect kembali ke halaman hewan
            header('Location: index.php?page=hewan&status=success_add_kandang');
            exit;
        } else {
            // Jika gagal
            header('Location: index.php?page=hewan&status=error_add_kandang');
            exit;
        }
    } 
    // ... Tambahkan logic untuk 'delete' kandang di sini
    
    // Jika tidak ada action yang cocok, tampilkan halaman hewan (ini mungkin perlu disesuaikan)
    // Jika Anda ingin langsung kembali ke view hewan, mungkin tidak perlu di sini.
    // Error "Action not found" biasanya terjadi di mekanisme routing utama Anda.
}

// ... logic routing lainnya (untuk 'hewan', 'pelanggan', 'transaksi')