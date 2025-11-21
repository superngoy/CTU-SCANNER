<?php
session_start();

// Add authentication check for security
if (!isset($_SESSION['security_id']) || $_SESSION['user_type'] !== 'security') {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

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
            
            // Use DATE(NOW()) to get today's date from server timezone
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE DATE(Timestamp) = DATE(NOW()) OR Date = ?");
            $stmt->execute([$today]);
            $totalEntries = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM exitlogs WHERE DATE(Timestamp) = DATE(NOW()) OR Date = ?");
            $stmt->execute([$today]);
            $totalExits = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE (DATE(Timestamp) = DATE(NOW()) OR Date = ?) AND PersonType = 'student'");
            $stmt->execute([$today]);
            $studentEntries = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE (DATE(Timestamp) = DATE(NOW()) OR Date = ?) AND PersonType = 'faculty'");
            $stmt->execute([$today]);
            $facultyEntries = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE (DATE(Timestamp) = DATE(NOW()) OR Date = ?) AND PersonType = 'staff'");
            $stmt->execute([$today]);
            $staffEntries = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            echo json_encode([
                'total_entries' => (int)$totalEntries,
                'total_exits' => (int)$totalExits,
                'student_entries' => (int)$studentEntries,
                'faculty_entries' => (int)$facultyEntries,
                'staff_entries' => (int)$staffEntries
            ]);
            break;
            
        case 'entries':
            $stmt = $conn->prepare("
                SELECT 
                    e.PersonID,
                    e.PersonType as PersonCategory,
                    e.Timestamp,
                    s.StudentID,
                    s.StudentFName,
                    s.StudentLName,
                    s.image as student_image,
                    f.FacultyID,
                    f.FacultyFName,
                    f.FacultyLName,
                    f.image as faculty_image,
                    st.StaffID,
                    st.StaffFName,
                    st.StaffLName,
                    st.image as staff_image
                FROM entrylogs e
                LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff'
                ORDER BY e.Timestamp DESC 
                LIMIT 20
            ");
            $stmt->execute();
            $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Process entries with comprehensive debugging
            foreach ($entries as &$entry) {
                // Determine which name to use
                if ($entry['PersonCategory'] === 'student' && $entry['StudentID']) {
                    $entry['StudentFName'] = $entry['StudentFName'] ?? 'Unknown';
                    $entry['StudentLName'] = $entry['StudentLName'] ?? '';
                    $entry['image'] = $entry['student_image'];
                } elseif ($entry['PersonCategory'] === 'faculty' && $entry['FacultyID']) {
                    $entry['StudentFName'] = $entry['FacultyFName'] ?? 'Unknown';
                    $entry['StudentLName'] = $entry['FacultyLName'] ?? '';
                    $entry['image'] = $entry['faculty_image'];
                } elseif ($entry['PersonCategory'] === 'staff' && $entry['StaffID']) {
                    $entry['StudentFName'] = $entry['StaffFName'] ?? 'Unknown';
                    $entry['StudentLName'] = $entry['StaffLName'] ?? '';
                    $entry['image'] = $entry['staff_image'];
                } else {
                    $entry['StudentFName'] = 'Unknown';
                    $entry['StudentLName'] = '';
                    $entry['image'] = null;
                }
                
                // Add image path prefix if image exists
                if ($entry['image'] && strpos($entry['image'], 'http') !== 0 && strpos($entry['image'], '/') !== 0) {
                    $entry['image'] = '../../' . $entry['image'];
                }
                
                // Clean up temp fields
                unset($entry['StudentID'], $entry['FacultyID'], $entry['StaffID']);
                unset($entry['FacultyFName'], $entry['FacultyLName'], $entry['StaffFName'], $entry['StaffLName']);
                unset($entry['student_image'], $entry['faculty_image'], $entry['staff_image']);
            }
            
            echo json_encode(['entries' => $entries]);
            break;
            
        case 'exits':
            $stmt = $conn->prepare("
                SELECT 
                    e.PersonID,
                    e.PersonType as PersonCategory,
                    e.Timestamp,
                    s.StudentID,
                    s.StudentFName,
                    s.StudentLName,
                    s.image as student_image,
                    f.FacultyID,
                    f.FacultyFName,
                    f.FacultyLName,
                    f.image as faculty_image,
                    st.StaffID,
                    st.StaffFName,
                    st.StaffLName,
                    st.image as staff_image
                FROM exitlogs e
                LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                LEFT JOIN staff st ON e.PersonID = st.StaffID AND e.PersonType = 'staff'
                ORDER BY e.Timestamp DESC 
                LIMIT 20
            ");
            $stmt->execute();
            $exits = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Process exits
            foreach ($exits as &$exit) {
                // Determine which name to use
                if ($exit['PersonCategory'] === 'student' && $exit['StudentID']) {
                    $exit['StudentFName'] = $exit['StudentFName'] ?? 'Unknown';
                    $exit['StudentLName'] = $exit['StudentLName'] ?? '';
                    $exit['image'] = $exit['student_image'];
                } elseif ($exit['PersonCategory'] === 'faculty' && $exit['FacultyID']) {
                    $exit['StudentFName'] = $exit['FacultyFName'] ?? 'Unknown';
                    $exit['StudentLName'] = $exit['FacultyLName'] ?? '';
                    $exit['image'] = $exit['faculty_image'];
                } elseif ($exit['PersonCategory'] === 'staff' && $exit['StaffID']) {
                    $exit['StudentFName'] = $exit['StaffFName'] ?? 'Unknown';
                    $exit['StudentLName'] = $exit['StaffLName'] ?? '';
                    $exit['image'] = $exit['staff_image'];
                } else {
                    $exit['StudentFName'] = 'Unknown';
                    $exit['StudentLName'] = '';
                    $exit['image'] = null;
                }
                
                // Add image path prefix if image exists
                if ($exit['image'] && strpos($exit['image'], 'http') !== 0 && strpos($exit['image'], '/') !== 0) {
                    $exit['image'] = '../../' . $exit['image'];
                }
                
                // Clean up temp fields
                unset($exit['StudentID'], $exit['FacultyID'], $exit['StaffID']);
                unset($exit['FacultyFName'], $exit['FacultyLName'], $exit['StaffFName'], $exit['StaffLName']);
                unset($exit['student_image'], $exit['faculty_image'], $exit['staff_image']);
            }
            
            echo json_encode(['exits' => $exits]);
            break;

        case 'entry_logs_hourly':
            try {
                $dateRange = $_GET['dateRange'] ?? 'today';
                
                // Get today's date
                $today = date('Y-m-d');
                $startDate = $today;
                $endDate = $today;
                
                // Query to get hourly entry counts using Timestamp for timezone safety
                $query = "
                    SELECT 
                        HOUR(e.Timestamp) as hour,
                        COUNT(*) as count
                    FROM entrylogs e
                    WHERE DATE(e.Timestamp) = DATE(NOW()) OR (e.Date = ?)
                    GROUP BY HOUR(e.Timestamp)
                    ORDER BY HOUR(e.Timestamp)
                ";
                
                $stmt = $conn->prepare($query);
                $stmt->execute([$startDate]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Convert to array of objects
                $data = [];
                foreach ($results as $row) {
                    $data[] = [
                        'hour' => (int)$row['hour'],
                        'count' => (int)$row['count']
                    ];
                }
                
                echo json_encode($data);
            } catch (PDOException $e) {
                error_log("Entry logs hourly error: " . $e->getMessage());
                echo json_encode([]);
            }
            break;

        case 'exit_logs_hourly':
            try {
                $dateRange = $_GET['dateRange'] ?? 'today';
                
                // Get today's date
                $today = date('Y-m-d');
                $startDate = $today;
                $endDate = $today;
                
                // Query to get hourly exit counts using Timestamp for timezone safety
                $query = "
                    SELECT 
                        HOUR(ex.Timestamp) as hour,
                        COUNT(*) as count
                    FROM exitlogs ex
                    WHERE DATE(ex.Timestamp) = DATE(NOW()) OR (ex.Date = ?)
                    GROUP BY HOUR(ex.Timestamp)
                    ORDER BY HOUR(ex.Timestamp)
                ";
                
                $stmt = $conn->prepare($query);
                $stmt->execute([$startDate]);
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Convert to array of objects
                $data = [];
                foreach ($results as $row) {
                    $data[] = [
                        'hour' => (int)$row['hour'],
                        'count' => (int)$row['count']
                    ];
                }
                
                echo json_encode($data);
            } catch (PDOException $e) {
                error_log("Exit logs hourly error: " . $e->getMessage());
                echo json_encode([]);
            }
            break;

        case 'entry_exit_hourly':
            try {
                $dateRange = $_GET['dateRange'] ?? 'today';
                
                // Get today's date
                $today = date('Y-m-d');
                $startDate = $today;
                $endDate = $today;
                
                // Get entry data using Timestamp for timezone safety
                $query = "
                    SELECT 
                        HOUR(e.Timestamp) as hour,
                        'entry' as type,
                        COUNT(*) as count
                    FROM entrylogs e
                    WHERE DATE(e.Timestamp) = DATE(NOW()) OR (e.Date = ?)
                    GROUP BY HOUR(e.Timestamp)
                ";
                
                $stmt = $conn->prepare($query);
                $stmt->execute([$startDate]);
                $entryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Get exit data using Timestamp for timezone safety
                $query = "
                    SELECT 
                        HOUR(ex.Timestamp) as hour,
                        'exit' as type,
                        COUNT(*) as count
                    FROM exitlogs ex
                    WHERE DATE(ex.Timestamp) = DATE(NOW()) OR (ex.Date = ?)
                    GROUP BY HOUR(ex.Timestamp)
                ";
                
                $stmt = $conn->prepare($query);
                $stmt->execute([$startDate]);
                $exitResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Merge and format results
                $data = [];
                foreach ($entryResults as $row) {
                    $data[] = [
                        'hour' => (int)$row['hour'],
                        'type' => $row['type'],
                        'count' => (int)$row['count']
                    ];
                }
                foreach ($exitResults as $row) {
                    $data[] = [
                        'hour' => (int)$row['hour'],
                        'type' => $row['type'],
                        'count' => (int)$row['count']
                    ];
                }
                
                echo json_encode($data);
            } catch (PDOException $e) {
                error_log("Entry exit hourly error: " . $e->getMessage());
                echo json_encode([]);
            }
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