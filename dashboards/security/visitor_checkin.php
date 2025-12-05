<?php
session_start();

// Add authentication check for security
if (!isset($_SESSION['security_id']) || $_SESSION['user_type'] !== 'security') {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/visitor_checkin_error.log');

if (!ob_get_level()) ob_start();

require_once '../../config/database.php';
require_once '../../includes/notification_helpers.php';

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

    $action = $_POST['action'] ?? '';
    $visitorId = (int)($_POST['visitor_id'] ?? 0);

    if (!$visitorId) {
        send_json(['success' => false, 'message' => 'Visitor ID required']);
    }

    $database = new Database();
    if (method_exists($database, 'connect')) {
        $conn = $database->connect();
    } elseif (method_exists($database, 'getConnection')) {
        $conn = $database->getConnection();
    } else {
        $conn = $database->connection();
    }

    // Get visitor details
    $stmtVisitor = $conn->prepare("SELECT * FROM visitors WHERE id = ?");
    $stmtVisitor->execute([$visitorId]);
    $visitor = $stmtVisitor->fetch(PDO::FETCH_ASSOC);

    if (!$visitor) {
        send_json(['success' => false, 'message' => 'Visitor not found']);
    }

    $location = $_POST['location'] ?? 'Main Entrance';

    if ($action === 'check_in') {
        // Create new check-in record
        $stmt = $conn->prepare("
            INSERT INTO visitor_logs (visitor_id, check_in_time, location)
            VALUES (?, NOW(), ?)
        ");
        $stmt->execute([$visitorId, $location]);
        $logId = $conn->lastInsertId();

        // Send notification to admin
        $visitorName = trim($visitor['first_name'] . ' ' . $visitor['middle_name'] . ' ' . $visitor['last_name']);
        notifyVisitorCheckIn($visitorName, 'Guest', $location);

        send_json([
            'success' => true,
            'message' => 'Visitor checked in successfully',
            'log_id' => $logId,
            'visitor' => [
                'id' => $visitor['id'],
                'visitor_code' => $visitor['visitor_code'],
                'name' => trim($visitor['first_name'] . ' ' . $visitor['middle_name'] . ' ' . $visitor['last_name']),
                'first_name' => $visitor['first_name'],
                'last_name' => $visitor['last_name'],
                'company' => $visitor['company'],
                'purpose' => $visitor['purpose'],
                'image' => $visitor['image'],
                'contact_number' => $visitor['contact_number']
            ]
        ]);

    } elseif ($action === 'check_out') {
        // Get active log record
        $stmtLog = $conn->prepare("
            SELECT * FROM visitor_logs 
            WHERE visitor_id = ? AND check_out_time IS NULL 
            ORDER BY check_in_time DESC LIMIT 1
        ");
        $stmtLog->execute([$visitorId]);
        $log = $stmtLog->fetch(PDO::FETCH_ASSOC);

        if (!$log) {
            send_json(['success' => false, 'message' => 'No active check-in found for this visitor']);
        }

        // Update check-out time
        $stmt = $conn->prepare("
            UPDATE visitor_logs 
            SET check_out_time = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$log['id']]);

        // Calculate dwell time
        $checkinTime = strtotime($log['check_in_time']);
        $checkoutTime = time();
        $dwellMinutes = round(($checkoutTime - $checkinTime) / 60);

        // Send notification to admin
        $visitorName = trim($visitor['first_name'] . ' ' . $visitor['middle_name'] . ' ' . $visitor['last_name']);
        notifyVisitorCheckOut($visitorName);

        send_json([
            'success' => true,
            'message' => 'Visitor checked out successfully',
            'dwell_time' => $dwellMinutes . ' minutes',
            'visitor_code' => $visitor['visitor_code']
        ]);

    } else {
        send_json(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    error_log("Visitor checkin error: " . $e->getMessage());
    send_json(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
?>
