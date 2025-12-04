<?php
// views/template/header.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Default page title
if (!isset($pageTitle)) {
    $pageTitle = 'Sistem Penitipan Hewan';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="icon" type="image/png" href="/img/kucing.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- AdminLTE & Custom CSS -->
    <!-- Pakai root-relative path, aman di localhost & Vercel -->
    <link rel="stylesheet" href="/css/adminlte.css">
    <link rel="stylesheet" href="/css/custom.css">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<!-- sidebar-expand-lg: Sidebar otomatis muncul di desktop -->
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">

        <!-- HEADER / NAVBAR -->
        <nav class="app-header navbar navbar-expand bg-body border-bottom shadow-sm">
            <div class="container-fluid">

                <!-- Tombol Toggle Sidebar (HANYA MUNCUL DI HP / Mobile) -->
                <ul class="navbar-nav">
                    <!-- class 'd-lg-none' menyembunyikan tombol ini di layar Desktop -->
                    <li class="nav-item d-lg-none">
                        <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                            <i class="bi bi-list"></i>
                        </a>
                    </li>
                </ul>

                <!-- Spacer -->
                <div class="flex-grow-1"></div>

                <!-- Badge User -->
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

        <!-- Load Sidebar -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Content Wrapper -->
        <main class="app-main">
            <div class="app-content p-3">
