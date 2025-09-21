<?php
error_reporting(E_ALL);
ini_set('log_errors', 1);

header('Content-Type: application/json');
require_once '../../includes/functions.php';

if ($_POST['action'] === 'scan') {
    $scanner = new CTUScanner();
    $qr_data = $_POST['qr_data'] ?? '';
    $scanner_id = $_POST['scanner_id'] ?? '';
    
    error_log("Scan process - QR Data: $qr_data, Scanner: $scanner_id");
    
    try {
        // Verify QR Code
        $person = $scanner->verifyQRCode($qr_data);
        
        if ($person) {
            error_log("Person found: " . json_encode($person));
            
            // Get person's image from database
            $database = new Database();
            
            // Use the same method detection as in CTUScanner
            if (method_exists($database, 'connect')) {
                $conn = $database->connect();
            } elseif (method_exists($database, 'getConnection')) {
                $conn = $database->getConnection();
            } elseif (method_exists($database, 'connection')) {
                $conn = $database->connection();
            } else {
                throw new Exception('Cannot find database connection method');
            }
            
            // Get scanner info to determine entry/exit
            $stmt = $conn->prepare("SELECT typeofScanner FROM scanner WHERE ScannerID = ?");
            $stmt->execute([$scanner_id]);
            $scanner_info = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$scanner_info) {
                echo json_encode(['success' => false, 'message' => 'Invalid scanner ID']);
                exit();
            }
            
            $person_id = $person['type'] === 'student' ? $person['StudentID'] : 
                        ($person['type'] === 'faculty' ? $person['FacultyID'] : $person['SecurityID']);
            
            // Get person's profile image
            $image_path = null;
            if ($person['type'] === 'student') {
                $stmt = $conn->prepare("SELECT image FROM students WHERE StudentID = ?");
            } elseif ($person['type'] === 'faculty') {
                $stmt = $conn->prepare("SELECT image FROM faculty WHERE FacultyID = ?");
            } elseif ($person['type'] === 'security') {
                $stmt = $conn->prepare("SELECT image FROM security WHERE SecurityID = ?");
            }
            
            if (isset($stmt)) {
                $stmt->execute([$person_id]);
                $image_result = $stmt->fetch(PDO::FETCH_ASSOC);
                $image_path = $image_result['image'] ?? null;
            }
            
            // Generate image URL
            $image_url = getImageUrl($image_path);
            
            if ($scanner_info['typeofScanner'] === 'entrance') {
                $result = $scanner->logEntry($person_id, $person['type'], $scanner_id);
                $action = 'Entry';
                error_log("Logging entry for $person_id ($person[type])");
            } else {
                $result = $scanner->logExit($person_id, $person['type'], $scanner_id);
                $action = 'Exit';
                error_log("Logging exit for $person_id ($person[type])");
            }
            
            if ($result) {
                // Get person's name based on type
                if ($person['type'] === 'student') {
                    $firstName = $person['StudentFName'];
                    $middleName = $person['StudentMName'] ?? '';
                    $lastName = $person['StudentLName'];
                } elseif ($person['type'] === 'faculty') {
                    $firstName = $person['FacultyFName'];
                    $middleName = $person['FacultyMName'] ?? '';
                    $lastName = $person['FacultyLName'];
                } elseif ($person['type'] === 'security') {
                    $firstName = $person['SecurityFName'];
                    $middleName = $person['SecurityMName'] ?? '';
                    $lastName = $person['SecurityLName'];
                } else {
                    $firstName = 'Unknown';
                    $middleName = '';
                    $lastName = '';
                }
                
                $name = trim($firstName . ' ' . $middleName . ' ' . $lastName);
                $name = preg_replace('/\s+/', ' ', $name); // Remove extra spaces
                
                error_log("$action recorded successfully for $name");
                
                echo json_encode([
                    'success' => true,
                    'message' => $action . ' recorded successfully',
                    'person' => [
                        'name' => $name,
                        'id' => $person_id,
                        'type' => ucfirst($person['type']),
                        'action' => $action,
                        'image' => $image_url,
                        'firstName' => $firstName,
                        'middleName' => $middleName,
                        'lastName' => $lastName
                    ]
                ]);
            } else {
                error_log("Failed to record $action for $person_id");
                echo json_encode(['success' => false, 'message' => 'Failed to record ' . strtolower($action)]);
            }
        } else {
            error_log("Invalid QR Code: $qr_data");
            echo json_encode(['success' => false, 'message' => 'Invalid QR Code or inactive account']);
        }
    } catch (Exception $e) {
        error_log("Scan process error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

/**
 * Generate image URL for display
 */
function getImageUrl($imagePath) {
    if (empty($imagePath)) {
        return null; // Return null for no image, frontend will handle default avatar
    }
    
    // Check if image file exists
    $fullPath = '../../' . $imagePath;
    if (file_exists($fullPath)) {
        // Return relative path from scanner directory
        return '../../' . $imagePath;
    }
    
    return null; // File doesn't exist
}
?>