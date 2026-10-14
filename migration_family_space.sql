-- ============================================================
-- ApexSpend — Family Space migration (SAFE, idempotent)
-- Run on the CURRENT database. Does NOT touch `users` or any
-- existing table: only ADDS two new tables. No DROP, no ALTER,
-- no DELETE — existing data is fully preserved.
--
-- Apply with:  mysql -u root apexspend_db < migration_family_space.sql
-- Or open migrate_family.php in the browser.
-- Safe to run multiple times (IF NOT EXISTS everywhere).
-- ============================================================

CREATE TABLE IF NOT EXISTS families (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    invite_code VARCHAR(10) NOT NULL UNIQUE,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS family_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    family_id VARCHAR(50) NOT NULL,
    user_id INT NOT NULL,
    role ENUM('admin','member') NOT NULL DEFAULT 'member',
    status ENUM('active','pending','rejected') NOT NULL DEFAULT 'active',
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_family_user (family_id, user_id),
    FOREIGN KEY (family_id) REFERENCES families(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
