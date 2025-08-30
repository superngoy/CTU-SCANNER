<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/functions.php';

$method = $_SERVER['REQUEST_METHOD'];
$endpoint = $_GET['endpoint'] ?? '';
$scanner = new CTUScanner();

switch ($endpoint) {
    case 'scan':
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $qrData = $input['qr_data'] ?? '';
            $scannerId = $input['scanner_id'] ?? '';
            
            $person = $scanner->verifyQRCode($qrData);
            if ($person) {
                // Determine scan type
                $stmt = $scanner->conn->prepare("SELECT typeofScanner FROM scanner WHERE ScannerID = ?");
                $stmt->execute([$scannerId]);
                $scannerInfo = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($scannerInfo['typeofScanner'] === 'entrance') {
                    $result = $scanner->logEntry($person[$person['type'] === 'student' ? 'StudentID' : 'FacultyID'], $person['type'], $scannerId);
                    $action = 'Entry';
                } else {
                    $result = $scanner->logExit($person[$person['type'] === 'student' ? 'StudentID' : 'FacultyID'], $person['type'], $scannerId);
                    $action = 'Exit';
                }
                
                echo json_encode([
                    'success' => $result,
                    'message' => $result ? "$action recorded successfully" : "Failed to record $action",
                    'person' => $person,
                    'action' => $action
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid QR Code']);
            }
        }
        break;
        
    case 'stats':
        if ($method === 'GET') {
            $date = $_GET['date'] ?? date('Y-m-d');
            $stats = $scanner->getDailyStats($date);
            echo json_encode($stats);
        }
        break;
        
    case 'recent_activity':
        if ($method === 'GET') {
            $type = $_GET['type'] ?? 'entries';
            $limit = $_GET['limit'] ?? 20;
            
            if ($type === 'entries') {
                $data = $scanner->getRecentEntries($limit);
            } else {
                $data = $scanner->getRecentExits($limit);
            }
            
            echo json_encode(['data' => $data]);
        }
        break;
        
    case 'peak_hours':
        if ($method === 'GET') {
            $date = $_GET['date'] ?? date('Y-m-d');
            $peakHours = $scanner->getPeakHours($date);
            echo json_encode(['peak_hours' => $peakHours]);
        }
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}
?>