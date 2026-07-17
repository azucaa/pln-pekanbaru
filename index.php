<?php
require_once 'includes/config.php';
require_once 'includes/database.php';

// Ambil data statistik
$db = Database::getInstance();

$stats = [
    'darurat' => $db->fetch("SELECT COUNT(*) as total FROM pemadaman WHERE status = 'darurat' AND status_pekerjaan != 'selesai'")['total'],
    'gangguan' => $db->fetch("SELECT COUNT(*) as total FROM pemadaman WHERE status = 'gangguan' AND status_pekerjaan != 'selesai'")['total'],
    'terencana' => $db->fetch("SELECT COUNT(*) as total FROM pemadaman WHERE status = 'terencana' AND status_pekerjaan != 'selesai'")['total'],
    'terdampak' => $db->fetch("SELECT SUM(pelanggan_terdampak) as total FROM pemadaman WHERE status_pekerjaan != 'selesai'")['total'] ?? 0
];

// Ambil data pemadaman untuk peta
$pemadaman = $db->fetchAll("SELECT p.*, a.nama_area, a.kecamatan 
    FROM pemadaman p 
    LEFT JOIN area a ON p.area_id = a.id 
    WHERE p.status_pekerjaan != 'selesai'
    ORDER BY p.created_at DESC");

// Ambil data notifikasi
$notifikasi = $db->fetchAll("SELECT * FROM notifikasi 
    WHERE is_active = 1 
    AND (expires_at IS NULL OR expires_at > NOW())
    ORDER BY created_at DESC 
    LIMIT 4");

// Ambil data pemadaman terbaru untuk tabel
$pemadaman_terbaru = $db->fetchAll("SELECT p.*, a.nama_area 
    FROM pemadaman p 
    LEFT JOIN area a ON p.area_id = a.id 
    ORDER BY p.created_at DESC 
    LIMIT 10");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="assets/images/pln-logo.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-top">
            <div class="container">
                <div class="header-contact">
                    <span><i class="fas fa-phone"></i> Call Center: 123</span>
                    <span><i class="fas fa-envelope"></i> up2d.riau@pln.co.id</span>
                    <span><i class="fas fa-clock"></i> 24 Jam Siaga</span>
                </div>
                <div class="header-social">
                    <span>Ikuti kami:</span>
                    <a href="#" style="color: white; margin-left: 10px;"><i class="fab fa-facebook"></i></a>
                    <a href="#" style="color: white; margin-left: 10px;"><i class="fab fa-twitter"></i></a>
                    <a href="#" style="color: white; margin-left: 10px;"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="header-main">
            <div class="container">
                <div class="logo">
                    <img src="assets/images/pln-logo.png" alt="PLN Logo" onerror="this.src='https://via.placeholder.com/50x50/e31e24/ffffff?text=PLN'">
                    <div class="logo-text">
                        <h1>PLN <span>UP2D</span> RIAU</h1>
                        <span>Unit Pelaksana Pelayanan Listrik Daerah Riau</span>
                    </div>
                </div>
                <nav>
                    <ul class="nav-menu">
                        <li><a href="index.php" class="active"><i class="fas fa-home"></i> Beranda</a></li>
                        <li><a href="#peta"><i class="fas fa-map"></i> Peta</a></li>
                        <li><a href="#status"><i class="fas fa-info-circle"></i> Status</a></li>
                        <li><a href="#notifikasi"><i class="fas fa-bell"></i> Notifikasi</a></li>
                        <li><a href="admin/login.php" class="btn-admin"><i class="fas fa-user-lock"></i> Admin</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Hero Section -->
            <section class="hero-section">
                <div class="hero-content">
                    <div class="hero-text">
                        <h2>Informasi Pemadaman Listrik Real-time</h2>
                        <p>Dapatkan informasi terkini tentang pemadaman listrik di wilayah Provinsi Riau. Pantau status, lokasi, dan estimasi waktu pemulihan listrik.</p>
                        <div style="display: flex; gap: 15px;">
                            <a href="#peta" class="btn btn-primary" style="text-decoration: none;">
                                <i class="fas fa-map-marked-alt"></i> Lihat Peta
                            </a>
                            <a href="#status" class="btn" style="background: rgba(255,255,255,0.2); color: white; text-decoration: none;">
                                <i class="fas fa-list"></i> Daftar Pemadaman
                            </a>
                        </div>
                    </div>
                    <div class="hero-stats">
                        <div class="stat-card">
                            <div class="number" id="stat-darurat"><?php echo $stats['darurat']; ?></div>
                            <div class="label">Darurat</div>
                        </div>
                        <div class="stat-card">
                            <div class="number" id="stat-gangguan"><?php echo $stats['gangguan']; ?></div>
                            <div class="label">Gangguan</div>
                        </div>
                        <div class="stat-card">
                            <div class="number" id="stat-terencana"><?php echo $stats['terencana']; ?></div>
                            <div class="label">Terencana</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Status Cards -->
            <section class="status-section">
                <div class="status-grid">
                    <div class="status-card darurat" onclick="filterMap('darurat')">
                        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <h3>Pemadaman Darurat</h3>
                        <div class="count"><?php echo $stats['darurat']; ?></div>
                    </div>
                    <div class="status-card gangguan" onclick="filterMap('gangguan')">
                        <div class="icon"><i class="fas fa-bolt"></i></div>
                        <h3>Gangguan Listrik</h3>
                        <div class="count"><?php echo $stats['gangguan']; ?></div>
                    </div>
                    <div class="status-card terencana" onclick="filterMap('terencana')">
                        <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                        <h3>Pemadaman Terencana</h3>
                        <div class="count"><?php echo $stats['terencana']; ?></div>
                    </div>
                    <div class="status-card terdampak" onclick="filterMap('terdampak')">
                        <div class="icon"><i class="fas fa-home"></i></div>
                        <h3>Pelanggan Terdampak</h3>
                        <div class="count"><?php echo number_format($stats['terdampak']); ?></div>
                    </div>
                </div>
            </section>

            <!-- Map Section -->
            <section class="map-section" id="peta">
                <div class="map-header">
                    <h2><i class="fas fa-map-marked-alt"></i> Peta Pemadaman Listrik</h2>
                    <div class="map-filters">
                        <button class="filter-btn active" onclick="filterMap('all')">
                            <span class="dot" style="background: #666;"></span> Semua
                        </button>
                        <button class="filter-btn" onclick="filterMap('darurat')">
                            <span class="dot" style="background: <?php echo COLOR_DARURAT; ?>;"></span> Darurat
                        </button>
                        <button class="filter-btn" onclick="filterMap('gangguan')">
                            <span class="dot" style="background: <?php echo COLOR_GANGGUAN; ?>;"></span> Gangguan
                        </button>
                        <button class="filter-btn" onclick="filterMap('terencana')">
                            <span class="dot" style="background: <?php echo COLOR_TERENCANA; ?>;"></span> Terencana
                        </button>
                    </div>
                </div>
                <div id="map"></div>
                <div class="map-legend">
                    <div class="legend-title">Keterangan:</div>
                    <div class="legend-items">
                        <div class="legend-item">
                            <div class="legend-icon darurat"><i class="fas fa-exclamation"></i></div>
                            <span>Darurat - Butuh perhatian segera</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-icon gangguan"><i class="fas fa-bolt"></i></div>
                            <span>Gangguan - Sedang diperbaiki</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-icon terencana"><i class="fas fa-calendar"></i></div>
                            <span>Terencana - Maintenance rutin</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-icon terdampak"><i class="fas fa-home"></i></div>
                            <span>Terdampak - Area pelanggan terkena</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Table Section -->
            <section class="table-section" id="status">
                <div class="table-header">
                    <h2><i class="fas fa-list-alt"></i> Daftar Pemadaman Terbaru</h2>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchTable" placeholder="Cari pemadaman..." onkeyup="searchTable()">
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <table class="data-table" id="dataTable">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Judul</th>
                                <th>Area</th>
                                <th>Status</th>
                                <th>Mulai</th>
                                <th>Estimasi</th>
                                <th>Pelanggan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pemadaman_terbaru as $row): ?>
                            <tr data-id="<?php echo $row['id']; ?>" data-lat="<?php echo $row['lat']; ?>" data-lng="<?php echo $row['lng']; ?>" onclick="focusToMap(<?php echo $row['id']; ?>, <?php echo $row['lat']; ?>, <?php echo $row['lng']; ?>)" title="Klik untuk melihat lokasi di peta">
                                <td><strong><?php echo htmlspecialchars($row['kode_pemadaman']); ?></strong></td>
                                <td>
                                    <?php echo htmlspecialchars($row['judul']); ?>
                                    <div class="map-click-hint"><i class="fas fa-map-marker-alt"></i> Klik untuk lihat di peta</div>
                                </td>
                                <td><?php echo htmlspecialchars($row['nama_area'] ?? '-'); ?></td>
                                <td>
                                    <span class="status-badge <?php echo $row['status']; ?>">
                                        <?php echo getStatusLabel($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatTanggal($row['tanggal_mulai']); ?></td>
                                <td><?php echo $row['estimasi_durasi'] ? $row['estimasi_durasi'] . ' menit' : '-'; ?></td>
                                <td><?php echo number_format($row['pelanggan_terdampak']); ?></td>
                                <td>
                                    <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); showDetail(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Notification Section -->
            <section class="notification-section" id="notifikasi">
                <div class="table-header" style="margin-bottom: 20px;">
                    <h2><i class="fas fa-bell"></i> Pengumuman & Notifikasi</h2>
                </div>
                <div class="notification-grid">
                    <?php foreach ($notifikasi as $notif): ?>
                    <div class="notification-card <?php echo $notif['tipe']; ?>">
                        <div class="notification-icon">
                            <i class="fas fa-<?php 
                                echo $notif['tipe'] == 'info' ? 'info-circle' : 
                                    ($notif['tipe'] == 'warning' ? 'exclamation-triangle' : 
                                    ($notif['tipe'] == 'danger' ? 'times-circle' : 'check-circle')); 
                            ?>"></i>
                        </div>
                        <div class="notification-content">
                            <h4><?php echo htmlspecialchars($notif['judul']); ?></h4>
                            <p><?php echo htmlspecialchars($notif['pesan']); ?></p>
                            <div class="notification-date">
                                <i class="fas fa-clock"></i> <?php echo formatTanggal($notif['created_at']); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h3><i class="fas fa-bolt"></i> PLN UP2D Riau</h3>
                    <p>Sistem Informasi Pemadaman Listrik PLN UP2D Riau. Memberikan informasi real-time tentang pemadaman listrik untuk kenyamanan Anda.</p>
                    <div style="margin-top: 20px;">
                        <p><i class="fas fa-map-marker-alt"></i> Jl. Pattimura No. 1, Pekanbaru, Riau</p>
                        <p><i class="fas fa-phone"></i> Call Center: 123</p>
                        <p><i class="fas fa-envelope"></i> up2d.riau@pln.co.id</p>
                    </div>
                </div>
                <div class="footer-links">
                    <h4>Tautan Cepat</h4>
                    <ul>
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="#peta">Peta Pemadaman</a></li>
                        <li><a href="#status">Status Listrik</a></li>
                        <li><a href="#notifikasi">Pengumuman</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Layanan PLN</h4>
                    <ul>
                        <li><a href="https://www.pln.co.id" target="_blank">Website PLN</a></li>
                        <li><a href="https://portal.pln.co.id" target="_blank">Portal Pelanggan</a></li>
                        <li><a href="https://apps.pln.co.id" target="_blank">PLN Mobile</a></li>
                        <li><a href="#">Cek Tagihan</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Bantuan</h4>
                    <ul>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Hubungi Kami</a></li>
                        <li><a href="#">Syarat & Ketentuan</a></li>
                        <li><a href="#">Kebijakan Privasi</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> PT PLN (Persero) UP2D Riau. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Detail Modal -->
    <div class="modal-overlay" id="detailModal">
        <div class="modal">
            <div class="modal-header">
                <h3><i class="fas fa-info-circle"></i> Detail Pemadaman</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal()">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Data pemadaman dari PHP
        const pemadamanData = <?php echo json_encode($pemadaman); ?>;
        
        // Status colors
        const statusColors = {
            darurat: '<?php echo COLOR_DARURAT; ?>',
            gangguan: '<?php echo COLOR_GANGGUAN; ?>',
            terencana: '<?php echo COLOR_TERENCANA; ?>',
            terdampak: '<?php echo COLOR_TERDAMPAK; ?>'
        };
        
        // Status icons
        const statusIcons = {
            darurat: 'exclamation',
            gangguan: 'bolt',
            terencana: 'calendar',
            terdampak: 'home'
        };
        
        // Initialize map
        const map = L.map('map').setView([<?php echo MAP_CENTER_LAT; ?>, <?php echo MAP_CENTER_LNG; ?>], <?php echo MAP_DEFAULT_ZOOM; ?>);
        
        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        // Store markers
        let markers = [];
        let circles = [];
        
        // Custom icon creator
        function createCustomIcon(status) {
            return L.divIcon({
                className: 'custom-marker',
                html: `<div style="
                    background-color: ${statusColors[status]};
                    width: 36px;
                    height: 36px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                    border: 3px solid white;
                    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                    font-size: 14px;
                "><i class="fas fa-${statusIcons[status]}"></i></div>`,
                iconSize: [36, 36],
                iconAnchor: [18, 18]
            });
        }
        
        // Add markers to map
        function addMarkers(filter = 'all') {
            // Clear existing markers
            markers.forEach(m => map.removeLayer(m));
            circles.forEach(c => map.removeLayer(c));
            markers = [];
            circles = [];
            
            pemadamanData.forEach(item => {
                if (filter !== 'all' && item.status !== filter) return;
                
                // Add marker
                const marker = L.marker([item.lat, item.lng], {
                    icon: createCustomIcon(item.status)
                }).addTo(map);
                
                // Add popup
                const popupContent = `
                    <div class="info-window">
                        <h4>${item.judul}</h4>
                        <span class="status-badge ${item.status}">${item.status.toUpperCase()}</span>
                        <div class="info-row">
                            <span class="info-label">Kode:</span>
                            <span class="info-value">${item.kode_pemadaman}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Area:</span>
                            <span class="info-value">${item.nama_area || '-'}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Alamat:</span>
                            <span class="info-value">${item.alamat || '-'}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Mulai:</span>
                            <span class="info-value">${formatDate(item.tanggal_mulai)}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Pelanggan:</span>
                            <span class="info-value">${item.pelanggan_terdampak} pelanggan</span>
                        </div>
                        <button class="btn btn-primary btn-sm" onclick="showDetail(${item.id})" style="margin-top: 10px; width: 100%;">
                            <i class="fas fa-eye"></i> Lihat Detail
                        </button>
                    </div>
                `;
                
                marker.bindPopup(popupContent);
                markers.push(marker);
                
                // Add circle for affected area
                const circle = L.circle([item.lat, item.lng], {
                    color: statusColors[item.status],
                    fillColor: statusColors[item.status],
                    fillOpacity: 0.2,
                    radius: item.radius || 100
                }).addTo(map);
                
                circles.push(circle);
            });
        }
        
        // Format date
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        // Filter map
        function filterMap(status) {
            addMarkers(status);
            
            // Update active button
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.closest('.filter-btn').classList.add('active');
        }
        
        // Show detail modal
        function showDetail(id) {
            const item = pemadamanData.find(p => p.id == id);
            if (!item) return;
            
            const content = `
                <div class="info-window" style="min-width: auto;">
                    <h4>${item.judul}</h4>
                    <span class="status-badge ${item.status}">${item.status.toUpperCase()}</span>
                    <div class="info-row">
                        <span class="info-label">Kode:</span>
                        <span class="info-value">${item.kode_pemadaman}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Area:</span>
                        <span class="info-value">${item.nama_area || '-'}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Alamat:</span>
                        <span class="info-value">${item.alamat || '-'}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Deskripsi:</span>
                        <span class="info-value">${item.deskripsi || '-'}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Mulai:</span>
                        <span class="info-value">${formatDate(item.tanggal_mulai)}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Estimasi:</span>
                        <span class="info-value">${item.estimasi_durasi ? item.estimasi_durasi + ' menit' : '-'}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Pelanggan:</span>
                        <span class="info-value">${item.pelanggan_terdampak} pelanggan</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Petugas:</span>
                        <span class="info-value">${item.petugas || '-'}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">No. Tiket:</span>
                        <span class="info-value">${item.no_tiket || '-'}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status Pekerjaan:</span>
                        <span class="info-value">
                            <span class="status-badge ${item.status_pekerjaan}">${item.status_pekerjaan.toUpperCase()}</span>
                        </span>
                    </div>
                </div>
            `;
            
            document.getElementById('modalContent').innerHTML = content;
            document.getElementById('detailModal').classList.add('active');
        }
        
        // Close modal
        function closeModal() {
            document.getElementById('detailModal').classList.remove('active');
        }
        
        // Search table
        function searchTable() {
            const input = document.getElementById('searchTable');
            const filter = input.value.toLowerCase();
            const table = document.getElementById('dataTable');
            const tr = table.getElementsByTagName('tr');
            
            for (let i = 1; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < td.length; j++) {
                    if (td[j]) {
                        const txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toLowerCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }
                
                tr[i].style.display = found ? '' : 'none';
            }
        }
        
        // Close modal on overlay click
        document.getElementById('detailModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // Focus map to specific marker
        function focusToMap(id, lat, lng) {
            // Scroll to map section
            document.getElementById('peta').scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Pan map to location with animation
            map.flyTo([lat, lng], 16, {
                animate: true,
                duration: 1.5
            });
            
            // Find and open popup for this marker after fly animation
            setTimeout(() => {
                const marker = markers.find(m => {
                    const mLatLng = m.getLatLng();
                    return Math.abs(mLatLng.lat - lat) < 0.0001 && Math.abs(mLatLng.lng - lng) < 0.0001;
                });
                
                if (marker) {
                    marker.openPopup();
                }
            }, 1600);
        }
        
        // Initialize
        addMarkers();
        
        // Auto refresh every 5 minutes
        setInterval(() => {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
