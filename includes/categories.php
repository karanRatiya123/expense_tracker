<?php
/**
 * includes/categories.php — single source of truth for categories.
 *
 * Owns:
 *   - the canonical built-in palette (name → icon + color)
 *   - the Font Awesome icon whitelist for the picker
 *   - lazy auto-migration (CREATE TABLE + seed) so no manual step
 *   - per-user category reads with a no-DB fallback to built-ins
 *   - apex_category_icon() / apex_category_color() — the ONE PHP lookup,
 *     replacing the 4 duplicated category_icon() maps
 */

if (!defined('APEX_BUILTIN_CATEGORIES')) {
    define('APEX_BUILTIN_CATEGORIES', [
        'Food & Dining'       => ['icon' => 'fa-utensils',          'color' => '#f59e0b'],
        'Housing & Utilities' => ['icon' => 'fa-bolt',              'color' => '#3b82f6'],
        'Shopping & Retail'   => ['icon' => 'fa-bag-shopping',      'color' => '#a855f7'],
        'Transportation'      => ['icon' => 'fa-car',               'color' => '#4ade80'],
        'Entertainment'       => ['icon' => 'fa-film',              'color' => '#ec4899'],
        'Health & Medical'    => ['icon' => 'fa-briefcase-medical', 'color' => '#22d3ee'],
        'Income'              => ['icon' => 'fa-money-bill-wave',   'color' => '#8a8a8a'],
    ]);
}

function apex_builtin_categories(): array {
    return APEX_BUILTIN_CATEGORIES;
}

/** Icon whitelist for the picker (Font Awesome 6 solid). */
function apex_icon_whitelist(): array {
    static $icons = null;
    if ($icons === null) {
        $icons = [
            'fa-tag', 'fa-utensils', 'fa-bolt', 'fa-bag-shopping', 'fa-car',
            'fa-film', 'fa-briefcase-medical', 'fa-money-bill-wave',
            'fa-house', 'fa-heart', 'fa-plane', 'fa-dog', 'fa-book', 'fa-graduation-cap',
            'fa-shirt', 'fa-gift', 'fa-gamepad', 'fa-mug-hot', 'fa-basket-shopping',
            'fa-dumbbell', 'fa-paw', 'fa-phone', 'fa-wifi', 'fa-droplet', 'fa-fire-flame-simple',
            'fa-baby', 'fa-children', 'fa-church', 'fa-spa', 'fa-umbrella',
            'fa-taxi', 'fa-bus', 'fa-gas-pump', 'fa-toolbox', 'fa-scissors',
            'fa-lightbulb', 'fa-laptop', 'fa-mobile-screen', 'fa-camera', 'fa-music',
            'fa-vault', 'fa-piggy-bank', 'fa-credit-card', 'fa-receipt', 'fa-wand-magic-sparkles',
        ];
    }
    return $icons;
}

/** Tangerine-led swatch palette offered in the color picker. */
function apex_color_palette(): array {
    return [
        '#f59e0b', '#3b82f6', '#a855f7', '#4ade80', '#ec4899', '#22d3ee',
        '#8a8a8a', '#f87171', '#34d399', '#fbbf24', '#60a5fa', '#e879f9',
    ];
}

/**
 * Ensure the categories table exists and built-ins are seeded for the user.
 * Idempotent; returns false when MySQL is unavailable.
 */
function apex_categories_ready(PDO $pdo, int $userId): bool {
    try {
        $pdo->query('SELECT id FROM categories LIMIT 1');
    } catch (PDOException $e) {
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
                id VARCHAR(50) PRIMARY KEY,
                user_id INT NOT NULL,
                name VARCHAR(100) NOT NULL,
                icon VARCHAR(60) NOT NULL DEFAULT 'fa-tag',
                color VARCHAR(9) NOT NULL DEFAULT '#f59e0b',
                is_builtin TINYINT(1) NOT NULL DEFAULT 0,
                position INT NOT NULL DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_user_category (user_id, name),
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        } catch (PDOException $e2) {
            return false; // no-DB fallback handles the rest
        }
    }

    // Lazy seed: make sure this user has every built-in (skips existing).
    // NOTE: ids are per-user — a name-derived id would collide on the
    // PRIMARY KEY as soon as a second user is seeded.
    try {
        $stmt = $pdo->prepare('INSERT IGNORE INTO categories (id, user_id, name, icon, color, is_builtin, position)
                               VALUES (?, ?, ?, ?, ?, 1, ?)');
        $pos = 1;
        foreach (apex_builtin_categories() as $name => $meta) {
            $stmt->execute(['cat_' . $userId . '_' . md5($name), $userId, $name, $meta['icon'], $meta['color'], $pos]);
            $pos++;
        }
    } catch (PDOException $e) {
        // Table exists but seeding failed — reads below will still try.
    }
    return true;
}

/**
 * Categories for a user, ordered by position then name.
 * Falls back to the built-in array when MySQL is down, so the app boots.
 */
function apex_get_categories(PDO $pdo, int $userId): array {
    if (!apex_categories_ready($pdo, $userId)) {
        $fallback = [];
        $pos = 1;
        foreach (apex_builtin_categories() as $name => $meta) {
            $fallback[] = [
                'id' => 'cat_' . $userId . '_' . md5($name), 'user_id' => $userId, 'name' => $name,
                'icon' => $meta['icon'], 'color' => $meta['color'],
                'is_builtin' => 1, 'position' => $pos,
            ];
            $pos++;
        }
        return $fallback;
    }

    try {
        $stmt = $pdo->prepare('SELECT id, user_id, name, icon, color, is_builtin, position
                               FROM categories WHERE user_id = ? ORDER BY position, name');
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

/** Load the registry once per request so icon/color lookups are O(1). */
function apex_load_category_registry(PDO $pdo, int $userId): void {
    if (isset($GLOBALS['APEX_CAT_REG'])) return;
    $reg = [];
    foreach (apex_get_categories($pdo, $userId) as $row) {
        $reg[$row['name']] = ['icon' => $row['icon'], 'color' => $row['color']];
    }
    foreach (apex_builtin_categories() as $name => $meta) {
        if (!isset($reg[$name])) $reg[$name] = $meta;
    }
    $GLOBALS['APEX_CAT_REG'] = $reg;
}

/** The ONE PHP icon lookup — replaces the 4 duplicated category_icon() maps. */
function apex_category_icon(string $name): string {
    $reg = $GLOBALS['APEX_CAT_REG'] ?? [];
    if (isset($reg[$name])) return $reg[$name]['icon'];
    $builtin = apex_builtin_categories();
    return $builtin[$name]['icon'] ?? 'fa-tag';
}

/** The ONE PHP color lookup. */
function apex_category_color(string $name): string {
    $reg = $GLOBALS['APEX_CAT_REG'] ?? [];
    if (isset($reg[$name])) return $reg[$name]['color'];
    $builtin = apex_builtin_categories();
    return $builtin[$name]['color'] ?? '#f59e0b';
}
