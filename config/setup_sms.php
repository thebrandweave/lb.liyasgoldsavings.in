<?php
/**
 * SMS Setup Script
 * Run this script to set up the SMS API with the provided Airtel credentials
 */

require_once("config.php");

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    echo "Setting up SMS API configuration...\n";
    
    // Check if SMSAPIConfig table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'SMSAPIConfig'");
    if ($stmt->rowCount() == 0) {
        echo "Creating SMSAPIConfig table...\n";
        
        $createTable = "
        CREATE TABLE SMSAPIConfig (
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
        )";
        
        $conn->exec($createTable);
        echo "SMSAPIConfig table created successfully!\n";
    }
    
    // Check if MessagingPreference table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'MessagingPreference'");
    if ($stmt->rowCount() == 0) {
        echo "Creating MessagingPreference table...\n";
        
        $createTable = "
        CREATE TABLE MessagingPreference (
            PreferenceID INT AUTO_INCREMENT PRIMARY KEY,
            PreferredMethod ENUM('SMS', 'WhatsApp') DEFAULT 'WhatsApp',
            UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $conn->exec($createTable);
        echo "MessagingPreference table created successfully!\n";
    }
    
    // Insert default SMS configuration
    $stmt = $conn->prepare("
        INSERT INTO SMSAPIConfig (
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
        )
        ON DUPLICATE KEY UPDATE
        Username = VALUES(Username),
        Password = VALUES(Password),
        CustomerID = VALUES(CustomerID),
        SourceAddress = VALUES(SourceAddress),
        DLTEntityID = VALUES(DLTEntityID),
        DLTTemplateID = VALUES(DLTTemplateID),
        MessageType = VALUES(MessageType),
        MessageTemplate = VALUES(MessageTemplate),
        Status = VALUES(Status)
    ");
    
    $stmt->execute();
    echo "SMS API configuration inserted/updated successfully!\n";
    
    // Insert default messaging preference
    $stmt = $conn->prepare("
        INSERT IGNORE INTO MessagingPreference (PreferredMethod) VALUES ('WhatsApp')
    ");
    $stmt->execute();
    echo "Default messaging preference set to WhatsApp!\n";
    
    echo "\nSetup completed successfully!\n";
    echo "You can now:\n";
    echo "1. Go to Admin Panel > Settings to configure messaging preferences\n";
    echo "2. Go to Admin Panel > Settings > SMS Integration to test SMS\n";
    echo "3. Use the MessagingService in your code to send messages\n";
    
} catch (Exception $e) {
    echo "Error during setup: " . $e->getMessage() . "\n";
}
?>
