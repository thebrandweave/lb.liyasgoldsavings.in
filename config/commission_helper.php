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
        return false;
    }

    try {
        // Fetch customer details and latest scheme payment
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
            return false;
        }

        $directPromoterID = trim($customerDetails['PromoterID']);
        $schemeName = !empty($customerDetails['SchemeName']) ? $customerDetails['SchemeName'] : 'Gold Savings Plan';

        // Fetch Direct Promoter
        $stmt = $conn->prepare("SELECT PromoterID, PromoterUniqueID, ParentPromoterID, Commission, ParentCommission, Name FROM Promoters WHERE TRIM(PromoterUniqueID) = ?");
        $stmt->execute([$directPromoterID]);
        $directPromoter = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$directPromoter) {
            return false;
        }

        $commissionAmount = convertCommissionToInt($directPromoter['Commission']);

        // Check if direct commission already logged for this customer
        $checkStmt = $conn->prepare("
            SELECT COUNT(*) as already_credited 
            FROM WalletLogs 
            WHERE TRIM(PromoterUniqueID) = ? 
              AND (Message LIKE ? OR Message LIKE ?)
        ");
        $checkStmt->execute([
            $directPromoterID,
            "%" . $customerUniqueID . "%",
            "%" . $customerDetails['Name'] . "%"
        ]);
        $directAlreadyCredited = ($checkStmt->fetch(PDO::FETCH_ASSOC)['already_credited'] > 0);

        // Process Direct Promoter Commission if missing
        if (!$directAlreadyCredited && $commissionAmount > 0) {
            $stmt = $conn->prepare("SELECT BalanceID FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ?");
            $stmt->execute([$directPromoterID]);
            $walletRecord = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($walletRecord) {
                $stmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = BalanceAmount + ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ?");
                $stmt->execute([$directPromoterID]);
            } else {
                $stmt = $conn->prepare("INSERT INTO PromoterWallet (UserID, PromoterUniqueID, BalanceAmount, Message) VALUES (?, ?, ?, ?)");
                $stmt->execute([$directPromoter['PromoterID'], $directPromoterID, $commissionAmount, "Commission from payment"]);
            }

            $logMessage = "Commission earned from customer " . $customerDetails['Name'] . " (" . $customerUniqueID . ") for " . $schemeName . " scheme";
            $stmt = $conn->prepare("INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message, TransactionType) VALUES (?, ?, ?, 'Credit')");
            $stmt->execute([$directPromoterID, $commissionAmount, $logMessage]);
        }

        // Process Parent Promoter Commission (Dynamically calculate gap if ParentCommission is empty)
        if (!empty($directPromoter['ParentPromoterID'])) {
            $parentPromoterID = trim($directPromoter['ParentPromoterID']);

            $stmt = $conn->prepare("SELECT PromoterID, PromoterUniqueID, Commission, Name FROM Promoters WHERE TRIM(PromoterUniqueID) = ?");
            $stmt->execute([$parentPromoterID]);
            $parentPromoter = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($parentPromoter) {
                // Calculate gap commission: Parent Commission - Child Commission
                $parentCommissionAmount = 0;
                if (!empty($directPromoter['ParentCommission']) && convertCommissionToInt($directPromoter['ParentCommission']) > 0) {
                    $parentCommissionAmount = convertCommissionToInt($directPromoter['ParentCommission']);
                } else {
                    $parentActual = convertCommissionToInt($parentPromoter['Commission']);
                    $childActual = convertCommissionToInt($directPromoter['Commission']);
                    if ($parentActual > $childActual) {
                        $parentCommissionAmount = $parentActual - $childActual;
                    }
                }

                if ($parentCommissionAmount > 0) {
                    $pCheckStmt = $conn->prepare("
                        SELECT COUNT(*) as parent_already_credited 
                        FROM WalletLogs 
                        WHERE TRIM(PromoterUniqueID) = ? 
                          AND (Message LIKE ? OR Message LIKE ?)
                    ");
                    $pCheckStmt->execute([
                        $parentPromoterID,
                        "%" . $customerUniqueID . "%",
                        "%" . $customerDetails['Name'] . "%"
                    ]);

                    if ($pCheckStmt->fetch(PDO::FETCH_ASSOC)['parent_already_credited'] == 0) {
                        $stmt = $conn->prepare("SELECT BalanceID FROM PromoterWallet WHERE TRIM(PromoterUniqueID) = ?");
                        $stmt->execute([$parentPromoterID]);
                        $parentWallet = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($parentWallet) {
                            $stmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = BalanceAmount + ?, LastUpdated = CURRENT_TIMESTAMP WHERE TRIM(PromoterUniqueID) = ?");
                            $stmt->execute([$parentCommissionAmount, $parentPromoterID]);
                        } else {
                            $stmt = $conn->prepare("INSERT INTO PromoterWallet (UserID, PromoterUniqueID, BalanceAmount, Message) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$parentPromoter['PromoterID'], $parentPromoterID, $parentCommissionAmount, "Parent commission from payment"]);
                        }

                        $pLogMessage = "Parent commission earned from customer " . $customerDetails['Name'] . " (" . $customerUniqueID . ") for " . $schemeName . " scheme";
                        $stmt = $conn->prepare("INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message, TransactionType) VALUES (?, ?, ?, 'Credit')");
                        $stmt->execute([$parentPromoterID, $parentCommissionAmount, $pLogMessage]);
                    }
                }
            }
        }

        return true;
    } catch (Exception $e) {
        error_log("Error in processPromoterCommission: " . $e->getMessage());
        return false;
    }
}
