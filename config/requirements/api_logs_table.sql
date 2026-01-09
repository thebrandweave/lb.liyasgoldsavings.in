-- API Logs Table for Customer Login Verification API
-- This table tracks all API login attempts for security monitoring

CREATE TABLE IF NOT EXISTS `ApiLogs` (
  `LogID` int(11) NOT NULL AUTO_INCREMENT,
  `CustomerID` int(11) DEFAULT NULL,
  `Email` varchar(255) NOT NULL,
  `IPAddress` varchar(45) NOT NULL,
  `Success` tinyint(1) NOT NULL DEFAULT 0,
  `UserAgent` text,
  `CreatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`LogID`),
  KEY `idx_customer_id` (`CustomerID`),
  KEY `idx_email` (`Email`),
  KEY `idx_ip_address` (`IPAddress`),
  KEY `idx_success` (`Success`),
  KEY `idx_created_at` (`CreatedAt`),
  FOREIGN KEY (`CustomerID`) REFERENCES `Customers`(`CustomerID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add index for better performance on date range queries
CREATE INDEX `idx_created_at_range` ON `ApiLogs` (`CreatedAt`, `Success`);

-- Add index for security monitoring (failed attempts by IP)
CREATE INDEX `idx_failed_attempts_ip` ON `ApiLogs` (`IPAddress`, `Success`, `CreatedAt`);
