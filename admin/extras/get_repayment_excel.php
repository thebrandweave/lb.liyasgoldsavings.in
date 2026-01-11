<?php
session_start();
$menuPath = "../";
$currentPage = "extras";
require_once($menuPath . "../config/config.php");
require_once($menuPath . "../vendor/autoload.php");
$database = new Database();
$conn = $database->getConnection();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Fetch all schemes
$schemes = $conn->query("SELECT SchemeID, SchemeName FROM Schemes WHERE Status = 'Active' ORDER BY SchemeName")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all installments for all schemes (simplified approach)
$allInstallments = $conn->query("
    SELECT i.InstallmentID, i.SchemeID, i.InstallmentName, i.InstallmentNumber, i.Amount, i.DrawDate
    FROM Installments i 
    JOIN Schemes s ON i.SchemeID = s.SchemeID
    WHERE i.Status = 'Active' AND s.Status = 'Active'
    ORDER BY i.SchemeID, i.InstallmentNumber
")->fetchAll(PDO::FETCH_ASSOC);

// Group installments by scheme for easy access
$installmentsByScheme = [];
foreach ($allInstallments as $inst) {
    $installmentsByScheme[$inst['SchemeID']][] = $inst;
}

// Fetch all promoters with their customer count
$promoters = $conn->query("
    SELECT p.PromoterUniqueID, p.Name, COUNT(c.CustomerID) as customer_count
    FROM Promoters p
    LEFT JOIN Customers c ON c.PromoterID = p.PromoterUniqueID
    WHERE p.Status = 'Active'
    GROUP BY p.PromoterUniqueID, p.Name
    ORDER BY customer_count DESC, p.Name ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Handle Excel download
if (isset($_GET['download']) && isset($_GET['promoter_id']) && isset($_GET['scheme_id']) && isset($_GET['installment_id'])) {
    $promoterId = $_GET['promoter_id'];
    $schemeId = $_GET['scheme_id'];
    $installmentId = $_GET['installment_id'];

    $stmt = $conn->prepare("
        SELECT p.PromoterUniqueID, c.CustomerUniqueID as FromCustomerID, pay.Amount, s.SchemeName, i.InstallmentName
        FROM Payments pay
        JOIN Customers c ON pay.CustomerID = c.CustomerID
        JOIN Promoters p ON c.PromoterID = p.PromoterUniqueID
        JOIN Schemes s ON pay.SchemeID = s.SchemeID
        JOIN Installments i ON pay.InstallmentID = i.InstallmentID
        WHERE c.PromoterID = ?
          AND pay.SchemeID = ?
          AND pay.InstallmentID = ?
          AND pay.Status = 'Verified'
        ORDER BY pay.PaymentID DESC
    ");
    $stmt->execute([$promoterId, $schemeId, $installmentId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'PromoterUniqueID');
    $sheet->setCellValue('B1', 'FromCustomerID');
    $sheet->setCellValue('C1', 'Amount');
    $sheet->setCellValue('D1', 'Scheme Name');
    $sheet->setCellValue('E1', 'Installment Name');
    $rowNum = 2;
    foreach ($rows as $row) {
        $sheet->setCellValue('A' . $rowNum, $row['PromoterUniqueID']);
        $sheet->setCellValue('B' . $rowNum, $row['FromCustomerID']);
        $sheet->setCellValue('C' . $rowNum, $row['Amount']);
        $sheet->setCellValue('D' . $rowNum, $row['SchemeName']);
        $sheet->setCellValue('E' . $rowNum, $row['InstallmentName']);
        $rowNum++;
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="monthly_payments_report.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// Handle form selection and preview
$selectedPromoter = $_GET['promoter_id'] ?? '';
$selectedScheme = $_GET['scheme_id'] ?? '';
$selectedInstallment = $_GET['installment_id'] ?? '';
$previewRows = [];
if ($selectedPromoter && $selectedScheme && $selectedInstallment) {
    $stmt = $conn->prepare("
        SELECT p.PromoterUniqueID, c.CustomerUniqueID as FromCustomerID, pay.Amount, s.SchemeName, i.InstallmentName
        FROM Payments pay
        JOIN Customers c ON pay.CustomerID = c.CustomerID
        JOIN Promoters p ON c.PromoterID = p.PromoterUniqueID
        JOIN Schemes s ON pay.SchemeID = s.SchemeID
        JOIN Installments i ON pay.InstallmentID = i.InstallmentID
        WHERE c.PromoterID = ?
          AND pay.SchemeID = ?
          AND pay.InstallmentID = ?
          AND pay.Status = 'Verified'
        ORDER BY pay.PaymentID DESC
    ");
    $stmt->execute([$selectedPromoter, $selectedScheme, $selectedInstallment]);
    $previewRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

include($menuPath . "components/sidebar.php");
include($menuPath . "components/topbar.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get Monthly Payments Excel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .extras-container {
            padding: 20px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .extras-title {
            font-size: 24px;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 25px;
        }

        .extras-form {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .extras-form label {
            font-weight: 500;
            color: var(--secondary-color);
            margin-bottom: 8px;
            display: block;
        }

        .extras-form select,
        .extras-form button {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 15px;
            margin-bottom: 15px;
            width: 100%;
        }

        .extras-form button {
            background: var(--primary-color);
            color: #fff;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .extras-form button:hover {
            background: var(--hover-color);
        }

        .extras-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .extras-table th,
        .extras-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
            font-size: 14px;
        }

        .extras-table th {
            background: #f4f8fb;
            color: #34495e;
            font-weight: 600;
        }

        .extras-table tr:last-child td {
            border-bottom: none;
        }

        .download-btn {
            margin-top: 20px;
            display: inline-block;
            background: #3a7bd5;
            color: #fff;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.2s;
        }

        .download-btn:hover {
            background: #00d2ff;
        }

        /* Searchable Dropdown Styles */
        .searchable-dropdown {
            position: relative;
            display: block;
            width: 100%;
        }

        .searchable-dropdown .select-wrapper {
            position: relative;
        }

        .searchable-dropdown input[type="text"] {
            width: 100%;
            padding: 10px 30px 10px 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
            box-sizing: border-box;
        }

        .searchable-dropdown input[type="text"]:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(58, 123, 213, 0.1);
        }

        .searchable-dropdown .dropdown-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: #666;
        }

        .searchable-dropdown .dropdown-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 6px 6px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-top: -1px;
        }

        .searchable-dropdown .dropdown-list.show {
            display: block;
        }

        .searchable-dropdown .dropdown-item {
            padding: 10px 12px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
        }

        .searchable-dropdown .dropdown-item:last-child {
            border-bottom: none;
        }

        .searchable-dropdown .dropdown-item:hover {
            background-color: #f5f5f5;
        }

        .searchable-dropdown .dropdown-item.selected {
            background-color: #e3f2fd;
            color: var(--primary-color);
            font-weight: 500;
        }

        .searchable-dropdown .dropdown-item.hidden {
            display: none;
        }

        .searchable-dropdown .no-results {
            padding: 10px 12px;
            color: #999;
            text-align: center;
            font-style: italic;
            display: none;
        }

        .searchable-dropdown .no-results.show {
            display: block;
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <div class="extras-container">
            <div class="extras-title"><i class="fas fa-file-excel"></i> Get Monthly Payments Excel</div>
            <form class="extras-form" method="GET" id="filterForm">
                <label for="promoter-search">Select Promoter:</label>
                <div class="searchable-dropdown" id="promoterDropdown">
                    <input type="hidden" name="promoter_id" id="promoter_id" value="<?php echo htmlspecialchars($selectedPromoter); ?>">
                    <div class="select-wrapper">
                        <input type="text" id="promoter-search" placeholder="Search promoters..." autocomplete="off"
                            value="<?php
                                    if ($selectedPromoter) {
                                        foreach ($promoters as $promoter) {
                                            if ($promoter['PromoterUniqueID'] === $selectedPromoter) {
                                                echo htmlspecialchars($promoter['PromoterUniqueID'] . ' - ' . $promoter['Name'] . ' (' . $promoter['customer_count'] . ' customers)');
                                                break;
                                            }
                                        }
                                    }
                                    ?>">
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </div>
                    <div class="dropdown-list" id="promoterList">
                        <?php foreach ($promoters as $promoter): ?>
                            <div class="dropdown-item <?php echo ($selectedPromoter === $promoter['PromoterUniqueID']) ? 'selected' : ''; ?>"
                                data-value="<?php echo htmlspecialchars($promoter['PromoterUniqueID']); ?>"
                                data-text="<?php echo htmlspecialchars($promoter['PromoterUniqueID'] . ' - ' . $promoter['Name'] . ' (' . $promoter['customer_count'] . ' customers)'); ?>">
                                <?php echo htmlspecialchars($promoter['PromoterUniqueID'] . ' - ' . $promoter['Name'] . ' (' . $promoter['customer_count'] . ' customers)'); ?>
                            </div>
                        <?php endforeach; ?>
                        <div class="no-results">No promoters found</div>
                    </div>
                </div>
                <label for="scheme_id">Select Scheme:</label>
                <select name="scheme_id" id="scheme_id" required onchange="filterInstallments()">
                    <option value="">Select Scheme</option>
                    <?php foreach ($schemes as $scheme): ?>
                        <option value="<?php echo $scheme['SchemeID']; ?>" <?php if ($selectedScheme == $scheme['SchemeID']) echo 'selected'; ?>><?php echo htmlspecialchars($scheme['SchemeName']); ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="installment_id">Select Installment:</label>
                <select name="installment_id" id="installment_id" required>
                    <option value="">Select Installment</option>
                </select>
                <button type="submit">Preview Data</button>
            </form>
            <?php if ($selectedPromoter && $selectedScheme && $selectedInstallment): ?>
                <a class="download-btn" href="?download=1&promoter_id=<?php echo urlencode($selectedPromoter); ?>&scheme_id=<?php echo urlencode($selectedScheme); ?>&installment_id=<?php echo urlencode($selectedInstallment); ?>">Download Excel</a>
                <div style="margin-top:20px;"></div>
                <table class="extras-table">
                    <thead>
                        <tr>
                            <th>PromoterUniqueID</th>
                            <th>FromCustomerID</th>
                            <th>Amount</th>
                            <th>Scheme Name</th>
                            <th>Installment Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($previewRows as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['PromoterUniqueID']); ?></td>
                                <td><?php echo htmlspecialchars($row['FromCustomerID']); ?></td>
                                <td><?php echo htmlspecialchars($row['Amount']); ?></td>
                                <td><?php echo htmlspecialchars($row['SchemeName']); ?></td>
                                <td><?php echo htmlspecialchars($row['InstallmentName']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($previewRows)): ?>
                            <tr>
                                <td colspan="5" style="text-align:center; color:#888;">No data found for this selection.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <script>
        // Searchable Dropdown Functionality
        (function() {
            const dropdown = document.getElementById('promoterDropdown');
            const searchInput = document.getElementById('promoter-search');
            const hiddenInput = document.getElementById('promoter_id');
            const dropdownList = document.getElementById('promoterList');
            const dropdownItems = dropdownList.querySelectorAll('.dropdown-item:not(.no-results)');
            const noResults = dropdownList.querySelector('.no-results');

            // Toggle dropdown on input click/focus
            searchInput.addEventListener('focus', function() {
                dropdownList.classList.add('show');
                filterItems();
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!dropdown.contains(e.target)) {
                    dropdownList.classList.remove('show');
                }
            });

            // Filter items based on search input
            function filterItems() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                let visibleCount = 0;

                dropdownItems.forEach(item => {
                    const text = item.getAttribute('data-text').toLowerCase();
                    if (text.includes(searchTerm)) {
                        item.classList.remove('hidden');
                        visibleCount++;

                        // Highlight selected item
                        if (item.getAttribute('data-value') === hiddenInput.value) {
                            item.classList.add('selected');
                        } else {
                            item.classList.remove('selected');
                        }
                    } else {
                        item.classList.add('hidden');
                    }
                });

                // Show/hide "no results" message
                if (visibleCount === 0) {
                    noResults.classList.add('show');
                } else {
                    noResults.classList.remove('show');
                }
            }

            // Filter on input
            searchInput.addEventListener('input', function() {
                dropdownList.classList.add('show');
                filterItems();
            });

            // Handle item selection
            dropdownItems.forEach(item => {
                item.addEventListener('click', function() {
                    const value = this.getAttribute('data-value');
                    const text = this.getAttribute('data-text');

                    hiddenInput.value = value;
                    searchInput.value = text;

                    // Update selected state
                    dropdownItems.forEach(i => i.classList.remove('selected'));
                    this.classList.add('selected');

                    // Close dropdown (don't auto-submit form)
                    dropdownList.classList.remove('show');
                });
            });

            // Handle keyboard navigation
            let selectedIndex = -1;

            searchInput.addEventListener('keydown', function(e) {
                const visibleItems = Array.from(dropdownItems).filter(item => !item.classList.contains('hidden'));

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = Math.min(selectedIndex + 1, visibleItems.length - 1);
                    updateHighlight(visibleItems);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = Math.max(selectedIndex - 1, -1);
                    updateHighlight(visibleItems);
                } else if (e.key === 'Enter' && selectedIndex >= 0) {
                    e.preventDefault();
                    visibleItems[selectedIndex].click();
                } else if (e.key === 'Escape') {
                    dropdownList.classList.remove('show');
                }
            });

            function updateHighlight(visibleItems) {
                visibleItems.forEach((item, index) => {
                    if (index === selectedIndex) {
                        item.style.backgroundColor = '#e3f2fd';
                    } else {
                        item.style.backgroundColor = '';
                    }
                });
            }

            // Reset selected index when typing
            searchInput.addEventListener('input', function() {
                selectedIndex = -1;
            });
        })();

        // Pass PHP data to JavaScript
        const installmentsByScheme = <?php echo json_encode($installmentsByScheme); ?>;
        const selectedInstallment = '<?php echo $selectedInstallment; ?>';

        function filterInstallments() {
            const schemeId = document.getElementById('scheme_id').value;
            const installmentSelect = document.getElementById('installment_id');

            // Clear current options
            installmentSelect.innerHTML = '<option value="">Select Installment</option>';

            if (schemeId && installmentsByScheme[schemeId]) {
                const installments = installmentsByScheme[schemeId];
                installments.forEach(inst => {
                    const option = document.createElement('option');
                    option.value = inst.InstallmentID;
                    option.textContent = inst.InstallmentName;
                    if (selectedInstallment == inst.InstallmentID) {
                        option.selected = true;
                    }
                    installmentSelect.appendChild(option);
                });
            }
        }

        // Initialize installments if scheme is already selected
        document.addEventListener('DOMContentLoaded', function() {
            const schemeSelect = document.getElementById('scheme_id');
            if (schemeSelect.value) {
                filterInstallments();
            }
        });

        // Form validation
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            const promoterId = document.getElementById('promoter_id').value;
            if (!promoterId) {
                e.preventDefault();
                alert('Please select a promoter.');
                document.getElementById('promoter-search').focus();
                return false;
            }
        });
    </script>
</body>

</html>