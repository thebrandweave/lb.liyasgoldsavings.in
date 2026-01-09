# CustomerUniqueID Automatic Generation - MySQL Trigger Implementation

## Overview

This implementation replaces manual CustomerUniqueID generation with a MySQL BEFORE INSERT trigger that automatically generates unique IDs in the format `GDC0XXXX` where XXXX is a sequential number starting from 1000. The system supports numbers above 10000 without padding issues.

## Key Features

- **Automatic Generation**: CustomerUniqueID is generated automatically by MySQL trigger
- **Sequential Numbers**: Starts from 1000 and increments automatically
- **No Padding Issues**: Works correctly with numbers above 10000 (GDC010000, GDC0100000, etc.)
- **Thread Safe**: Uses MySQL's AUTO_INCREMENT for race condition prevention
- **Backward Compatible**: Existing code continues to work without changes

## Files Modified

### 1. Database Schema Changes

- **File**: `config/requirements/customer_unique_id_trigger.sql`
- **Purpose**: Contains the SQL script to implement the trigger

### 2. PHP Files Updated

- **File**: `refer/index.php` - Registration form (already updated)
- **File**: `admin/customers/add.php` - Admin customer creation (already updated)
- **File**: `promoter/Customers/add.php` - Promoter customer creation (already updated)
- **File**: `customer/signup/process_signup.php` - Customer self-registration (already updated)

## Implementation Steps

### Step 1: Execute the SQL Script

Run the following SQL commands in your MySQL database:

```sql
-- Make CustomerUniqueID nullable
ALTER TABLE Customers MODIFY CustomerUniqueID VARCHAR(50) NULL;

-- Create counter table for custom sequence
CREATE TABLE IF NOT EXISTS CustomerUniqueCounter (
    id INT PRIMARY KEY AUTO_INCREMENT
) AUTO_INCREMENT = 1000;

-- Create the BEFORE INSERT trigger
DELIMITER $$
CREATE TRIGGER before_customers_insert
BEFORE INSERT ON Customers
FOR EACH ROW
BEGIN
    DECLARE newCounter INT;

    -- Insert a dummy row into the counter table to get the next value
    INSERT INTO CustomerUniqueCounter () VALUES ();
    SET newCounter = LAST_INSERT_ID();

    -- Set the custom unique ID (no padding needed, works with any number)
    SET NEW.CustomerUniqueID = CONCAT('GDC0', newCounter);
END$$
DELIMITER ;
```

### Step 2: For mp_customers Table (if needed)

If you have an `mp_customers` table that also needs this functionality:

```sql
-- Make CustomerUniqueID nullable for mp_customers
ALTER TABLE mp_customers MODIFY CustomerUniqueID VARCHAR(50) NULL;

-- Create counter table for mp_customers
CREATE TABLE IF NOT EXISTS MPCustomerUniqueCounter (
    id INT PRIMARY KEY AUTO_INCREMENT
) AUTO_INCREMENT = 1000;

-- Create the BEFORE INSERT trigger for mp_customers
DELIMITER $$
CREATE TRIGGER before_mp_customers_insert
BEFORE INSERT ON mp_customers
FOR EACH ROW
BEGIN
    DECLARE newCounter INT;

    -- Insert a dummy row into the counter table to get the next value
    INSERT INTO MPCustomerUniqueCounter () VALUES ();
    SET newCounter = LAST_INSERT_ID();

    -- Set the custom unique ID (no padding needed, works with any number)
    SET NEW.CustomerUniqueID = CONCAT('GDC0', newCounter);
END$$
DELIMITER ;
```

## How It Works

### Before (Manual Generation)

```php
// Old way - manual generation with potential race conditions
function generateUniqueID($conn, $registrationType, $maxRetries = 10) {
    // Complex logic with retries and race condition handling
    $stmt = $conn->prepare("SELECT MAX(CustomerID) as max_id FROM Customers");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $nextCustomerID = ($result['max_id'] ?? 0) + 1;
    $uniqueID = 'GDC0'.$nextCustomerID;
    // ... more complex logic
}
```

### After (Automatic Trigger)

```php
// New way - automatic generation by MySQL trigger
$stmt = $conn->prepare("
    INSERT INTO Customers (Name, Contact, PasswordHash, Status)
    VALUES (?, ?, ?, 'Active')
");
$stmt->execute([$name, $contact, $passwordHash]);

// Get the auto-generated CustomerUniqueID
$stmt = $conn->prepare("SELECT CustomerUniqueID FROM Customers WHERE CustomerID = ?");
$stmt->execute([$conn->lastInsertId()]);
$customerData = $stmt->fetch(PDO::FETCH_ASSOC);
$customerUniqueID = $customerData['CustomerUniqueID'];
```

## Number Format Examples

The trigger generates IDs in this format:

- Customer #1000: `GDC01000`
- Customer #10000: `GDC010000`
- Customer #100000: `GDC0100000`
- Customer #999999: `GDC0999999`

No padding is needed because we're using the actual counter value directly.

## Benefits

1. **Performance**: No need for complex PHP logic or database queries to generate IDs
2. **Reliability**: MySQL handles the sequence, eliminating race conditions
3. **Simplicity**: PHP code is cleaner and easier to maintain
4. **Scalability**: Works with any number of customers without padding issues
5. **Consistency**: All customer registrations use the same ID generation method

## Testing

To test the trigger implementation:

```sql
-- Test customer insertion
INSERT INTO Customers (Name, Contact, PasswordHash, Status)
VALUES ('Test User', '1234567890', '$2y$10$test', 'Active');

-- Check the generated ID
SELECT CustomerID, CustomerUniqueID, Name FROM Customers WHERE Name = 'Test User';

-- Check the counter
SELECT * FROM CustomerUniqueCounter ORDER BY id DESC LIMIT 5;
```

## Migration Notes

- Existing customers with manually generated IDs will continue to work
- New customers will use the automatic trigger system
- No data migration is required
- The system is backward compatible

## Troubleshooting

### If trigger doesn't work:

1. Check if the trigger exists: `SHOW TRIGGERS LIKE 'Customers';`
2. Verify counter table exists: `DESCRIBE CustomerUniqueCounter;`
3. Check for errors: `SHOW ERRORS;`

### If you need to reset the counter:

```sql
-- Reset counter to start from 1000 again
TRUNCATE TABLE CustomerUniqueCounter;
ALTER TABLE CustomerUniqueCounter AUTO_INCREMENT = 1000;
```

### If you need to drop the trigger:

```sql
DROP TRIGGER IF EXISTS before_customers_insert;
```

## Security Considerations

- The trigger runs with the same privileges as the user executing the INSERT
- No additional security risks introduced
- Counter table is internal and not exposed to users
- IDs are predictable but this is acceptable for customer IDs
