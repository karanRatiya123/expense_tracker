<?php
/**
 * ApexSpend - Configuration & Database Helper
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Credentials (Customize for your MySQL setup)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'apexspend_db');

/**
 * Get Database Connection (PDO)
 * Falls back gracefully if MySQL is not available yet
 */
function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        return $pdo;
    } catch (PDOException $e) {
        return null; // Null fallback triggers Session Storage Mode for quick testing
    }
}

/**
 * Initialize Demo User in Session if not using DB
 */
if (!isset($_SESSION['users_db'])) {
    $_SESSION['users_db'] = [
        [
            'id' => 1,
            'name' => 'Alex Morgan',
            'email' => 'demo@apexspend.com',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];
}

/**
 * Helper to display flash messages
 */
function getFlashMessage($type) {
    if (isset($_SESSION['flash_' . $type])) {
        $msg = $_SESSION['flash_' . $type];
        unset($_SESSION['flash_' . $type]);
        return $msg;
    }
    return null;
}
?>
