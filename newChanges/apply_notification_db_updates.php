<?php
/**
 * Direct DB updater for notification channel + Meta WhatsApp fields.
 *
 * Usage (CLI):
 *   php newChanges/apply_notification_db_updates.php
 *
 * Usage (Browser):
 *   https://your-domain/newChanges/apply_notification_db_updates.php
 */

require_once(__DIR__ . "/../config/config.php");

header('Content-Type: text/plain');

function out($message)
{
    echo $message . PHP_EOL;
}

function tableExists(PDO $conn, $tableName)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS cnt
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
    ");
    $stmt->execute([$tableName]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return !empty($row['cnt']);
}

function columnExists(PDO $conn, $tableName, $columnName)
{
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS cnt
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = ?
          AND COLUMN_NAME = ?
    ");
    $stmt->execute([$tableName, $columnName]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return !empty($row['cnt']);
}

try {
    $database = new Database();
    $conn = $database->getConnection();

    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    out("Connected to DB successfully.");
    out("Applying notification and WhatsApp schema updates...");

    // 1) Create NotificationChannelSettings table (if missing)
    if (!tableExists($conn, 'NotificationChannelSettings')) {
        $conn->exec("
            CREATE TABLE NotificationChannelSettings (
                SettingID INT AUTO_INCREMENT PRIMARY KEY,
                IsSMSEnabled TINYINT(1) NOT NULL DEFAULT 1,
                IsWhatsAppEnabled TINYINT(1) NOT NULL DEFAULT 1,
                UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        out("Created table: NotificationChannelSettings");
    } else {
        out("Table already exists: NotificationChannelSettings");
    }

    // Seed one default row if table is empty
    $stmt = $conn->query("SELECT COUNT(*) AS cnt FROM NotificationChannelSettings");
    $countRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if ((int)$countRow['cnt'] === 0) {
        $conn->exec("INSERT INTO NotificationChannelSettings (IsSMSEnabled, IsWhatsAppEnabled) VALUES (1, 1)");
        out("Inserted default row into NotificationChannelSettings");
    } else {
        out("Default row already present in NotificationChannelSettings");
    }

    // 2) Ensure WhatsAppAPIConfig table exists
    if (!tableExists($conn, 'WhatsAppAPIConfig')) {
        $conn->exec("
            CREATE TABLE WhatsAppAPIConfig (
                ConfigID INT AUTO_INCREMENT PRIMARY KEY,
                APIProviderName VARCHAR(100),
                APIEndpoint VARCHAR(255),
                AccessToken TEXT,
                Token VARCHAR(255),
                InstanceID VARCHAR(100),
                PhoneNumberID VARCHAR(100),
                DefaultTemplateName VARCHAR(120) DEFAULT 'hello_world',
                TemplateLanguageCode VARCHAR(20) DEFAULT 'en_US',
                Status VARCHAR(20) DEFAULT 'Active',
                CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        out("Created table: WhatsAppAPIConfig");
    } else {
        out("Table already exists: WhatsAppAPIConfig");
    }

    // 3) Add missing columns safely
    if (!columnExists($conn, 'WhatsAppAPIConfig', 'PhoneNumberID')) {
        $conn->exec("ALTER TABLE WhatsAppAPIConfig ADD COLUMN PhoneNumberID VARCHAR(100) NULL AFTER InstanceID");
        out("Added column: WhatsAppAPIConfig.PhoneNumberID");
    } else {
        out("Column already exists: WhatsAppAPIConfig.PhoneNumberID");
    }

    if (!columnExists($conn, 'WhatsAppAPIConfig', 'DefaultTemplateName')) {
        $conn->exec("ALTER TABLE WhatsAppAPIConfig ADD COLUMN DefaultTemplateName VARCHAR(120) NOT NULL DEFAULT 'hello_world' AFTER PhoneNumberID");
        out("Added column: WhatsAppAPIConfig.DefaultTemplateName");
    } else {
        out("Column already exists: WhatsAppAPIConfig.DefaultTemplateName");
    }

    if (!columnExists($conn, 'WhatsAppAPIConfig', 'TemplateLanguageCode')) {
        $conn->exec("ALTER TABLE WhatsAppAPIConfig ADD COLUMN TemplateLanguageCode VARCHAR(20) NOT NULL DEFAULT 'en_US' AFTER DefaultTemplateName");
        out("Added column: WhatsAppAPIConfig.TemplateLanguageCode");
    } else {
        out("Column already exists: WhatsAppAPIConfig.TemplateLanguageCode");
    }

    // 4) Ensure at least one WhatsApp config row exists
    $stmt = $conn->query("SELECT COUNT(*) AS cnt FROM WhatsAppAPIConfig");
    $waCountRow = $stmt->fetch(PDO::FETCH_ASSOC);
    if ((int)$waCountRow['cnt'] === 0) {
        $conn->exec("
            INSERT INTO WhatsAppAPIConfig
            (APIProviderName, APIEndpoint, AccessToken, Token, InstanceID, PhoneNumberID, DefaultTemplateName, TemplateLanguageCode, Status)
            VALUES
            ('Meta Cloud API', 'https://graph.facebook.com/v25.0', '', '', '', '', 'hello_world', 'en_US', 'Active')
        ");
        out("Inserted default row into WhatsAppAPIConfig");
    } else {
        out("WhatsAppAPIConfig already has data; skipped default insert");
    }

    out("All required DB updates completed successfully.");
} catch (Exception $e) {
    http_response_code(500);
    out("DB update failed: " . $e->getMessage());
    exit(1);
}

