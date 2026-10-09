-- Run this if you already have the Nurses table and need to record which campus an on-duty nurse is at.
-- (The app also adds this column automatically the first time it is needed.)
USE Clinic;

ALTER TABLE Nurses
    ADD COLUMN DutyCampus VARCHAR(50) NULL AFTER IsOnDuty;
