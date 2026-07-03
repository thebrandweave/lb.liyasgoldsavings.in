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

UPDATE Customers SET CustomerUniqueID = 'LB1000' WHERE CustomerID = 1;
UPDATE Customers SET CustomerUniqueID = 'LB1001' WHERE CustomerID = 2;
TRUNCATE TABLE CustomerUniqueCounter;
INSERT INTO CustomerUniqueCounter (id) VALUES (1001);
