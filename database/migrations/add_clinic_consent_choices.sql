-- Run this if you already have a Clinic database and need persisted consent choices.
USE Clinic;

ALTER TABLE Students
    ADD COLUMN ConsentFirstAid TINYINT(1) NULL AFTER ProfileImagePath,
    ADD COLUMN ConsentNonPrescription TINYINT(1) NULL AFTER ConsentFirstAid,
    ADD COLUMN ConsentHospitalization TINYINT(1) NULL AFTER ConsentNonPrescription,
    ADD COLUMN EmergencyDecisionConsent TINYINT(1) NOT NULL DEFAULT 0 AFTER ConsentHospitalization,
    ADD COLUMN EmergencyCallGuardian TINYINT(1) NOT NULL DEFAULT 0 AFTER EmergencyDecisionConsent,
    ADD COLUMN EmergencyOther VARCHAR(255) NULL AFTER EmergencyCallGuardian;