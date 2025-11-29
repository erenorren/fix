-- ============================================
-- DATABASE SCHEMA - SISTEM PENITIPAN HEWAN
-- ============================================

-- Hapus database jika sudah ada (opsional)
-- DROP DATABASE IF EXISTS penitipan_hewan;

-- Buat database baru
CREATE DATABASE IF NOT EXISTS penitipan_hewan;
USE penitipan_hewan;

-- ============================================
-- TABEL: user
-- Menyimpan data pengguna sistem (kasir/admin)
-- ============================================
CREATE TABLE user (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin', 'kasir') NOT NULL DEFAULT 'kasir',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABEL: pelanggan
-- Menyimpan data pelanggan/pemilik hewan
-- ============================================
CREATE TABLE pelanggan (
    id_pelanggan INT AUTO_INCREMENT PRIMARY KEY,
    kode_pelanggan VARCHAR(20) NOT NULL UNIQUE,
    nama_pelanggan VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_kode (kode_pelanggan),
    INDEX idx_nama (nama_pelanggan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABEL: hewan
-- Menyimpan data hewan peliharaan
-- ============================================
CREATE TABLE hewan (
    id_hewan INT AUTO_INCREMENT PRIMARY KEY,
    id_pelanggan INT NOT NULL,
    nama_hewan VARCHAR(100) NOT NULL,
    jenis ENUM('Kucing', 'Anjing') NOT NULL,
    ras VARCHAR(50),
    ukuran ENUM('Kecil', 'Sedang', 'Besar'),
    warna VARCHAR(50),
    catatan TEXT,
    status ENUM('tersedia', 'sedang_dititipkan', 'sudah_diambil') DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan) ON DELETE CASCADE,
    INDEX idx_pelanggan (id_pelanggan),
    INDEX idx_jenis (jenis),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABEL: kandang
-- Menyimpan data kandang untuk penitipan
-- ============================================
CREATE TABLE kandang (
    id_kandang INT AUTO_INCREMENT PRIMARY KEY,
    kode_kandang VARCHAR(20) NOT NULL UNIQUE,
    tipe ENUM('Kecil', 'Sedang', 'Besar') NOT NULL,
    status ENUM('tersedia', 'terisi') DEFAULT 'tersedia',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_kode (kode_kandang),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABEL: layanan
-- Menyimpan data layanan tambahan
-- ============================================
CREATE TABLE layanan (
    id_layanan INT AUTO_INCREMENT PRIMARY KEY,
    nama_layanan VARCHAR(100) NOT NULL,
    harga DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nama (nama_layanan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABEL: transaksi
-- Menyimpan data transaksi penitipan
-- ============================================
CREATE TABLE transaksi (
    id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
    kode_transaksi VARCHAR(20) NOT NULL UNIQUE,
    id_pelanggan INT NOT NULL,
    id_hewan INT NOT NULL,
    id_kandang INT,
    id_layanan INT,
    id_user INT,
    biaya_paket DECIMAL(10,2) DEFAULT 0.00,
    tanggal_masuk DATE NOT NULL,
    tanggal_keluar DATE,
    tanggal_keluar_aktual DATE,
    jam_keluar_aktual TIME,
    durasi INT DEFAULT 1 COMMENT 'Durasi dalam hari',
    durasi_hari INT DEFAULT 1,
    total_biaya DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    diskon DECIMAL(10,2) DEFAULT 0.00,
    metode_pembayaran ENUM('tunai', 'transfer', 'debit', 'kredit'),
    status ENUM('active', 'completed', 'sedang_dititipkan', 'selesai') DEFAULT 'active',
    status_pembayaran ENUM('belum_lunas', 'lunas') DEFAULT 'belum_lunas',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan) ON DELETE RESTRICT,
    FOREIGN KEY (id_hewan) REFERENCES hewan(id_hewan) ON DELETE RESTRICT,
    FOREIGN KEY (id_kandang) REFERENCES kandang(id_kandang) ON DELETE SET NULL,
    FOREIGN KEY (id_layanan) REFERENCES layanan(id_layanan) ON DELETE SET NULL,
    FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE SET NULL,
    INDEX idx_kode (kode_transaksi),
    INDEX idx_pelanggan (id_pelanggan),
    INDEX idx_hewan (id_hewan),
    INDEX idx_status (status),
    INDEX idx_tanggal (tanggal_masuk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABEL: detail_transaksi
-- Menyimpan detail layanan tambahan per transaksi
-- ============================================
CREATE TABLE detail_transaksi (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    kode_layanan VARCHAR(20),
    nama_layanan VARCHAR(100) NOT NULL,
    harga DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    harga_satuan DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    quantity INT DEFAULT 1,
    jumlah INT DEFAULT 1,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE CASCADE,
    INDEX idx_transaksi (id_transaksi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- DATA AWAL (SEED DATA)
-- ============================================

-- Insert user default (password: admin123 dan kasir123)
INSERT INTO user (username, password, nama_lengkap, role) VALUES
('admin', '$2y$10$eBSNNR/j5e6P9SvRVolm1OaY2LvtKPdW10UmuTQO5JnEBmUPY7Api', 'Administrator', 'admin'),
('kasir', '$2y$10$uL6uisfCESeWPLVpio4SCOEU/6hB.hbqC9do/n1gBriaBGP1US.2S', 'Kasir Utama', 'kasir');

-- Insert kandang contoh
INSERT INTO kandang (kode_kandang, tipe, status) VALUES
('K001', 'Kecil', 'tersedia'),
('K002', 'Kecil', 'tersedia'),
('K003', 'Kecil', 'tersedia'),
('S001', 'Sedang', 'tersedia'),
('S002', 'Sedang', 'tersedia'),
('S003', 'Sedang', 'tersedia'),
('B001', 'Besar', 'tersedia'),
('B002', 'Besar', 'tersedia'),
('B003', 'Besar', 'tersedia');

-- Insert layanan contoh
INSERT INTO layanan (nama_layanan, harga, deskripsi) VALUES
('Grooming Basic', 50000.00, 'Mandi dan potong kuku'),
('Grooming Premium', 100000.00, 'Mandi, potong kuku, dan styling'),
('Vaksinasi', 150000.00, 'Vaksinasi lengkap'),
('Konsultasi Dokter', 75000.00, 'Konsultasi kesehatan hewan'),
('Vitamin', 30000.00, 'Pemberian vitamin hewan');

-- Insert pelanggan contoh
INSERT INTO pelanggan (kode_pelanggan, nama_pelanggan, no_hp, alamat) VALUES
('PLG001', 'Budi Santoso', '081234567890', 'Jl. Merdeka No. 123'),
('PLG002', 'Ani Wijaya', '082345678901', 'Jl. Sudirman No. 456'),
('PLG003', 'Citra Dewi', '083456789012', 'Jl. Gatot Subroto No. 789');

-- Insert hewan contoh
INSERT INTO hewan (id_pelanggan, nama_hewan, jenis, ras, ukuran, warna, status) VALUES
(1, 'Momo', 'Kucing', 'Persia', 'Sedang', 'Putih', 'tersedia'),
(1, 'Brownie', 'Anjing', 'Golden Retriever', 'Besar', 'Coklat', 'tersedia'),
(2, 'Luna', 'Kucing', 'Anggora', 'Kecil', 'Abu-abu', 'tersedia'),
(3, 'Max', 'Anjing', 'Beagle', 'Sedang', 'Coklat Putih', 'tersedia');

-- ============================================
-- VIEW UNTUK LAPORAN
-- ============================================

-- View untuk laporan transaksi lengkap
CREATE OR REPLACE VIEW v_transaksi_lengkap AS
SELECT 
    t.id_transaksi,
    t.kode_transaksi,
    t.tanggal_masuk,
    t.tanggal_keluar_aktual,
    t.durasi_hari,
    t.total_biaya,
    t.diskon,
    t.metode_pembayaran,
    t.status,
    t.status_pembayaran,
    p.nama_pelanggan,
    p.no_hp,
    h.nama_hewan,
    h.jenis as jenis_hewan,
    h.ras,
    k.kode_kandang,
    k.tipe as tipe_kandang,
    u.nama_lengkap as nama_kasir
FROM transaksi t
LEFT JOIN pelanggan p ON t.id_pelanggan = p.id_pelanggan
LEFT JOIN hewan h ON t.id_hewan = h.id_hewan
LEFT JOIN kandang k ON t.id_kandang = k.id_kandang
LEFT JOIN user u ON t.id_user = u.id_user;

-- View untuk statistik kandang
CREATE OR REPLACE VIEW v_statistik_kandang AS
SELECT 
    tipe,
    COUNT(*) as total_kandang,
    SUM(CASE WHEN status = 'tersedia' THEN 1 ELSE 0 END) as tersedia,
    SUM(CASE WHEN status = 'terisi' THEN 1 ELSE 0 END) as terisi
FROM kandang
GROUP BY tipe;

-- ============================================
-- STORED PROCEDURE (Opsional)
-- ============================================

-- Procedure untuk checkout transaksi
DELIMITER $$
CREATE PROCEDURE sp_checkout_transaksi(
    IN p_id_transaksi INT,
    IN p_tanggal_keluar DATE,
    IN p_metode_pembayaran VARCHAR(20)
)
BEGIN
    DECLARE v_id_hewan INT;
    DECLARE v_id_kandang INT;
    
    -- Ambil data hewan dan kandang
    SELECT id_hewan, id_kandang INTO v_id_hewan, v_id_kandang
    FROM transaksi
    WHERE id_transaksi = p_id_transaksi;
    
    -- Update transaksi
    UPDATE transaksi 
    SET 
        tanggal_keluar_aktual = p_tanggal_keluar,
        status = 'selesai',
        status_pembayaran = 'lunas',
        metode_pembayaran = p_metode_pembayaran
    WHERE id_transaksi = p_id_transaksi;
    
    -- Update status hewan
    UPDATE hewan 
    SET status = 'sudah_diambil'
    WHERE id_hewan = v_id_hewan;
    
    -- Update status kandang
    UPDATE kandang 
    SET status = 'tersedia'
    WHERE id_kandang = v_id_kandang;
    
END$$
DELIMITER ;

-- ============================================
-- INDEXES TAMBAHAN UNTUK OPTIMASI
-- ============================================

-- Index untuk pencarian cepat
ALTER TABLE transaksi ADD INDEX idx_status_tanggal (status, tanggal_masuk);
ALTER TABLE hewan ADD INDEX idx_pelanggan_jenis (id_pelanggan, jenis);

-- ============================================
-- SELESAI
-- ============================================