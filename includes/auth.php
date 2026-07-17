<?php
require_once 'config.php';
require_once 'database.php';

class Auth {
    private static $instance = null;
    private $db;
    
    private function __construct() {
        $this->db = Database::getInstance();
        $this->startSession();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function startSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_name(SESSION_NAME);
            session_start();
        }
    }
    
    public function login($username, $password) {
        $user = $this->db->fetch(
            "SELECT * FROM admin WHERE username = :username AND status = 'aktif'",
            ['username' => $username]
        );
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_nama'] = $user['nama_lengkap'];
            $_SESSION['admin_level'] = $user['level'];
            $_SESSION['login_time'] = time();
            
            // Update last login
            $this->db->update(
                'admin',
                ['last_login' => date('Y-m-d H:i:s')],
                'id = :id',
                ['id' => $user['id']]
            );
            
            return true;
        }
        
        return false;
    }
    
    public function logout() {
        session_destroy();
        $_SESSION = [];
    }
    
    public function isLoggedIn() {
        if (!isset($_SESSION['admin_id'])) {
            return false;
        }
        
        // Cek session timeout
        if (time() - $_SESSION['login_time'] > SESSION_LIFETIME) {
            $this->logout();
            return false;
        }
        
        // Update login time
        $_SESSION['login_time'] = time();
        
        return true;
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            redirect(ADMIN_URL . '/login.php');
        }
    }
    
    public function requireAdmin() {
        $this->requireLogin();
        if ($_SESSION['admin_level'] !== 'admin') {
            redirect(ADMIN_URL . '/dashboard.php');
        }
    }
    
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username'],
            'nama' => $_SESSION['admin_nama'],
            'level' => $_SESSION['admin_level']
        ];
    }
    
    public function changePassword($userId, $oldPassword, $newPassword) {
        $user = $this->db->fetch(
            "SELECT * FROM admin WHERE id = :id",
            ['id' => $userId]
        );
        
        if (!$user || !password_verify($oldPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Password lama tidak benar'];
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->update(
            'admin',
            ['password' => $hashedPassword],
            'id = :id',
            ['id' => $userId]
        );
        
        return ['success' => true, 'message' => 'Password berhasil diubah'];
    }
}

// Fungsi helper
function auth() {
    return Auth::getInstance();
}
?>
