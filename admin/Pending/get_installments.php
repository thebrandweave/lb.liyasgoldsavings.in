<?php
session_start();
require_once("../../config/config.php");
$database = new Database();
$conn = $database->getConnection();

header('Content-Type: application/json');

if (!isset($_GET['scheme_id']) || empty($_GET['scheme_id'])) {
    echo json_encode(['success' => false, 'error' => 'Scheme ID is required']);
    exit;
}

$schemeId = $_GET['scheme_id'];

try {
    $stmt = $conn->prepare("
        SELECT InstallmentID, InstallmentName, InstallmentNumber, Amount, DrawDate 
        FROM Installments 
        WHERE SchemeID = ? AND Status = 'Active'
        ORDER BY InstallmentNumber ASC
    ");
    $stmt->execute([$schemeId]);
    $installments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'installments' => $installments
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
}
