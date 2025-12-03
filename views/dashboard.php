<?php
// views/dashboard.php
?>
<div class="container mt-4">
    <h1>Dashboard</h1>
    <p>Selamat datang, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>!</p>
    
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5>Total Hewan</h5>
                    <h2>12</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Transaksi Aktif</h5>
                    <h2>5</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Kandang Tersedia</h5>
                    <h2>8</h2>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <a href="index.php?page=logout" class="btn btn-danger">Logout</a>
    </div>
</div>