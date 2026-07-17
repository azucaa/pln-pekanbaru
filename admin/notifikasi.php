<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

auth()->requireLogin();

$db = Database::getInstance();
$user = auth()->getCurrentUser();

$error = '';
$success = '';

// Add notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        $db->insert('notifikasi', [
            'judul' => $_POST['judul'],
            'pesan' => $_POST['pesan'],
            'tipe' => $_POST['tipe'],
            'target' => $_POST['target'],
            'area_id' => $_POST['area_id'] ?: null,
            'expires_at' => $_POST['expires_at'] ?: null,
            'created_by' => $user['id']
        ]);
        $success = 'Notifikasi berhasil dikirim';
    } catch (Exception $e) {
        $error = 'Gagal mengirim notifikasi: ' . $e->getMessage();
    }
}

// Delete notification
if (isset($_GET['delete']) && $user['level'] === 'admin') {
    try {
        $db->delete('notifikasi', 'id = :id', ['id' => $_GET['delete']]);
        $success = 'Notifikasi berhasil dihapus';
    } catch (Exception $e) {
        $error = 'Gagal menghapus notifikasi: ' . $e->getMessage();
    }
}

// Get all notifications
$notifikasi = $db->fetchAll("SELECT n.*, a.nama_area, adm.nama_lengkap as created_by_name 
    FROM notifikasi n 
    LEFT JOIN area a ON n.area_id = a.id 
    LEFT JOIN admin adm ON n.created_by = adm.id
    ORDER BY n.created_at DESC");

// Get areas
$areas = $db->fetchAll("SELECT * FROM area ORDER BY nama_area");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Notifikasi - <?php echo SITE_NAME; ?></title>
    
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
            <a href="notifikasi.php" class="active">
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
                <span>Kelola Notifikasi</span>
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
            
            <div class="dashboard-grid">
                <!-- Form Tambah Notifikasi -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-plus-circle"></i> Kirim Notifikasi Baru</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="form-group">
                                <label>Judul Notifikasi <span class="required">*</span></label>
                                <input type="text" name="judul" class="form-control" required placeholder="Contoh: Pemadaman Terencana">
                            </div>
                            
                            <div class="form-group">
                                <label>Pesan <span class="required">*</span></label>
                                <textarea name="pesan" class="form-control" rows="4" required placeholder="Isi pesan notifikasi..."></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Tipe <span class="required">*</span></label>
                                    <select name="tipe" class="form-control" required>
                                        <option value="info">Info</option>
                                        <option value="warning">Peringatan</option>
                                        <option value="danger">Bahaya</option>
                                        <option value="success">Sukses</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Target</label>
                                    <select name="target" class="form-control">
                                        <option value="semua">Semua</option>
                                        <option value="area">Area Tertentu</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label>Area (jika target = area)</label>
                                <select name="area_id" class="form-control">
                                    <option value="">Pilih Area</option>
                                    <?php foreach ($areas as $area): ?>
                                    <option value="<?php echo $area['id']; ?>"><?php echo htmlspecialchars($area['nama_area']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Kadaluarsa</label>
                                <input type="datetime-local" name="expires_at" class="form-control">
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane"></i> Kirim Notifikasi
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Daftar Notifikasi -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> Riwayat Notifikasi</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Judul</th>
                                        <th>Tipe</th>
                                        <th>Target</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($notifikasi as $row): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($row['judul']); ?></strong></td>
                                        <td>
                                            <span class="badge badge-<?php echo $row['tipe']; ?>">
                                                <?php echo ucfirst($row['tipe']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo $row['target'] === 'area' ? htmlspecialchars($row['nama_area']) : 'Semua'; ?></td>
                                        <td>
                                            <?php if ($row['is_active']): ?>
                                            <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                            <span class="badge badge-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatTanggal($row['created_at']); ?></td>
                                        <td>
                                            <?php if ($user['level'] === 'admin'): ?>
                                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary" onclick="return confirm('Yakin ingin menghapus notifikasi ini?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('active');
        }
    </script>
</body>
</html>
