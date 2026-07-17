<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

auth()->requireAdmin();

$db = Database::getInstance();
$user = auth()->getCurrentUser();

$error = '';
$success = '';

// Update settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $settings = [
            'site_title',
            'site_description',
            'contact_phone',
            'contact_email',
            'office_address',
            'map_default_lat',
            'map_default_lng',
            'map_default_zoom',
            'maintenance_mode',
            'notification_enabled'
        ];
        
        foreach ($settings as $key) {
            $db->update(
                'pengaturan',
                ['nilai' => $_POST[$key] ?? ''],
                'kunci = :kunci',
                ['kunci' => $key]
            );
        }
        
        $success = 'Pengaturan berhasil disimpan';
    } catch (Exception $e) {
        $error = 'Gagal menyimpan pengaturan: ' . $e->getMessage();
    }
}

// Get settings
$settings = [];
$rows = $db->fetchAll("SELECT * FROM pengaturan");
foreach ($rows as $row) {
    $settings[$row['kunci']] = $row['nilai'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - <?php echo SITE_NAME; ?></title>
    
    <link rel="icon" type="image/x-icon" href="../assets/images/pln-logo.png">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            <a href="pemadaman.php">
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
            <a href="pengguna.php">
                <i class="fas fa-user-cog"></i>
                <span>Kelola Pengguna</span>
            </a>
            <a href="pengaturan.php" class="active">
                <i class="fas fa-cog"></i>
                <span>Pengaturan</span>
            </a>
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
                <span>Pengaturan</span>
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
            
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-section">
                    <div class="form-header">
                        <h2><i class="fas fa-cog"></i> Pengaturan Website</h2>
                        <p>Konfigurasi umum website PLN Pekanbaru</p>
                    </div>
                    
                    <div class="form-body">
                        <h3 style="margin-bottom: 20px; color: var(--pln-dark);">Informasi Umum</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Judul Website</label>
                                <input type="text" name="site_title" class="form-control" value="<?php echo htmlspecialchars($settings['site_title']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Deskripsi Website</label>
                                <input type="text" name="site_description" class="form-control" value="<?php echo htmlspecialchars($settings['site_description']); ?>">
                            </div>
                        </div>
                        
                        <h3 style="margin: 30px 0 20px; color: var(--pln-dark);">Kontak</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nomor Telepon</label>
                                <input type="text" name="contact_phone" class="form-control" value="<?php echo htmlspecialchars($settings['contact_phone']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($settings['contact_email']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Alamat Kantor</label>
                            <textarea name="office_address" class="form-control" rows="2"><?php echo htmlspecialchars($settings['office_address']); ?></textarea>
                        </div>
                        
                        <h3 style="margin: 30px 0 20px; color: var(--pln-dark);">Peta Default</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Latitude</label>
                                <input type="text" name="map_default_lat" class="form-control" value="<?php echo htmlspecialchars($settings['map_default_lat']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Longitude</label>
                                <input type="text" name="map_default_lng" class="form-control" value="<?php echo htmlspecialchars($settings['map_default_lng']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Zoom Level</label>
                                <input type="number" name="map_default_zoom" class="form-control" value="<?php echo htmlspecialchars($settings['map_default_zoom']); ?>">
                            </div>
                        </div>
                        
                        <h3 style="margin: 30px 0 20px; color: var(--pln-dark);">Sistem</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Mode Maintenance</label>
                                <select name="maintenance_mode" class="form-control">
                                    <option value="0" <?php echo $settings['maintenance_mode'] === '0' ? 'selected' : ''; ?>>Nonaktif</option>
                                    <option value="1" <?php echo $settings['maintenance_mode'] === '1' ? 'selected' : ''; ?>>Aktif</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Notifikasi Aktif</label>
                                <select name="notification_enabled" class="form-control">
                                    <option value="1" <?php echo $settings['notification_enabled'] === '1' ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="0" <?php echo $settings['notification_enabled'] === '0' ? 'selected' : ''; ?>>Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
    
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }
    </script>
</body>
</html>
