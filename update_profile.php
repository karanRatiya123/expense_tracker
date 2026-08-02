<?php
/**
 * ApexSpend — Profile update handler
 * POST-only. Two actions: update_info, change_password.
 * Writes to MySQL when available, falls back to $_SESSION['users_db'] (same pattern as auth.php).
 */
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

csrf_validate();

$action = $_POST['action'] ?? '';
$userId = (int) $_SESSION['user']['id'];
$pdo = getDBConnection();

// Small helper to apply a row update in either storage backend.
function apply_user_update($pdo, $userId, $fields) {
    if ($pdo) {
        $sets = [];
        $vals = [];
        foreach ($fields as $col => $val) {
            $sets[] = "$col = ?";
            $vals[] = $val;
        }
        $vals[] = $userId;
        $sql = 'UPDATE users SET ' . implode(', ', $sets) . ' WHERE id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($vals);
    } else {
        foreach ($_SESSION['users_db'] as &$u) {
            if ((int) $u['id'] === $userId) {
                foreach ($fields as $col => $val) $u[$col] = $val;
                break;
            }
        }
        unset($u);
    }
}

function fetch_user_hash($pdo, $userId) {
    if ($pdo) {
        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ? $row['password'] : null;
    }
    foreach ($_SESSION['users_db'] as $u) {
        if ((int) $u['id'] === $userId) return $u['password'];
    }
    return null;
}

// ---- UPDATE NAME / EMAIL ----
if ($action === 'update_info') {
    $name = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));

    if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_error'] = 'Name and a valid email are required.';
        header('Location: profile.php');
        exit;
    }

    // Block duplicate email (someone else's)
    if ($pdo) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
        $stmt->execute([$email, $userId]);
        if ($stmt->fetch()) {
            $_SESSION['flash_error'] = 'That email is already in use.';
            header('Location: profile.php');
            exit;
        }
    } else {
        foreach ($_SESSION['users_db'] as $u) {
            if ((int) $u['id'] !== $userId && strtolower($u['email']) === $email) {
                $_SESSION['flash_error'] = 'That email is already in use.';
                header('Location: profile.php');
                exit;
            }
        }
    }

    apply_user_update($pdo, $userId, ['name' => $name, 'email' => $email]);
    $_SESSION['user']['name'] = $name;
    $_SESSION['user']['email'] = $email;
    $_SESSION['flash_success'] = 'Profile updated.';
    header('Location: profile.php');
    exit;
}

// ---- CHANGE PASSWORD ----
if ($action === 'change_password') {
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($new) < 6 || $new !== $confirm) {
        $_SESSION['flash_error'] = 'New password must be 6+ characters and match the confirmation.';
        header('Location: profile.php');
        exit;
    }

    $hash = fetch_user_hash($pdo, $userId);
    if (!$hash || !password_verify($old, $hash)) {
        $_SESSION['flash_error'] = 'Current password is incorrect.';
        header('Location: profile.php');
        exit;
    }

    $newHash = password_hash($new, PASSWORD_BCRYPT);
    apply_user_update($pdo, $userId, ['password' => $newHash]);
    $_SESSION['flash_success'] = 'Password changed.';
    header('Location: profile.php');
    exit;
}

header('Location: profile.php');
exit;
