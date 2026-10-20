-- ============================================================
--  ApexSpend — Email notification preferences (additive only)
--  Safe to run more than once: the column is added only if missing.
-- ============================================================
USE apexspend_db;

SET @col_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'users'
      AND COLUMN_NAME  = 'email_notifications'
);

SET @ddl = IF(
    @col_exists = 0,
    'ALTER TABLE users ADD COLUMN email_notifications TINYINT(1) NOT NULL DEFAULT 1 AFTER avatar_path',
    'SELECT ''email_notifications already exists'' AS message'
);

PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 1 = notification emails ON (default)
-- 0 = OFF — users can change this in Profile → Preferences
