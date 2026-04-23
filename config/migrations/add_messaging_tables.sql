    -- Migration script to add SMS and messaging preference tables
    -- Run this script to add the new tables to your existing database

    -- SMS API Configuration Table
    CREATE TABLE IF NOT EXISTS SMSAPIConfig (
        ConfigID INT AUTO_INCREMENT PRIMARY KEY,
        APIProviderName VARCHAR(100) NOT NULL,
        APIEndpoint VARCHAR(255) NOT NULL,
        Username VARCHAR(255) NOT NULL,
        Password VARCHAR(255) NOT NULL,
        CustomerID VARCHAR(100) NOT NULL,
        SourceAddress VARCHAR(20) NOT NULL,
        DLTEntityID VARCHAR(100) NOT NULL,
        DLTTemplateID VARCHAR(100) NOT NULL,
        MessageType VARCHAR(20) DEFAULT 'SERVICE_EXPLICIT',
        MessageTemplate TEXT,
        Status VARCHAR(20) DEFAULT 'Active',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    -- Messaging Preference Table (Toggle between SMS and WhatsApp)
    CREATE TABLE IF NOT EXISTS MessagingPreference (
        PreferenceID INT AUTO_INCREMENT PRIMARY KEY,
        PreferredMethod ENUM('SMS', 'WhatsApp') DEFAULT 'WhatsApp',
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    -- Notification Channel Settings (supports SMS, WhatsApp, or both)
    CREATE TABLE IF NOT EXISTS NotificationChannelSettings (
        SettingID INT AUTO_INCREMENT PRIMARY KEY,
        IsSMSEnabled TINYINT(1) NOT NULL DEFAULT 1,
        IsWhatsAppEnabled TINYINT(1) NOT NULL DEFAULT 1,
        UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    );

    -- Insert default messaging preference
    INSERT IGNORE INTO MessagingPreference (PreferredMethod) VALUES ('WhatsApp');

    -- Insert default channel settings
    INSERT IGNORE INTO NotificationChannelSettings (SettingID, IsSMSEnabled, IsWhatsAppEnabled) VALUES (1, 1, 1);

    -- Insert default SMS configuration with actual Airtel credentials
    INSERT IGNORE INTO SMSAPIConfig (
        APIProviderName, 
        APIEndpoint, 
        Username, 
        Password, 
        CustomerID, 
        SourceAddress, 
        DLTEntityID, 
        DLTTemplateID, 
        MessageType, 
        MessageTemplate,
        Status
    ) VALUES (
        'Airtel',
        'https://iqsms.airtel.in/api/v1/send-prepaid-sms',
        'f8758d62_260c_404b_8d4a_a15ce94593d4',
        'jx0NgVQjBT',
        '72c5ff0d-4624-4972-bc1c-dcef261dd7f7',
        'PRGDVN',
        '1001791223662244844',
        '1007000046423973167',
        'SERVICE_EXPLICIT',
        'Dear {var1}, Thank you for choosing PROGEEDEE Ventures Private Limited Golden Dream Savings Plan. We have received your payment of Rs {var2}.',
        'Active'
    );
