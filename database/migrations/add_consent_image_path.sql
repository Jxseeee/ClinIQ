-- Run this if you already have a Clinic database and need consent/guardian image uploads.
USE Clinic;

ALTER TABLE Students
    ADD COLUMN ConsentImagePath VARCHAR(255) NULL AFTER CorrespondenceTo;
