-- Run this if you already have a Clinic database and need nurse directory management.
USE Clinic;

CREATE TABLE IF NOT EXISTS Nurses (
    NurseID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(120) NOT NULL,
    Position VARCHAR(100) NOT NULL DEFAULT 'Clinic Nurse',
    LicenseNumber VARCHAR(80),
    IsOnDuty TINYINT(1) NOT NULL DEFAULT 0,
    CreatedByAdminID INT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CreatedByAdminID) REFERENCES Admins(AdminID) ON DELETE SET NULL,
    INDEX idx_nurses_duty_name (IsOnDuty, FullName)
);