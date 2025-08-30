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
        // Use the new public method instead of accessing $conn directly
        $scanner_info = $scanner->getScannerType($scanner_id);
        
        if ($scanner_info && $scanner_info['typeofScanner'] === 'entrance') {
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