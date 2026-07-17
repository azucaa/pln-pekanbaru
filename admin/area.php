<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

auth()->requireLogin();

$db = Database::getInstance();
$user = auth()->getCurrentUser();

$error = '';
$success = '';

// Add area
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        $db->insert('area', [
            'nama_area' => $_POST['nama_area'],
            'kecamatan' => $_POST['kecamatan'],
            'kelurahan' => $_POST['kelurahan'],
            'kode_pos' => $_POST['kode_pos'],
            'deskripsi' => $_POST['deskripsi']
        ]);
        $success = 'Area berhasil ditambahkan';
    } catch (Exception $e) {
        $error = 'Gagal menambahkan area: ' . $e->getMessage();
    }
}

// Delete area
if (isset($_GET['delete']) && $user['level'] === 'admin') {
    try {
        $db->delete('area', 'id = :id', ['id' => $_GET['delete']]);
        $success = 'Area berhasil dihapus';
    } catch (Exception $e) {
        $error = 'Gagal menghapus area: ' . $e->getMessage();
    }
}

// Get all areas
$areas = $db->fetchAll("SELECT a.*, COUNT(p.id) as total_pemadaman 
    FROM area a 
    LEFT JOIN pemadaman p ON a.id = p.area_id 
    GROUP BY a.id 
    ORDER BY a.nama_area");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Area - <?php echo SITE_NAME; ?></title>
    
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
            <a href="area.php" class="active">
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
                <span>Kelola Area</span>
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
                <!-- Form Tambah Area -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-plus-circle"></i> Tambah Area Baru</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="form-group">
                                <label>Nama Area <span class="required">*</span></label>
                                <input type="text" name="nama_area" class="form-control" required placeholder="Contoh: Sail">
                            </div>
                            
                            <div class="form-group">
                                <label>Kecamatan <span class="required">*</span></label>
                                <input type="text" name="kecamatan" class="form-control" required placeholder="Contoh: Tenayan Raya">
                            </div>
                            
                            <div class="form-group">
                                <label>Kelurahan</label>
                                <input type="text" name="kelurahan" class="form-control" placeholder="Contoh: Sail">
                            </div>
                            
                            <div class="form-group">
                                <label>Kode Pos</label>
                                <input type="text" name="kode_pos" class="form-control" placeholder="Contoh: 28125">
                            </div>
                            
                            <div class="form-group">
                                <label>Deskripsi</label>
                                <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi area..."></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Simpan Area
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Daftar Area -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> Daftar Area</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Nama Area</th>
                                        <th>Kecamatan</th>
                                        <th>Kelurahan</th>
                                        <th>Pemadaman</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($areas as $area): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($area['nama_area']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($area['kecamatan']); ?></td>
                                        <td><?php echo htmlspecialchars($area['kelurahan'] ?? '-'); ?></td>
                                        <td><?php echo $area['total_pemadaman']; ?></td>
                                        <td>
                                            <?php if ($user['level'] === 'admin' && $area['total_pemadaman'] == 0): ?>
                                            <a href="?delete=<?php echo $area['id']; ?>" class="btn btn-sm btn-secondary" onclick="return confirm('Yakin ingin menghapus area ini?')">
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
