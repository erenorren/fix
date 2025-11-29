-- ============================================
-- DATABASE SCHEMA - SISTEM PENITIPAN HEWAN
-- PostgreSQL Version
-- ============================================

-- Hapus database jika sudah ada (opsional)
-- DROP DATABASE IF EXISTS penitipan_hewan;

-- Buat database baru
-- CREATE DATABASE penitipan_hewan;
-- \c penitipan_hewan;

-- ==========================
-- DROP ALL TABLES
-- ==========================
-- DO $$ DECLARE
--     r RECORD;
-- BEGIN
--     FOR r IN (SELECT tablename FROM pg_tables WHERE schemaname = 'public') LOOP
--         EXECUTE 'DROP TABLE IF EXISTS ' || quote_ident(r.tablename) || ' CASCADE';
--     END LOOP;
-- END $$;

-- Drop tables jika sudah ada (untuk development)
DROP TABLE IF EXISTS detail_transaksi CASCADE;
DROP TABLE IF EXISTS transaksi CASCADE;
DROP TABLE IF EXISTS layanan CASCADE;
DROP TABLE IF EXISTS kandang CASCADE;
DROP TABLE IF EXISTS hewan CASCADE;
DROP TABLE IF EXISTS pelanggan CASCADE;
DROP TABLE IF EXISTS "user" CASCADE;

-- Drop types jika sudah ada
DROP TYPE IF EXISTS role_type CASCADE;
DROP TYPE IF EXISTS jenis_hewan_type CASCADE;
DROP TYPE IF EXISTS ukuran_type CASCADE;
DROP TYPE IF EXISTS status_hewan_type CASCADE;
DROP TYPE IF EXISTS tipe_kandang_type CASCADE;
DROP TYPE IF EXISTS status_kandang_type CASCADE;
DROP TYPE IF EXISTS status_transaksi_type CASCADE;
DROP TYPE IF EXISTS status_pembayaran_type CASCADE;
DROP TYPE IF EXISTS metode_pembayaran_type CASCADE;

-- ============================================
-- CREATE CUSTOM TYPES (ENUM)
-- ============================================
CREATE TYPE role_type AS ENUM ('admin', 'kasir');
CREATE TYPE jenis_hewan_type AS ENUM ('Kucing', 'Anjing');
CREATE TYPE ukuran_type AS ENUM ('Kecil', 'Sedang', 'Besar');
CREATE TYPE status_hewan_type AS ENUM ('tersedia', 'sedang_dititipkan', 'sudah_diambil');
CREATE TYPE tipe_kandang_type AS ENUM ('Kecil', 'Sedang', 'Besar');
CREATE TYPE status_kandang_type AS ENUM ('tersedia', 'terisi');
CREATE TYPE status_transaksi_type AS ENUM ('active', 'completed', 'sedang_dititipkan', 'selesai');
CREATE TYPE status_pembayaran_type AS ENUM ('belum_lunas', 'lunas');
CREATE TYPE metode_pembayaran_type AS ENUM ('tunai', 'transfer', 'debit', 'kredit');

-- ============================================
-- TABEL: user
-- Menyimpan data pengguna sistem (kasir/admin)
-- ============================================
CREATE TABLE "user" (
    id_user SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role role_type NOT NULL DEFAULT 'kasir',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Trigger untuk auto-update updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_user_updated_at BEFORE UPDATE ON "user"
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ============================================
-- TABEL: pelanggan
-- Menyimpan data pelanggan/pemilik hewan
-- ============================================
CREATE TABLE pelanggan (
    id_pelanggan SERIAL PRIMARY KEY,
    kode_pelanggan VARCHAR(20) NOT NULL UNIQUE,
    nama_pelanggan VARCHAR(100) NOT NULL,
    no_hp VARCHAR(20),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_pelanggan_kode ON pelanggan(kode_pelanggan);
CREATE INDEX idx_pelanggan_nama ON pelanggan(nama_pelanggan);

CREATE TRIGGER update_pelanggan_updated_at BEFORE UPDATE ON pelanggan
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ============================================
-- TABEL: hewan
-- Menyimpan data hewan peliharaan
-- ============================================
CREATE TABLE hewan (
    id_hewan SERIAL PRIMARY KEY,
    id_pelanggan INTEGER NOT NULL,
    nama_hewan VARCHAR(100) NOT NULL,
    jenis jenis_hewan_type NOT NULL,
    ras VARCHAR(50),
    ukuran ukuran_type,
    warna VARCHAR(50),
    catatan TEXT,
    status status_hewan_type DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan) ON DELETE CASCADE
);

CREATE INDEX idx_hewan_pelanggan ON hewan(id_pelanggan);
CREATE INDEX idx_hewan_jenis ON hewan(jenis);
CREATE INDEX idx_hewan_status ON hewan(status);

CREATE TRIGGER update_hewan_updated_at BEFORE UPDATE ON hewan
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ============================================
-- TABEL: kandang
-- Menyimpan data kandang untuk penitipan
-- ============================================
CREATE TABLE kandang (
    id_kandang SERIAL PRIMARY KEY,
    kode_kandang VARCHAR(20) NOT NULL UNIQUE,
    tipe tipe_kandang_type NOT NULL,
    status status_kandang_type DEFAULT 'tersedia',
    catatan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_kandang_kode ON kandang(kode_kandang);
CREATE INDEX idx_kandang_status ON kandang(status);

CREATE TRIGGER update_kandang_updated_at BEFORE UPDATE ON kandang
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ============================================
-- TABEL: layanan
-- Menyimpan data layanan tambahan
-- ============================================
CREATE TABLE layanan (
    id_layanan SERIAL PRIMARY KEY,
    nama_layanan VARCHAR(100) NOT NULL,
    harga NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_layanan_nama ON layanan(nama_layanan);

CREATE TRIGGER update_layanan_updated_at BEFORE UPDATE ON layanan
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ============================================
-- TABEL: transaksi
-- Menyimpan data transaksi penitipan
-- ============================================
CREATE TABLE transaksi (
    id_transaksi SERIAL PRIMARY KEY,
    kode_transaksi VARCHAR(20) NOT NULL UNIQUE,
    id_pelanggan INTEGER NOT NULL,
    id_hewan INTEGER NOT NULL,
    id_kandang INTEGER,
    id_layanan INTEGER,
    id_user INTEGER,
    biaya_paket NUMERIC(10,2) DEFAULT 0.00,
    tanggal_masuk DATE NOT NULL,
    tanggal_keluar DATE,
    tanggal_keluar_aktual DATE,
    jam_keluar_aktual TIME,
    durasi INTEGER DEFAULT 1,
    durasi_hari INTEGER DEFAULT 1,
    total_biaya NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    diskon NUMERIC(10,2) DEFAULT 0.00,
    metode_pembayaran metode_pembayaran_type,
    status status_transaksi_type DEFAULT 'active',
    status_pembayaran status_pembayaran_type DEFAULT 'belum_lunas',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan) ON DELETE RESTRICT,
    FOREIGN KEY (id_hewan) REFERENCES hewan(id_hewan) ON DELETE RESTRICT,
    FOREIGN KEY (id_kandang) REFERENCES kandang(id_kandang) ON DELETE SET NULL,
    FOREIGN KEY (id_layanan) REFERENCES layanan(id_layanan) ON DELETE SET NULL,
    FOREIGN KEY (id_user) REFERENCES "user"(id_user) ON DELETE SET NULL
);

CREATE INDEX idx_transaksi_kode ON transaksi(kode_transaksi);
CREATE INDEX idx_transaksi_pelanggan ON transaksi(id_pelanggan);
CREATE INDEX idx_transaksi_hewan ON transaksi(id_hewan);
CREATE INDEX idx_transaksi_status ON transaksi(status);
CREATE INDEX idx_transaksi_tanggal ON transaksi(tanggal_masuk);
CREATE INDEX idx_transaksi_status_tanggal ON transaksi(status, tanggal_masuk);

CREATE TRIGGER update_transaksi_updated_at BEFORE UPDATE ON transaksi
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

COMMENT ON COLUMN transaksi.durasi IS 'Durasi dalam hari';

-- ============================================
-- TABEL: detail_transaksi
-- Menyimpan detail layanan tambahan per transaksi
-- ============================================
CREATE TABLE detail_transaksi (
    id_detail SERIAL PRIMARY KEY,
    id_transaksi INTEGER NOT NULL,
    kode_layanan VARCHAR(20),
    nama_layanan VARCHAR(100) NOT NULL,
    harga NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    harga_satuan NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    quantity INTEGER DEFAULT 1,
    jumlah INTEGER DEFAULT 1,
    subtotal NUMERIC(10,2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE CASCADE
);

CREATE INDEX idx_detail_transaksi ON detail_transaksi(id_transaksi);

-- ============================================
-- DATA AWAL (SEED DATA)
-- ============================================

-- Insert user default (password: admin123 dan kasir123)
-- Hash menggunakan bcrypt: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
INSERT INTO "user" (username, password, nama_lengkap, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin'),
('kasir', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kasir Utama', 'kasir');

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
LEFT JOIN "user" u ON t.id_user = u.id_user;

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
-- STORED PROCEDURE / FUNCTION
-- ============================================

-- Function untuk checkout transaksi
CREATE OR REPLACE FUNCTION sp_checkout_transaksi(
    p_id_transaksi INTEGER,
    p_tanggal_keluar DATE,
    p_metode_pembayaran metode_pembayaran_type
)
RETURNS VOID AS $$
DECLARE
    v_id_hewan INTEGER;
    v_id_kandang INTEGER;
BEGIN
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
    
END;
$$ LANGUAGE plpgsql;

-- Function untuk generate kode pelanggan
CREATE OR REPLACE FUNCTION generate_kode_pelanggan()
RETURNS VARCHAR AS $$
DECLARE
    max_number INTEGER;
    next_number INTEGER;
    new_kode VARCHAR;
BEGIN
    SELECT COALESCE(MAX(CAST(SUBSTRING(kode_pelanggan FROM 4) AS INTEGER)), 0) 
    INTO max_number
    FROM pelanggan 
    WHERE kode_pelanggan LIKE 'PLG%';
    
    next_number := max_number + 1;
    new_kode := 'PLG' || LPAD(next_number::TEXT, 3, '0');
    
    RETURN new_kode;
END;
$$ LANGUAGE plpgsql;

-- Function untuk generate kode transaksi
CREATE OR REPLACE FUNCTION generate_kode_transaksi()
RETURNS VARCHAR AS $$
DECLARE
    max_number INTEGER;
    next_number INTEGER;
    new_kode VARCHAR;
BEGIN
    SELECT COALESCE(MAX(CAST(SUBSTRING(kode_transaksi FROM 4) AS INTEGER)), 0) 
    INTO max_number
    FROM transaksi 
    WHERE kode_transaksi LIKE 'TRX%';
    
    next_number := max_number + 1;
    new_kode := 'TRX' || LPAD(next_number::TEXT, 3, '0');
    
    RETURN new_kode;
END;
$$ LANGUAGE plpgsql;

-- ============================================
-- INDEXES TAMBAHAN UNTUK OPTIMASI
-- ============================================

CREATE INDEX idx_hewan_pelanggan_jenis ON hewan(id_pelanggan, jenis);

-- ============================================
-- GRANT PRIVILEGES (Sesuaikan dengan user Anda)
-- ============================================

-- GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA public TO your_user;
-- GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA public TO your_user;
-- GRANT EXECUTE ON ALL FUNCTIONS IN SCHEMA public TO your_user;

-- ============================================
-- SELESAI
-- ============================================

-- Untuk melihat semua tabel yang dibuat:
-- \dt

-- Untuk melihat semua custom types:
-- \dT

-- Untuk melihat struktur tabel:
-- \d nama_tabel