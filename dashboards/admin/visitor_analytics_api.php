<?php
session_start();

// Add authentication check for admin
if (!isset($_SESSION['admin_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../../config/database.php';

header('Content-Type: application/json');

$database = new Database();
if (method_exists($database, 'connect')) {
    $conn = $database->connect();
} elseif (method_exists($database, 'getConnection')) {
    $conn = $database->getConnection();
} else {
    $conn = $database->connection();
}

$action = $_POST['action'] ?? '';
$startDate = $_POST['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_POST['end_date'] ?? date('Y-m-d');

if ($action === 'get_visitor_analytics') {
    try {
        // Total visitors registered
        $stmt = $conn->prepare("
            SELECT COUNT(*) as total FROM visitors 
            WHERE DATE(created_at) BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $totalVisitors = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total check-ins
        $stmt = $conn->prepare("
            SELECT COUNT(*) as total FROM visitor_logs 
            WHERE DATE(check_in_time) BETWEEN ? AND ?
        ");
        $stmt->execute([$startDate, $endDate]);
        $totalCheckIns = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total check-outs
        $stmt = $conn->prepare("
            SELECT COUNT(*) as total FROM visitor_logs 
            WHERE DATE(check_out_time) BETWEEN ? AND ? AND check_out_time IS NOT NULL
        ");
        $stmt->execute([$startDate, $endDate]);
        $totalCheckOuts = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // Average dwell time
        $stmt = $conn->prepare("
            SELECT AVG(TIMESTAMPDIFF(MINUTE, check_in_time, check_out_time)) as avg_dwell
            FROM visitor_logs 
            WHERE DATE(check_in_time) BETWEEN ? AND ? AND check_out_time IS NOT NULL
        ");
        $stmt->execute([$startDate, $endDate]);
        $avgDwell = round($stmt->fetch(PDO::FETCH_ASSOC)['avg_dwell'] ?? 0, 1);

        // Visitors by purpose
        $stmt = $conn->prepare("
            SELECT purpose, COUNT(*) as count 
            FROM visitors 
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY purpose 
            ORDER BY count DESC 
            LIMIT 10
        ");
        $stmt->execute([$startDate, $endDate]);
        $visitorsByPurpose = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Daily visitor trend
        $stmt = $conn->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count 
            FROM visitors 
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at) 
            ORDER BY date ASC
        ");
        $stmt->execute([$startDate, $endDate]);
        $dailyTrend = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Recent visitors
        $stmt = $conn->prepare("
            SELECT id, visitor_code, first_name, middle_name, last_name, company, purpose, contact_number, created_at
            FROM visitors
            WHERE DATE(created_at) BETWEEN ? AND ?
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$startDate, $endDate]);
        $recentVisitors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Visitor logs
        $stmt = $conn->prepare("
            SELECT 
                v.visitor_code,
                v.first_name,
                v.last_name,
                vl.check_in_time,
                vl.check_out_time,
                CASE 
                    WHEN vl.check_out_time IS NOT NULL 
                    THEN CONCAT(TIMESTAMPDIFF(HOUR, vl.check_in_time, vl.check_out_time), 'h ', MOD(TIMESTAMPDIFF(MINUTE, vl.check_in_time, vl.check_out_time), 60), 'm')
                    ELSE '--'
                END as dwell_time
            FROM visitor_logs vl
            LEFT JOIN visitors v ON vl.visitor_id = v.id
            WHERE DATE(vl.check_in_time) BETWEEN ? AND ?
            ORDER BY vl.check_in_time DESC
            LIMIT 50
        ");
        $stmt->execute([$startDate, $endDate]);
        $visitorLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'totalVisitors' => $totalVisitors,
            'totalCheckIns' => $totalCheckIns,
            'totalCheckOuts' => $totalCheckOuts,
            'avgDwell' => $avgDwell,
            'visitorsByPurpose' => $visitorsByPurpose,
            'dailyTrend' => $dailyTrend,
            'recentVisitors' => $recentVisitors,
            'visitorLogs' => $visitorLogs
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error loading analytics: ' . $e->getMessage()
        ]);
    }
} elseif ($action === 'recent_visitors') {
    try {
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));
        
        $stmt = $conn->prepare("
            SELECT id, visitor_code, first_name, middle_name, last_name, company, purpose, contact_number, created_at
            FROM visitors
            WHERE DATE(created_at) BETWEEN ? AND ?
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $stmt->execute([$startDate, $endDate]);
        $visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($visitors);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} elseif ($action === 'check_in_out') {
    try {
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $startDate = date('Y-m-d', strtotime($startDate));
        $endDate = date('Y-m-d', strtotime($endDate));
        
        $stmt = $conn->prepare("
            SELECT 
                v.visitor_code,
                v.first_name,
                v.last_name,
                vl.check_in_time,
                vl.check_out_time,
                CASE 
                    WHEN vl.check_out_time IS NOT NULL 
                    THEN CONCAT(TIMESTAMPDIFF(HOUR, vl.check_in_time, vl.check_out_time), 'h ', MOD(TIMESTAMPDIFF(MINUTE, vl.check_in_time, vl.check_out_time), 60), 'm')
                    ELSE '--'
                END as dwell_time
            FROM visitor_logs vl
            LEFT JOIN visitors v ON vl.visitor_id = v.id
            WHERE DATE(vl.check_in_time) BETWEEN ? AND ?
            ORDER BY vl.check_in_time DESC
            LIMIT 50
        ");
        $stmt->execute([$startDate, $endDate]);
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($logs);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
}
?>
