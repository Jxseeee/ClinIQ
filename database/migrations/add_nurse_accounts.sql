-- Run this if you already have the Nurses table and want nurses to sign in with their own accounts.
-- (The app also adds these columns automatically the first time a nurse account is needed.)
-- Nurse accounts are created by an admin on the Nurses page. They start with the default password
-- (the same one new students get) and must change it at their first sign-in.
USE Clinic;

ALTER TABLE Nurses
    ADD COLUMN Username VARCHAR(50) NULL UNIQUE AFTER LicenseNumber,
    ADD COLUMN Password VARCHAR(255) NULL AFTER Username,
    ADD COLUMN MustChangePassword TINYINT(1) NOT NULL DEFAULT 1 AFTER Password,
    ADD COLUMN ProfileImagePath VARCHAR(255) NULL AFTER MustChangePassword;

-- Nurses get the clinic staff notifications (e.g. new student messages) with their own read state.
CREATE TABLE IF NOT EXISTS NurseNotificationReads (
    NotificationID INT NOT NULL,
    NurseID INT NOT NULL,
    ReadAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (NotificationID, NurseID),
    FOREIGN KEY (NotificationID) REFERENCES Notifications(NotificationID) ON DELETE CASCADE,
    FOREIGN KEY (NurseID) REFERENCES Nurses(NurseID) ON DELETE CASCADE
);

-- Visits a nurse records at Check In are credited to the nurse ("Consulted by" in Reports).
ALTER TABLE ClinicVisits
    ADD COLUMN NurseID INT NULL AFTER AdminID,
    ADD CONSTRAINT fk_clinic_visits_nurse FOREIGN KEY (NurseID) REFERENCES Nurses(NurseID) ON DELETE SET NULL;

-- Nurses answer student chats and post announcements; their replies and posts are credited to them.
ALTER TABLE Messages
    ADD COLUMN NurseID INT NULL AFTER AdminID,
    ADD CONSTRAINT fk_messages_nurse FOREIGN KEY (NurseID) REFERENCES Nurses(NurseID) ON DELETE SET NULL;
ALTER TABLE Announcements
    ADD COLUMN NurseID INT NULL AFTER AdminID,
    ADD CONSTRAINT fk_announcements_nurse FOREIGN KEY (NurseID) REFERENCES Nurses(NurseID) ON DELETE SET NULL;

-- Nurses take chat slots (and wait in the chat queue) like admins do.
ALTER TABLE ActiveChatSessions MODIFY UserRole ENUM('student', 'admin', 'nurse') NOT NULL;
ALTER TABLE ChatQueue MODIFY UserRole ENUM('student', 'admin', 'nurse') NOT NULL;

-- Audit entries can now record a nurse as the person who acted or the account concerned.
ALTER TABLE AuditLogs
    MODIFY ActorRole ENUM('admin', 'student', 'nurse', 'guest') NOT NULL,
    MODIFY SubjectRole ENUM('admin', 'student', 'nurse') NULL;

-- Nurses can be checked in as patients. PatientNurseID is the nurse who is the patient
-- (ClinicVisits.NurseID above is the nurse who recorded the visit).
ALTER TABLE ClinicVisits
    ADD COLUMN PatientNurseID INT NULL AFTER StaffID,
    ADD INDEX idx_clinic_visits_patient_nurse_created (PatientNurseID, CreatedAt),
    ADD CONSTRAINT fk_clinic_visits_patient_nurse FOREIGN KEY (PatientNurseID) REFERENCES Nurses(NurseID) ON DELETE CASCADE;
ALTER TABLE ClinicCheckIns
    MODIFY PersonType ENUM('student', 'staff', 'nurse') NOT NULL,
    ADD COLUMN PatientNurseID INT NULL AFTER StaffID,
    ADD INDEX idx_checkins_patient_nurse_active (PatientNurseID, CheckedOutAt),
    ADD CONSTRAINT fk_checkins_patient_nurse FOREIGN KEY (PatientNurseID) REFERENCES Nurses(NurseID) ON DELETE CASCADE;

-- Each visit and check-in still has exactly one patient, which may now be a nurse.
-- (Skip the two DROP CHECK lines if your tables never had these checks.)
ALTER TABLE ClinicVisits DROP CHECK chk_clinic_visit_person;
ALTER TABLE ClinicVisits ADD CONSTRAINT chk_clinic_visit_person CHECK (
    ((StudentID IS NOT NULL) + (StaffID IS NOT NULL) + (PatientNurseID IS NOT NULL)) = 1
);
ALTER TABLE ClinicCheckIns DROP CHECK chk_checkin_person;
ALTER TABLE ClinicCheckIns ADD CONSTRAINT chk_checkin_person CHECK (
    (PersonType = 'student' AND StudentID IS NOT NULL AND StaffID IS NULL AND PatientNurseID IS NULL)
    OR (PersonType = 'staff' AND StaffID IS NOT NULL AND StudentID IS NULL AND PatientNurseID IS NULL)
    OR (PersonType = 'nurse' AND PatientNurseID IS NOT NULL AND StudentID IS NULL AND StaffID IS NULL)
);
