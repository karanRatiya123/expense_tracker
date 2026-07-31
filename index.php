<?php
require_once 'config.php';
header('Location: ' . (isset($_SESSION['user']) ? 'dashboard.php' : 'login.php'));
exit;
