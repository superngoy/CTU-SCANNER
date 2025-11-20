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

    // Get currently checked-in visitors (check_out_time IS NULL)
    $stmt = $conn->prepare("
        SELECT 
            v.id,
            v.visitor_code,
            v.first_name,
            v.middle_name,
            v.last_name,
            v.company,
            v.purpose,
            v.image,
            v.contact_number,
            vl.check_in_time,
            vl.location,
            vl.id as log_id
        FROM visitors v
        INNER JOIN visitor_logs vl ON v.id = vl.visitor_id
        WHERE vl.check_out_time IS NULL AND DATE(vl.check_in_time) = CURDATE()
        ORDER BY vl.check_in_time DESC
    ");
    $stmt->execute();
    $activeVisitors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['active_visitors' => $activeVisitors]);
} catch (Exception $e) {
    error_log("get_active_visitors error: " . $e->getMessage());
    echo json_encode(['active_visitors' => [], 'error' => $e->getMessage()]);
}
?>
