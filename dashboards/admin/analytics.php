<?php
header('Content-Type: application/json');
require_once '../../includes/functions.php';

try {
    $scanner = new CTUScanner();
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    switch ($action) {
        case 'department':
            try {
                // Fixed query to properly aggregate department data
                $stmt = $scanner->conn->prepare("
                    SELECT 
                        COALESCE(s.Department, f.Department) as Department,
                        COUNT(*) as count
                    FROM entrylogs e
                    LEFT JOIN students s ON e.PersonID = s.StudentID AND e.PersonType = 'student'
                    LEFT JOIN faculty f ON e.PersonID = f.FacultyID AND e.PersonType = 'faculty'
                    WHERE e.Date = CURDATE()
                    GROUP BY COALESCE(s.Department, f.Department)
                ");
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $data = ['COTE' => 0, 'COED' => 0];
                foreach ($results as $row) {
                    if (isset($data[$row['Department']])) {
                        $data[$row['Department']] = (int)$row['count'];
                    }
                }
                
                echo json_encode($data);
            } catch (PDOException $e) {
                error_log("Department query error: " . $e->getMessage());
                echo json_encode(['COTE' => 0, 'COED' => 0]);
            }
            break;
            
        case 'weekly':
            try {
                $stmt = $scanner->conn->prepare("
                    SELECT DAYOFWEEK(Date) as day, COUNT(*) as count
                    FROM entrylogs
                    WHERE Date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                    GROUP BY DAYOFWEEK(Date)
                    ORDER BY day
                ");
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Convert to proper format
                $weeklyData = [];
                foreach ($results as $row) {
                    $weeklyData[] = [
                        'day' => (int)$row['day'],
                        'count' => (int)$row['count']
                    ];
                }
                
                echo json_encode($weeklyData);
            } catch (PDOException $e) {
                error_log("Weekly query error: " . $e->getMessage());
                echo json_encode([]);
            }
            break;
            
        case 'custom_report':
            $startDate = $_POST['start_date'] ?? '';
            $endDate = $_POST['end_date'] ?? '';
            
            if (empty($startDate) || empty($endDate)) {
                echo json_encode(['error' => 'Start date and end date are required']);
                break;
            }
            
            try {
                // Total entries
                $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE Date BETWEEN ? AND ?");
                $stmt->execute([$startDate, $endDate]);
                $totalEntries = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Total exits
                $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM exitlogs WHERE Date BETWEEN ? AND ?");
                $stmt->execute([$startDate, $endDate]);
                $totalExits = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Student entries
                $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE Date BETWEEN ? AND ? AND PersonType = 'student'");
                $stmt->execute([$startDate, $endDate]);
                $studentEntries = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Faculty entries
                $stmt = $scanner->conn->prepare("SELECT COUNT(*) as count FROM entrylogs WHERE Date BETWEEN ? AND ? AND PersonType = 'faculty'");
                $stmt->execute([$startDate, $endDate]);
                $facultyEntries = (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                echo json_encode([
                    'total_entries' => $totalEntries,
                    'total_exits' => $totalExits,
                    'student_entries' => $studentEntries,
                    'faculty_entries' => $facultyEntries
                ]);
            } catch (PDOException $e) {
                error_log("Custom report error: " . $e->getMessage());
                echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    error_log("Analytics error: " . $e->getMessage());
    echo json_encode(['error' => 'System error: ' . $e->getMessage()]);
}
?>