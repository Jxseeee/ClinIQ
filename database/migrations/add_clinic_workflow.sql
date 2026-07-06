-- Run this if you already have a Clinic database and need clinic visits,
-- appointments, notifications, and chat conversation status.
USE Clinic;

CREATE TABLE IF NOT EXISTS ChatConversations (
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

CREATE TABLE IF NOT EXISTS ClinicVisits (
    VisitID INT AUTO_INCREMENT PRIMARY KEY,
    StudentID INT NOT NULL,
    AdminID INT NULL,
    Complaint TEXT NOT NULL,
    Vitals VARCHAR(255),
    Assessment TEXT,
    Treatment TEXT,
    Status ENUM('open', 'completed', 'follow-up') NOT NULL DEFAULT 'completed',
    Disposition VARCHAR(100),
    FollowUpDate DATE NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID) ON DELETE CASCADE,
    FOREIGN KEY (AdminID) REFERENCES Admins(AdminID) ON DELETE SET NULL,
    INDEX idx_clinic_visits_student_created (StudentID, CreatedAt)
);

CREATE TABLE IF NOT EXISTS Appointments (
    AppointmentID INT AUTO_INCREMENT PRIMARY KEY,
    StudentID INT NOT NULL,
    RequestedFor DATETIME NOT NULL,
    Reason TEXT NOT NULL,
    Status ENUM('pending', 'approved', 'declined', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    AdminNotes TEXT,
    HandledByAdminID INT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID) ON DELETE CASCADE,
    FOREIGN KEY (HandledByAdminID) REFERENCES Admins(AdminID) ON DELETE SET NULL,
    INDEX idx_appointments_status_requested (Status, RequestedFor),
    INDEX idx_appointments_student_created (StudentID, CreatedAt)
);

CREATE TABLE IF NOT EXISTS Notifications (
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
