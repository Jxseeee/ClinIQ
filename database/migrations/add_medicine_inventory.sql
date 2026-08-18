-- Run this if you already have a Clinic database and need medicine inventory reports.
USE Clinic;

CREATE TABLE IF NOT EXISTS Medicines (
    MedicineID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(120) NOT NULL UNIQUE,
    Stock INT UNSIGNED NOT NULL DEFAULT 0,
    Dosage VARCHAR(120),
    Description VARCHAR(255),
    LowStockThreshold INT UNSIGNED NOT NULL DEFAULT 20,
    CreatedByAdminID INT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CreatedByAdminID) REFERENCES Admins(AdminID) ON DELETE SET NULL,
    INDEX idx_medicines_stock (Stock, LowStockThreshold)
);

INSERT IGNORE INTO Medicines (Name, Stock, Dosage, Description, LowStockThreshold, CreatedByAdminID) VALUES
('Paracetamol', 80, '500 mg tablet', 'Used for mild to moderate pain and fever.', 20, 1),
('Ibuprofen', 18, '200 mg tablet', 'Used for pain and inflammation.', 20, 1),
('Cetirizine', 12, '10 mg tablet', 'Allergy relief medication.', 20, 1),
('Bandage', 77, 'Sterile bandage', 'Used to cover and protect minor wounds.', 20, 1),
('Ferrous Sulfate', 67, '30 mg tablet', 'Used to support iron deficiency care.', 20, 1),
('Oral Rehydration Salts', 15, 'One sachet', 'Used to help prevent dehydration.', 20, 1);