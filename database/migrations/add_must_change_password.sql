-- Run this if you already have a Clinic database and need first-login password changes.
USE Clinic;

ALTER TABLE Students
    ADD COLUMN MustChangePassword TINYINT(1) NOT NULL DEFAULT 0 AFTER Password;
