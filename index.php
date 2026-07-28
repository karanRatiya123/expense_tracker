<?php
require_once 'config.php';

// Auto-route to the dashboard — login page has been removed.
header('Location: dashboard.php');
exit;
?>