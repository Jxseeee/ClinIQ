-- Run this if you already have a Clinic database and need student notification preferences.
USE Clinic;

SET @notification_column_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'Students'
      AND COLUMN_NAME = 'NotificationsEnabled'
);

SET @notification_column_sql = IF(
    @notification_column_exists = 0,
    'ALTER TABLE Students ADD COLUMN NotificationsEnabled TINYINT(1) NOT NULL DEFAULT 1 AFTER MustChangePassword',
    'SELECT 1'
);

PREPARE notification_column_statement FROM @notification_column_sql;
EXECUTE notification_column_statement;
DEALLOCATE PREPARE notification_column_statement;