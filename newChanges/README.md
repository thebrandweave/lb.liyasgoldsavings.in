# Notification Channel + Meta Cloud API Changes

This update adds:

1. Admin toggle for notification channels (`SMS`, `WhatsApp`, or both)
2. Centralized notification delivery service
3. Meta WhatsApp Cloud API integration (`/messages` template API)

## What was added

- `admin/settings/notifications/index.php`
  - New admin settings page to toggle channels:
    - `IsSMSEnabled`
    - `IsWhatsAppEnabled`

- `config/NotificationService.php`
  - Central service to send notifications according to enabled channels.
  - If both enabled -> sends both.
  - If only one enabled -> sends only that channel.

- `config/WhatsAppMetaAPI.php`
  - Meta Cloud API implementation:
  - `POST {APIEndpoint}/{PhoneNumberID}/messages`
  - Uses Bearer token from `WhatsAppAPIConfig.AccessToken`
  - Sends template messages.

## Updated files (core)

- `admin/settings/index.php` (added Notification Channels + WhatsApp settings cards)
- `admin/settings/whatsapp/index.php` (added Meta Cloud fields)
- `admin/wallets/index.php` (now uses NotificationService)
- `admin/withdrawals/index.php` (now uses NotificationService)
- `admin/Pending/send_reminders.php` (now uses NotificationService)
- `admin/payments/index.php` (now uses NotificationService for verify/reject)
- `customer/signup/process_signup.php` (welcome notification via NotificationService)
- `refer/index.php` (registration notifications via NotificationService)
- `config/requirements/schema.sql` (new table + WhatsApp config fields)
- `config/migrations/add_notification_channel_settings.sql` (migration SQL)

## Meta Cloud fields required in WhatsApp settings

- API Endpoint: `https://graph.facebook.com/v25.0`
- Access Token: permanent/long-lived token
- Phone Number ID: WhatsApp business phone number id
- Default Template Name: e.g. `hello_world`
- Template Language Code: e.g. `en_US`

## Behavior

- SMS ON + WhatsApp OFF -> only SMS
- SMS OFF + WhatsApp ON -> only WhatsApp
- SMS ON + WhatsApp ON -> both channels
- Both OFF -> blocked in UI validation (at least one required)

