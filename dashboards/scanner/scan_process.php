<?php
// Suppress direct error output to the browser (prevents injected warnings on free hosts)
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/scan_error.log');

// Start output buffering so we can clean any accidental output before returning JSON
if (!ob_get_level()) ob_start();

require_once '../../includes/functions.php';

// Helper to send clean JSON and exit
function send_json($arr) {
    if (ob_get_length()) {
        @ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr);
    exit;
}

if ($_POST['action'] === 'scan') {
    $scanner = new CTUScanner();
    $barcode_data = $_POST['barcode_data'] ?? $_POST['qr_data'] ?? ''; // Support both barcode and legacy qr_data
    $scanner_id = $_POST['scanner_id'] ?? '';
    
    error_log("Scan process - Barcode Data: $barcode_data, Scanner: $scanner_id");
    
    try {
        // Get database connection early - we'll need it for logging
        $database = new Database();
        if (method_exists($database, 'connect')) {
            $conn = $database->connect();
        } elseif (method_exists($database, 'getConnection')) {
            $conn = $database->getConnection();
        } elseif (method_exists($database, 'connection')) {
            $conn = $database->connection();
        } else {
            throw new Exception('Cannot find database connection method');
        }
        
        // Verify Barcode
        $person = $scanner->verifyQRCode($barcode_data);
        
        if ($person) {
            error_log("Person found: " . json_encode($person));
            
            // CHECK IF STUDENT IS ENROLLED - if not, reject the entry
            if ($person['type'] === 'student' && (!isset($person['IsEnroll']) || $person['IsEnroll'] == 0)) {
                error_log("Student not enrolled: {$person['StudentID']}");
                
                $person_id = $person['StudentID'];
                
                // Get scanner info for location
                $stmt = $conn->prepare("SELECT Location FROM scanner WHERE ScannerID = ?");
                $stmt->execute([$scanner_id]);
                $scanner_info = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Log failed attempt - not enrolled
                $stmtAttempts = $conn->prepare("
                    INSERT INTO scan_attempts (scanned_at, qr_data, person_id, person_type, scanner_id, location, status, reason, ip_address)
                    VALUES (NOW(), ?, ?, 'student', ?, ?, 'failed', 'not_enrolled', ?)
                ");
                $stmtAttempts->execute([
                    $barcode_data,
                    $person_id,
                    $scanner_id,
                    $scanner_info['Location'] ?? 'Unknown',
                    $_SERVER['REMOTE_ADDR'] ?? null
                ]);
                
                send_json([
                    'success' => false,
                    'message' => 'Access Denied: Student is not enrolled',
                    'reason' => 'not_enrolled'
                ]);
            }
            
            // Get scanner info to determine entry/exit
            $stmt = $conn->prepare("SELECT typeofScanner, Location FROM scanner WHERE ScannerID = ?");
            $stmt->execute([$scanner_id]);
            $scanner_info = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$scanner_info) {
                send_json(['success' => false, 'message' => 'Invalid scanner ID']);
            }
            
            $person_id = $person['type'] === 'student' ? $person['StudentID'] : 
                        ($person['type'] === 'faculty' ? $person['FacultyID'] : 
                        ($person['type'] === 'staff' ? $person['StaffID'] : $person['SecurityID']));
            
            // Get person's profile image
            $image_path = null;
            if ($person['type'] === 'student') {
                $stmt = $conn->prepare("SELECT image FROM students WHERE StudentID = ?");
            } elseif ($person['type'] === 'faculty') {
                $stmt = $conn->prepare("SELECT image FROM faculty WHERE FacultyID = ?");
            } elseif ($person['type'] === 'staff') {
                $stmt = $conn->prepare("SELECT image FROM staff WHERE StaffID = ?");
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
                } elseif ($person['type'] === 'staff') {
                    $firstName = $person['StaffFName'];
                    $middleName = $person['StaffMName'] ?? '';
                    $lastName = $person['StaffLName'];
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
                
                // Log successful scan attempt to scan_attempts table
                $meta = json_encode([
                    'action' => $action,
                    'department' => $person['Department'] ?? null
                ]);
                
                $stmtAttempts = $conn->prepare("
                    INSERT INTO scan_attempts (scanned_at, qr_data, person_id, person_type, scanner_id, location, status, reason, meta, ip_address)
                    VALUES (NOW(), ?, ?, ?, ?, ?, 'success', NULL, ?, ?)
                ");
                $stmtAttempts->execute([
                    $barcode_data,
                    $person_id,
                    $person['type'],
                    $scanner_id,
                    $scanner_info['Location'] ?? 'Unknown',
                    $meta,
                    $_SERVER['REMOTE_ADDR'] ?? null
                ]);
                
                // Get additional information based on user type
                $additionalInfo = [];
                if ($person['type'] === 'student') {
                    $additionalInfo = [
                        'department' => $person['Department'] ?? 'N/A',
                        'course' => $person['Course'] ?? 'N/A',
                        'year' => $person['YearLvl'] ?? 'N/A',
                        'section' => $person['Section'] ?? 'N/A',
                        'isEnroll' => $person['IsEnroll'] ?? 1
                    ];
                } elseif ($person['type'] === 'faculty') {
                    $additionalInfo = [
                        'department' => $person['Department'] ?? 'N/A'
                    ];
                } elseif ($person['type'] === 'staff') {
                    $additionalInfo = [
                        'department' => $person['Department'] ?? 'N/A'
                    ];
                }
                
                error_log("$action recorded successfully for $name");
                
                send_json([
                    'success' => true,
                    'message' => $action . ' recorded successfully',
                    'person' => array_merge([
                        'name' => $name,
                        'id' => $person_id,
                        'type' => ucfirst($person['type']),
                        'action' => $action,
                        'image' => $image_url,
                        'firstName' => $firstName,
                        'middleName' => $middleName,
                        'lastName' => $lastName
                    ], $additionalInfo)
                ]);
            } else {
                error_log("Failed to record $action for $person_id");
                
                // Log failed attempt
                $stmtAttempts = $conn->prepare("
                    INSERT INTO scan_attempts (scanned_at, qr_data, person_id, person_type, scanner_id, location, status, reason, ip_address)
                    VALUES (NOW(), ?, ?, ?, ?, ?, 'failed', 'log_failed', ?)
                ");
                $stmtAttempts->execute([
                    $barcode_data,
                    $person_id,
                    $person['type'],
                    $scanner_id,
                    $scanner_info['Location'] ?? 'Unknown',
                    $_SERVER['REMOTE_ADDR'] ?? null
                ]);
                
                send_json(['success' => false, 'message' => 'Failed to record ' . strtolower($action)]);
            }
        } else {
            error_log("Barcode not found or user inactive: $barcode_data");
            
            // Get scanner location for logging
            $stmt = $conn->prepare("SELECT Location FROM scanner WHERE ScannerID = ?");
            $stmt->execute([$scanner_id]);
            $scanner_info = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if the QR data exists but is inactive (student, faculty, or security)
            $reason = 'invalid_qr';
            $inactiveUser = null;
            
            // Check if it's an inactive student
            $stmt = $conn->prepare("SELECT 'student' as type, StudentID as id FROM students WHERE StudentID = ? AND isActive = 0");
            $stmt->execute([$barcode_data]);
            $inactiveUser = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$inactiveUser) {
                // Check if it's an inactive faculty
                $stmt = $conn->prepare("SELECT 'faculty' as type, FacultyID as id FROM faculty WHERE FacultyID = ? AND isActive = 0");
                $stmt->execute([$barcode_data]);
                $inactiveUser = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if (!$inactiveUser) {
                // Check if it's an inactive staff
                $stmt = $conn->prepare("SELECT 'staff' as type, StaffID as id FROM staff WHERE StaffID = ? AND isActive = 0");
                $stmt->execute([$barcode_data]);
                $inactiveUser = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if (!$inactiveUser) {
                // Check if it's an inactive security
                $stmt = $conn->prepare("SELECT 'security' as type, SecurityID as id FROM security WHERE SecurityID = ? AND isActive = 0");
                $stmt->execute([$barcode_data]);
                $inactiveUser = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            // If we found an inactive user, log with 'inactive' reason
            if ($inactiveUser) {
                $reason = 'inactive';
                $log_person_id = $inactiveUser['id'];
                $log_person_type = $inactiveUser['type'];
            }
            
            // Log failed attempt
            if ($inactiveUser) {
                $stmtAttempts = $conn->prepare("
                    INSERT INTO scan_attempts (scanned_at, qr_data, person_id, person_type, scanner_id, location, status, reason, ip_address)
                    VALUES (NOW(), ?, ?, ?, ?, ?, 'failed', ?, ?)
                ");
                $stmtAttempts->execute([
                    $barcode_data,
                    $log_person_id,
                    $log_person_type,
                    $scanner_id,
                    $scanner_info['Location'] ?? 'Unknown',
                    $reason,
                    $_SERVER['REMOTE_ADDR'] ?? null
                ]);
            } else {
                $stmtAttempts = $conn->prepare("
                    INSERT INTO scan_attempts (scanned_at, qr_data, scanner_id, location, status, reason, ip_address)
                    VALUES (NOW(), ?, ?, ?, 'failed', ?, ?)
                ");
                $stmtAttempts->execute([
                    $barcode_data,
                    $scanner_id,
                    $scanner_info['Location'] ?? 'Unknown',
                    $reason,
                    $_SERVER['REMOTE_ADDR'] ?? null
                ]);
            }
            
            send_json([
                'success' => false,
                'message' => $reason === 'inactive' ? 'Account is inactive' : 'Invalid Barcode',
                'reason' => $reason
            ]);
        }
    } catch (Exception $e) {
        error_log("Scan process error: " . $e->getMessage());
        send_json(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
    }
} else {
    send_json(['success' => false, 'message' => 'Invalid request']);
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