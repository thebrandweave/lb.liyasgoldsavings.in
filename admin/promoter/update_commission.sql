-- Update commission to 850 for specific promoters
-- Based on PromoterUniqueID

UPDATE Promoters 
SET Commission = '850', UpdatedAt = NOW()
WHERE PromoterUniqueID IN (
    'GD011551',  -- ASHOK T D
    'GD012157',  -- THOUSIF
    'GD011521',  -- B KRISHNAVENI
    'GD012169',  -- MUNEER
    'GD011516',  -- MANU
    'GD012194',  -- Prasad Shetty
    'GD012071'   -- Manjunath swami
);

-- Verify the update
SELECT PromoterUniqueID, Name, Commission, UpdatedAt 
FROM Promoters 
WHERE PromoterUniqueID IN (
    'GD011551',
    'GD012157',
    'GD011521',
    'GD012169',
    'GD011516',
    'GD012194',
    'GD012071'
)
ORDER BY PromoterUniqueID;

