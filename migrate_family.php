<?php
// migrate_family.php — one-click, SAFE Family Space migration (UI only runner).
// Adds `families` + `family_members` tables. Touches NOTHING else.
// Idempotent: safe to open multiple times.
require_once 'config.php';

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$statements = [
    'families' => "CREATE TABLE IF NOT EXISTS families (
        id VARCHAR(50) PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        invite_code VARCHAR(10) NOT NULL UNIQUE,
        created_by INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
    'family_members' => "CREATE TABLE IF NOT EXISTS family_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        family_id VARCHAR(50) NOT NULL,
        user_id INT NOT NULL,
        role ENUM('admin','member') NOT NULL DEFAULT 'member',
        status ENUM('active','pending','rejected') NOT NULL DEFAULT 'active',
        joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_family_user (family_id, user_id),
        FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

$results = [];
$pdo = getDBConnection();
if (!$pdo) {
    $results[] = ['table' => 'connection', 'ok' => false, 'msg' => 'No database connection. Start MySQL in XAMPP and retry. No changes made.'];
} else {
    // Safety snapshot: count existing users before touching anything.
    try {
        $before = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    } catch (PDOException $e) {
        $before = -1;
    }
    foreach ($statements as $table => $sql) {
        try {
            $pdo->exec($sql);
            $results[] = ['table' => $table, 'ok' => true, 'msg' => "Table `$table` ready (created or already existed)."];
        } catch (PDOException $e) {
            $results[] = ['table' => $table, 'ok' => false, 'msg' => 'Error: ' . $e->getMessage()];
        }
    }
    try {
        $after = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $results[] = [
            'table' => 'users',
            'ok' => $after === $before,
            'msg' => "Users before: $before · after: $after — " . ($after === $before ? 'untouched, no data lost.' : 'COUNT CHANGED, investigate!'),
        ];
    } catch (PDOException $e) {
        $results[] = ['table' => 'users', 'ok' => false, 'msg' => 'Could not verify users: ' . $e->getMessage()];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ApexSpend — Family Migration</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
<main class="canvas" style="max-width:720px;">
    <div class="kicker"><span class="kicker-num">§ 05</span><span class="kicker-text">Family Space migration</span></div>
    <h1 style="font-family:var(--serif);font-style:italic;font-weight:400;">Migration report.</h1>
    <?php foreach ($results as $r): ?>
        <div class="fam-notice" role="status">
            <span class="fam-notice-ic" style="color:<?php echo $r['ok'] ? 'var(--gain)' : 'var(--loss)'; ?>;">
                <?php echo $r['ok'] ? '✓' : '!'; ?>
            </span>
            <div class="fam-notice-body">
                <strong><?php echo htmlspecialchars($r['table']); ?></strong>
                <span><?php echo htmlspecialchars($r['msg']); ?></span>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="actions">
        <button type="button" class="add" onclick="window.location.href='family.php'"><span>Back to Family Space</span></button>
    </div>
</main>
</body>
</html>
