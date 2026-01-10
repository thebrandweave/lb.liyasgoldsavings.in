-- Create Popups Table
-- This table stores popup images that are shown to promoters
-- Only one popup can be active at a time

CREATE TABLE IF NOT EXISTS Popups (
    PopupID INT AUTO_INCREMENT PRIMARY KEY,
    ImageURL VARCHAR(255) NOT NULL,
    IsActive BOOLEAN DEFAULT FALSE,
    CreatedBy INT NOT NULL,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (CreatedBy) REFERENCES Admins(AdminID) ON DELETE RESTRICT
);

-- Note: We cannot use triggers to enforce single active popup due to MySQL limitation
-- (Error: Can't update table 'Popups' in stored function/trigger because it is already 
--  used by statement which invoked this stored function/trigger)
-- 
-- Instead, this logic is handled in the PHP application code (admin/popups/index.php).
-- The PHP code will deactivate all other popups BEFORE inserting/updating a new active popup.

-- Drop any existing triggers that might have been created earlier (if any exist)
DROP TRIGGER IF EXISTS before_popups_insert;
DROP TRIGGER IF EXISTS before_popups_update;

