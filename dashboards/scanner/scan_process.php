<?php
header('Content-Type: application/json');
require_once '../../includes/functions.php';

if ($_POST['action'] === 'scan') {
    $scanner = new CTUScanner();
    $qr_data = $_POST['qr_data'];
    $scanner_id = $_POST['scanner_id'];
    
    // Verify QR Code
    $person = $scanner->verifyQRCode($qr_data);
    
    if ($person) {
        // Determine if it's entry or exit based on scanner type
        $stmt = $scanner->conn->prepare("SELECT typeofScanner FROM scanner WHERE ScannerID = ?");
        $stmt->execute([$scanner_id]);
        $scanner_info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($scanner_info['typeofScanner'] === 'entrance') {
            $result = $scanner->logEntry($person[$person['type'] === 'student' ? 'StudentID' : 'FacultyID'], $person['type'], $scanner_id);
            $action = 'Entry';
        } else {
            $result = $scanner->logExit($person[$person['type'] === 'student' ? 'StudentID' : 'FacultyID'], $person['type'], $scanner_id);
            $action = 'Exit';
        }
        
        if ($result) {
            $name = $person[$person['type'] === 'student' ? 'StudentFName' : 'FacultyFName'] . ' ' . 
                   $person[$person['type'] === 'student' ? 'StudentLName' : 'FacultyLName'];
            
            echo json_encode([
                'success' => true,
                'message' => $action . ' recorded successfully',
                'person' => [
                    'name' => $name,
                    'id' => $person[$person['type'] === 'student' ? 'StudentID' : 'FacultyID'],
                    'type' => ucfirst($person['type']),
                    'action' => $action
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to record ' . strtolower($action)]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid QR Code or inactive account']);
    }
}
?>