-- ============================================================
-- ApexSpend — Custom Categories migration (SAFE, idempotent)
-- Run on the CURRENT database. Only ADDS one new table.
-- No DROP, no ALTER on existing tables, no data backfill —
-- transactions.category stays VARCHAR(100) and every existing
-- row keeps resolving because the built-in seeds match today's
-- names exactly. Safe to re-run.
--
-- Apply with:  mysql -u root apexspend_db < migration_custom_categories.sql
-- Or just use the app: includes/categories.php auto-migrates.
-- ============================================================

CREATE TABLE IF NOT EXISTS categories (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed the built-ins for one existing account (lowest user id) so it looks
-- identical to today. Other users get theirs lazily via
-- includes/categories.php (auto-seeds on first page load).
-- Ids are per-user (cat_<uid>_<md5>) — a name-only id would collide on the
-- PRIMARY KEY across users.
INSERT IGNORE INTO categories (id, user_id, name, icon, color, is_builtin, position)
SELECT 'cat_seed_food',    u.id, 'Food & Dining',       'fa-utensils',          '#f59e0b', 1, 1 FROM users u ORDER BY u.id LIMIT 1;
INSERT IGNORE INTO categories (id, user_id, name, icon, color, is_builtin, position)
SELECT 'cat_seed_house',   u.id, 'Housing & Utilities', 'fa-bolt',              '#3b82f6', 1, 2 FROM users u ORDER BY u.id LIMIT 1;
INSERT IGNORE INTO categories (id, user_id, name, icon, color, is_builtin, position)
SELECT 'cat_seed_shop',    u.id, 'Shopping & Retail',   'fa-bag-shopping',      '#a855f7', 1, 3 FROM users u ORDER BY u.id LIMIT 1;
INSERT IGNORE INTO categories (id, user_id, name, icon, color, is_builtin, position)
SELECT 'cat_seed_trans',   u.id, 'Transportation',      'fa-car',               '#4ade80', 1, 4 FROM users u ORDER BY u.id LIMIT 1;
INSERT IGNORE INTO categories (id, user_id, name, icon, color, is_builtin, position)
SELECT 'cat_seed_ent',     u.id, 'Entertainment',       'fa-film',              '#ec4899', 1, 5 FROM users u ORDER BY u.id LIMIT 1;
INSERT IGNORE INTO categories (id, user_id, name, icon, color, is_builtin, position)
SELECT 'cat_seed_health',  u.id, 'Health & Medical',    'fa-briefcase-medical', '#22d3ee', 1, 6 FROM users u ORDER BY u.id LIMIT 1;
INSERT IGNORE INTO categories (id, user_id, name, icon, color, is_builtin, position)
SELECT 'cat_seed_income',  u.id, 'Income',              'fa-money-bill-wave',   '#8a8a8a', 1, 7 FROM users u ORDER BY u.id LIMIT 1;
