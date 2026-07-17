<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$db = Database::getInstance();

$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        // Get all active pemadaman
        $data = $db->fetchAll("SELECT p.*, a.nama_area 
            FROM pemadaman p 
            LEFT JOIN area a ON p.area_id = a.id 
            WHERE p.status_pekerjaan != 'selesai'
            ORDER BY p.created_at DESC");
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'detail':
        // Get detail by ID
        $id = $_GET['id'] ?? 0;
        $data = $db->fetch("SELECT p.*, a.nama_area, adm.nama_lengkap as created_by_name 
            FROM pemadaman p 
            LEFT JOIN area a ON p.area_id = a.id 
            LEFT JOIN admin adm ON p.created_by = adm.id
            WHERE p.id = :id", ['id' => $id]);
        
        if ($data) {
            // Get affected customers
            $pelanggan = $db->fetchAll("SELECT * FROM pelanggan_terdampak WHERE pemadaman_id = :id", ['id' => $id]);
            $data['pelanggan_terdampak_list'] = $pelanggan;
            
            // Get history
            $riwayat = $db->fetchAll("SELECT r.*, adm.nama_lengkap as created_by_name 
                FROM riwayat_pemadaman r 
                LEFT JOIN admin adm ON r.created_by = adm.id
                WHERE r.pemadaman_id = :id 
                ORDER BY r.created_at DESC", ['id' => $id]);
            $data['riwayat'] = $riwayat;
            
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Data tidak ditemukan']);
        }
        break;
        
    case 'by-status':
        // Get by status
        $status = $_GET['status'] ?? '';
        $data = $db->fetchAll("SELECT p.*, a.nama_area 
            FROM pemadaman p 
            LEFT JOIN area a ON p.area_id = a.id 
            WHERE p.status = :status AND p.status_pekerjaan != 'selesai'
            ORDER BY p.created_at DESC", ['status' => $status]);
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'by-area':
        // Get by area
        $area_id = $_GET['area_id'] ?? 0;
        $data = $db->fetchAll("SELECT p.*, a.nama_area 
            FROM pemadaman p 
            LEFT JOIN area a ON p.area_id = a.id 
            WHERE p.area_id = :area_id AND p.status_pekerjaan != 'selesai'
            ORDER BY p.created_at DESC", ['area_id' => $area_id]);
        echo json_encode(['success' => true, 'data' => $data]);
        break;
        
    case 'stats':
        // Get statistics
        $stats = [
            'darurat' => $db->fetch("SELECT COUNT(*) as total FROM pemadaman WHERE status = 'darurat' AND status_pekerjaan != 'selesai'")['total'],
            'gangguan' => $db->fetch("SELECT COUNT(*) as total FROM pemadaman WHERE status = 'gangguan' AND status_pekerjaan != 'selesai'")['total'],
            'terencana' => $db->fetch("SELECT COUNT(*) as total FROM pemadaman WHERE status = 'terencana' AND status_pekerjaan != 'selesai'")['total'],
            'terdampak' => $db->fetch("SELECT SUM(pelanggan_terdampak) as total FROM pemadaman WHERE status_pekerjaan != 'selesai'")['total'] ?? 0,
            'total' => $db->fetch("SELECT COUNT(*) as total FROM pemadaman WHERE status_pekerjaan != 'selesai'")['total']
        ];
        echo json_encode(['success' => true, 'data' => $stats]);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Action tidak valid']);
}
?>
