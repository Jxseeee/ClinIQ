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
