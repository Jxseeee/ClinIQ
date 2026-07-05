CREATE DATABASE IF NOT EXISTS Clinic;
USE Clinic;

-- Drop existing tables in reverse dependency order
DROP TABLE IF EXISTS ChatQueue;
DROP TABLE IF EXISTS ActiveChatSessions;
DROP TABLE IF EXISTS Messages;
DROP TABLE IF EXISTS MedicalHistory;
DROP TABLE IF EXISTS Guardians;
DROP TABLE IF EXISTS Announcements;
DROP TABLE IF EXISTS Admins;
DROP TABLE IF EXISTS Students;

CREATE TABLE Students (
    StudentID INT PRIMARY KEY,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    MiddleName VARCHAR(50),
    Email VARCHAR(100) UNIQUE,
    Phone VARCHAR(20),
    Password VARCHAR(255) NOT NULL,
    MustChangePassword TINYINT(1) NOT NULL DEFAULT 0,

    Course VARCHAR(100),
    YearLevel VARCHAR(20),
    Department VARCHAR(100),
    PreferredName VARCHAR(50),
    DateOfBirth DATE,
    Citizenship VARCHAR(50),
    Gender VARCHAR(20),
    Height VARCHAR(10),
    Weight VARCHAR(10),
    HomeTelephone VARCHAR(20),
    StreetAddress VARCHAR(255),
    Municipality VARCHAR(100),
    City VARCHAR(100),

    MedicationsRegular TEXT,
    AllergyFood VARCHAR(255),
    AllergyMedicine VARCHAR(255),
    AllergyOthers VARCHAR(255),
    CustodialParent VARCHAR(20),
    CorrespondenceTo VARCHAR(20),
    ConsentImagePath VARCHAR(255),

    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Guardians (
    GuardianID INT AUTO_INCREMENT PRIMARY KEY,
    StudentID INT NOT NULL,
    GuardianType VARCHAR(20) NOT NULL,
    Relationship VARCHAR(50),
    LastName VARCHAR(50),
    FirstName VARCHAR(50),
    MiddleName VARCHAR(50),
    OfficePhone VARCHAR(20),
    MobileNumber VARCHAR(20),
    EmailAddress VARCHAR(100),
    EmergencyContactName VARCHAR(100),
    EmergencyContactMobile VARCHAR(20),
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID) ON DELETE CASCADE
);

CREATE TABLE MedicalHistory (
    MedicalHistoryID INT AUTO_INCREMENT PRIMARY KEY,
    StudentID INT NOT NULL,
    Illness VARCHAR(100) NOT NULL,
    DiagnosisDate VARCHAR(20),
    DiagnosisAge INT,
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID) ON DELETE CASCADE
);

CREATE TABLE Admins (
    AdminID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    FullName VARCHAR(100) NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Announcements (
    AnnouncementID INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(200) NOT NULL,
    Content TEXT NOT NULL,
    AdminID INT,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (AdminID) REFERENCES Admins(AdminID) ON DELETE SET NULL
);

CREATE TABLE Messages (
    MessageID INT AUTO_INCREMENT PRIMARY KEY,
    StudentID INT NOT NULL,
    SenderRole ENUM('student', 'admin') NOT NULL,
    AdminID INT NULL,
    Content TEXT NOT NULL,
    IsRead TINYINT(1) NOT NULL DEFAULT 0,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID) ON DELETE CASCADE,
    FOREIGN KEY (AdminID) REFERENCES Admins(AdminID) ON DELETE SET NULL
);

CREATE TABLE ActiveChatSessions (
    SessionID VARCHAR(128) PRIMARY KEY,
    UserRole ENUM('student', 'admin') NOT NULL,
    UserID INT NOT NULL,
    LastSeenAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ChatQueue (
    QueueID INT AUTO_INCREMENT PRIMARY KEY,
    SessionID VARCHAR(128) NOT NULL UNIQUE,
    UserRole ENUM('student', 'admin') NOT NULL,
    UserID INT NOT NULL,
    CreatedAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    LastSeenAt DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Default admin account (username: admin, password: admin123)
INSERT INTO Admins (Username, Password, FullName)
VALUES ('admin', '$2y$10$wmDKaC1keu.3.gLKoiMgGe6.o41USYZRn1TU3N6WpbeNW4HERwB46', 'System Administrator');

-- Test student account (StudentID: 10001, password: student123)
INSERT INTO Students (
    StudentID, FirstName, LastName, MiddleName, Email, Phone, Password, MustChangePassword,
    Course, YearLevel, Department, PreferredName, DateOfBirth,
    Citizenship, Gender, Height, Weight, HomeTelephone,
    StreetAddress, Municipality, City,
    MedicationsRegular, AllergyFood, AllergyMedicine, AllergyOthers,
    CustodialParent, CorrespondenceTo
) VALUES (
    10001, 'Juan', 'Dela Cruz', 'Santos', 'juan.delacruz@university.edu.ph', '0917-123-4567',
    '$2y$10$D1/kLLW8lhu9FSRBrdS1yOaIbw/o4lHt3nQAZBZBBmmcV2WfRwb7q', 0,
    'Bachelor of Science in Information Technology', '2nd Year', 'College of Computing',
    'Juan', '2003-05-15', 'Filipino', 'Male', '1.70', '65',
    '02-123-4567', '123 Rizal Street, Brgy. Poblacion', 'Bacoor', 'Cavite',
    NULL, NULL, NULL, NULL, 'guardian1', 'guardian1'
);

INSERT INTO Guardians (StudentID, GuardianType, Relationship, LastName, FirstName, MiddleName, OfficePhone, MobileNumber, EmailAddress, EmergencyContactName, EmergencyContactMobile)
VALUES (10001, 'guardian1', 'Father', 'Dela Cruz', 'Pedro', 'Santos', NULL, '0918-765-4321', NULL, 'Maria Dela Cruz', '0919-111-2222');

INSERT INTO MedicalHistory (StudentID, Illness, DiagnosisDate, DiagnosisAge)
VALUES (10001, 'Asthma', '2010-03-01', 7),
       (10001, 'Chicken Pox', '2008-06-15', 5);
