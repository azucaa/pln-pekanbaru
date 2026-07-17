<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

auth()->requireLogin();

$db = Database::getInstance();
$user = auth()->getCurrentUser();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    redirect(ADMIN_URL . '/pemadaman.php');
}

// Get data
$data = $db->fetch("SELECT * FROM pemadaman WHERE id = :id", ['id' => $id]);
if (!$data) {
    redirect(ADMIN_URL . '/pemadaman.php');
}

$error = '';

// Get areas
$areas = $db->fetchAll("SELECT * FROM area ORDER BY nama_area");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $updateData = [
            'judul' => $_POST['judul'],
            'deskripsi' => $_POST['deskripsi'],
            'status' => $_POST['status'],
            'area_id' => $_POST['area_id'] ?: null,
            'tanggal_mulai' => $_POST['tanggal_mulai'],
            'tanggal_selesai' => $_POST['tanggal_selesai'] ?: null,
            'estimasi_durasi' => $_POST['estimasi_durasi'] ?: null,
            'lat' => $_POST['lat'],
            'lng' => $_POST['lng'],
            'radius' => $_POST['radius'] ?: 100,
            'alamat' => $_POST['alamat'],
            'pelanggan_terdampak' => $_POST['pelanggan_terdampak'] ?: 0,
            'petugas' => $_POST['petugas'],
            'no_tiket' => $_POST['no_tiket'],
            'status_pekerjaan' => $_POST['status_pekerjaan']
        ];
        
        $db->update('pemadaman', $updateData, 'id = :id', ['id' => $id]);
        
        // Add to history if status changed
        if ($data['status_pekerjaan'] !== $_POST['status_pekerjaan']) {
            $db->insert('riwayat_pemadaman', [
                'pemadaman_id' => $id,
                'status_lama' => $data['status_pekerjaan'],
                'status_baru' => $_POST['status_pekerjaan'],
                'keterangan' => 'Status diperbarui',
                'created_by' => $user['id']
            ]);
        }
        
        redirect(ADMIN_URL . '/pemadaman.php?msg=updated');
    } catch (Exception $e) {
        $error = 'Gagal memperbarui data: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pemadaman - <?php echo SITE_NAME; ?></title>
    
    <link rel="icon" type="image/x-icon" href="../assets/images/pln-logo.png">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>
<body class="admin-page">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="../assets/images/pln-logo.png" alt="PLN Logo" onerror="this.src='https://via.placeholder.com/40x40/e31e24/ffffff?text=PLN'">
            <div>
                <h3>PLN Pekanbaru</h3>
                <span>Admin Panel</span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="pemadaman.php" class="active">
                <i class="fas fa-bolt"></i>
                <span>Data Pemadaman</span>
            </a>
            <a href="area.php">
                <i class="fas fa-map-marker-alt"></i>
                <span>Area/Wilayah</span>
            </a>
            <a href="pelanggan.php">
                <i class="fas fa-users"></i>
                <span>Pelanggan</span>
            </a>
            <a href="notifikasi.php">
                <i class="fas fa-bell"></i>
                <span>Notifikasi</span>
            </a>
            <?php if ($user['level'] === 'admin'): ?>
            <a href="pengguna.php">
                <i class="fas fa-user-cog"></i>
                <span>Kelola Pengguna</span>
            </a>
            <a href="pengaturan.php">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
            </a>
            <?php endif; ?>
        </nav>
        
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="admin-main">
        <header class="admin-header">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="breadcrumb">
                <span><a href="pemadaman.php">Data Pemadaman</a> / Edit</span>
            </div>
            
            <div class="header-actions">
                <a href="../index.php" target="_blank" class="btn btn-secondary btn-sm">
                    <i class="fas fa-external-link-alt"></i> Lihat Website
                </a>
                <div class="user-menu">
                    <img src="https://via.placeholder.com/35x35/e31e24/ffffff?text=<?php echo substr($user['nama'], 0, 1); ?>" alt="User">
                    <span><?php echo htmlspecialchars($user['nama']); ?></span>
                </div>
            </div>
        </header>
        
        <div class="admin-content">
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-section">
                    <div class="form-header">
                        <h2><i class="fas fa-edit"></i> Edit Pemadaman</h2>
                        <p>Kode: <?php echo htmlspecialchars($data['kode_pemadaman']); ?></p>
                    </div>
                    
                    <div class="form-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Judul Pemadaman <span class="required">*</span></label>
                                <input type="text" name="judul" class="form-control" required value="<?php echo htmlspecialchars($data['judul']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Status <span class="required">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="">Pilih Status</option>
                                    <option value="darurat" <?php echo $data['status'] === 'darurat' ? 'selected' : ''; ?>>Darurat</option>
                                    <option value="gangguan" <?php echo $data['status'] === 'gangguan' ? 'selected' : ''; ?>>Gangguan</option>
                                    <option value="terencana" <?php echo $data['status'] === 'terencana' ? 'selected' : ''; ?>>Terencana</option>
                                    <option value="terdampak" <?php echo $data['status'] === 'terdampak' ? 'selected' : ''; ?>>Terdampak</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" class="form-control" rows="3"><?php echo htmlspecialchars($data['deskripsi']); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Area/Wilayah</label>
                                <select name="area_id" class="form-control">
                                    <option value="">Pilih Area</option>
                                    <?php foreach ($areas as $area): ?>
                                    <option value="<?php echo $area['id']; ?>" <?php echo $data['area_id'] == $area['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($area['nama_area']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Alamat Lokasi</label>
                                <input type="text" name="alamat" class="form-control" value="<?php echo htmlspecialchars($data['alamat']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tanggal & Waktu Mulai <span class="required">*</span></label>
                                <input type="datetime-local" name="tanggal_mulai" class="form-control" required 
                                    value="<?php echo date('Y-m-d\TH:i', strtotime($data['tanggal_mulai'])); ?>">
                            </div>
                            <div class="form-group">
                                <label>Tanggal & Waktu Selesai (Estimasi)</label>
                                <input type="datetime-local" name="tanggal_selesai" class="form-control" 
                                    value="<?php echo $data['tanggal_selesai'] ? date('Y-m-d\TH:i', strtotime($data['tanggal_selesai'])) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Estimasi Durasi (menit)</label>
                                <input type="number" name="estimasi_durasi" class="form-control" value="<?php echo $data['estimasi_durasi']; ?>">
                            </div>
                            <div class="form-group">
                                <label>Jumlah Pelanggan Terdampak</label>
                                <input type="number" name="pelanggan_terdampak" class="form-control" value="<?php echo $data['pelanggan_terdampak']; ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Lokasi di Peta <span class="required">*</span></label>
                            <div id="map" class="map-picker"></div>
                            <input type="hidden" name="lat" id="lat" value="<?php echo $data['lat']; ?>" required>
                            <input type="hidden" name="lng" id="lng" value="<?php echo $data['lng']; ?>" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Radius Dampak (meter)</label>
                                <input type="number" name="radius" class="form-control" value="<?php echo $data['radius']; ?>" min="50" max="5000">
                            </div>
                            <div class="form-group">
                                <label>Status Pekerjaan</label>
                                <select name="status_pekerjaan" class="form-control">
                                    <option value="menunggu" <?php echo $data['status_pekerjaan'] === 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                                    <option value="proses" <?php echo $data['status_pekerjaan'] === 'proses' ? 'selected' : ''; ?>>Proses</option>
                                    <option value="selesai" <?php echo $data['status_pekerjaan'] === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                    <option value="dibatalkan" <?php echo $data['status_pekerjaan'] === 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nama Petugas</label>
                                <input type="text" name="petugas" class="form-control" value="<?php echo htmlspecialchars($data['petugas']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Nomor Tiket</label>
                                <input type="text" name="no_tiket" class="form-control" value="<?php echo htmlspecialchars($data['no_tiket']); ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-footer">
                        <a href="pemadaman.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize map with existing location
        const lat = <?php echo $data['lat']; ?>;
        const lng = <?php echo $data['lng']; ?>;
        const radius = <?php echo $data['radius']; ?>;
        
        const map = L.map('map').setView([lat, lng], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Add existing marker and circle
        let marker = L.marker([lat, lng]).addTo(map);
        let circle = L.circle([lat, lng], {
            color: '#e31e24',
            fillColor: '#e31e24',
            fillOpacity: 0.2,
            radius: radius
        }).addTo(map);
        
        // Click on map to update location
        map.on('click', function(e) {
            const newLat = e.latlng.lat;
            const newLng = e.latlng.lng;
            
            document.getElementById('lat').value = newLat;
            document.getElementById('lng').value = newLng;
            
            // Remove existing marker and circle
            map.removeLayer(marker);
            map.removeLayer(circle);
            
            // Add new marker
            marker = L.marker([newLat, newLng]).addTo(map);
            
            // Add circle
            const newRadius = document.querySelector('input[name="radius"]').value || 100;
            circle = L.circle([newLat, newLng], {
                color: '#e31e24',
                fillColor: '#e31e24',
                fillOpacity: 0.2,
                radius: newRadius
            }).addTo(map);
        });
        
        // Update circle when radius changes
        document.querySelector('input[name="radius"]').addEventListener('change', function() {
            if (circle && marker) {
                const latLng = marker.getLatLng();
                map.removeLayer(circle);
                circle = L.circle([latLng.lat, latLng.lng], {
                    color: '#e31e24',
                    fillColor: '#e31e24',
                    fillOpacity: 0.2,
                    radius: this.value
                }).addTo(map);
            }
        });
        
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }
    </script>
</body>
</html>
