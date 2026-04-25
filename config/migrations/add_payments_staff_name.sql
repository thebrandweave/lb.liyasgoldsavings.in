-- Add staff name on admin-entered payments (optional text).
-- Safe to run once; skip if column already exists.

SET @exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Payments' AND COLUMN_NAME = 'StaffName'
);
SET @after := IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Payments' AND COLUMN_NAME = 'UTRNumber') > 0,
    'UTRNumber',
    'Amount'
);
SET @sql := IF(@exists = 0,
    CONCAT('ALTER TABLE `Payments` ADD `StaffName` VARCHAR(255) NULL DEFAULT NULL AFTER `', @after, '`'),
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
