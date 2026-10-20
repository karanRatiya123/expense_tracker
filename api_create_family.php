<?php
require_once 'config.php';
require_once 'includes/mailer.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

csrf_validate();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$name = trim($_POST['name'] ?? '');
if (mb_strlen($name) < 2 || mb_strlen($name) > 40) {
    echo json_encode(['success' => false, 'error' => 'Family name must be 2–40 characters']);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

// Unique 6-char invite code (unambiguous alphabet, retry on collision).
$alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
$code = null;
for ($i = 0; $i < 25; $i++) {
    $c = '';
    for ($j = 0; $j < 6; $j++) {
        $c .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    $check = $pdo->prepare('SELECT id FROM families WHERE invite_code = ?');
    $check->execute([$c]);
    if (!$check->fetch()) {
        $code = $c;
        break;
    }
}
if (!$code) {
    echo json_encode(['success' => false, 'error' => 'Could not generate a code, please retry']);
    exit;
}

$famId = 'fam_' . uniqid();
try {
    $pdo->beginTransaction();
    $ins = $pdo->prepare('INSERT INTO families (id, name, invite_code, created_by) VALUES (?, ?, ?, ?)');
    $ins->execute([$famId, $name, $code, $_SESSION['user']['id']]);
    $mem = $pdo->prepare("INSERT INTO family_members (family_id, user_id, role, status) VALUES (?, ?, 'admin', 'active')");
    $mem->execute([$famId, $_SESSION['user']['id']]);
    $pdo->commit();

    echo json_encode(['success' => true, 'family_id' => $famId, 'name' => $name, 'invite_code' => $code]);

    defer_notification_email('family_created', [
        'name'        => $name,
        'invite_code' => $code,
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => 'Could not create family, please retry']);
}
