<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');

try {
    require_once '../../includes/functions.php';
    
    $action = $_GET['action'] ?? '';
    
    // Test basic database connection first
    $database = new Database();
    
    // Try different method names since getConnection() doesn't exist
    if (method_exists($database, 'connect')) {
        $conn = $database->connect();
    } elseif (method_exists($database, 'getConnection')) {
        $conn = $database->getConnection();
    } elseif (method_exists($database, 'connection')) {
        $conn = $database->connection();
    } else {
        throw new Exception('Cannot find database connection method');
    }
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    switch ($action) {
        case 'stats':
            $today = date('Y-m-d');
            
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE Date = ?");
            $stmt->execute([$today]);
            $totalEntries = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM exitlogs WHERE Date = ?");
            $stmt->execute([$today]);
            $totalExits = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE Date = ? AND PersonType = 'student'");
            $stmt->execute([$today]);
            $studentEntries = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE Date = ? AND PersonType = 'faculty'");
            $stmt->execute([$today]);
            $facultyEntries = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            echo json_encode([
                'total_entries' => (int)$totalEntries,
                'total_exits' => (int)$totalExits,
                'student_entries' => (int)$studentEntries,
                'faculty_entries' => (int)$facultyEntries
            ]);
            break;
            
        case 'entries':
            $stmt = $conn->prepare("
                SELECT 
                    e.PersonID,
                    e.PersonType as PersonCategory,
                    e.Timestamp,
                    CASE 
                        WHEN e.PersonType = 'student' THEN s.StudentFName
                        ELSE f.FacultyFName
                    END as StudentFName,
                    CASE 
                        WHEN e.PersonType = 'student' THEN s.StudentLName
                        ELSE f.FacultyLName
                    END as StudentLName,
                    f.FacultyFName,
                    f.FacultyLName
                FROM entrylogs e
                LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                ORDER BY e.Timestamp DESC 
                LIMIT 20
            ");
            $stmt->execute();
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['entries' => $entries]);
            break;
            
        case 'exits':
            $stmt = $conn->prepare("
                SELECT 
                    e.PersonID,
                    e.PersonType as PersonCategory,
                    e.Timestamp,
                    CASE 
                        WHEN e.PersonType = 'student' THEN s.StudentFName
                        ELSE f.FacultyFName
                    END as StudentFName,
                    CASE 
                        WHEN e.PersonType = 'student' THEN s.StudentLName
                        ELSE f.FacultyLName
                    END as StudentLName,
                    f.FacultyFName,
                    f.FacultyLName
                FROM exitlogs e
                LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                ORDER BY e.Timestamp DESC 
                LIMIT 20
            ");
            $stmt->execute();
            $exits = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['exits' => $exits]);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action: ' . $action]);
            break;
    }
    
} catch (Exception $e) {
    error_log("Realtime data error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}
?>