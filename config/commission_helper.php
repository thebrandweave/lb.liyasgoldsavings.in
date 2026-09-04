<?php
// config/commission_helper.php

if (!function_exists('convertCommissionToInt')) {
    function convertCommissionToInt($commission)
    {
        return intval(preg_replace('/[^0-9]/', '', (string)$commission));
    }
}

function processPromoterCommission($customerUniqueID, $conn)
{
    if (empty($customerUniqueID)) {
        return ['success' => false, 'credited' => []];
    }

    $creditedSummary = [];

    try {
        // Fetch customer details and latest payment
        $stmt = $conn->prepare("
            SELECT c.*, s.SchemeName, p.Amount 
            FROM Customers c 
            LEFT JOIN Payments p ON c.CustomerID = p.CustomerID 
            LEFT JOIN Schemes s ON p.SchemeID = s.SchemeID 
            WHERE c.CustomerUniqueID = ? 
            ORDER BY p.SubmittedAt DESC LIMIT 1
        ");
        $stmt->execute([$customerUniqueID]);
        $customerDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customerDetails || empty($customerDetails['PromoterID'])) {
            return ['success' => false, 'credited' => []];
        }

        $directPromoterRef = trim($customerDetails['PromoterID']);
        $schemeName = !empty($customerDetails['SchemeName']) ? $customerDetails['SchemeName'] : 'Gold Savings Plan';
        $custName = $customerDetails['Name'];

        // Pre-load all promoters to build multi-level hierarchy matching both string and numeric IDs
        $pStmt = $conn->prepare("SELECT PromoterID, PromoterUniqueID, ParentPromoterID, Commission, ParentCommission, Name FROM Promoters");
        $pStmt->execute();
        $allPromoters = $pStmt->fetchAll(PDO::FETCH_ASSOC);

        $promoterByRef = [];
        foreach ($allPromoters as $p) {
            $pID = (string)$p['PromoterID'];
            $uID = trim($p['PromoterUniqueID']);
            if (!empty($uID)) {
                $promoterByRef[$uID] = $p;
            }
            if (!empty($pID)) {
                $promoterByRef[$pID] = $p;
            }
        }

        // Build hierarchy chain: Direct (index 0) -> Parent (index 1) -> Grandparent (index 2) ...
        $hierarchy = [];
        $currRef = $directPromoterRef;
        $visited = [];

        while (!empty($currRef) && !isset($visited[$currRef])) {
            $visited[$currRef] = true;
            if (!isset($promoterByRef[$currRef])) {
                break;
            }
            $pData = $promoterByRef[$currRef];
            $hierarchy[] = $pData;
            $currRef = !empty($pData['ParentPromoterID']) ? trim($pData['ParentPromoterID']) : null;
        }

        if (empty($hierarchy)) {
            return ['success' => false, 'credited' => []];
        }

        // 1. Process Direct Promoter (Index 0)
        $directPromoter = $hierarchy[0];
        $directID = trim($directPromoter['PromoterUniqueID']);
        $directNumID = (string)$directPromoter['PromoterID'];
        $directCommission = convertCommissionToInt($directPromoter['Commission']);

        if ($directCommission > 0) {
            $checkStmt = $conn->prepare("
                SELECT COUNT(*) as already_credited 
                FROM WalletLogs 
                WHERE (TRIM(PromoterUniqueID) = ? OR TRIM(PromoterUniqueID) = ?) 
                  AND (Message LIKE ? OR Message LIKE ?)
                  AND (TransactionType = 'Credit' OR TransactionType IS NULL OR TransactionType = '')
            ");
            $checkStmt->execute([
                $directID,
                $directNumID,
                "%" . $customerUniqueID . "%",
                "%" . $custName . "%"
            ]);

            if ($checkStmt->fetch(PDO::FETCH_ASSOC)['already_credited'] == 0) {
                $wStmt = $conn->prepare("SELECT BalanceID FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");
                $wStmt->execute([$directID, $directNumID]);
                $wRecord = $wStmt->fetch(PDO::FETCH_ASSOC);

                if ($wRecord) {
                    $uStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = BalanceAmount + ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");
                    $uStmt->execute([$directCommission, $directID, $directNumID]);
                } else {
                    $inStmt = $conn->prepare("INSERT INTO PromoterWallet (UserID, PromoterUniqueID, BalanceAmount, Message) VALUES (?, ?, ?, 'Commission from payment')");
                    $inStmt->execute([$directPromoter['PromoterID'], $directID, $directCommission]);
                }

                $logMsg = "Commission earned from customer " . $custName . " (" . $customerUniqueID . ") for " . $schemeName . " scheme";
                $lStmt = $conn->prepare("INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message, TransactionType) VALUES (?, ?, ?, 'Credit')");
                $lStmt->execute([$directID, $directCommission, $logMsg]);

                $creditedSummary[] = [
                    'role' => 'Direct Promoter',
                    'name' => $directPromoter['Name'],
                    'id' => $directID,
                    'amount' => $directCommission
                ];
            }
        }

        // 2. Process All Multi-level Parent Promoters in Hierarchy
        for ($i = 0; $i < count($hierarchy) - 1; $i++) {
            $childPromoter = $hierarchy[$i];
            $parentPromoter = $hierarchy[$i + 1];

            $parentID = trim($parentPromoter['PromoterUniqueID']);
            $parentNumID = (string)$parentPromoter['PromoterID'];

            $childCommission = convertCommissionToInt($childPromoter['Commission']);
            $parentCommission = convertCommissionToInt($parentPromoter['Commission']);

            $gapAmount = 0;
            if (!empty($childPromoter['ParentCommission']) && convertCommissionToInt($childPromoter['ParentCommission']) > 0) {
                $gapAmount = convertCommissionToInt($childPromoter['ParentCommission']);
            } else if ($parentCommission > $childCommission) {
                $gapAmount = $parentCommission - $childCommission;
            }

            if ($gapAmount > 0) {
                $pCheckStmt = $conn->prepare("
                    SELECT COUNT(*) as parent_already_credited 
                    FROM WalletLogs 
                    WHERE (TRIM(PromoterUniqueID) = ? OR TRIM(PromoterUniqueID) = ?) 
                      AND (Message LIKE ? OR Message LIKE ?)
                      AND (TransactionType = 'Credit' OR TransactionType IS NULL OR TransactionType = '')
                ");
                $pCheckStmt->execute([
                    $parentID,
                    $parentNumID,
                    "%" . $customerUniqueID . "%",
                    "%" . $custName . "%"
                ]);

                if ($pCheckStmt->fetch(PDO::FETCH_ASSOC)['parent_already_credited'] == 0) {
                    $pwStmt = $conn->prepare("SELECT BalanceID FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");
                    $pwStmt->execute([$parentID, $parentNumID]);
                    $pwRecord = $pwStmt->fetch(PDO::FETCH_ASSOC);

                    if ($pwRecord) {
                        $puStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = BalanceAmount + ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ? OR UserID = ?");
                        $puStmt->execute([$gapAmount, $parentID, $parentNumID]);
                    } else {
                        $pinStmt = $conn->prepare("INSERT INTO PromoterWallet (UserID, PromoterUniqueID, BalanceAmount, Message) VALUES (?, ?, ?, 'Parent commission from payment')");
                        $pinStmt->execute([$parentPromoter['PromoterID'], $parentID, $gapAmount]);
                    }

                    $pLogMsg = "Parent commission earned from customer " . $custName . " (" . $customerUniqueID . ") for " . $schemeName . " scheme";
                    $plStmt = $conn->prepare("INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message, TransactionType) VALUES (?, ?, ?, 'Credit')");
                    $plStmt->execute([$parentID, $gapAmount, $pLogMsg]);

                    $roleLabel = ($i === 0) ? 'Parent Promoter' : 'Grandparent Promoter';
                    $creditedSummary[] = [
                        'role' => $roleLabel,
                        'name' => $parentPromoter['Name'],
                        'id' => $parentID,
                        'amount' => $gapAmount
                    ];
                }
            }
        }

        return ['success' => true, 'credited' => $creditedSummary];
    } catch (Exception $e) {
        error_log("Error in processPromoterCommission: " . $e->getMessage());
        return ['success' => false, 'credited' => []];
    }
}
