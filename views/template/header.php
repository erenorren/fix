<?php
// Mulai session jika belum berjalan
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Title default
if (!isset($pageTitle)) {
    $pageTitle = 'Sistem Penitipan Hewan';
}

// Base URL hanya dibuat jika belum ada
if (!isset($base_url)) {
    $host = $_SERVER['HTTP_HOST'];
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $base_url = $protocol . '://' . $host;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSS FIX untuk Vercel -->
    <link rel="stylesheet" href="/css/adminlte.css">
    <link rel="stylesheet" href="/css/custom.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

        <!-- HEADER -->
        <nav class="app-header navbar navbar-expand bg-body border-bottom shadow-sm">
            <div class="container-fluid">

                <button class="navbar-toggler" type="button" data-lte-toggle="sidebar" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="flex-grow-1"></div>

                <div class="d-flex align-items-center">
                    <div class="px-3 py-1 rounded-pill bg-primary text-white d-flex align-items-center">
                        <i class="bi bi-person-fill me-2"></i>
                        <span class="small">
                            <?= htmlspecialchars($_SESSION['username'] ?? 'admin'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </nav>

        <!-- SIDEBAR -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- MAIN -->
        <main class="app-main">
            <div class="app-content p-3">