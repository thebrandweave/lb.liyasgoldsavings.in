-- Migration script for receiving and storing incoming WhatsApp replies (text, image, documents, etc.)

CREATE TABLE IF NOT EXISTS WhatsAppReplies (
    ReplyID INT AUTO_INCREMENT PRIMARY KEY,
    MessageID VARCHAR(255) UNIQUE,
    SenderPhone VARCHAR(50) NOT NULL,
    SenderName VARCHAR(150) DEFAULT NULL,
    MessageType VARCHAR(50) NOT NULL DEFAULT 'text',
    MessageBody TEXT DEFAULT NULL,
    MediaID VARCHAR(255) DEFAULT NULL,
    MediaMimeType VARCHAR(100) DEFAULT NULL,
    LocalFilePath VARCHAR(255) DEFAULT NULL,
    RawPayload LONGTEXT DEFAULT NULL,
    Status VARCHAR(50) DEFAULT 'Unread',
    ReceivedAt DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sender_phone (SenderPhone),
    INDEX idx_message_type (MessageType),
    INDEX idx_received_at (ReceivedAt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add VerifyToken to WhatsAppAPIConfig if missing
SET @dbname = DATABASE();
SET @tablename = "WhatsAppAPIConfig";
SET @columnname = "VerifyToken";
SET @preparedStatement = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE
            TABLE_SCHEMA = @dbname
            AND TABLE_NAME = @tablename
            AND COLUMN_NAME = @columnname
    ) > 0,
    "SELECT 1",
    "ALTER TABLE WhatsAppAPIConfig ADD COLUMN VerifyToken VARCHAR(255) DEFAULT 'liyas_whatsapp_verify_token_2026';"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
