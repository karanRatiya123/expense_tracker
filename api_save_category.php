<?php
require_once 'config.php';
require_once __DIR__ . '/includes/categories.php';

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
$icon = trim($_POST['icon'] ?? 'fa-tag');
$color = trim($_POST['color'] ?? '#f59e0b');
$id = trim($_POST['id'] ?? '');

if ($name === '' || mb_strlen($name) > 100) {
    echo json_encode(['success' => false, 'error' => 'Name must be 1–100 characters.']);
    exit;
}
if (!preg_match('/^fa-[a-z0-9-]+$/', $icon) || !in_array($icon, apex_icon_whitelist(), true)) {
    echo json_encode(['success' => false, 'error' => 'Unknown icon.']);
    exit;
}
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
    echo json_encode(['success' => false, 'error' => 'Color must be a hex value like #f59e0b.']);
    exit;
}

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$userId = (int) $_SESSION['user']['id'];

try {
    if ($id !== '') {
        // ---- EDIT ----
        $stmt = $pdo->prepare('SELECT * FROM categories WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            echo json_encode(['success' => false, 'error' => 'Category not found.']);
            exit;
        }

        $isBuiltin = (int) $row['is_builtin'] === 1;
        $oldName = $row['name'];
        $renamed = $name !== $oldName;

        // Built-ins are locked but recolorable: icon/color may change, name may not.
        if ($isBuiltin && $renamed) {
            echo json_encode(['success' => false, 'error' => 'Built-in categories cannot be renamed. Restyle them instead.']);
            exit;
        }

        $upd = $pdo->prepare('UPDATE categories SET name = ?, icon = ?, color = ? WHERE id = ? AND user_id = ?');
        $upd->execute([$name, $icon, $color, $id, $userId]);

        // Rename cascade: history follows the new name, nothing orphans.
        if ($renamed) {
            $pdo->prepare('UPDATE transactions SET category = ? WHERE user_id = ? AND category = ?')
                ->execute([$name, $userId, $oldName]);
            $pdo->prepare('UPDATE budgets SET category = ? WHERE user_id = ? AND category = ?')
                ->execute([$name, $userId, $oldName]);
        }

        echo json_encode(['success' => true, 'item' => [
            'id' => $id, 'name' => $name, 'icon' => $icon, 'color' => $color,
            'is_builtin' => $isBuiltin ? 1 : 0, 'position' => (int) $row['position'],
        ]]);
        exit;
    }

    // ---- CREATE ----
    $catId = 'cat_' . uniqid();
    $pos = $pdo->prepare('SELECT COALESCE(MAX(position), 0) + 1 FROM categories WHERE user_id = ?');
    $pos->execute([$userId]);
    $nextPos = (int) $pos->fetchColumn();

    try {
        $ins = $pdo->prepare('INSERT INTO categories (id, user_id, name, icon, color, is_builtin, position) VALUES (?, ?, ?, ?, ?, 0, ?)');
        $ins->execute([$catId, $userId, $name, $icon, $color, $nextPos]);
    } catch (PDOException $dup) {
        if ((int) ($dup->errorInfo[1] ?? 0) === 1062) {
            echo json_encode(['success' => false, 'error' => 'That category already exists.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Could not save category, please retry.']);
        }
        exit;
    }

    echo json_encode(['success' => true, 'item' => [
        'id' => $catId, 'name' => $name, 'icon' => $icon, 'color' => $color,
        'is_builtin' => 0, 'position' => $nextPos,
    ]]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Could not save category, please retry.']);
}
