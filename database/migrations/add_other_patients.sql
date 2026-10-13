-- Run this after add_nurse_accounts.sql if you want Check In to record faculty, non-teaching staff and
-- visitors (patients who aren't students, nurses or admins).
-- (The app also makes these changes automatically the first time Check In or the Nurses page is opened.)
USE Clinic;

CREATE TABLE IF NOT EXISTS OtherPatients (
    OtherPatientID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(120) NOT NULL,
    PatientType VARCHAR(50) NOT NULL,
    Department VARCHAR(100) NULL,
    ContactNumber VARCHAR(20) NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_other_patients_name (FullName)
);

ALTER TABLE ClinicVisits
    ADD COLUMN OtherPatientID INT NULL AFTER PatientNurseID,
    ADD INDEX idx_clinic_visits_other_patient_created (OtherPatientID, CreatedAt),
    ADD CONSTRAINT fk_clinic_visits_other_patient FOREIGN KEY (OtherPatientID) REFERENCES OtherPatients(OtherPatientID) ON DELETE CASCADE;
ALTER TABLE ClinicCheckIns
    MODIFY PersonType ENUM('student', 'staff', 'nurse', 'other') NOT NULL,
    ADD COLUMN OtherPatientID INT NULL AFTER PatientNurseID,
    ADD INDEX idx_checkins_other_patient_active (OtherPatientID, CheckedOutAt),
    ADD CONSTRAINT fk_checkins_other_patient FOREIGN KEY (OtherPatientID) REFERENCES OtherPatients(OtherPatientID) ON DELETE CASCADE;

-- Each visit and check-in still has exactly one patient, which may now be another patient.
-- (Skip the two DROP CHECK lines if your tables never had these checks.)
ALTER TABLE ClinicVisits DROP CHECK chk_clinic_visit_person;
ALTER TABLE ClinicVisits ADD CONSTRAINT chk_clinic_visit_person CHECK (
    ((StudentID IS NOT NULL) + (StaffID IS NOT NULL) + (PatientNurseID IS NOT NULL) + (OtherPatientID IS NOT NULL)) = 1
);
ALTER TABLE ClinicCheckIns DROP CHECK chk_checkin_person;
ALTER TABLE ClinicCheckIns ADD CONSTRAINT chk_checkin_person CHECK (
    ((StudentID IS NOT NULL) + (StaffID IS NOT NULL) + (PatientNurseID IS NOT NULL) + (OtherPatientID IS NOT NULL)) = 1
    AND ((PersonType = 'student' AND StudentID IS NOT NULL)
      OR (PersonType = 'staff' AND StaffID IS NOT NULL)
      OR (PersonType = 'nurse' AND PatientNurseID IS NOT NULL)
      OR (PersonType = 'other' AND OtherPatientID IS NOT NULL))
);
