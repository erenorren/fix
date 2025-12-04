<?php
// views/template/sidebar.php - TETAP SAMA PERSIS
?>
<<<<<<< HEAD
<aside class="app-sidebar bg-body shadow-sm">
    <div class="sidebar-brand">
        <a href="index.php?page=dashboard" class="brand-link text-decoration-none text-dark">
            <i class="bi bi-heart-pulse brand-icon"></i>
            <span class="brand-text fw-bold">PetCare System</span>
        </a>
=======

<aside class="app-sidebar modern-sidebar bg-primary-blue">

  <!-- Brand -->
  <div class="sidebar-brand d-flex align-items-center">
    <div class="brand-logo-circle me-2">
      <img src="<?= $base_url ?>/img/LOGO.png" class="brand-logo-img" alt="Logo">
>>>>>>> e62c6b676c2a3583c832b6b19014a7851f8306e7
    </div>
    
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-lte-toggle="treeview" role="menu">
                <li class="nav-item">
                    <a href="index.php?page=dashboard" class="nav-link <?= ($_GET['page'] ?? '') == 'dashboard' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="index.php?page=hewan" class="nav-link <?= ($_GET['page'] ?? '') == 'hewan' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-heart-fill"></i>
                        <p>Data Hewan</p>
                    </a>
                </li>
                
                <!-- Menu lainnya tetap sama -->
                
                <li class="nav-item mt-4">
                    <a href="index.php?page=logout" class="nav-link text-danger">
                        <i class="nav-icon bi bi-box-arrow-right"></i>
                        <p>Logout</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>