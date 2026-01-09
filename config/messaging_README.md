# Golden Dream Messaging System

This document describes the unified messaging system that allows switching between SMS and WhatsApp for sending notifications.

## Features

- **Toggle between SMS and WhatsApp**: Admin can switch the default messaging method
- **Airtel SMS API Integration**: Full support for Airtel's SMS API with DLT compliance
- **WhatsApp Integration**: Existing WhatsApp Business API integration
- **Unified Messaging Service**: Single interface for both SMS and WhatsApp
- **Automatic Fallback**: If preferred method fails, automatically tries the other method
- **Bulk Messaging**: Support for sending messages to multiple recipients
- **Golden Dream Branding**: Automatic branding for all messages

## Database Tables

### SMSAPIConfig

Stores SMS API configuration including Airtel credentials.

### MessagingPreference

Stores the preferred messaging method (SMS or WhatsApp).

## Setup Instructions

1. **Run Migration Script**:

   ```sql
   -- Run the migration script
   source config/migrations/add_messaging_tables.sql
   ```

2. **Configure SMS API**:

   - Go to Admin Panel > Settings > SMS Integration
   - Enter your Airtel SMS API credentials:
     - Authorization Token
     - Customer ID
     - Source Address (Sender ID)
     - DLT Entity ID
     - DLT Template ID

3. **Set Messaging Preference**:
   - Go to Admin Panel > Settings
   - Use the toggle switch to select SMS or WhatsApp as default method

## Usage Examples

### Basic Usage

```php
require_once("config/MessagingService.php");

$database = new Database();
$messagingService = new MessagingService($database);

// Send message using preferred method
$messagingService->sendMessage('9876543210', 'Hello from Golden Dream!');

// Send notification with branding
$messagingService->sendNotification('9876543210', 'Payment received!', 'payment');

// Force specific method
$messagingService->sendMessage('9876543210', 'Urgent message', 'SMS');
```

### Bulk Messaging

```php
$phoneNumbers = ['9876543210', '9876543211', '9876543212'];
$message = 'Important announcement from Golden Dream!';

$messagingService->sendBulkMessage($phoneNumbers, $message);
```

### Check Configuration

```php
// Check available methods
$methods = $messagingService->getAvailableMethods();
// Returns: ['SMS', 'WhatsApp'] or ['SMS'] or ['WhatsApp']

// Check if specific method is configured
$smsReady = $messagingService->isSMSConfigured();
$whatsappReady = $messagingService->isWhatsAppConfigured();
```

## Airtel SMS API Configuration

The system uses Airtel's SMS API with the following parameters:

- **Endpoint**: `https://iqsms.airtel.in/api/v1/send-prepaid-sms`
- **Method**: POST
- **Headers**:
  - `accept: application/json`
  - `content-type: application/json`
  - `Authorization: {your_token}`

### Request Format

```json
{
  "customerId": "string",
  "destinationAddress": ["array"],
  "dltTemplateId": "string",
  "entityId": "string",
  "message": "string",
  "messageType": "string",
  "sourceAddress": "string"
}
```

## Message Types

The system supports different message types with automatic branding:

- **general**: Default Golden Dream branding
- **payment**: Payment confirmation messages
- **winner**: Winner announcement messages
- **reminder**: Payment reminder messages

## Error Handling

The system includes comprehensive error handling:

- **Configuration Check**: Verifies API credentials before sending
- **Automatic Fallback**: If preferred method fails, tries the other method
- **Logging**: All API responses and errors are logged
- **Timeout Protection**: 30-second timeout for API calls

## Security

- All API credentials are stored securely in the database
- Authorization tokens are masked in the admin interface
- All API calls use HTTPS
- Input validation for phone numbers and messages

## Troubleshooting

### SMS Not Working

1. Check if SMS API is configured in Admin Panel > Settings > SMS Integration
2. Verify Airtel credentials are correct
3. Check if DLT template is approved
4. Review error logs for specific error messages

### WhatsApp Not Working

1. Check if WhatsApp API is configured in Admin Panel > Settings > WhatsApp Integration
2. Verify WhatsApp Business API credentials
3. Check if instance is active
4. Review error logs for specific error messages

### Messages Not Sending

1. Check the preferred messaging method in settings
2. Verify at least one messaging method is properly configured
3. Check phone number format (should include country code)
4. Review system logs for detailed error information

## Support

For technical support or questions about the messaging system, please contact the development team.
