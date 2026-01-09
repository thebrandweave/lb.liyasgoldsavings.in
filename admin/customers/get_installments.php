<?php
session_start();

require_once("../../config/config.php");

header('Content-Type: application/json');

try {
    $database = new Database();
    $conn = $database->getConnection();

    if (!isset($_GET['scheme_id']) || $_GET['scheme_id'] === '') {
        echo json_encode([]);
        exit;
    }

    $schemeId = $_GET['scheme_id'];

    $stmt = $conn->prepare("SELECT i.InstallmentID, i.InstallmentName, i.InstallmentNumber, s.SchemeName
                             FROM Installments i
                             JOIN Schemes s ON s.SchemeID = i.SchemeID
                             WHERE i.SchemeID = :schemeId AND i.Status = 'Active'
                             ORDER BY i.InstallmentNumber ASC");
    $stmt->bindValue(':schemeId', $schemeId, PDO::PARAM_INT);
    $stmt->execute();
    $installments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($installments);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal Server Error']);
}




