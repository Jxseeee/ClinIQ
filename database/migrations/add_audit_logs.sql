-- Run this if you already have a Clinic database and need the audit log.
-- Names are stored with each entry so history survives account deletion.
USE Clinic;

CREATE TABLE IF NOT EXISTS AuditLogs (
    AuditLogID INT AUTO_INCREMENT PRIMARY KEY,
    ActorRole ENUM('admin', 'student', 'guest') NOT NULL,
    ActorID INT NULL,
    ActorName VARCHAR(150) NOT NULL,
    SubjectRole ENUM('admin', 'student') NULL,
    SubjectID INT NULL,
    SubjectName VARCHAR(150) NULL,
    Category VARCHAR(30) NOT NULL,
    Action VARCHAR(60) NOT NULL,
    Description VARCHAR(500) NOT NULL,
    IPAddress VARCHAR(45) NULL,
    UserAgent VARCHAR(255) NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_created (CreatedAt),
    INDEX idx_audit_subject (SubjectRole, SubjectID, CreatedAt),
    INDEX idx_audit_actor (ActorRole, ActorID, CreatedAt),
    INDEX idx_audit_category (Category, CreatedAt)
);
