<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Add authentication check
if (!isset($_SESSION['security_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

require_once '../../config/database.php';
require_once '../../includes/functions.php';

try {
    $scanner = new CTUScanner();
    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'stats':
            $stats = $scanner->getDailyStats();
            echo json_encode($stats);
            break;
            
        case 'entries':
            $entries = $scanner->getRecentEntries(20);
            echo json_encode(['entries' => $entries]);
            break;
            
        case 'exits':
            $exits = $scanner->getRecentExits(20);
            echo json_encode(['exits' => $exits]);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Realtime data error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'message' => $e->getMessage()]);
}
?>