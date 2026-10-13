CREATE DATABASE IF NOT EXISTS Clinic;
USE Clinic;

-- Drop existing tables in reverse dependency order
DROP TABLE IF EXISTS AuditLogs;
DROP TABLE IF EXISTS ChatQueue;
DROP TABLE IF EXISTS ActiveChatSessions;
DROP TABLE IF EXISTS AdminNotificationReads;
DROP TABLE IF EXISTS Notifications;
-- ClinicOccupancy is no longer used (counts are live); dropped so old installs are cleaned up.
DROP TABLE IF EXISTS ClinicOccupancy;
DROP TABLE IF EXISTS Nurses;
DROP TABLE IF EXISTS Medicines;
DROP TABLE IF EXISTS ClinicCheckIns;
DROP TABLE IF EXISTS ClinicVisits;
DROP TABLE IF EXISTS ChatConversations;
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
    NotificationsEnabled TINYINT(1) NOT NULL DEFAULT 1,

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
    ProfileImagePath VARCHAR(255),
    ConsentFirstAid TINYINT(1) NULL,
    ConsentNonPrescription TINYINT(1) NULL,
    ConsentHospitalization TINYINT(1) NULL,
    EmergencyDecisionConsent TINYINT(1) NOT NULL DEFAULT 0,
    EmergencyCallGuardian TINYINT(1) NOT NULL DEFAULT 0,
    EmergencyOther VARCHAR(255),
    PrivacyConsentAt DATETIME NULL,

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
    ProfileImagePath VARCHAR(255),
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

CREATE TABLE ChatConversations (
    StudentID INT PRIMARY KEY,
    Status ENUM('open', 'resolved') NOT NULL DEFAULT 'open',
    LastMessageAt DATETIME NULL,
    ResolvedAt DATETIME NULL,
    ResolvedByAdminID INT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID) ON DELETE CASCADE,
    FOREIGN KEY (ResolvedByAdminID) REFERENCES Admins(AdminID) ON DELETE SET NULL
);

CREATE TABLE ClinicVisits (
    VisitID INT AUTO_INCREMENT PRIMARY KEY,
    StudentID INT NULL,
    StaffID INT NULL,
    AdminID INT NULL,
    Complaint TEXT NOT NULL,
    Vitals VARCHAR(255),
    Assessment TEXT,
    Treatment TEXT,
    MedicineGiven VARCHAR(255),
    MedicineID INT NULL,
    MedicineDosage VARCHAR(120),
    MedicineQuantity INT UNSIGNED NULL,
    Status ENUM('open', 'completed', 'follow-up') NOT NULL DEFAULT 'completed',
    Disposition VARCHAR(100),
    FollowUpDate DATE NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID) ON DELETE CASCADE,
    FOREIGN KEY (StaffID) REFERENCES Admins(AdminID) ON DELETE CASCADE,
    FOREIGN KEY (AdminID) REFERENCES Admins(AdminID) ON DELETE SET NULL,
    INDEX idx_clinic_visits_student_created (StudentID, CreatedAt),
    INDEX idx_clinic_visits_staff_created (StaffID, CreatedAt),
    CONSTRAINT chk_clinic_visit_person CHECK (
        (StudentID IS NOT NULL AND StaffID IS NULL)
        OR (StudentID IS NULL AND StaffID IS NOT NULL)
    ),
    CONSTRAINT chk_clinic_visit_medicine_quantity CHECK (
        MedicineQuantity IS NULL OR MedicineQuantity > 0
    )
);

CREATE TABLE Medicines (
    MedicineID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(120) NOT NULL UNIQUE,
    Stock INT UNSIGNED NOT NULL DEFAULT 0,
    Dosage VARCHAR(120),
    Description VARCHAR(255),
    LowStockThreshold INT UNSIGNED NOT NULL DEFAULT 20,
    IsArchived TINYINT(1) NOT NULL DEFAULT 0,
    ArchivedAt DATETIME NULL,
    CreatedByAdminID INT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CreatedByAdminID) REFERENCES Admins(AdminID) ON DELETE SET NULL,
    INDEX idx_medicines_stock (Stock, LowStockThreshold),
    INDEX idx_medicines_archived (IsArchived, Name)
);

INSERT INTO Medicines (Name, Stock, Dosage, Description, LowStockThreshold, CreatedByAdminID) VALUES
('Paracetamol', 80, '500 mg tablet', 'Used for mild to moderate pain and fever.', 20, NULL),
('Ibuprofen', 18, '200 mg tablet', 'Used for pain and inflammation.', 20, NULL),
('Cetirizine', 12, '10 mg tablet', 'Allergy relief medication.', 20, NULL),
('Bandage', 77, 'Sterile bandage', 'Used to cover and protect minor wounds.', 20, NULL),
('Ferrous Sulfate', 67, '30 mg tablet', 'Used to support iron deficiency care.', 20, NULL),
('Oral Rehydration Salts', 15, 'One sachet', 'Used to help prevent dehydration.', 20, NULL);

CREATE TABLE Nurses (
    NurseID INT AUTO_INCREMENT PRIMARY KEY,
    FullName VARCHAR(120) NOT NULL,
    Position VARCHAR(100) NOT NULL DEFAULT 'Clinic Nurse',
    LicenseNumber VARCHAR(80),
    IsOnDuty TINYINT(1) NOT NULL DEFAULT 0,
    DutyCampus VARCHAR(50) NULL,
    CreatedByAdminID INT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CreatedByAdminID) REFERENCES Admins(AdminID) ON DELETE SET NULL,
    INDEX idx_nurses_duty_name (IsOnDuty, FullName)
);

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
    INDEX idx_checkins_staff_active (StaffID, CheckedOutAt),
    CONSTRAINT chk_checkin_person CHECK (
        (PersonType = 'student' AND StudentID IS NOT NULL AND StaffID IS NULL)
        OR (PersonType = 'staff' AND StudentID IS NULL AND StaffID IS NOT NULL)
    )
);

CREATE TABLE Notifications (
    NotificationID INT AUTO_INCREMENT PRIMARY KEY,
    TargetRole ENUM('admin', 'student') NOT NULL,
    TargetUserID INT NULL,
    Type VARCHAR(50) NOT NULL,
    Title VARCHAR(160) NOT NULL,
    Body TEXT,
    Link VARCHAR(255),
    IsRead TINYINT(1) NOT NULL DEFAULT 0,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notifications_target_read (TargetRole, TargetUserID, IsRead, CreatedAt)
);

-- Per-admin read state for clinic-wide admin notifications.
CREATE TABLE AdminNotificationReads (
    NotificationID INT NOT NULL,
    AdminID INT NOT NULL,
    ReadAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (NotificationID, AdminID),
    FOREIGN KEY (NotificationID) REFERENCES Notifications(NotificationID) ON DELETE CASCADE,
    FOREIGN KEY (AdminID) REFERENCES Admins(AdminID) ON DELETE CASCADE
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

CREATE TABLE AuditLogs (
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

-- Default admin account (username: admin, password: admin123)
INSERT INTO Admins (Username, Password, FullName)
VALUES ('admin', '$2y$10$wmDKaC1keu.3.gLKoiMgGe6.o41USYZRn1TU3N6WpbeNW4HERwB46', 'Admin');

-- Test student account (StudentID: 10001, password: student123)
INSERT INTO Students (
    StudentID, FirstName, LastName, MiddleName, Email, Phone, Password, MustChangePassword,
    Course, YearLevel, Department, PreferredName, DateOfBirth,
    Citizenship, Gender, Height, Weight, HomeTelephone,
    StreetAddress, Municipality, City,
    MedicationsRegular, AllergyFood, AllergyMedicine, AllergyOthers,
    CustodialParent, CorrespondenceTo
) VALUES (
    10001, 'Juan', 'Dela Cruz', 'Santos', 'juan.delacruz@fcat.com.ph', '0917-123-4567',
    '$2y$10$D1/kLLW8lhu9FSRBrdS1yOaIbw/o4lHt3nQAZBZBBmmcV2WfRwb7q', 0,
    'Bachelor of Science in Computer Science', '2nd Year', 'College of Computer Studies',
    'Juan', '2003-05-15', 'Filipino', 'Male', '1.70', '65',
    '02-123-4567', '123 Rizal Street, Brgy. Poblacion', 'Bacoor', 'Cavite',
    NULL, NULL, NULL, NULL, 'guardian1', 'guardian1'
);

INSERT INTO Guardians (StudentID, GuardianType, Relationship, LastName, FirstName, MiddleName, OfficePhone, MobileNumber, EmailAddress, EmergencyContactName, EmergencyContactMobile)
VALUES (10001, 'guardian1', 'Father', 'Dela Cruz', 'Pedro', 'Santos', NULL, '0918-765-4321', NULL, 'Maria Dela Cruz', '0919-111-2222');

INSERT INTO MedicalHistory (StudentID, Illness, DiagnosisDate, DiagnosisAge)
VALUES (10001, 'Asthma', '2010-03-01', 7),
       (10001, 'Chicken Pox', '2008-06-15', 5);
