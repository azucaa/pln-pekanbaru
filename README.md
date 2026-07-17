# PLN Pekanbaru - Sistem Informasi Pemadaman Listrik

Website informasi pemadaman listrik untuk wilayah Kota Pekanbaru yang dibangun dengan PHP dan MySQL.

## Fitur Utama

### Halaman Publik
- **Peta Interaktif** - Menampilkan lokasi pemadaman listrik dengan peta Leaflet.js
- **Legenda Status** - 4 kategori status dengan warna berbeda:
  - 🔴 **Merah** - Darurat
  - 🟡 **Kuning** - Gangguan
  - 🔵 **Biru** - Terencana
  - 🩵 **Cyan** - Terdampak
- **Statistik Real-time** - Jumlah pemadaman berdasarkan status
- **Daftar Pemadaman** - Tabel pemadaman terbaru dengan fitur pencarian
- **Notifikasi** - Pengumuman dan informasi terkini

### Panel Admin
- **Dashboard** - Statistik dan grafik pemadaman
- **Kelola Pemadaman** - CRUD data pemadaman dengan peta picker
- **Kelola Area** - Manajemen wilayah/kecamatan
- **Data Pelanggan** - Daftar pelanggan terdampak
- **Notifikasi** - Kirim dan kelola pengumuman
- **Pengguna** - Manajemen admin/operator (khusus admin)
- **Pengaturan** - Konfigurasi website

## Teknologi

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Peta**: Leaflet.js + OpenStreetMap
- **Chart**: Chart.js
- **Icons**: Font Awesome 6

## Instalasi

### 1. Clone/Download Repository
```bash
cd /var/www/html
git clone [repository-url] pln-pekanbaru
```

### 2. Buat Database
```bash
mysql -u root -p
```
```sql
CREATE DATABASE pln_pekanbaru CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Import Database
```bash
mysql -u root -p pln_pekanbaru < database.sql
```

### 4. Konfigurasi Database
Edit file `includes/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'password_anda');
define('DB_NAME', 'pln_pekanbaru');
```

### 5. Akses Website
- **Website**: http://localhost/pln-pekanbaru/
- **Admin**: http://localhost/pln-pekanbaru/admin/

### Default Login
- **Username**: admin
- **Password**: admin123

Atau

- **Username**: operator
- **Password**: admin123

## Struktur Folder

```
pln-pekanbaru/
├── admin/              # Panel admin
│   ├── login.php
│   ├── dashboard.php
│   ├── pemadaman.php
│   ├── pemadaman-tambah.php
│   ├── pemadaman-edit.php
│   ├── area.php
│   ├── pelanggan.php
│   ├── notifikasi.php
│   ├── pengguna.php
│   ├── pengaturan.php
│   └── logout.php
├── api/                # API endpoints
│   └── pemadaman.php
├── assets/             # Aset statis
│   ├── css/
│   │   ├── style.css
│   │   └── admin.css
│   ├── js/
│   └── images/
├── includes/           # File konfigurasi
│   ├── config.php
│   ├── database.php
│   └── auth.php
├── database.sql        # Schema database
├── index.php          # Halaman utama
└── README.md          # Dokumentasi
```

## API Endpoints

### GET /api/pemadaman.php

#### Parameters

| Parameter | Type | Description |
|-----------|------|-------------|
| action | string | `list`, `detail`, `by-status`, `by-area`, `stats` |
| id | int | ID pemadaman (untuk action=detail) |
| status | string | Status pemadaman (untuk action=by-status) |
| area_id | int | ID area (untuk action=by-area) |

#### Contoh Response

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "kode_pemadaman": "PAD-2024-001",
      "judul": "Pemadaman Terencana",
      "status": "terencana",
      "lat": 0.5071,
      "lng": 101.4478,
      "pelanggan_terdampak": 150
    }
  ]
}
```

## Konfigurasi

### Pengaturan Peta
Edit di `includes/config.php`:
```php
define('MAP_CENTER_LAT', 0.5071);    // Latitude Pekanbaru
define('MAP_CENTER_LNG', 101.4478);  // Longitude Pekanbaru
define('MAP_DEFAULT_ZOOM', 12);      // Zoom level default
```

### Warna Status
```php
define('COLOR_DARURAT', '#ef4444');   // Merah
define('COLOR_GANGGUAN', '#eab308');  // Kuning
define('COLOR_TERENCANA', '#3b82f6'); // Biru
define('COLOR_TERDAMPAK', '#06b6d4'); // Cyan
```

## Keamanan

- Password di-hash dengan `password_hash()`
- Session management dengan timeout
- SQL injection protection dengan PDO prepared statements
- XSS protection dengan `htmlspecialchars()`
- CSRF protection (dapat ditambahkan)

## Pengembangan

### Menambah Fitur Baru
1. Buat file di folder `admin/`
2. Tambahkan link di sidebar
3. Update database jika diperlukan

### Custom Marker Peta
Edit di file `index.php`:
```javascript
function createCustomIcon(status) {
    return L.divIcon({
        className: 'custom-marker',
        html: `<div style="background-color: ${statusColors[status]}; ...">...</div>`,
        iconSize: [36, 36],
        iconAnchor: [18, 18]
    });
}
```

## Troubleshooting

### Error Koneksi Database
- Periksa konfigurasi di `includes/config.php`
- Pastikan MySQL running
- Cek user dan password database

### Peta Tidak Muncul
- Periksa koneksi internet (Leaflet.js CDN)
- Cek console browser untuk error JavaScript
- Pastikan koordinat valid

### Login Gagal
- Default password: `admin123`
- Cek tabel `admin` di database
- Pastikan session PHP berfungsi

## Lisensi

© 2024 PT PLN (Persero) Area Pekanbaru. All Rights Reserved.

## Kontak

- **Call Center**: 123
- **Email**: pln.pekanbaru@pln.co.id
- **Alamat**: Jl. Jenderal Sudirman No. 123, Pekanbaru, Riau
