-- Database: sewa_alat_masak
CREATE DATABASE IF NOT EXISTS sewa_alat_masak CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sewa_alat_masak;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','petugas','peminjam') DEFAULT 'peminjam',
    telepon VARCHAR(20),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE alat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_id INT,
    nama VARCHAR(150) NOT NULL,
    kode VARCHAR(50) UNIQUE,
    deskripsi TEXT,
    stok INT DEFAULT 1,
    harga_sewa DECIMAL(10,2) DEFAULT 0,
    kondisi ENUM('baik','rusak_ringan','rusak_berat') DEFAULT 'baik',
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE SET NULL
);

CREATE TABLE peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_pinjam VARCHAR(50) UNIQUE,
    user_id INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE NOT NULL,
    status ENUM('menunggu','disetujui','ditolak','dipinjam','selesai') DEFAULT 'menunggu',
    catatan TEXT,
    disetujui_oleh INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (disetujui_oleh) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE detail_peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peminjaman_id INT NOT NULL,
    alat_id INT NOT NULL,
    jumlah INT DEFAULT 1,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE CASCADE,
    FOREIGN KEY (alat_id) REFERENCES alat(id)
);

CREATE TABLE pengembalian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peminjaman_id INT NOT NULL,
    tanggal_kembali DATE NOT NULL,
    kondisi_alat ENUM('baik','rusak_ringan','rusak_berat') DEFAULT 'baik',
    denda DECIMAL(10,2) DEFAULT 0,
    catatan TEXT,
    dicatat_oleh INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id),
    FOREIGN KEY (dicatat_oleh) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE log_aktifitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    aksi VARCHAR(255),
    detail TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Default data
INSERT INTO users (nama, email, password, role) VALUES
('Administrator', 'admin@sewaalat.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Petugas Satu', 'petugas@sewaalat.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'petugas'),
('User Demo', 'user@sewaalat.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'peminjam');

INSERT INTO kategori (nama, deskripsi) VALUES
('Peralatan Memasak', 'Wajan, panci, dan sejenisnya'),
('Peralatan Memanggang', 'Oven, loyang, dan sejenisnya'),
('Peralatan Minum', 'Teko, blender, dan sejenisnya'),
('Peralatan Makan', 'Piring, sendok set besar, dan sejenisnya');

INSERT INTO alat (kategori_id, nama, kode, deskripsi, stok, harga_sewa, kondisi) VALUES
(1, 'Wajan Besar 40cm', 'ALT-001', 'Wajan anti lengket ukuran besar', 3, 15000, 'baik'),
(1, 'Panci Presto 8L', 'ALT-002', 'Panci presto kapasitas 8 liter', 2, 25000, 'baik'),
(1, 'Wok Stainless 35cm', 'ALT-003', 'Wok stainless steel premium', 2, 20000, 'baik'),
(2, 'Oven Gas Besar', 'ALT-004', 'Oven gas kapasitas 60 liter', 1, 75000, 'baik'),
(2, 'Loyang Brownies Set', 'ALT-005', 'Set loyang brownies 6 pcs', 5, 10000, 'baik'),
(3, 'Blender Industrial', 'ALT-006', 'Blender kapasitas 2 liter', 2, 30000, 'baik'),
(3, 'Teko Listrik 5L', 'ALT-007', 'Teko listrik kapasitas besar', 3, 15000, 'baik'),
(4, 'Set Piring 50pcs', 'ALT-008', 'Set piring makan 50 buah', 4, 50000, 'baik');
-- Password for all accounts: password