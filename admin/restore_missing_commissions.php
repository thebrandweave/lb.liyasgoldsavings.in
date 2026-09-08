<?php

require_once("../config/config.php");

$database = new Database();
$conn = $database->getConnection();

$PROMOTER_ID = 'GDP01386';

/*
|--------------------------------------------------------------------------
| SAFETY
|--------------------------------------------------------------------------
| true  = preview only, NO wallet changes
| false = actually restore missing commissions
|--------------------------------------------------------------------------
*/
$DRY_RUN = true;


/*
|--------------------------------------------------------------------------
| Commission converter
|--------------------------------------------------------------------------
*/
function convertCommissionToInt($commission)
{
    return intval(
        preg_replace('/[^0-9]/', '', (string)$commission)
    );
}


/*
|--------------------------------------------------------------------------
| Load promoter GDP01386
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT
        PromoterID,
        PromoterUniqueID,
        ParentPromoterID,
        Commission,
        ParentCommission,
        Name
    FROM Promoters
    WHERE TRIM(PromoterUniqueID) = ?
    LIMIT 1
");

$stmt->execute([$PROMOTER_ID]);

$directPromoter = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$directPromoter) {
    die("ERROR: Promoter GDP01386 not found.");
}


/*
|--------------------------------------------------------------------------
| Build parent hierarchy
|--------------------------------------------------------------------------
*/
$allStmt = $conn->query("
    SELECT
        PromoterID,
        PromoterUniqueID,
        ParentPromoterID,
        Commission,
        ParentCommission,
        Name
    FROM Promoters
");

$allPromoters = $allStmt->fetchAll(PDO::FETCH_ASSOC);

$promoterByRef = [];

foreach ($allPromoters as $p) {

    $pID = trim((string)$p['PromoterID']);
    $uID = trim((string)$p['PromoterUniqueID']);

    if ($uID !== '') {
        $promoterByRef[$uID] = $p;
    }

    if ($pID !== '') {
        $promoterByRef[$pID] = $p;
    }
}


/*
|--------------------------------------------------------------------------
| Get GDP01386 customers
|--------------------------------------------------------------------------
*/
$cStmt = $conn->prepare("
    SELECT
        c.CustomerID,
        c.CustomerUniqueID,
        c.Name,
        c.Contact,
        c.PromoterID
    FROM Customers c
    WHERE TRIM(c.PromoterID) = ?
    ORDER BY c.CustomerID
");

$cStmt->execute([$PROMOTER_ID]);

$customers = $cStmt->fetchAll(PDO::FETCH_ASSOC);


$totalCustomers = 0;
$totalDirectMissing = 0;
$totalDirectAmount = 0;
$totalParentMissing = 0;
$totalParentAmount = 0;

$results = [];


/*
|--------------------------------------------------------------------------
| Process every GDP01386 customer
|--------------------------------------------------------------------------
*/
foreach ($customers as $customer) {

    $customerUniqueID = trim($customer['CustomerUniqueID']);

    if ($customerUniqueID === '') {
        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | Find FIRST VERIFIED PAYMENT FOR EACH SCHEME
    |--------------------------------------------------------------------------
    */
    $pmtStmt = $conn->prepare("
        SELECT
            p.PaymentID,
            p.CustomerID,
            p.SchemeID,
            p.Amount,
            p.SubmittedAt,
            s.SchemeName
        FROM Payments p
        LEFT JOIN Schemes s
            ON p.SchemeID = s.SchemeID
        WHERE p.CustomerID = ?
          AND p.Status = 'Verified'
        ORDER BY p.SubmittedAt ASC, p.PaymentID ASC
    ");

    $pmtStmt->execute([
        $customer['CustomerID']
    ]);

    $payments = $pmtStmt->fetchAll(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | Keep only FIRST verified payment per scheme
    |--------------------------------------------------------------------------
    */
    $firstPaymentByScheme = [];

    foreach ($payments as $payment) {

        $schemeID = $payment['SchemeID'];

        if (!isset($firstPaymentByScheme[$schemeID])) {
            $firstPaymentByScheme[$schemeID] = $payment;
        }
    }


    foreach ($firstPaymentByScheme as $payment) {

        $totalCustomers++;

        /*
        |--------------------------------------------------------------------------
        | Build hierarchy starting from GDP01386
        |--------------------------------------------------------------------------
        */
        $hierarchy = [];

        $currRef = trim($customer['PromoterID']);
        $visited = [];

        while (
            !empty($currRef) &&
            !isset($visited[$currRef])
        ) {

            $visited[$currRef] = true;

            if (!isset($promoterByRef[$currRef])) {
                break;
            }

            $pData = $promoterByRef[$currRef];

            $hierarchy[] = $pData;

            $currRef = !empty($pData['ParentPromoterID'])
                ? trim($pData['ParentPromoterID'])
                : null;
        }


        if (empty($hierarchy)) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | DIRECT COMMISSION
        |--------------------------------------------------------------------------
        */
        $direct = $hierarchy[0];

        $directID =
            trim($direct['PromoterUniqueID']);

        $directNumID =
            (string)$direct['PromoterID'];

        $directCommission =
            convertCommissionToInt($direct['Commission']);


        if ($directCommission > 0) {

            /*
            |--------------------------------------------------------------------------
            | IMPORTANT:
            | ONLY CustomerUniqueID is used.
            |
            | Customer NAME is NOT used.
            |--------------------------------------------------------------------------
            */
            $check = $conn->prepare("
                SELECT COUNT(*) AS cnt
                FROM WalletLogs
                WHERE
                    (
                        TRIM(PromoterUniqueID) = ?
                        OR TRIM(PromoterUniqueID) = ?
                    )
                    AND Message LIKE ?
                    AND TransactionType = 'Credit'
            ");

            $check->execute([
                $directID,
                $directNumID,
                '%' . $customerUniqueID . '%'
            ]);

            $already =
                intval(
                    $check->fetch(PDO::FETCH_ASSOC)['cnt']
                );


            if ($already === 0) {

                $totalDirectMissing++;
                $totalDirectAmount += $directCommission;

                $results[] = [
                    'type' => 'DIRECT',
                    'customer' => $customerUniqueID,
                    'name' => $customer['Name'],
                    'amount' => $directCommission,
                    'scheme' => $payment['SchemeName']
                ];


                /*
                |--------------------------------------------------------------------------
                | ACTUAL CREDIT
                |--------------------------------------------------------------------------
                */
                if (!$DRY_RUN) {

                    $conn->beginTransaction();

                    try {

                        /*
                        | Lock wallet row before updating
                        */
                        $walletStmt = $conn->prepare("
                            SELECT BalanceID
                            FROM PromoterWallet
                            WHERE
                                TRIM(PromoterUniqueID) = ?
                                OR UserID = ?
                            LIMIT 1
                            FOR UPDATE
                        ");

                        $walletStmt->execute([
                            $directID,
                            $directNumID
                        ]);

                        $wallet =
                            $walletStmt->fetch(PDO::FETCH_ASSOC);


                        if ($wallet) {

                            $update = $conn->prepare("
                                UPDATE PromoterWallet
                                SET
                                    BalanceAmount =
                                        BalanceAmount + ?,
                                    LastUpdated =
                                        CURRENT_TIMESTAMP
                                WHERE BalanceID = ?
                            ");

                            $update->execute([
                                $directCommission,
                                $wallet['BalanceID']
                            ]);

                        } else {

                            $insertWallet = $conn->prepare("
                                INSERT INTO PromoterWallet
                                (
                                    UserID,
                                    PromoterUniqueID,
                                    BalanceAmount,
                                    Message
                                )
                                VALUES
                                (?, ?, ?, 'Commission recovery')
                            ");

                            $insertWallet->execute([
                                $direct['PromoterID'],
                                $directID,
                                $directCommission
                            ]);
                        }


                        $logMessage =
                            "Commission earned from customer "
                            . $customer['Name']
                            . " ("
                            . $customerUniqueID
                            . ") for "
                            . ($payment['SchemeName'] ?: 'Gold Savings Plan')
                            . " scheme";


                        $log = $conn->prepare("
                            INSERT INTO WalletLogs
                            (
                                PromoterUniqueID,
                                Amount,
                                Message,
                                TransactionType
                            )
                            VALUES (?, ?, ?, 'Credit')
                        ");

                        $log->execute([
                            $directID,
                            $directCommission,
                            $logMessage
                        ]);


                        $conn->commit();

                    } catch (Exception $e) {

                        if ($conn->inTransaction()) {
                            $conn->rollBack();
                        }

                        echo "DIRECT ERROR for "
                            . htmlspecialchars($customerUniqueID)
                            . ": "
                            . htmlspecialchars($e->getMessage())
                            . "<br>";

                    }
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | PARENT COMMISSION
        |--------------------------------------------------------------------------
        */
        for ($i = 0; $i < count($hierarchy) - 1; $i++) {

            $child =
                $hierarchy[$i];

            $parent =
                $hierarchy[$i + 1];


            $parentID =
                trim($parent['PromoterUniqueID']);

            $parentNumID =
                (string)$parent['PromoterID'];


            $childCommission =
                convertCommissionToInt(
                    $child['Commission']
                );

            $parentCommission =
                convertCommissionToInt(
                    $parent['Commission']
                );


            /*
            |--------------------------------------------------------------------------
            | Calculate differential
            |--------------------------------------------------------------------------
            */
            $gapAmount = 0;

            if (
                !empty($child['ParentCommission']) &&
                convertCommissionToInt(
                    $child['ParentCommission']
                ) > 0
            ) {

                $gapAmount =
                    convertCommissionToInt(
                        $child['ParentCommission']
                    );

            } elseif (
                $parentCommission > $childCommission
            ) {

                $gapAmount =
                    $parentCommission - $childCommission;
            }


            if ($gapAmount <= 0) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | Check by CustomerUniqueID ONLY
            |--------------------------------------------------------------------------
            */
            $pCheck = $conn->prepare("
                SELECT COUNT(*) AS cnt
                FROM WalletLogs
                WHERE
                    (
                        TRIM(PromoterUniqueID) = ?
                        OR TRIM(PromoterUniqueID) = ?
                    )
                    AND Message LIKE ?
                    AND TransactionType = 'Credit'
            ");

            $pCheck->execute([
                $parentID,
                $parentNumID,
                '%' . $customerUniqueID . '%'
            ]);

            $parentAlready =
                intval(
                    $pCheck->fetch(PDO::FETCH_ASSOC)['cnt']
                );


            if ($parentAlready === 0) {

                $totalParentMissing++;
                $totalParentAmount += $gapAmount;

                $results[] = [
                    'type' => 'PARENT',
                    'customer' => $customerUniqueID,
                    'name' => $customer['Name'],
                    'amount' => $gapAmount,
                    'scheme' => $payment['SchemeName'],
                    'promoter' => $parentID
                ];


                /*
                |--------------------------------------------------------------------------
                | ACTUAL CREDIT
                |--------------------------------------------------------------------------
                */
                if (!$DRY_RUN) {

                    $conn->beginTransaction();

                    try {

                        $walletStmt = $conn->prepare("
                            SELECT BalanceID
                            FROM PromoterWallet
                            WHERE
                                TRIM(PromoterUniqueID) = ?
                                OR UserID = ?
                            LIMIT 1
                            FOR UPDATE
                        ");

                        $walletStmt->execute([
                            $parentID,
                            $parentNumID
                        ]);

                        $wallet =
                            $walletStmt->fetch(PDO::FETCH_ASSOC);


                        if ($wallet) {

                            $update = $conn->prepare("
                                UPDATE PromoterWallet
                                SET
                                    BalanceAmount =
                                        BalanceAmount + ?,
                                    LastUpdated =
                                        CURRENT_TIMESTAMP
                                WHERE BalanceID = ?
                            ");

                            $update->execute([
                                $gapAmount,
                                $wallet['BalanceID']
                            ]);

                        } else {

                            $insertWallet = $conn->prepare("
                                INSERT INTO PromoterWallet
                                (
                                    UserID,
                                    PromoterUniqueID,
                                    BalanceAmount,
                                    Message
                                )
                                VALUES
                                (?, ?, ?, 'Parent commission recovery')
                            ");

                            $insertWallet->execute([
                                $parent['PromoterID'],
                                $parentID,
                                $gapAmount
                            ]);
                        }


                        $logMessage =
                            "Parent commission earned from customer "
                            . $customer['Name']
                            . " ("
                            . $customerUniqueID
                            . ") for "
                            . ($payment['SchemeName'] ?: 'Gold Savings Plan')
                            . " scheme";


                        $log = $conn->prepare("
                            INSERT INTO WalletLogs
                            (
                                PromoterUniqueID,
                                Amount,
                                Message,
                                TransactionType
                            )
                            VALUES (?, ?, ?, 'Credit')
                        ");

                        $log->execute([
                            $parentID,
                            $gapAmount,
                            $logMessage
                        ]);


                        $conn->commit();

                    } catch (Exception $e) {

                        if ($conn->inTransaction()) {
                            $conn->rollBack();
                        }

                        echo "PARENT ERROR for "
                            . htmlspecialchars($customerUniqueID)
                            . ": "
                            . htmlspecialchars($e->getMessage())
                            . "<br>";
                    }
                }
            }
        }
    }
}


/*
|--------------------------------------------------------------------------
| REPORT
|--------------------------------------------------------------------------
*/

echo "<h2>GDP01386 Commission Recovery</h2>";

if ($DRY_RUN) {

    echo "<h3 style='color:orange'>
        DRY RUN — NO MONEY WAS ADDED
    </h3>";

} else {

    echo "<h3 style='color:green'>
        RECOVERY COMPLETED
    </h3>";
}

echo "<p>
<strong>Customers / first verified scheme payments checked:</strong>
$totalCustomers
</p>";

echo "<p>
<strong>Missing Direct Commissions:</strong>
$totalDirectMissing
</p>";

echo "<p>
<strong>Missing Direct Amount:</strong>
₹" . number_format($totalDirectAmount, 2) . "
</p>";

echo "<p>
<strong>Missing Parent Commissions:</strong>
$totalParentMissing
</p>";

echo "<p>
<strong>Missing Parent Amount:</strong>
₹" . number_format($totalParentAmount, 2) . "
</p>";

echo "<h3>
Total Recovery:
₹" . number_format(
    $totalDirectAmount + $totalParentAmount,
    2
) . "
</h3>";


if (!empty($results)) {

    echo "
    <table border='1'
           cellpadding='8'
           cellspacing='0'
           style='border-collapse:collapse'>
        <tr>
            <th>Type</th>
            <th>Customer ID</th>
            <th>Name</th>
            <th>Scheme</th>
            <th>Promoter</th>
            <th>Amount</th>
        </tr>
    ";

    foreach ($results as $r) {

        echo "
        <tr>
            <td>" . htmlspecialchars($r['type']) . "</td>
            <td>" . htmlspecialchars($r['customer']) . "</td>
            <td>" . htmlspecialchars($r['name']) . "</td>
            <td>" . htmlspecialchars($r['scheme'] ?? '') . "</td>
            <td>" . htmlspecialchars($r['promoter'] ?? $PROMOTER_ID) . "</td>
            <td>₹" . number_format($r['amount'], 2) . "</td>
        </tr>
        ";
    }

    echo "</table>";

} else {

    echo "<h3>No missing commissions found.</h3>";
}

?>