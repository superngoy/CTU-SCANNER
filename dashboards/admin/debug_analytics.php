<?php
header('Content-Type: application/json');
require_once '../../includes/functions.php';

try {
    $scanner = new CTUScanner();
    
    $debug = [];
    
    // Test 1: Get today's raw entries
    $stmt = $scanner->conn->prepare("SELECT * FROM entrylogs WHERE Date = CURDATE() ORDER BY Timestamp");
    $stmt->execute();
    $debug['today_raw_entries'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Test 2: Department query - with debug info
    $stmt = $scanner->conn->prepare("
        SELECT 
            e.PersonID,
            e.PersonType,
            s.Department as student_dept,
            f.Department as faculty_dept,
            COALESCE(s.Department, f.Department) as final_department
        FROM entrylogs e
        LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
        LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
        WHERE e.Date = CURDATE()
    ");
    $stmt->execute();
    $debug['department_debug'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Test 3: Total entries count
    $stmt = $scanner->conn->prepare("SELECT COUNT(*) as total FROM entrylogs WHERE Date = CURDATE()");
    $stmt->execute();
    $debug['total_count'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Test 4: Peak hours query
    $stmt = $scanner->conn->prepare("
        SELECT 
            HOUR(Timestamp) as hour,
            COUNT(*) as count
        FROM entrylogs
        WHERE Date = CURDATE()
        GROUP BY HOUR(Timestamp)
        ORDER BY hour
    ");
    $stmt->execute();
    $debug['peak_hours'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Test 5: User type distribution
    $stmt = $scanner->conn->prepare("
        SELECT 
            PersonType,
            COUNT(*) as count
        FROM entrylogs
        WHERE Date = CURDATE()
        GROUP BY PersonType
    ");
    $stmt->execute();
    $debug['user_types'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($debug, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}
?>
