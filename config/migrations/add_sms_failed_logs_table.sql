-- Migration script for creating SMSFailedLogs table
-- Run this script to add table for tracking failed SMS attempts and WhatsApp auto-resends

CREATE TABLE IF NOT EXISTS SMSFailedLogs (
    LogID INT AUTO_INCREMENT PRIMARY KEY,
    MessageID VARCHAR(255) DEFAULT NULL,
    Phone VARCHAR(50) NOT NULL,
    CustomerName VARCHAR(150) DEFAULT NULL,
    MessageType VARCHAR(50) NOT NULL DEFAULT 'general',
    MessageContent TEXT DEFAULT NULL,
    StatusError VARCHAR(100) NOT NULL,
    ResentViaWhatsApp TINYINT(1) DEFAULT 0,
    ResentViaSMS TINYINT(1) DEFAULT 0,
    ResentStatus VARCHAR(150) DEFAULT 'Pending',
    CreatedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (Phone),
    INDEX idx_message_type (MessageType),
    INDEX idx_status_error (StatusError),
    INDEX idx_created_at (CreatedAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
