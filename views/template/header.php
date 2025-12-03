<?php
// views/template/header.php - TETAP SAMA PERSIS
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($pageTitle)) {
    $pageTitle = 'Sistem Penitipan Hewan';
}

$host = $_SERVER['HTTP_HOST'];

if (strpos($host, 'localhost') !== false) {
    $base_url = 'http://' . $host;
} else {
    $base_url = 'https://' . $host;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

     <!-- AdminLTE v4 CSS -->
    <link rel="stylesheet" href="<?= $base_url ?>/css/adminlte.css">
    <link rel="stylesheet" href="<?= $base_url ?>/css/custom.css">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

        <!-- HEADER / NAVBAR -->
        <nav class="app-header navbar navbar-expand bg-body border-bottom shadow-sm">
            <div class="container-fluid">

                <!-- Tombol toggle sidebar (kiri) -->
                <button class="navbar-toggler" type="button" data-lte-toggle="sidebar" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Kosongkan sisi kiri, biar badge user di kanan -->
                <div class="flex-grow-1"></div>

                <!-- Badge user di pojok kanan -->
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
        <!-- /HEADER -->

        <!-- SIDEBAR -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- MAIN CONTENT WRAPPER -->
        <main class="app-main">
            <div class="app-content p-3">