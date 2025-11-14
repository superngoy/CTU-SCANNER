<?php
// Direct test of analytics endpoint
header('Content-Type: application/json');

// Set error reporting
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Log errors to file
ini_set('log_errors', 1);
ini_set('error_log', '../../test_analytics_errors.log');

try {
    require_once '../../includes/functions.php';
    
    $scanner = new CTUScanner();
    
    // Test dashboard_stats action
    $dateRange = 'today';
    $userType = 'all';
    
    $today = date('Y-m-d');
    $startDate = $today;
    $endDate = $today;
    
    // Test total entries query
    $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM entrylogs e WHERE e.Date BETWEEN ? AND ?");
    $stmt->execute([$startDate, $endDate]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalEntries = (int)$result['count'];
    
    echo json_encode([
        'status' => 'success',
        'totalEntries' => $totalEntries,
        'database_connected' => true,
        'date' => $today,
        'startDate' => $startDate,
        'endDate' => $endDate
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
