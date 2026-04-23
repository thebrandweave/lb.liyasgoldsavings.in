-- 1) New notification channel settings table
CREATE TABLE IF NOT EXISTS NotificationChannelSettings (
    SettingID INT AUTO_INCREMENT PRIMARY KEY,
    IsSMSEnabled TINYINT(1) NOT NULL DEFAULT 1,
    IsWhatsAppEnabled TINYINT(1) NOT NULL DEFAULT 1,
    UpdatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Seed default row if missing
INSERT INTO NotificationChannelSettings (IsSMSEnabled, IsWhatsAppEnabled)
SELECT 1, 1
WHERE NOT EXISTS (SELECT 1 FROM NotificationChannelSettings);

-- 2) Extend WhatsApp API config for Meta Cloud API
ALTER TABLE WhatsAppAPIConfig
    ADD COLUMN IF NOT EXISTS PhoneNumberID VARCHAR(100) NULL AFTER InstanceID,
    ADD COLUMN IF NOT EXISTS DefaultTemplateName VARCHAR(120) NOT NULL DEFAULT 'hello_world' AFTER PhoneNumberID,
    ADD COLUMN IF NOT EXISTS TemplateLanguageCode VARCHAR(20) NOT NULL DEFAULT 'en_US' AFTER DefaultTemplateName;

-- 3) Optional: set defaults for existing row
UPDATE WhatsAppAPIConfig
SET APIProviderName = COALESCE(NULLIF(APIProviderName, ''), 'Meta Cloud API'),
    APIEndpoint = COALESCE(NULLIF(APIEndpoint, ''), 'https://graph.facebook.com/v25.0'),
    DefaultTemplateName = COALESCE(NULLIF(DefaultTemplateName, ''), 'hello_world'),
    TemplateLanguageCode = COALESCE(NULLIF(TemplateLanguageCode, ''), 'en_US');

