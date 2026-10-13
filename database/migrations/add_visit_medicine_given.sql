-- Run this if you already have ClinicVisits and need a separate medicine-given field.
USE Clinic;

ALTER TABLE ClinicVisits
    ADD COLUMN MedicineGiven VARCHAR(255) NULL AFTER Treatment;