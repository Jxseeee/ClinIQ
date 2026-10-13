-- Run this if you already have the Medicines table and need medicine archiving.
USE Clinic;

ALTER TABLE Medicines
    ADD COLUMN IsArchived TINYINT(1) NOT NULL DEFAULT 0 AFTER LowStockThreshold,
    ADD COLUMN ArchivedAt DATETIME NULL AFTER IsArchived,
    ADD INDEX idx_medicines_archived (IsArchived, Name);
