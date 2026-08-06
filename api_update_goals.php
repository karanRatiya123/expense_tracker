<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

csrf_validate();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $savings_goal = floatval($_POST['savings_goal'] ?? 0);
    $run_rate_target = floatval($_POST['run_rate_target'] ?? 0);

    if ($savings_goal <= 0 || $run_rate_target <= 0) {
        echo json_encode(['success' => false, 'error' => 'Goals must be positive numbers.']);
        exit;
    }

    $userId = (int) $_SESSION['user']['id'];
    $pdo = getDBConnection();

    if ($pdo) {
        // Ensure columns exist in users table
        try {
            $pdo->query("SELECT savings_goal, run_rate_target FROM users LIMIT 1");
        } catch (PDOException $e) {
            try {
                $pdo->exec("ALTER TABLE users ADD COLUMN savings_goal DECIMAL(15,2) DEFAULT 50000.00");
                $pdo->exec("ALTER TABLE users ADD COLUMN run_rate_target DECIMAL(15,2) DEFAULT 1000000.00");
            } catch (PDOException $e2) {
                // ignore
            }
        }

        // Update in database
        try {
            $stmt = $pdo->prepare('UPDATE users SET savings_goal = ?, run_rate_target = ? WHERE id = ?');
            $stmt->execute([$savings_goal, $run_rate_target, $userId]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => 'Failed to save to database.']);
            exit;
        }
    }

    // Update in session
    $_SESSION['user']['savings_goal'] = $savings_goal;
    $_SESSION['user']['run_rate_target'] = $run_rate_target;

    // Update in session user database if it exists
    if (isset($_SESSION['users_db'])) {
        foreach ($_SESSION['users_db'] as &$u) {
            if ((int)$u['id'] === (int)$userId) {
                $u['savings_goal'] = $savings_goal;
                $u['run_rate_target'] = $run_rate_target;
                break;
            }
        }
        unset($u);
    }

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
