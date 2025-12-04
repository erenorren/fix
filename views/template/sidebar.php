<?php
// views/template/sidebar.php

// Prioritaskan $page, lalu $activeMenu, lalu default ke 'dashboard'
$activePage = isset($page) ? $page : (isset($activeMenu) ? $activeMenu : 'dashboard');
?>

<aside class="app-sidebar modern-sidebar bg-primary-blue">

  <div class="sidebar-brand d-flex align-items-center">
    <div class="brand-logo me-2">
      <!-- Root-relative logo path -->
      <img src="/img/kucing.png" class="brand-logo-img" alt="Logo">
    </div>
    <div class="d-flex flex-column">
      <span class="brand-name text-white fw-bold">SIP Hewan</span>
      <span class="brand-subtitle text-light">Admin Panel</span>
    </div>
  </div>

  <div class="sidebar-wrapper d-flex flex-column">
    <nav class="mt-2 flex-grow-1">
      <ul class="nav flex-column modern-menu">

        <!-- DASHBOARD -->
        <li class="nav-item mb-1">
          <a href="index.php?page=dashboard" 
             class="nav-link modern-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-grid-3x3-gap modern-icon"></i>
            <span>Dashboard</span>
          </a>
        </li>

        <!-- MENU DATA -->
        <li class="nav-item mb-1 menu-group">
          <?php 
            $isDataActive = in_array($activePage, ['hewan', 'pemilik', 'pelanggan', 'layanan']); 
          ?>
          <a href="#"
             class="nav-link modern-link has-dropdown <?= $isDataActive ? 'active-dropdown' : '' ?>"
             data-target="#menuData">
            <i class="bi bi-folder modern-icon"></i>
            <span class="flex-grow-1">Data</span>
            <i class="bi bi-chevron-down dropdown-arrow"></i>
          </a>

          <div class="submenu <?= $isDataActive ? 'show' : '' ?>" id="menuData">
            <a href="index.php?page=hewan"
               class="nav-link sub-link <?= $activePage === 'hewan' ? 'active' : '' ?>">
              <i class="bi bi-emoji-smile me-1"></i> Data Hewan
            </a>

            <a href="index.php?page=pemilik"
               class="nav-link sub-link <?= $activePage === 'pemilik' || $activePage === 'pelanggan' ? 'active' : '' ?>">
              <i class="bi bi-people me-1"></i> Data Pelanggan
            </a>

            <a href="index.php?page=layanan"
               class="nav-link sub-link <?= $activePage === 'layanan' ? 'active' : '' ?>">
              <i class="bi bi-tools me-1"></i> Jenis Layanan
            </a>
          </div>
        </li>

        <!-- TRANSAKSI -->
        <li class="nav-item mb-1">
          <a href="index.php?page=transaksi"
             class="nav-link modern-link <?= $activePage === 'transaksi' ? 'active' : '' ?>">
            <i class="bi bi-receipt modern-icon"></i>
            <span>Transaksi Penitipan</span>
          </a>
        </li>

        <!-- LOGOUT -->
        <li class="nav-item mt-2">
          <a href="index.php?page=logout" class="nav-link modern-link text-warning">
            <i class="bi bi-box-arrow-right modern-icon"></i>
            <span>Logout</span>
          </a>
        </li>

      </ul>
    </nav>
  </div>
</aside>
