<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

auth()->logout();
redirect(ADMIN_URL . '/login.php');
?>
