<?php
header('Content-Type: application/json');
require_once '../../includes/functions.php';

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
?>