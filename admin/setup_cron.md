# WhatsApp Queue Background Processor Setup

## Overview

The WhatsApp queue processor handles sending WhatsApp messages in the background to prevent blocking the registration process.

## Setup Instructions

### 1. Create the WhatsApp Queue Table

Run the updated schema.sql to create the WhatsAppQueue table.

### 2. Set up Cron Job

Add this cron job to run every minute:

```bash
# Edit crontab
crontab -e

# Add this line (replace with your actual path)
* * * * * php /path/to/your/project/admin/process_whatsapp_queue.php
```

### 3. Alternative: Manual Processing

You can also manually process the queue by visiting:

```
https://yourdomain.com/admin/process_whatsapp_queue.php
```

## How it Works

1. When a user registers, WhatsApp messages are queued instead of sent immediately
2. The cron job processes pending messages every minute
3. Messages are retried up to 3 times if they fail
4. Failed messages are marked as 'Failed' after max attempts

## Benefits

- **Faster Registration**: No waiting for WhatsApp API responses
- **Reliability**: Messages are retried if they fail
- **Scalability**: Can handle high registration volumes
- **Monitoring**: Track message status in the database

## Database Tables

- `WhatsAppQueue`: Stores queued messages
- `WhatsAppAPIConfig`: Stores API configuration

## Monitoring

Check the queue status:

```sql
SELECT Status, COUNT(*) as count FROM WhatsAppQueue GROUP BY Status;
```
