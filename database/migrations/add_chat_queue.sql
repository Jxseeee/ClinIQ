-- Run this if you already have a Clinic database and need chat active-session limits.
USE Clinic;

CREATE TABLE IF NOT EXISTS ActiveChatSessions (
    SessionID VARCHAR(128) PRIMARY KEY,
    UserRole ENUM('student', 'admin') NOT NULL,
    UserID INT NOT NULL,
    LastSeenAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ChatQueue (
    QueueID INT AUTO_INCREMENT PRIMARY KEY,
    SessionID VARCHAR(128) NOT NULL UNIQUE,
    UserRole ENUM('student', 'admin') NOT NULL,
    UserID INT NOT NULL,
    CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    LastSeenAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
