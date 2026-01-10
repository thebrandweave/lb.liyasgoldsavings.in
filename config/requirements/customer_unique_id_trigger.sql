-- MySQL BEFORE INSERT Trigger for Automatic CustomerUniqueID Generation
-- This script implements automatic generation of CustomerUniqueID using triggers
-- Supports numbers above 10000 with proper padding

-- Step 1: Make CustomerUniqueID nullable
ALTER TABLE Customers MODIFY CustomerUniqueID VARCHAR(50) NULL;

-- Step 2: Create counter table for custom sequence
CREATE TABLE IF NOT EXISTS CustomerUniqueCounter (
    id INT PRIMARY KEY AUTO_INCREMENT
) AUTO_INCREMENT = 1000;

-- Step 3: Drop existing trigger if it exists
DROP TRIGGER IF EXISTS before_customers_insert;

-- Step 4: Create the BEFORE INSERT trigger
DELIMITER $$
CREATE TRIGGER before_customers_insert
BEFORE INSERT ON Customers
FOR EACH ROW
BEGIN
    DECLARE newCounter INT;

    -- Insert a dummy row into the counter table to get the next value
    INSERT INTO CustomerUniqueCounter () VALUES ();
    SET newCounter = LAST_INSERT_ID();

    -- Set the custom unique ID with proper padding for numbers above 10000
    -- This will handle: 1000->GDC01000, 10000->GDC010000, 100000->GDC0100000, etc.
    SET NEW.CustomerUniqueID = CONCAT('LA0', newCounter);
END$$
DELIMITER ;

