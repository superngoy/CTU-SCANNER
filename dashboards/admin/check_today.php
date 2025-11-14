<?php
header('Content-Type: application/json');
require_once '../../includes/functions.php';

try {
    $scanner = new CTUScanner();
    
    // Get today's entry logs
    $stmt = $scanner->conn->prepare("
        SELECT PersonID, PersonType, Date, Timestamp, ScannerID
        FROM entrylogs
        WHERE Date = CURDATE()
        ORDER BY Timestamp
    ");
    $stmt->execute();
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'today_entries' => $entries,
        'count' => count($entries)
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
