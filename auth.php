<?php
/**
 * ApexSpend — Auth handler (login + signup)
 * POST-only. Uses DB if available, else session-backed store (see config.php).
 */
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

csrf_validate();

$action = $_POST['action'] ?? '';
$email = trim(strtolower($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';
$name = trim($_POST['name'] ?? '');

// ---- SIGNUP ----
if ($action === 'signup') {
    if ($name === '' || $email === '' || strlen($password) < 6) {
        $_SESSION['flash_error'] = 'Name, email, and a 6+ char password are required.';
        header('Location: login.php?tab=signup');
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_error'] = 'That email looks off.';
        header('Location: login.php?tab=signup');
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $pdo = getDBConnection();

    if ($pdo) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $_SESSION['flash_error'] = 'An account with that email already exists.';
            header('Location: login.php?tab=signup');
            exit;
        }
        $ins = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
        $ins->execute([$name, $email, $hash]);
    } else {
        foreach ($_SESSION['users_db'] as $u) {
            if (strtolower($u['email']) === $email) {
                $_SESSION['flash_error'] = 'An account with that email already exists.';
                header('Location: login.php?tab=signup');
                exit;
            }
        }
        $_SESSION['users_db'][] = [
            'id' => count($_SESSION['users_db']) + 1,
            'name' => $name,
            'email' => $email,
            'password' => $hash,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    $_SESSION['flash_success'] = 'Account created. Sign in to continue.';
    header('Location: login.php?tab=login');
    exit;
}

// ---- LOGIN ----
if ($action === 'login') {
    if ($email === '' || $password === '') {
        $_SESSION['flash_error'] = 'Email and password are required.';
        header('Location: login.php?tab=login');
        exit;
    }

    $pdo = getDBConnection();
    $user = null;

    if ($pdo) {
        $stmt = $pdo->prepare('SELECT id, name, email, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        if ($row && password_verify($password, $row['password'])) {
            $user = ['id' => $row['id'], 'name' => $row['name'], 'email' => $row['email']];
        }
    } else {
        foreach ($_SESSION['users_db'] as $u) {
            if (strtolower($u['email']) === $email && password_verify($password, $u['password'])) {
                $user = ['id' => $u['id'], 'name' => $u['name'], 'email' => $u['email']];
                break;
            }
        }
    }

    if (!$user) {
        $_SESSION['flash_error'] = 'Wrong email or password.';
        header('Location: login.php?tab=login');
        exit;
    }

    session_regenerate_id(true);
    $_SESSION['user'] = $user;
    header('Location: dashboard.php');
    exit;
}

header('Location: login.php');
exit;
