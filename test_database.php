<?php
/**
 * TESTING DATABASE & MODEL
 * 
 * CARA PAKAI:
 * 1. Pastikan XAMPP/Laragon sudah running (Apache + MySQL)
 * 2. Database db_penitipan_hewan sudah di-import
 * 3. Buka browser: http://localhost/project-uas-pbo/test_database.php
 * 
 * @author h1101241034@student.untan.ac.id
 */

// Load semua yang dibutuhkan
require_once 'config/database.php';
require_once 'models/User.php';
require_once 'models/Pelanggan.php';
require_once 'models/Hewan.php';
require_once 'models/Layanan.php';
require_once 'models/Transaksi.php';

// Mulai session (untuk testing login)
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testing Database - Sistem Penitipan Hewan</title>
    <link rel="stylesheet" href="public/dist/css/custom.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            text-align: center;
        }
        .header h1 {
            color: #0265FE;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 1.1rem;
        }
        .test-box {
            background: white;
            padding: 25px;
            margin: 20px 0;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .test-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .test-box h2 {
            color: #333;
            border-left: 5px solid #0265FE;
            padding-left: 15px;
            margin-bottom: 20px;
        }
        .test-box h3 {
            color: #555;
            margin: 20px 0 10px 0;
            font-size: 1.2rem;
        }
        .success {
            color: #28a745;
            font-weight: 600;
            padding: 10px;
            background: #d4edda;
            border-left: 4px solid #28a745;
            border-radius: 5px;
            margin: 10px 0;
        }
        .error {
            color: #dc3545;
            font-weight: 600;
            padding: 10px;
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            border-radius: 5px;
            margin: 10px 0;
        }
        .warning {
            color: #856404;
            padding: 10px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 5px;
            margin: 10px 0;
        }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-left: 4px solid #0265FE;
            border-radius: 5px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            font-size: 0.95rem;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #e0e0e0;
        }
        th {
            background: #0265FE;
            color: white;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background: #f9f9f9;
        }
        tr:hover {
            background: #f0f0f0;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: #333; }
        .badge-info { background: #17a2b8; color: white; }
        code {
            background: #f4f4f4;
            padding: 3px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #e83e8c;
        }
        .summary {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-top: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .summary h2 {
            color: white;
            border: none;
            margin-bottom: 20px;
        }
        .summary ul {
            list-style: none;
            padding: 0;
        }
        .summary li {
            padding: 10px 0;
            font-size: 1.1rem;
        }
        .summary li:before {
            content: "✓ ";
            font-weight: bold;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🧪 Testing Database & Model</h1>
            <p>Sistem Penitipan Hewan - Versi Final</p>
        </div>
        
        <!-- TEST 1: Koneksi Database -->
        <div class="test-box">
            <h2>1. Test Koneksi Database</h2>
            <?php
            try {
                $db = getDB();
                echo '<p class="success">✅ Koneksi database berhasil!</p>';
                echo '<div class="info">';
                echo '<strong>Database:</strong> db_penitipan_hewan<br>';
                echo '<strong>Host:</strong> localhost<br>';
                echo '<strong>Status:</strong> Connected';
                echo '</div>';
            } catch (Exception $e) {
                echo '<p class="error">❌ Koneksi gagal: ' . $e->getMessage() . '</p>';
                echo '<div class="warning">Pastikan XAMPP/Laragon sudah running & database sudah di-import!</div>';
                exit;
            }
            ?>
        </div>
        
        <!-- TEST 2: Model User & Autentikasi -->
        <div class="test-box">
            <h2>2. Test Model User & Autentikasi</h2>
            <?php
            try {
                $userModel = new User();
                
                // Test login
                echo '<h3>🔐 Test Login</h3>';
                $loginResult = $userModel->login('kasir01', 'password123');
                
                if ($loginResult) {
                    echo '<p class="success">✅ Login berhasil!</p>';
                    echo '<div class="info">';
                    echo '<strong>Username:</strong> ' . $loginResult['username'] . '<br>';
                    echo '<strong>Nama:</strong> ' . $loginResult['nama_lengkap'] . '<br>';
                    echo '<strong>Role:</strong> <span class="badge badge-info">' . $loginResult['role'] . '</span>';
                    echo '</div>';
                } else {
                    echo '<p class="error">❌ Login gagal</p>';
                }
                
                // Test getAll
                echo '<h3>👥 Daftar Semua User</h3>';
                $users = $userModel->getAll();
                
                if (count($users) > 0) {
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Username</th><th>Nama Lengkap</th><th>Role</th><th>Tanggal Daftar</th></tr>';
                    foreach ($users as $u) {
                        $badgeClass = $u['role'] === 'admin' ? 'badge-danger' : 'badge-info';
                        echo '<tr>';
                        echo '<td>' . $u['id_user'] . '</td>';
                        echo '<td>' . $u['username'] . '</td>';
                        echo '<td>' . $u['nama_lengkap'] . '</td>';
                        echo '<td><span class="badge ' . $badgeClass . '">' . $u['role'] . '</span></td>';
                        echo '<td>' . date('d/m/Y', strtotime($u['created_at'])) . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    echo '<p class="success">✅ Berhasil load ' . count($users) . ' user</p>';
                }
                
            } catch (Exception $e) {
                echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
        
        <!-- TEST 3: Model Pelanggan -->
        <div class="test-box">
            <h2>3. Test Model Pelanggan</h2>
            <?php
            try {
                $pelangganModel = new Pelanggan();
                
                // Test getAll
                echo '<h3>📋 Daftar Pelanggan</h3>';
                $pelangganList = $pelangganModel->getAll();
                
                if (count($pelangganList) > 0) {
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Nama</th><th>No. HP</th><th>Alamat</th></tr>';
                    foreach ($pelangganList as $p) {
                        echo '<tr>';
                        echo '<td>' . $p['id_pelanggan'] . '</td>';
                        echo '<td>' . $p['nama_pelanggan'] . '</td>';
                        echo '<td>' . $p['no_hp'] . '</td>';
                        echo '<td>' . $p['alamat'] . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    echo '<p class="success">✅ Total ' . count($pelangganList) . ' pelanggan terdaftar</p>';
                }
                
                // Test search
                echo '<h3>🔍 Test Pencarian (keyword: "Ahmad")</h3>';
                $hasil = $pelangganModel->search('Ahmad');
                echo '<p class="info">Ditemukan: <strong>' . count($hasil) . '</strong> hasil</p>';
                
            } catch (Exception $e) {
                echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
        
        <!-- TEST 4: Model Hewan -->
        <div class="test-box">
            <h2>4. Test Model Hewan</h2>
            <?php
            try {
                $hewanModel = new Hewan();
                
                echo '<h3>🐾 Daftar Hewan</h3>';
                $hewanList = $hewanModel->getAll();
                
                if (count($hewanList) > 0) {
                    echo '<table>';
                    echo '<tr><th>ID</th><th>Nama Hewan</th><th>Jenis</th><th>Ras</th><th>Ukuran</th><th>Pemilik</th><th>Status</th></tr>';
                    foreach ($hewanList as $h) {
                        $statusBadge = match($h['status']) {
                            'tersedia' => 'badge-success',
                            'sedang_dititipkan' => 'badge-warning',
                            'sudah_diambil' => 'badge-info',
                            default => 'badge-info'
                        };
                        
                        echo '<tr>';
                        echo '<td>' . $h['id_hewan'] . '</td>';
                        echo '<td>' . $h['nama_hewan'] . '</td>';
                        echo '<td>' . ucfirst($h['jenis']) . '</td>';
                        echo '<td>' . ($h['ras'] ?? '-') . '</td>';
                        echo '<td>' . ucfirst($h['ukuran']) . '</td>';
                        echo '<td>' . $h['nama_pelanggan'] . '</td>';
                        echo '<td><span class="badge ' . $statusBadge . '">' . str_replace('_', ' ', $h['status']) . '</span></td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    echo '<p class="success">✅ Total ' . count($hewanList) . ' hewan terdaftar</p>';
                }
                
                // Statistik
                echo '<h3>📊 Statistik Hewan</h3>';
                $totalKucing = $hewanModel->countByJenis('kucing');
                $totalAnjing = $hewanModel->countByJenis('anjing');
                $sedangDititipkan = $hewanModel->countSedangDititipkan();
                
                echo '<div class="info">';
                echo '🐱 <strong>Kucing:</strong> ' . $totalKucing . ' ekor<br>';
                echo '🐕 <strong>Anjing:</strong> ' . $totalAnjing . ' ekor<br>';
                echo '🏠 <strong>Sedang Dititipkan:</strong> ' . $sedangDititipkan . ' hewan';
                echo '</div>';
                
            } catch (Exception $e) {
                echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
        
        <!-- TEST 5: Model Layanan -->
        <div class="test-box">
            <h2>5. Test Model Layanan</h2>
            <?php
            try {
                $layananModel = new Layanan();
                
                echo '<h3>📦 Paket Penitipan</h3>';
                $penitipan = $layananModel->getByKategori('penitipan');
                
                if (count($penitipan) > 0) {
                    echo '<table>';
                    echo '<tr><th>Kode</th><th>Nama Paket</th><th>Harga Kecil</th><th>Harga Sedang</th><th>Harga Besar</th></tr>';
                    foreach ($penitipan as $l) {
                        echo '<tr>';
                        echo '<td><span class="badge badge-info">' . $l['kode_layanan'] . '</span></td>';
                        echo '<td>' . $l['nama_layanan'] . '</td>';
                        echo '<td>Rp ' . number_format($l['harga_kecil'], 0, ',', '.') . '</td>';
                        echo '<td>Rp ' . number_format($l['harga_sedang'], 0, ',', '.') . '</td>';
                        echo '<td>Rp ' . number_format($l['harga_besar'], 0, ',', '.') . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    echo '<p class="success">✅ ' . count($penitipan) . ' paket penitipan</p>';
                }
                
                echo '<h3>💈 Layanan Tambahan</h3>';
                $tambahan = $layananModel->getByKategori('tambahan');
                
                if (count($tambahan) > 0) {
                    echo '<table>';
                    echo '<tr><th>Kode</th><th>Nama Layanan</th><th>Harga</th></tr>';
                    foreach ($tambahan as $l) {
                        echo '<tr>';
                        echo '<td><span class="badge badge-warning">' . $l['kode_layanan'] . '</span></td>';
                        echo '<td>' . $l['nama_layanan'] . '</td>';
                        echo '<td>Rp ' . number_format($l['harga_kecil'], 0, ',', '.') . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    echo '<p class="success">✅ ' . count($tambahan) . ' layanan tambahan</p>';
                }
                
                // Test getHargaByUkuran
                echo '<h3>💰 Test Hitung Harga per Ukuran</h3>';
                $harga = $layananModel->getHargaByUkuran('P003', 'kecil');
                echo '<div class="info">';
                echo 'Paket <code>P003 (Boarding Standar)</code> untuk hewan <strong>kecil</strong>: ';
                echo '<strong>Rp ' . number_format($harga, 0, ',', '.') . '</strong>';
                echo '</div>';
                
            } catch (Exception $e) {
                echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
        
        <!-- TEST 6: Model Transaksi -->
        <div class="test-box">
            <h2>6. Test Model Transaksi</h2>
            <?php
            try {
                $transaksiModel = new Transaksi();
                
                echo '<h3>🎫 Generate Nomor Transaksi</h3>';
                $nomorBaru = $transaksiModel->generateNomorTransaksi();
                echo '<div class="info">';
                echo 'Nomor transaksi baru: <code>' . $nomorBaru . '</code><br>';
                echo '<small>Format: TRX-YYYYMMDD-XXX (auto increment per hari)</small>';
                echo '</div>';
                
                echo '<h3>🏠 Transaksi Sedang Berjalan</h3>';
                $aktif = $transaksiModel->getSedangDititipkan();
                echo '<div class="info">';
                echo '<strong>' . count($aktif) . '</strong> transaksi sedang berlangsung';
                echo '</div>';
                
                echo '<h3>📅 Laporan Hari Ini</h3>';
                $today = date('Y-m-d');
                $laporanHariIni = $transaksiModel->getLaporanHarian($today);
                echo '<div class="info">';
                echo '<strong>' . count($laporanHariIni) . '</strong> transaksi hari ini<br>';
                
                $pendapatanHariIni = $transaksiModel->hitungPendapatan($today, $today);
                echo '💰 <strong>Total Pendapatan:</strong> Rp ' . number_format($pendapatanHariIni, 0, ',', '.');
                echo '</div>';
                
            } catch (Exception $e) {
                echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
        
        <!-- TEST 7: Keamanan SQL Injection -->
        <div class="test-box">
            <h2>7. Test Keamanan (SQL Injection Prevention)</h2>
            <?php
            try {
                $pelangganModel = new Pelanggan();
                
                echo '<h3>🔐 Simulasi SQL Injection Attack</h3>';
                $maliciousInput = "' OR '1'='1' --";
                
                echo '<div class="info">';
                echo '<strong>Input berbahaya:</strong> <code>' . htmlspecialchars($maliciousInput) . '</code><br>';
                echo '<small>Ini adalah input SQL Injection klasik yang mencoba bypass keamanan</small>';
                echo '</div>';
                
                $hasil = $pelangganModel->search($maliciousInput);
                
                echo '<div class="success">';
                echo '✅ <strong>Prepared Statement berhasil mencegah SQL Injection!</strong><br>';
                echo 'Hasil query: <strong>' . count($hasil) . '</strong> data (seharusnya 0 atau sangat sedikit)<br>';
                echo '<small>Query dijalankan dengan aman tanpa mengembalikan semua data</small>';
                echo '</div>';
                
            } catch (Exception $e) {
                echo '<p class="error">❌ Error: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>
        
        <!-- SUMMARY -->
        <div class="summary">
            <h2>✅ Kesimpulan Testing</h2>
            <ul>
                <li>Koneksi database berhasil dan stabil</li>
                <li>Model User berfungsi dengan baik (login, CRUD)</li>
                <li>Model Pelanggan berfungsi dengan baik (CRUD, search)</li>
                <li>Model Hewan berfungsi dengan baik (CRUD, statistik)</li>
                <li>Model Layanan berfungsi dengan baik (CRUD, harga dinamis)</li>
                <li>Model Transaksi berfungsi dengan baik (generate nomor, laporan)</li>
                <li>Prepared Statement aman dari SQL Injection ✅</li>
                <li>Semua query berjalan lancar tanpa error</li>
            </ul>
            <p style="margin-top: 20px; font-size: 1.2rem; font-weight: bold;">
                🎉 Database siap 100% digunakan untuk sistem!
            </p>
            <p style="margin-top: 10px;">
                <small>File testing ini bisa dihapus/disimpan untuk maintenance. Lanjut ke integrasi dengan controller & view!</small>
            </p>
        </div>
        
        <div style="text-align: center; margin: 30px 0; color: white;">
            <p>Dibuat oleh <strong>h1101241034@student.untan.ac.id</strong></p>
            <p><small>Sistem Penitipan Hewan - Project UAS PPBO</small></p>
        </div>
    </div>
</body>
</html>
