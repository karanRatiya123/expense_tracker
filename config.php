<?php
/**
 * ApexSpend - Configuration & Database Helper
 */

if (session_status() === PHP_SESSION_NONE) {
    $sessionPath = __DIR__ . DIRECTORY_SEPARATOR . '.sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0775, true);
    }
    if (is_writable($sessionPath)) {
        session_save_path($sessionPath);
    }
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'path'     => '/',
    ]);
    session_start();
}

define('CSRF_FIELD', '_token');

/**
 * Return the per-session CSRF token, minting one if missing.
 * Forms print it via <input type="hidden" name="_token" value="<?= csrf_token() ?>">.
 * AJAX requests read it from the same field name in FormData,
 * or from the X-CSRF-Token header.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate the CSRF token on the current request. Call at the top of every
 * state-changing POST handler (forms and api_*.php). Halts on failure.
 */
function csrf_validate(): void {
    $sent = $_POST[CSRF_FIELD] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string) $sent)) {
        http_response_code(403);
        $isAjax = str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api_')
            || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        // The header above is fine for AJAX; for plain forms we also want a redirect:
        if (!$isAjax) {
            $_SESSION['flash_error'] = 'Session expired. Please try again.';
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'login.php'));
        }
        exit;
    }
}

// Database Credentials (Customize for your MySQL setup)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'apexspend_db');
define('ENABLE_RECAPS', false);

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
