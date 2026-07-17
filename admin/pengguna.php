<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

auth()->requireAdmin();

$db = Database::getInstance();
$user = auth()->getCurrentUser();

$error = '';
$success = '';

// Add user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    try {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $db->insert('admin', [
            'username' => $_POST['username'],
            'password' => $password,
            'nama_lengkap' => $_POST['nama_lengkap'],
            'email' => $_POST['email'],
            'no_hp' => $_POST['no_hp'],
            'level' => $_POST['level'],
            'status' => 'aktif'
        ]);
        $success = 'Pengguna berhasil ditambahkan';
    } catch (Exception $e) {
        $error = 'Gagal menambahkan pengguna: ' . $e->getMessage();
    }
}

// Delete user
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id !== $user['id']) {
        try {
            $db->delete('admin', 'id = :id', ['id' => $id]);
            $success = 'Pengguna berhasil dihapus';
        } catch (Exception $e) {
            $error = 'Gagal menghapus pengguna: ' . $e->getMessage();
        }
    } else {
        $error = 'Tidak dapat menghapus akun sendiri';
    }
}

// Get all users
$users = $db->fetchAll("SELECT * FROM admin ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - <?php echo SITE_NAME; ?></title>
    
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
            <a href="pengguna.php" class="active">
                <i class="fas fa-user-cog"></i>
                <span>Kelola Pengguna</span>
            </a>
            <a href="pengaturan.php">
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
                <span>Kelola Pengguna</span>
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
                <!-- Form Tambah Pengguna -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-plus-circle"></i> Tambah Pengguna Baru</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="add">
                            
                            <div class="form-group">
                                <label>Username <span class="required">*</span></label>
                                <input type="text" name="username" class="form-control" required placeholder="Username">
                            </div>
                            
                            <div class="form-group">
                                <label>Password <span class="required">*</span></label>
                                <input type="password" name="password" class="form-control" required placeholder="Password">
                            </div>
                            
                            <div class="form-group">
                                <label>Nama Lengkap <span class="required">*</span></label>
                                <input type="text" name="nama_lengkap" class="form-control" required placeholder="Nama lengkap">
                            </div>
                            
                            <div class="form-group">
                                <label>Email <span class="required">*</span></label>
                                <input type="email" name="email" class="form-control" required placeholder="email@example.com">
                            </div>
                            
                            <div class="form-group">
                                <label>No. HP</label>
                                <input type="text" name="no_hp" class="form-control" placeholder="081234567890">
                            </div>
                            
                            <div class="form-group">
                                <label>Level <span class="required">*</span></label>
                                <select name="level" class="form-control" required>
                                    <option value="operator">Operator</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save"></i> Simpan Pengguna
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Daftar Pengguna -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> Daftar Pengguna</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Nama</th>
                                        <th>Email</th>
                                        <th>Level</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($u['username']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($u['nama_lengkap']); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $u['level']; ?>">
                                                <?php echo ucfirst($u['level']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($u['status'] === 'aktif'): ?>
                                            <span class="badge badge-success">Aktif</span>
                                            <?php else: ?>
                                            <span class="badge badge-secondary">Nonaktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($u['id'] !== $user['id']): ?>
                                            <a href="?delete=<?php echo $u['id']; ?>" class="btn btn-sm btn-secondary" onclick="return confirm('Yakin ingin menghapus pengguna ini?')">
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
