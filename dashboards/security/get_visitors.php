<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $database = new Database();
    if (method_exists($database, 'connect')) {
        $conn = $database->connect();
    } elseif (method_exists($database, 'getConnection')) {
        $conn = $database->getConnection();
    } else {
        $conn = $database->connection();
    }

    // Get today's registered visitors
    $stmt = $conn->prepare("
        SELECT id, visitor_code, first_name, middle_name, last_name, contact_number, email, company, purpose, id_provided_type, id_provided_number, image, created_at
        FROM visitors
        WHERE DATE(created_at) = CURDATE() AND isActive = 1
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['visitors' => $visitors]);
} catch (Exception $e) {
    error_log("get_visitors error: " . $e->getMessage());
    echo json_encode(['visitors' => [], 'error' => $e->getMessage()]);
}
?>
