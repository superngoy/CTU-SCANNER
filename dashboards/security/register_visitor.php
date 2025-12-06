<?php
session_start();

// Add authentication check for security
if (!isset($_SESSION['security_id']) || $_SESSION['user_type'] !== 'security') {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Suppress direct error output to the browser (prevents injected warnings on free hosts)
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/visitor_error.log');

// Start output buffering so we can clean any accidental output before returning JSON
if (!ob_get_level()) ob_start();

require_once '../../config/database.php';
require_once '../../includes/notification_helpers.php';

// Helper to send clean JSON and exit
function send_json($arr) {
    if (ob_get_length()) {
        @ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        send_json(['success' => false, 'message' => 'Invalid request method']);
    }

    // Get database connection
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

    // Validate required fields using safe functions
    $firstName = getSafePOST('first_name', '', 'string');
    $middleName = getSafePOST('middle_name', '', 'string');
    $lastName = getSafePOST('last_name', '', 'string');
    $contactNumber = getSafePOST('contact_number', '', 'string');
    $email = getSafePOST('email', '', 'email');
    $company = getSafePOST('company', '', 'string');
    $purpose = getSafePOST('purpose', '', 'string');
    $idProvidedType = getSafePOST('id_provided_type', '', 'string');
    $idProvidedNumber = getSafePOST('id_provided_number', '', 'string');

    // Validate input lengths and formats
    if (!$firstName || strlen($firstName) > 50) {
        send_json(['success' => false, 'message' => 'Invalid first name (max 50 characters)']);
    }
    
    if (!$lastName || strlen($lastName) > 50) {
        send_json(['success' => false, 'message' => 'Invalid last name (max 50 characters)']);
    }
    
    if (!$contactNumber || !validatePhoneNumber($contactNumber)) {
        send_json(['success' => false, 'message' => 'Invalid contact number']);
    }
    
    if (!$purpose || strlen($purpose) > 255) {
        send_json(['success' => false, 'message' => 'Invalid purpose (max 255 characters)']);
    }

    // Validate email if provided
    if ($email && !validateEmail($email)) {
        send_json(['success' => false, 'message' => 'Invalid email format']);
    }

    // Generate unique visitor code
    $visitorCode = 'V' . date('YmdHis') . rand(1000, 9999);

    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/visitors/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = $visitorCode . '_' . time() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName = sanitizeFilename($fileName);
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $imagePath = 'uploads/visitors/' . $fileName;
        }
    }

    // Handle ID image upload
    $idImagePath = null;
    if (isset($_FILES['id_image']) && $_FILES['id_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../uploads/visitors/ids/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = $visitorCode . '_id_' . time() . '.' . pathinfo($_FILES['id_image']['name'], PATHINFO_EXTENSION);
        $fileName = sanitizeFilename($fileName);
        $uploadPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['id_image']['tmp_name'], $uploadPath)) {
            $idImagePath = 'uploads/visitors/ids/' . $fileName;
        }
    }

    // Insert into visitors table
    $stmt = $conn->prepare("
        INSERT INTO visitors 
        (visitor_code, first_name, middle_name, last_name, contact_number, email, company, purpose, id_provided_type, id_provided_number, image, id_image, isActive, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())
    ");

    $stmt->execute([
        $visitorCode,
        $firstName,
        $middleName,
        $lastName,
        $contactNumber,
        $email ?: null,
        $company ?: null,
        $purpose,
        $idProvidedType ?: null,
        $idProvidedNumber ?: null,
        $imagePath,
        $idImagePath
    ]);

    // Get the inserted visitor ID
    $visitorId = $conn->lastInsertId();

    // Get visitor details for response
    $stmtGet = $conn->prepare("
        SELECT * FROM visitors WHERE id = ?
    ");
    $stmtGet->execute([$visitorId]);
    $visitor = $stmtGet->fetch(PDO::FETCH_ASSOC);

    // Send notification to admin
    $visitorName = trim($firstName . ' ' . $middleName . ' ' . $lastName);
    notifyVisitorRegistered($visitorName, 'Guest', $purpose);

    send_json([
        'success' => true,
        'message' => 'Visitor registered successfully',
        'visitor' => [
            'id' => $visitor['id'],
            'visitor_code' => $visitor['visitor_code'],
            'name' => trim($firstName . ' ' . $middleName . ' ' . $lastName),
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'contact_number' => $contactNumber,
            'email' => $email,
            'company' => $company,
            'purpose' => $purpose,
            'id_provided_type' => $idProvidedType,
            'id_provided_number' => $idProvidedNumber,
            'image' => $imagePath,
            'id_image' => $idImagePath,
            'created_at' => $visitor['created_at']
        ]
    ]);

} catch (Exception $e) {
    error_log("Visitor registration error: " . $e->getMessage());
    send_json(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?>
