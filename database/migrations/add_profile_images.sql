-- Run this if you already have a Clinic database and need account profile photos.
USE Clinic;

ALTER TABLE Students
    ADD COLUMN ProfileImagePath VARCHAR(255) NULL AFTER ConsentImagePath;

ALTER TABLE Admins
    ADD COLUMN ProfileImagePath VARCHAR(255) NULL AFTER FullName;