<?php
// views/template/header.php - HARUS TANPA SPASI SEBELUM <?php

// Jika session belum dimulai, mulailah
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Deteksi environment Vercel
$isVercel = isset($_SERVER['VERCEL']) || (isset($_ENV['VERCEL']) && $_ENV['VERCEL'] === '1');

// Tentukan base URL
if ($isVercel) {
    $base_url = 'https://' . $_SERVER['HTTP_HOST'];
} else {
    $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/public';
}

// Pastikan $pageTitle sudah di-set
if (!isset($pageTitle)) {
    $pageTitle = 'Sistem Penitipan Hewan';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- CSS dari CDN untuk memastikan styling bekerja -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- AdminLTE CSS (jika ada) -->
    <!-- <link rel="stylesheet" href="<?= $base_url ?>/css/adminlte.css"> -->
    
    <!-- CSS Custom -->
    <link rel="stylesheet" href="<?= $base_url ?>/css/custom.css">
    
    <style>
        /* Fallback styling jika CSS gagal load */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fa;
        }
        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }
        .app-sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
        }
        .app-main {
            flex: 1;
            padding: 20px;
        }
        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
    </style>
</head>
<body>
    <!-- Simple layout untuk testing -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php?page=dashboard">
                <i class="bi bi-heart-pulse me-2"></i>Sistem Penitipan Hewan
            </a>
            <div class="d-flex align-items-center">
                <span class="me-3 text-dark">
                    <i class="bi bi-person-circle me-1"></i>
                    <?= htmlspecialchars($_SESSION['username'] ?? 'Guest') ?>
                </span>
                <a href="index.php?page=logout" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid mt-4">