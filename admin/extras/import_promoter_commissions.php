<?php
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// --- DATABASE CONNECTION ---
$servername = "srv1752.hstgr.io";
$username = "u229215627_GoldenDreamSQL";
$password = "Azl@n2002";
$dbname = "u229215627_goldenDreamSQL";

$successMessage = '';
$errorMessage = '';
$logFileName = '';

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// --- FILE PROCESSING ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["excelFile"])) {
    $file = $_FILES["excelFile"]["tmp_name"];
    $fileName = $_FILES["excelFile"]["name"];

    // Create logs directory if it doesn't exist
    $logsDir = __DIR__ . '/logs';
    if (!file_exists($logsDir)) {
        if (!mkdir($logsDir, 0777, true)) {
            $errorMessage = "Failed to create logs directory. Please check permissions.";
        }
    }

    if (empty($errorMessage)) {
        $logFileName = $logsDir . '/failed_commission_imports_' . date('Y-m-d_H-i-s') . '.txt';
        $logFile = @fopen($logFileName, 'w');

        if ($logFile === false) {
            $errorMessage = "Failed to create log file. Please check directory permissions.";
        } else {
            fwrite($logFile, "Failed Commission Import Log - " . date('Y-m-d H:i:s') . "\n");
            fwrite($logFile, "==========================================\n\n");
            fwrite($logFile, "Original File: " . $fileName . "\n\n");

            try {
                $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
                    throw new Exception("Invalid file type. Please upload an Excel (.xlsx, .xls) or CSV file.");
                }

                $spreadsheet = IOFactory::load($file);
                $worksheet = $spreadsheet->getActiveSheet();
                $highestRow = $worksheet->getHighestRow();

                $successCount = 0;
                $errorCount = 0;

                // Start from row 2 to skip header
                for ($row = 2; $row <= $highestRow; $row++) {
                    $promoterUniqueID = $worksheet->getCell('A' . $row)->getValue();
                    $fromCustomerID = $worksheet->getCell('B' . $row)->getValue();
                    $amount = $worksheet->getCell('C' . $row)->getValue();
                    $schemeName = $worksheet->getCell('D' . $row)->getValue();
                    $installmentName = $worksheet->getCell('E' . $row)->getValue();

                    if (empty($promoterUniqueID) || empty($fromCustomerID) || empty($amount) || empty($schemeName) || empty($installmentName)) {
                        $errorCount++;
                        $logMessage = "Error in row $row: One or more required fields are empty.\n";
                        $logMessage .= "Data: PromoterUniqueID: $promoterUniqueID, FromCustomerID: $fromCustomerID, Amount: $amount, Scheme: $schemeName, Installment: $installmentName\n";
                        $logMessage .= "----------------------------------------\n";
                        fwrite($logFile, $logMessage);
                        continue;
                    }

                    try {
                        $conn->beginTransaction();

                        // 1. Get PromoterID from Promoters table
                        $promoterStmt = $conn->prepare("SELECT PromoterID FROM Promoters WHERE PromoterUniqueID = ?");
                        $promoterStmt->execute([$promoterUniqueID]);
                        $promoter = $promoterStmt->fetch(PDO::FETCH_ASSOC);

                        if (!$promoter) {
                            throw new Exception("Promoter not found with UniqueID: " . $promoterUniqueID);
                        }
                        $promoterID = $promoter['PromoterID'];

                        // 2. Check if a wallet exists and update/create
                        $walletStmt = $conn->prepare("SELECT BalanceID FROM PromoterWallet WHERE PromoterUniqueID = ?");
                        $walletStmt->execute([$promoterUniqueID]);
                        $wallet = $walletStmt->fetch(PDO::FETCH_ASSOC);

                        $walletMessage = "Commission for {$schemeName} ({$installmentName}). Ref Customer: {$fromCustomerID}";

                        if ($wallet) {
                            $updateWalletStmt = $conn->prepare("UPDATE PromoterWallet SET BalanceAmount = BalanceAmount + ?, Message = ? WHERE PromoterUniqueID = ?");
                            $updateWalletStmt->execute([$amount, $walletMessage, $promoterUniqueID]);
                        } else {
                            $createWalletStmt = $conn->prepare("INSERT INTO PromoterWallet (UserID, PromoterUniqueID, BalanceAmount, Message) VALUES (?, ?, ?, ?)");
                            $createWalletStmt->execute([$promoterID, $promoterUniqueID, $amount, $walletMessage]);
                        }

                        // 3. Log the transaction in WalletLogs
                        $logMessage = "Commission for {$schemeName} ({$installmentName}) from customer {$fromCustomerID}";
                        $logStmt = $conn->prepare("INSERT INTO WalletLogs (PromoterUniqueID, Amount, Message, TransactionType) VALUES (?, ?, ?, 'Credit')");
                        $logStmt->execute([$promoterUniqueID, $amount, $logMessage]);

                        $conn->commit();
                        $successCount++;
                    } catch (Exception $e) {
                        $conn->rollBack();
                        $errorCount++;
                        $logMessage = "Error in row $row: " . $e->getMessage() . "\n";
                        $logMessage .= "Data: PromoterUniqueID: $promoterUniqueID, FromCustomerID: $fromCustomerID, Amount: $amount, Scheme: $schemeName, Installment: $installmentName\n";
                        $logMessage .= "----------------------------------------\n";
                        fwrite($logFile, $logMessage);
                    }
                }

                fclose($logFile);

                if ($errorCount > 0) {
                    $errorMessage = "Import completed with errors. <br> Success: $successCount, Failed: $errorCount. <br> Check log file for details: <a href='logs/" . basename($logFileName) . "' target='_blank'>" . basename($logFileName) . "</a>";
                } else {
                    $successMessage = "Import completed successfully. All $successCount records were imported.";
                }
            } catch (Exception $e) {
                $errorMessage = "Error processing file: " . $e->getMessage();
                if (isset($logFile) && $logFile !== false) {
                    fclose($logFile);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Promoter Commissions</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            color: #333;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
        }

        h2 {
            color: #1a73e8;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 16px;
            text-align: center;
        }

        .message.success {
            background-color: #e6f4ea;
            color: #1e8e3e;
            border: 1px solid #a8dba8;
        }

        .message.error {
            background-color: #fce8e6;
            color: #d93025;
            border: 1px solid #f5c6cb;
        }

        .message.error a {
            color: #d93025;
            font-weight: 600;
        }

        .format-info {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            background-color: #f9f9f9;
            border-radius: 8px;
        }

        .format-info h3 {
            margin-top: 0;
            color: #3c4043;
            font-weight: 500;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #dfe1e5;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f1f3f4;
            font-weight: 600;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        input[type="file"] {
            border: 2px dashed #1a73e8;
            border-radius: 8px;
            padding: 30px;
            width: 100%;
            text-align: center;
            background-color: #f8f9fa;
            cursor: pointer;
            margin-bottom: 20px;
        }

        button {
            background-color: #1a73e8;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #185abc;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Import Promoter Commissions</h2>

        <?php if (!empty($successMessage)): ?>
            <div class="message success"><?php echo $successMessage; ?></div>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <div class="message error"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <div class="format-info">
            <h3>Expected File Format</h3>
            <p>Your Excel/CSV file should have the following columns. The first row must be headers.</p>
            <table>
                <tr>
                    <th>Column</th>
                    <th>Description</th>
                    <th>Required</th>
                </tr>
                <tr>
                    <td>A</td>
                    <td>PromoterUniqueID</td>
                    <td>Yes</td>
                </tr>
                <tr>
                    <td>B</td>
                    <td>FromCustomerID</td>
                    <td>Yes (For logging purposes)</td>
                </tr>
                <tr>
                    <td>C</td>
                    <td>Amount</td>
                    <td>Yes</td>
                </tr>
                <tr>
                    <td>D</td>
                    <td>Scheme Name</td>
                    <td>Yes</td>
                </tr>
                <tr>
                    <td>E</td>
                    <td>Installment Name</td>
                    <td>Yes</td>
                </tr>
            </table>
        </div>

        <form method="post" enctype="multipart/form-data">
            <input type="file" name="excelFile" accept=".xlsx,.xls,.csv" required>
            <button type="submit">Upload and Import</button>
        </form>
    </div>
</body>

</html>