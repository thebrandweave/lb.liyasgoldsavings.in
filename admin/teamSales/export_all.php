<?php
session_start();

// Database connection
require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

// Get parameters
$fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-d');
$toDate = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');
$promoterId = isset($_GET['promoter']) ? $_GET['promoter'] : null;

// Get promoter name if filtered
$promoterName = '';
if ($promoterId) {
    $stmt = $conn->prepare("SELECT Name FROM Promoters WHERE PromoterUniqueID = ?");
    $stmt->execute([$promoterId]);
    $promoterData = $stmt->fetch(PDO::FETCH_ASSOC);
    $promoterName = $promoterData ? $promoterData['Name'] : '';
}

// Function to get all team statistics
function getAllTeamStats($conn, $fromDate, $toDate, $promoterId = null)
{
    // Get all unique team names (filtered by promoter if selected)
    $teamQuery = "SELECT DISTINCT TeamName FROM Customers WHERE TeamName IS NOT NULL";
    $teamParams = [];
    
    if ($promoterId) {
        $teamQuery .= " AND PromoterID = :promoterId";
        $teamParams[':promoterId'] = $promoterId;
    }
    
    $teamStmt = $conn->prepare($teamQuery);
    $teamStmt->execute($teamParams);
    $teams = $teamStmt->fetchAll(PDO::FETCH_COLUMN);

    $allTeamData = [];
    foreach ($teams as $teamName) {
        // Build customer filter condition based on promoter
        $customerFilter = "TeamName = :teamName";
        $params = [':teamName' => $teamName, ':fromDate' => $fromDate, ':toDate' => $toDate];
        
        if ($promoterId) {
            $customerFilter .= " AND PromoterID = :promoterId";
            $params[':promoterId'] = $promoterId;
        }
        
        // Get all team members in date range
        $whereClause = "WHERE c.TeamName = :teamName";
        $memberParams = [':teamName' => $teamName, ':fromDate' => $fromDate, ':toDate' => $toDate];
        
        if ($promoterId) {
            $whereClause .= " AND c.PromoterID = :promoterId";
            $memberParams[':promoterId'] = $promoterId;
        }
        
        $whereClause .= " AND (DATE(c.CreatedAt) BETWEEN :fromDate AND :toDate OR DATE(pay.SubmittedAt) BETWEEN :fromDate AND :toDate)";
        
        $memberQuery = "SELECT 
            c.CustomerUniqueID as unique_id,
            c.Name,
            c.Contact,
            c.Email,
            c.PromoterID as ParentPromoterID,
            p.Name as ParentName,
            COUNT(DISTINCT CASE WHEN DATE(pay.SubmittedAt) BETWEEN :fromDate AND :toDate THEN pay.PaymentID END) as total_payments,
            SUM(CASE WHEN DATE(pay.SubmittedAt) BETWEEN :fromDate AND :toDate AND pay.Status = 'Verified' THEN pay.Amount ELSE 0 END) as total_amount
            FROM Customers c
            LEFT JOIN Promoters p ON c.PromoterID = p.PromoterUniqueID
            LEFT JOIN Payments pay ON pay.CustomerID = c.CustomerID
            $whereClause
            GROUP BY c.CustomerID, c.CustomerUniqueID, c.Name, c.Contact, c.Email, c.PromoterID, p.Name";

        $stmt = $conn->prepare($memberQuery);
        foreach ($memberParams as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($members)) {
            $allTeamData[$teamName] = $members;
        }
    }

    return $allTeamData;
}

// Get all team data
$allTeamData = getAllTeamStats($conn, $fromDate, $toDate, $promoterId);

// Set headers for Excel download
$filename = 'all_teams_sales_' . $fromDate . '_to_' . $toDate;
if ($promoterId && $promoterName) {
    $filename .= '_' . str_replace(' ', '_', $promoterName);
}
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
header('Cache-Control: max-age=0');

// Output Excel content
echo "All Teams Sales Report\n";
if ($fromDate === $toDate) {
    echo "Date: " . date('F d, Y', strtotime($fromDate)) . "\n";
} else {
    echo "Date Range: " . date('F d, Y', strtotime($fromDate)) . " to " . date('F d, Y', strtotime($toDate)) . "\n";
}
if ($promoterId && $promoterName) {
    echo "Filtered by Promoter: " . $promoterName . " (" . $promoterId . ")\n";
}
echo "\n";

// Export each team
foreach ($allTeamData as $teamName => $members) {
    echo "Team: " . $teamName . "\n";
    echo "ID\tName\tContact\tEmail\tParent\tPayments (Period)\tAmount (Period)\n";
    
    foreach ($members as $member) {
        echo implode("\t", [
            $member['unique_id'],
            $member['Name'],
            $member['Contact'],
            $member['Email'],
            $member['ParentName'] ?? 'None',
            $member['total_payments'],
            '₹' . number_format($member['total_amount'])
        ]) . "\n";
    }
    
    echo "\n"; // Empty line between teams
}

