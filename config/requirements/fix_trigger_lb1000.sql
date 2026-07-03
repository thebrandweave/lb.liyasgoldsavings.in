-- Fix CustomerUniqueID trigger to use LB prefix starting from 1000
-- Drop existing trigger
DROP TRIGGER IF EXISTS before_customers_insert;

-- Create the new trigger with LB prefix
DELIMITER $$
CREATE TRIGGER before_customers_insert
BEFORE INSERT ON Customers
FOR EACH ROW
BEGIN
    DECLARE newCounter INT;

    -- Insert a dummy row into the counter table to get the next value
    INSERT INTO CustomerUniqueCounter () VALUES ();
    SET newCounter = LAST_INSERT_ID();

    -- Set the unique ID: LB1000, LB1001, LB1002, ...
    SET NEW.CustomerUniqueID = CONCAT('LB', newCounter);
END$$
DELIMITER ;
