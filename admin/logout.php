<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

auth()->logout();
redirect(ADMIN_URL . '/login.php');
?>
