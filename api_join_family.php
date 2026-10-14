<?php
require_once 'config.php';

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

$raw = $_POST['code'] ?? '';
$code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $raw));
if (strlen($code) < 4 || strlen($code) > 10) {
    echo json_encode(['success' => false, 'error' => 'Enter a valid invite code']);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$fam = $pdo->prepare('SELECT id, name FROM families WHERE invite_code = ?');
$fam->execute([$code]);
$family = $fam->fetch();
if (!$family) {
    echo json_encode(['success' => false, 'error' => 'Incorrect code. Ask a member to re-share it.']);
    exit;
}

$userId = $_SESSION['user']['id'];
$existing = $pdo->prepare("SELECT id FROM family_members WHERE family_id = ? AND user_id = ? AND status = 'active'");
$existing->execute([$family['id'], $userId]);
if ($existing->fetch()) {
    echo json_encode(['success' => true, 'already' => true, 'family_id' => $family['id'], 'name' => $family['name']]);
    exit;
}

try {
    $ins = $pdo->prepare("INSERT INTO family_members (family_id, user_id, role, status) VALUES (?, ?, 'member', 'active')");
    $ins->execute([$family['id'], $userId]);
    echo json_encode(['success' => true, 'family_id' => $family['id'], 'name' => $family['name']]);
} catch (PDOException $e) {
    // Race: membership appeared between check and insert (unique key).
    echo json_encode(['success' => true, 'already' => true, 'family_id' => $family['id'], 'name' => $family['name']]);
}
