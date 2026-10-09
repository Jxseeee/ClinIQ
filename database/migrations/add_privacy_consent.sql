-- Run this if you already have the Students table and need the required clinic form with
-- Data Privacy Act (RA 10173) consent. Students must complete and save the form once.
USE Clinic;

ALTER TABLE Students
    ADD COLUMN PrivacyConsentAt DATETIME NULL AFTER EmergencyOther;
