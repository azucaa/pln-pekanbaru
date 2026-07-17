<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

auth()->requireLogin();

$db = Database::getInstance();
$user = auth()->getCurrentUser();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter
$filter_status = $_GET['status'] ?? '';
$filter_area = $_GET['area'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where = [];
$params = [];

if ($filter_status) {
    $where[] = "p.status = :status";
    $params['status'] = $filter_status;
}

if ($filter_area) {
    $where[] = "p.area_id = :area";
    $params['area'] = $filter_area;
}

if ($search) {
    $where[] = "(p.judul LIKE :search OR p.kode_pemadaman LIKE :search OR p.alamat LIKE :search)";
    $params['search'] = "%$search%";
}

$whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total
$total = $db->fetch("SELECT COUNT(*) as total FROM pemadaman p $whereClause", $params)['total'];
$totalPages = ceil($total / $limit);

// Get data
$data = $db->fetchAll("SELECT p.*, a.nama_area, adm.nama_lengkap as created_by_name 
    FROM pemadaman p 
    LEFT JOIN area a ON p.area_id = a.id 
    LEFT JOIN admin adm ON p.created_by = adm.id
    $whereClause
    ORDER BY p.created_at DESC 
    LIMIT $limit OFFSET $offset", $params);

// Get areas for filter
$areas = $db->fetchAll("SELECT * FROM area ORDER BY nama_area");

// Delete action
if (isset($_GET['delete']) && $user['level'] === 'admin') {
    $id = (int)$_GET['delete'];
    $db->delete('pemadaman', 'id = :id', ['id' => $id]);
    redirect(ADMIN_URL . '/pemadaman.php?msg=deleted');
}

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pemadaman - <?php echo SITE_NAME; ?></title>
    
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
                <span>Data Pemadaman</span>
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
            <?php if ($msg === 'deleted'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Data pemadaman berhasil dihapus.
            </div>
            <?php elseif ($msg === 'added'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Data pemadaman berhasil ditambahkan.
            </div>
            <?php elseif ($msg === 'updated'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Data pemadaman berhasil diperbarui.
            </div>
            <?php endif; ?>
            
            <div class="page-header">
                <h2><i class="fas fa-bolt"></i> Data Pemadaman Listrik</h2>
                <a href="pemadaman-tambah.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Pemadaman
                </a>
            </div>
            
            <div class="dashboard-card">
                <div class="card-body">
                    <form method="GET" class="search-filter" style="margin-bottom: 20px;">
                        <input type="text" name="search" placeholder="Cari pemadaman..." value="<?php echo htmlspecialchars($search); ?>">
                        <select name="status">
                            <option value="">Semua Status</option>
                            <option value="darurat" <?php echo $filter_status === 'darurat' ? 'selected' : ''; ?>>Darurat</option>
                            <option value="gangguan" <?php echo $filter_status === 'gangguan' ? 'selected' : ''; ?>>Gangguan</option>
                            <option value="terencana" <?php echo $filter_status === 'terencana' ? 'selected' : ''; ?>>Terencana</option>
                            <option value="terdampak" <?php echo $filter_status === 'terdampak' ? 'selected' : ''; ?>>Terdampak</option>
                        </select>
                        <select name="area">
                            <option value="">Semua Area</option>
                            <?php foreach ($areas as $area): ?>
                            <option value="<?php echo $area['id']; ?>" <?php echo $filter_area == $area['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($area['nama_area']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <?php if ($search || $filter_status || $filter_area): ?>
                        <a href="pemadaman.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Reset
                        </a>
                        <?php endif; ?>
                    </form>
                    
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Judul</th>
                                    <th>Area</th>
                                    <th>Status</th>
                                    <th>Mulai</th>
                                    <th>Pelanggan</th>
                                    <th>Pekerjaan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['kode_pemadaman']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['judul']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_area'] ?? '-'); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $row['status']; ?>">
                                            <?php echo getStatusLabel($row['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatTanggal($row['tanggal_mulai']); ?></td>
                                    <td><?php echo number_format($row['pelanggan_terdampak']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $row['status_pekerjaan']; ?>">
                                            <?php echo ucfirst($row['status_pekerjaan']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="pemadaman-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['level'] === 'admin'): ?>
                                        <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary" title="Hapus" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $filter_status; ?>&area=<?php echo $filter_area; ?>&search=<?php echo $search; ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == $page): ?>
                        <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo $filter_status; ?>&area=<?php echo $filter_area; ?>&search=<?php echo $search; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $filter_status; ?>&area=<?php echo $filter_area; ?>&search=<?php echo $search; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
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
