<?php
// Konfigurasi Database (env vars for Railway, fallback for local)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'pln_pekanbaru');

// Konfigurasi Website
define('SITE_NAME', 'PLN Pekanbaru - Info Pemadaman Listrik');
$siteUrl = getenv('SITE_URL') ?: 'http://localhost/pln-pekanbaru';
define('SITE_URL', $siteUrl);
define('ADMIN_URL', SITE_URL . '/admin');

// Konfigurasi Peta
define('MAP_CENTER_LAT', 0.5071);
define('MAP_CENTER_LNG', 101.4478);
define('MAP_DEFAULT_ZOOM', 12);

// Status Pemadaman
define('STATUS_DARURAT', 'darurat');
define('STATUS_GANGGUAN', 'gangguan');
define('STATUS_TERENCANA', 'terencana');
define('STATUS_TERDAMPAK', 'terdampak');

// Warna Status
define('COLOR_DARURAT', '#ef4444');
define('COLOR_GANGGUAN', '#eab308');
define('COLOR_TERENCANA', '#3b82f6');
define('COLOR_TERDAMPAK', '#06b6d4');

// Session
define('SESSION_NAME', 'pln_pekanbaru_session');
define('SESSION_LIFETIME', 7200); // 2 jam

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error Reporting (production: off, local: on)
if (getenv('APP_ENV') === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Fungsi helper
function formatTanggal($date) {
    return date('d M Y H:i', strtotime($date));
}

function getStatusLabel($status) {
    $labels = [
        'darurat' => 'Darurat',
        'gangguan' => 'Gangguan',
        'terencana' => 'Terencana',
        'terdampak' => 'Terdampak'
    ];
    return $labels[$status] ?? $status;
}

function getStatusColor($status) {
    $colors = [
        'darurat' => COLOR_DARURAT,
        'gangguan' => COLOR_GANGGUAN,
        'terencana' => COLOR_TERENCANA,
        'terdampak' => COLOR_TERDAMPAK
    ];
    return $colors[$status] ?? '#999';
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}
?>
