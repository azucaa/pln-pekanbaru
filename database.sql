-- Database PLN Pekanbaru
-- Sistem Informasi Pemadaman Listrik

CREATE DATABASE IF NOT EXISTS pln_pekanbaru CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pln_pekanbaru;

-- Tabel Admin
CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    no_hp VARCHAR(20),
    level ENUM('admin', 'operator') DEFAULT 'operator',
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Area (Wilayah)
CREATE TABLE area (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_area VARCHAR(100) NOT NULL,
    kecamatan VARCHAR(100) NOT NULL,
    kelurahan VARCHAR(100),
    kode_pos VARCHAR(10),
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Pemadaman
CREATE TABLE pemadaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_pemadaman VARCHAR(20) UNIQUE NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    status ENUM('darurat', 'gangguan', 'terencana', 'terdampak') NOT NULL,
    area_id INT,
    tanggal_mulai DATETIME NOT NULL,
    tanggal_selesai DATETIME,
    estimasi_durasi INT COMMENT 'dalam menit',
    lat DECIMAL(10, 8) NOT NULL,
    lng DECIMAL(11, 8) NOT NULL,
    radius INT DEFAULT 100 COMMENT 'radius dampak dalam meter',
    alamat TEXT,
    pelanggan_terdampak INT DEFAULT 0,
    petugas VARCHAR(100),
    no_tiket VARCHAR(50),
    status_pekerjaan ENUM('menunggu', 'proses', 'selesai', 'dibatalkan') DEFAULT 'menunggu',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (area_id) REFERENCES area(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES admin(id) ON DELETE SET NULL
);

-- Tabel Pelanggan Terdampak
CREATE TABLE pelanggan_terdampak (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pemadaman_id INT NOT NULL,
    no_pelanggan VARCHAR(20) NOT NULL,
    nama_pelanggan VARCHAR(100),
    alamat TEXT,
    lat DECIMAL(10, 8),
    lng DECIMAL(11, 8),
    status ENUM('terdampak', 'pulih') DEFAULT 'terdampak',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pemadaman_id) REFERENCES pemadaman(id) ON DELETE CASCADE
);

-- Tabel Riwayat Pemadaman (Log)
CREATE TABLE riwayat_pemadaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pemadaman_id INT NOT NULL,
    status_lama VARCHAR(50),
    status_baru VARCHAR(50),
    keterangan TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pemadaman_id) REFERENCES pemadaman(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES admin(id) ON DELETE SET NULL
);

-- Tabel Notifikasi
CREATE TABLE notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    pesan TEXT NOT NULL,
    tipe ENUM('info', 'warning', 'danger', 'success') DEFAULT 'info',
    target ENUM('semua', 'area', 'pelanggan') DEFAULT 'semua',
    area_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME,
    FOREIGN KEY (area_id) REFERENCES area(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES admin(id) ON DELETE SET NULL
);

-- Tabel Pengaturan
CREATE TABLE pengaturan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kunci VARCHAR(50) UNIQUE NOT NULL,
    nilai TEXT,
    deskripsi VARCHAR(200),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel Sessions
CREATE TABLE sessions (
    id VARCHAR(128) PRIMARY KEY,
    data TEXT NOT NULL,
    last_access TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert data default

-- Admin default (password: admin123)
INSERT INTO admin (username, password, nama_lengkap, email, no_hp, level, status) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@pln.co.id', '081234567890', 'admin', 'aktif'),
('operator', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Operator PLN', 'operator@pln.co.id', '081234567891', 'operator', 'aktif');

-- Data Area Pekanbaru
INSERT INTO area (nama_area, kecamatan, kelurahan, deskripsi) VALUES
('Sail', 'Tenayan Raya', 'Sail', 'Wilayah Sail dan sekitarnya'),
('Tuanku Tambusai', 'Tenayan Raya', 'Tuanku Tambusai', 'Wilayah Tuanku Tambusai'),
('Perhentian Marpoyan', 'Marpoyan Damai', 'Perhentian Marpoyan', 'Wilayah Perhentian Marpoyan'),
('Marpoyan Damai', 'Marpoyan Damai', 'Marpoyan Damai', 'Wilayah Marpoyan Damai'),
('Sidomulyo Timur', 'Marpoyan Damai', 'Sidomulyo Timur', 'Wilayah Sidomulyo Timur'),
('Tangkerang Selatan', 'Bukit Raya', 'Tangkerang Selatan', 'Wilayah Tangkerang Selatan'),
('Tangkerang Utara', 'Bukit Raya', 'Tangkerang Utara', 'Wilayah Tangkerang Utara'),
('Simpang Tiga', 'Bukit Raya', 'Simpang Tiga', 'Wilayah Simpang Tiga'),
('Kulim', 'Tenayan Raya', 'Kulim', 'Wilayah Kulim'),
('Rejomulyo', 'Marpoyan Damai', 'Rejomulyo', 'Wilayah Rejomulyo'),
('Maharatu', 'Marpoyan Damai', 'Maharatu', 'Wilayah Maharatu'),
('Pematang Kapau', 'Tenayan Raya', 'Pematang Kapau', 'Wilayah Pematang Kapau'),
('Rumbai', 'Rumbai', 'Rumbai', 'Wilayah Rumbai'),
('Rumbai Pesisir', 'Rumbai Pesisir', 'Rumbai Pesisir', 'Wilayah Rumbai Pesisir'),
('Bukit Raya', 'Bukit Raya', 'Bukit Raya', 'Wilayah Bukit Raya');

-- Pengaturan default
INSERT INTO pengaturan (kunci, nilai, deskripsi) VALUES
('site_title', 'PLN Pekanbaru - Info Pemadaman Listrik', 'Judul Website'),
('site_description', 'Sistem Informasi Pemadaman Listrik PLN Kota Pekanbaru', 'Deskripsi Website'),
('contact_phone', '123', 'Nomor Telepon PLN'),
('contact_email', 'pln.pekanbaru@pln.co.id', 'Email PLN Pekanbaru'),
('office_address', 'Jl. Jenderal Sudirman No. 123, Pekanbaru, Riau', 'Alamat Kantor'),
('map_default_lat', '0.5071', 'Latitude default peta'),
('map_default_lng', '101.4478', 'Longitude default peta'),
('map_default_zoom', '12', 'Zoom default peta'),
('maintenance_mode', '0', 'Mode Maintenance'),
('notification_enabled', '1', 'Notifikasi Aktif');

-- Sample data pemadaman (untuk demo)
INSERT INTO pemadaman (kode_pemadaman, judul, deskripsi, status, area_id, tanggal_mulai, tanggal_selesai, estimasi_durasi, lat, lng, radius, alamat, pelanggan_terdampak, petugas, no_tiket, status_pekerjaan, created_by) VALUES
('PAD-2024-001', 'Pemadaman Terencana Maintenance', 'Pemeliharaan rutin trafo dan jaringan listrik di wilayah Sail', 'terencana', 1, DATE_ADD(NOW(), INTERVAL 2 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 2 DAY), INTERVAL 4 HOUR), 240, 0.5071, 101.4478, 500, 'Jl. Sudirman, Kel. Sail, Kec. Tenayan Raya', 150, 'Tim Maintenance A', 'TKT-2024-001', 'menunggu', 1),
('PAD-2024-002', 'Gangguan Jaringan Tegangan Menengah', 'Gangguan pada jaringan tegangan menengah akibat cuaca buruk', 'gangguan', 3, DATE_SUB(NOW(), INTERVAL 2 HOUR), NULL, 180, 0.5123, 101.4567, 800, 'Jl. Tuanku Tambusai, Kel. Perhentian Marpoyan', 320, 'Tim Emergency B', 'TKT-2024-002', 'proses', 1),
('PAD-2024-003', 'Darurat Kebakaran Gardu Listrik', 'Kebakaran di gardu listrik memerlukan pemadaman darurat', 'darurat', 7, DATE_SUB(NOW(), INTERVAL 30 MINUTE), NULL, 360, 0.4987, 101.4389, 1200, 'Jl. Nangka, Kel. Tangkerang Utara', 580, 'Tim Darurat C', 'TKT-2024-003', 'proses', 1),
('PAD-2024-004', 'Pemadaman Terencana Penggantian Trafo', 'Penggantian trafo distribusi yang sudah tua', 'terencana', 5, DATE_ADD(NOW(), INTERVAL 5 DAY), DATE_ADD(DATE_ADD(NOW(), INTERVAL 5 DAY), INTERVAL 6 HOUR), 360, 0.5234, 101.4654, 400, 'Jl. Melati, Kel. Sidomulyo Timur', 95, 'Tim Maintenance D', 'TKT-2024-004', 'menunggu', 1);

-- Sample data pelanggan terdampak
INSERT INTO pelanggan_terdampak (pemadaman_id, no_pelanggan, nama_pelanggan, alamat, lat, lng, status) VALUES
(1, '5210012345', 'Budi Santoso', 'Jl. Sudirman No. 12', 0.5075, 101.4482, 'terdampak'),
(1, '5210012346', 'Siti Aminah', 'Jl. Sudirman No. 15', 0.5078, 101.4485, 'terdampak'),
(2, '5210013456', 'Ahmad Wijaya', 'Jl. Tambusai No. 45', 0.5125, 101.4570, 'terdampak'),
(2, '5210013457', 'Dewi Kusuma', 'Jl. Tambusai No. 48', 0.5128, 101.4573, 'terdampak'),
(3, '5210045678', 'Rudi Hartono', 'Jl. Nangka No. 23', 0.4990, 101.4392, 'terdampak');

-- Sample notifikasi
INSERT INTO notifikasi (judul, pesan, tipe, target, is_active, created_by, expires_at) VALUES
('Info Pemadaman Terencana', 'Akan ada pemadaman terencana pada 2 hari mendatang di wilayah Sail. Mohon persiapkan diri Anda.', 'info', 'semua', TRUE, 1, DATE_ADD(NOW(), INTERVAL 7 DAY)),
('Peringatan Cuaca Buruk', 'Diperingatkan cuaca buruk yang dapat menyebabkan gangguan listrik. PLN siaga 24 jam.', 'warning', 'semua', TRUE, 1, DATE_ADD(NOW(), INTERVAL 3 DAY));
