-- Run this if you already have a Clinic database and need live clinic occupancy counts.
USE Clinic;

CREATE TABLE IF NOT EXISTS ClinicOccupancy (
    OccupancyID TINYINT PRIMARY KEY DEFAULT 1,
    NursesOnDuty INT UNSIGNED NOT NULL DEFAULT 0,
    StudentsInClinic INT UNSIGNED NOT NULL DEFAULT 0,
    UpdatedByAdminID INT NULL,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (UpdatedByAdminID) REFERENCES Admins(AdminID) ON DELETE SET NULL,
    CONSTRAINT chk_single_occupancy_row CHECK (OccupancyID = 1)
);

INSERT IGNORE INTO ClinicOccupancy (OccupancyID, NursesOnDuty, StudentsInClinic)
VALUES (1, 0, 0);