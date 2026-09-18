-- Run this after add_clinic_checkins.sql to track dispensed units and deduct inventory.
USE Clinic;

ALTER TABLE ClinicVisits
    ADD COLUMN MedicineQuantity INT UNSIGNED NULL AFTER MedicineDosage,
    ADD CONSTRAINT chk_clinic_visit_medicine_quantity
        CHECK (MedicineQuantity IS NULL OR MedicineQuantity > 0);