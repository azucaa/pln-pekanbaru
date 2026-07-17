<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Cek login
auth()->requireLogin();

$db = Database::getInstance();
$user = auth()->getCurrentUser();

// Statistik
$stats = [
    'total_pemadaman' => $db->fetch("SELECT COUNT(*) as total FROM pemadaman")['total'],
    'aktif' => $db->fetch("SELECT COUNT(*) as total FROM pemadaman WHERE status_pekerjaan != 'selesai'")['total'],
    'selesai' => $db->fetch("SELECT COUNT(*) as total FROM pemadaman WHERE status_pekerjaan = 'selesai'")['total'],
    'total_pelanggan' => $db->fetch("SELECT SUM(pelanggan_terdampak) as total FROM pemadaman")['total'] ?? 0
];

// Data pemadaman terbaru
$pemadaman_terbaru = $db->fetchAll("SELECT p.*, a.nama_area, adm.nama_lengkap as created_by_name 
    FROM pemadaman p 
    LEFT JOIN area a ON p.area_id = a.id 
    LEFT JOIN admin adm ON p.created_by = adm.id
    ORDER BY p.created_at DESC 
    LIMIT 5");

// Data untuk grafik (7 hari terakhir)
$grafik_data = $db->fetchAll("SELECT 
    DATE(created_at) as tanggal,
    COUNT(*) as jumlah,
    SUM(pelanggan_terdampak) as pelanggan
    FROM pemadaman 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY tanggal");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - <?php echo SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/pln-logo.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <a href="dashboard.php" class="active">
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
        <!-- Header -->
        <header class="admin-header">
            <button class="menu-toggle" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="breadcrumb">
                <span>Dashboard</span>
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
        
        <!-- Content -->
        <div class="admin-content">
            <!-- Welcome -->
            <div class="welcome-banner">
                <div>
                    <h2>Selamat Datang, <?php echo htmlspecialchars($user['nama']); ?>!</h2>
                    <p>Kelola informasi pemadaman listrik dengan mudah dan efisien.</p>
                </div>
                <a href="pemadaman-tambah.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Pemadaman
                </a>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Pemadaman</h3>
                        <p class="stat-number"><?php echo number_format($stats['total_pemadaman']); ?></p>
                    </div>
                </div>
                
                <div class="stat-card orange">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Sedang Berlangsung</h3>
                        <p class="stat-number"><?php echo number_format($stats['aktif']); ?></p>
                    </div>
                </div>
                
                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Selesai</h3>
                        <p class="stat-number"><?php echo number_format($stats['selesai']); ?></p>
                    </div>
                </div>
                
                <div class="stat-card purple">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Pelanggan Terdampak</h3>
                        <p class="stat-number"><?php echo number_format($stats['total_pelanggan']); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Charts & Tables -->
            <div class="dashboard-grid">
                <!-- Chart -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-bar"></i> Statistik 7 Hari Terakhir</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="statistikChart" height="200"></canvas>
                    </div>
                </div>
                
                <!-- Recent Data -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3><i class="fas fa-list"></i> Pemadaman Terbaru</h3>
                        <a href="pemadaman.php" class="btn btn-sm btn-secondary">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Kode</th>
                                        <th>Judul</th>
                                        <th>Status</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pemadaman_terbaru as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['kode_pemadaman']); ?></td>
                                        <td><?php echo htmlspecialchars($row['judul']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $row['status']; ?>">
                                                <?php echo getStatusLabel($row['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatTanggal($row['created_at']); ?></td>
                                        <td>
                                            <a href="pemadaman-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3><i class="fas fa-bolt"></i> Aksi Cepat</h3>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="pemadaman-tambah.php" class="quick-action">
                            <div class="icon" style="background: rgba(227, 30, 36, 0.1); color: var(--pln-red);">
                                <i class="fas fa-plus"></i>
                            </div>
                            <span>Tambah Pemadaman</span>
                        </a>
                        <a href="notifikasi-tambah.php" class="quick-action">
                            <div class="icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                                <i class="fas fa-bell"></i>
                            </div>
                            <span>Kirim Notifikasi</span>
                        </a>
                        <a href="area.php" class="quick-action">
                            <div class="icon" style="background: rgba(34, 197, 94, 0.1); color: #22c55e;">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <span>Kelola Area</span>
                        </a>
                        <a href="laporan.php" class="quick-action">
                            <div class="icon" style="background: rgba(168, 85, 247, 0.1); color: #a855f7;">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <span>Laporan</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        // Toggle sidebar
        function toggleSidebar() {
            document.body.classList.toggle('sidebar-collapsed');
        }
        
        // Chart
        const ctx = document.getElementById('statistikChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_map(function($d) { return date('d M', strtotime($d['tanggal'])); }, $grafik_data)); ?>,
                datasets: [{
                    label: 'Jumlah Pemadaman',
                    data: <?php echo json_encode(array_map(function($d) { return $d['jumlah']; }, $grafik_data)); ?>,
                    backgroundColor: 'rgba(227, 30, 36, 0.8)',
                    borderColor: 'rgba(227, 30, 36, 1)',
                    borderWidth: 1
                }, {
                    label: 'Pelanggan Terdampak',
                    data: <?php echo json_encode(array_map(function($d) { return $d['pelanggan']; }, $grafik_data)); ?>,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
