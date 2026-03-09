-- Database: ukk_peminjaman_alat_asep
DROP DATABASE IF EXISTS ukk_peminjaman_alat_asep;
CREATE DATABASE ukk_peminjaman_alat_asep;
USE ukk_peminjaman_alat_asep;

-- ==========================================================
-- 1. TABEL STRUKTUR
-- ==========================================================

-- Table roles
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE
);

-- Table users
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- Format MD5
    nama_lengkap VARCHAR(100) NOT NULL,
    role_id INT,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE SET NULL
);

-- Table kategori
CREATE TABLE IF NOT EXISTS kategori (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE
);

-- Table alat
CREATE TABLE IF NOT EXISTS alat (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_alat VARCHAR(100) NOT NULL,
    kategori_id INT,
    stok INT DEFAULT 0,
    deskripsi TEXT,
    gambar VARCHAR(255),
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE SET NULL
);

-- Table peminjaman
CREATE TABLE IF NOT EXISTS peminjaman (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    alat_id INT,
    tgl_pinjam DATE NOT NULL,
    tgl_kembali_rencana DATE NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'returned') DEFAULT 'pending',
    keterangan TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (alat_id) REFERENCES alat(id) ON DELETE CASCADE
);

-- Table pengembalian
CREATE TABLE IF NOT EXISTS pengembalian (
    id INT PRIMARY KEY AUTO_INCREMENT,
    peminjaman_id INT,
    tgl_kembali_aktual DATE NOT NULL,
    denda DECIMAL(10,2) DEFAULT 0,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE
);

-- Table log_aktivitas
CREATE TABLE IF NOT EXISTS log_aktivitas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    aktivitas VARCHAR(255) NOT NULL,
    waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- ==========================================================
-- 2. LOGIKA BISNIS (FUNCTION, TRIGGER, PROCEDURE)
-- ==========================================================

-- FUNCTION: Hitung Denda
DROP FUNCTION IF EXISTS hitung_denda;
DELIMITER //
CREATE FUNCTION hitung_denda(p_peminjaman_id INT, p_tgl_kembali DATE) 
RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE v_tgl_rencana DATE;
    DECLARE v_selisih INT;
    DECLARE v_denda_per_hari DECIMAL(10,2) DEFAULT 5000.00;
    
    SELECT tgl_kembali_rencana INTO v_tgl_rencana FROM peminjaman WHERE id = p_peminjaman_id;
    
    SET v_selisih = DATEDIFF(p_tgl_kembali, v_tgl_rencana);
    
    IF v_selisih > 0 THEN
        RETURN v_selisih * v_denda_per_hari;
    ELSE
        RETURN 0;
    END IF;
END //
DELIMITER ;

-- TRIGGER: Kelola Stok Alat Otomatis
DROP TRIGGER IF EXISTS tr_peminjaman_approved;
DELIMITER //
CREATE TRIGGER tr_peminjaman_approved
AFTER UPDATE ON peminjaman
FOR EACH ROW
BEGIN
    -- 1. SAAT DISETUJUI: Kurangi stok alat
    -- Kondisi: Status berubah dari apa saja ke 'approved'
    IF NEW.status = 'approved' AND OLD.status <> 'approved' THEN
        UPDATE alat SET stok = stok - 1 WHERE id = NEW.alat_id;
        
    -- 2. SAAT KEMBALI / BATAL / REJECT SETELAH SETUJU: Tambah stok alat kembali
    -- Kondisi: Status berubah dari 'approved' ke status lain ('returned', 'rejected', 'pending')
    ELSEIF OLD.status = 'approved' AND NEW.status <> 'approved' THEN
        UPDATE alat SET stok = stok + 1 WHERE id = OLD.alat_id;
    END IF;
END //
DELIMITER ;

-- STORED PROCEDURE: Ajukan Peminjaman dengan Validasi Stok
DROP PROCEDURE IF EXISTS ajukan_peminjaman;
DELIMITER //
CREATE PROCEDURE ajukan_peminjaman(
    IN p_user_id INT,
    IN p_alat_id INT,
    IN p_tgl_kembali_rencana DATE
)
BEGIN
    DECLARE v_stok INT;
    
    SELECT stok INTO v_stok FROM alat WHERE id = p_alat_id;
    
    IF v_stok > 0 THEN
        INSERT INTO peminjaman (user_id, alat_id, tgl_pinjam, tgl_kembali_rencana, status)
        VALUES (p_user_id, p_alat_id, CURDATE(), p_tgl_kembali_rencana, 'pending');
        
        INSERT INTO log_aktivitas (user_id, aktivitas) 
        VALUES (p_user_id, CONCAT('Mengajukan peminjaman alat ID: ', p_alat_id));
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Stok alat tidak mencukupi atau sedang kosong';
    END IF;
END //
DELIMITER ;

-- ==========================================================
-- 3. DATA AWAL (SEEDING)
-- ==========================================================

-- Roles
INSERT IGNORE INTO roles (id, role_name) VALUES 
(1, 'admin'), 
(2, 'petugas'), 
(3, 'peminjam');

-- Users (Password: admin123, petugas123, siswa123)
INSERT IGNORE INTO users (id, username, password, nama_lengkap, role_id) VALUES 
(1, 'admin', MD5('admin123'), 'Administrator Utama', 1),
(2, 'petugas', MD5('petugas123'), 'Petugas Sarpras', 2),
(3, 'siswa', MD5('siswa123'), 'Budi Siswa', 3);

-- Kategori
INSERT IGNORE INTO kategori (id, nama_kategori) VALUES 
(1, 'Elektronik'), 
(2, 'Olahraga'), 
(3, 'Musik');

-- Alat
INSERT IGNORE INTO alat (id, nama_alat, kategori_id, stok, deskripsi) VALUES 
(1, 'Laptop Dell Latitude', 1, 5, 'Laptop Core i5 RAM 8GB untuk praktik'),
(2, 'Proyektor Epson EB-X400', 1, 3, 'Proyektor LCD 3300 Lumens'),
(3, 'Bola Basket Molten', 2, 10, 'Bola Basket Size 7 Standar Kompetisi');
