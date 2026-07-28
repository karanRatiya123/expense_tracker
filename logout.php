<?php
require_once 'config.php';

// Unset user session
unset($_SESSION['user']);
$_SESSION['flash_success'] = 'You have been logged out safely.';

header('Location: dashboard.php');
exit;
?>