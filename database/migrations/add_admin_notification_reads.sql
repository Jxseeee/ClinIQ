-- Run this if you already have the Notifications table and want each admin to have their own
-- read/unread state for clinic-wide admin notifications.
USE Clinic;

CREATE TABLE IF NOT EXISTS AdminNotificationReads (
    NotificationID INT NOT NULL,
    AdminID INT NOT NULL,
    ReadAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (NotificationID, AdminID),
    FOREIGN KEY (NotificationID) REFERENCES Notifications(NotificationID) ON DELETE CASCADE,
    FOREIGN KEY (AdminID) REFERENCES Admins(AdminID) ON DELETE CASCADE
);
