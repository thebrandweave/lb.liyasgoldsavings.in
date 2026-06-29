<?php
require_once __DIR__ . '/../config/config.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "Connected to database successfully.\n";

    // 1. Create CareerOpenings table
    $sql1 = "CREATE TABLE IF NOT EXISTS CareerOpenings (
        OpeningID INT AUTO_INCREMENT PRIMARY KEY,
        Title VARCHAR(255) NOT NULL,
        Location VARCHAR(255) NOT NULL,
        Type VARCHAR(100) NOT NULL,
        Description TEXT NOT NULL,
        Requirements TEXT NOT NULL,
        Status ENUM('Active', 'Inactive') DEFAULT 'Active',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $db->exec($sql1);
    echo "Table 'CareerOpenings' created successfully.\n";

    // 2. Create CareerApplications table
    $sql2 = "CREATE TABLE IF NOT EXISTS CareerApplications (
        ApplicationID INT AUTO_INCREMENT PRIMARY KEY,
        OpeningID INT NULL,
        FullName VARCHAR(255) NOT NULL,
        Email VARCHAR(255) NOT NULL,
        Phone VARCHAR(100) NOT NULL,
        Position VARCHAR(255) NOT NULL,
        CoverLetter TEXT NOT NULL,
        ResumeURL VARCHAR(255) NOT NULL,
        Status ENUM('Pending', 'Reviewed', 'Accepted', 'Rejected') DEFAULT 'Pending',
        CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (OpeningID) REFERENCES CareerOpenings(OpeningID) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $db->exec($sql2);
    echo "Table 'CareerApplications' created successfully.\n";

    // 3. Populate default openings if table is empty
    $checkOpenings = $db->query("SELECT COUNT(*) FROM CareerOpenings")->fetchColumn();
    if ($checkOpenings == 0) {
        $insertStmt = $db->prepare("INSERT INTO CareerOpenings (Title, Location, Type, Description, Requirements) VALUES (?, ?, ?, ?, ?)");
        
        $insertStmt->execute([
            'Financial Advisor / Wealth Manager',
            'Bantwal, Karnataka (On-site)',
            'Full-time',
            'Develop tailored financial strategies, onboard high-net-worth investors, and build strong relationship pipelines.',
            'Bachelor\'s degree in Finance/Business, 2+ years of wealth management experience, strong communication skills.'
        ]);
        
        $insertStmt->execute([
            'Business Development Associate',
            'Remote (India)',
            'Full-time / Commission',
            'Identify market expansion opportunities, manage regional promoter hierarchies, and drive investment volume targets.',
            '1+ years experience in business development or sales, experience in marketing / promoter networks, self-motivated.'
        ]);

        $insertStmt->execute([
            'Customer Relationship Executive',
            'Bantwal, Karnataka (On-site)',
            'Full-time',
            'Resolve investor support queries, handle documentation audits, and guide customers through schemes and withdrawals.',
            'Strong interpersonal skills, fluent in local languages, basic computer operations and spreadsheet management.'
        ]);
        
        echo "Default career openings populated.\n";
    }

} catch (PDOException $e) {
    die("Error setting up database: " . $e->getMessage() . "\n");
}
?>
