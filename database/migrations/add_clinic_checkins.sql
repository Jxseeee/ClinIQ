-- Run this if you already have ClinicVisits and need searchable student/staff check-ins.
USE Clinic;

ALTER TABLE ClinicVisits
    MODIFY StudentID INT NULL,
    ADD COLUMN StaffID INT NULL AFTER StudentID,
    ADD COLUMN MedicineID INT NULL AFTER MedicineGiven,
    ADD COLUMN MedicineDosage VARCHAR(120) NULL AFTER MedicineID,
    ADD INDEX idx_clinic_visits_staff_created (StaffID, CreatedAt),
    ADD CONSTRAINT fk_clinic_visits_staff FOREIGN KEY (StaffID) REFERENCES Admins(AdminID) ON DELETE CASCADE;

CREATE TABLE ClinicCheckIns (
    CheckInID INT AUTO_INCREMENT PRIMARY KEY,
    PersonType ENUM('student', 'staff') NOT NULL,
    StudentID INT NULL,
    StaffID INT NULL,
    VisitID INT NOT NULL UNIQUE,
    CheckedInByAdminID INT NULL,
    CheckedInAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CheckedOutAt DATETIME NULL,
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID) ON DELETE CASCADE,
    FOREIGN KEY (StaffID) REFERENCES Admins(AdminID) ON DELETE CASCADE,
    FOREIGN KEY (VisitID) REFERENCES ClinicVisits(VisitID) ON DELETE CASCADE,
    FOREIGN KEY (CheckedInByAdminID) REFERENCES Admins(AdminID) ON DELETE SET NULL,
    INDEX idx_checkins_active (CheckedOutAt, CheckedInAt),
    INDEX idx_checkins_student_active (StudentID, CheckedOutAt),
    INDEX idx_checkins_staff_active (StaffID, CheckedOutAt)
);