-- Drop and recreate counter table cleanly
DROP TABLE IF EXISTS CustomerUniqueCounter;
CREATE TABLE CustomerUniqueCounter (
    id INT PRIMARY KEY AUTO_INCREMENT
) AUTO_INCREMENT = 1000;

-- Seed it so first customer gets LB1000
INSERT INTO CustomerUniqueCounter (id) VALUES (999);

-- Drop and recreate trigger
DROP TRIGGER IF EXISTS before_customers_insert;

DELIMITER $$
CREATE TRIGGER before_customers_insert
BEFORE INSERT ON Customers
FOR EACH ROW
BEGIN
    DECLARE newCounter INT;
    INSERT INTO CustomerUniqueCounter () VALUES ();
    SET newCounter = (SELECT MAX(id) FROM CustomerUniqueCounter);
    SET NEW.CustomerUniqueID = CONCAT('LB', newCounter);
END$$
DELIMITER ;

-- Fix existing customers
UPDATE Customers SET CustomerUniqueID = 'LB1000' WHERE CustomerID = 1;
UPDATE Customers SET CustomerUniqueID = 'LB1000' WHERE CustomerID = 2 AND (CustomerUniqueID IS NULL OR CustomerUniqueID = '');

-- Reset CustomerID auto increment
ALTER TABLE Customers AUTO_INCREMENT = 2;
